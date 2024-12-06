#!/usr/bin/env php
<?php
/**
 * Copyright (C) 2010-2024, the Friendica project
 * SPDX-FileCopyrightText: 2010-2024 the Friendica project
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 *
 */

namespace Friendica\Protocol\ATProtocol;

use Friendica\Core\Config\Capability\IManageConfigValues;
use Friendica\Core\KeyValueStorage\Capability\IManageKeyValuePairs;
use Friendica\Core\Protocol;
use Friendica\Core\System;
use Friendica\Model\Contact;
use Friendica\Model\Item;
use Friendica\Protocol\ATProtocol;
use Friendica\Util\DateTimeFormat;
use Psr\Log\LoggerInterface;
use stdClass;

/**
 * Class to handle the Bluesky Jetstream firehose
 *
 * Existing collections:
 * app.bsky.feed.like, app.bsky.graph.follow, app.bsky.feed.repost, app.bsky.feed.post, app.bsky.graph.block,
 * app.bsky.actor.profile, app.bsky.graph.listitem, app.bsky.graph.list, app.bsky.graph.listblock, app.bsky.feed.generator,
 * app.bsky.feed.threadgate, app.bsky.graph.starterpack, app.bsky.feed.postgate, chat.bsky.actor.declaration,
 * app.bsky.actor.domain, industries.geesawra.webpages
 *
 * Available servers:
 * jetstream1.us-east.bsky.network, jetstream2.us-east.bsky.network, jetstream1.us-west.bsky.network, jetstream2.us-west.bsky.network
 *
 * @see https://github.com/bluesky-social/jetstream
 * @todo Support more collections, support full firehose
 */
class Jetstream
{
	private $uids   = [];
	private $self   = [];
	private $capped = false;

	/** @var LoggerInterface */
	private $logger;

	/** @var \Friendica\Core\Config\Capability\IManageConfigValues */
	private $config;

	/** @var IManageKeyValuePairs */
	private $keyValue;

	/** @var ATProtocol */
	private $atprotocol;

	/** @var Actor */
	private $actor;

	/** @var Processor */
	private $processor;

	/** @var \WebSocket\Client */
	private $client;

	public function __construct(LoggerInterface $logger, IManageConfigValues $config, IManageKeyValuePairs $keyValue, ATProtocol $atprotocol, Actor $actor, Processor $processor)
	{
		$this->logger     = $logger;
		$this->config     = $config;
		$this->keyValue   = $keyValue;
		$this->atprotocol = $atprotocol;
		$this->actor      = $actor;
		$this->processor  = $processor;
	}

	// *****************************************
	// * Listener
	// *****************************************
	public function listen()
	{
		$timeout       = 300;
		$timeout_limit = 10;
		$timestamp     = $this->keyValue->get('jetstream_timestamp') ?? 0;
		$cursor        = '';
		while (true) {
			if ($timestamp) {
				$cursor = '&cursor=' . $timestamp;
				$this->logger->notice('Start with cursor', ['cursor' => $cursor]);
			}

			$this->syncContacts();
			try {
				// @todo make the path configurable
				$this->client = new \WebSocket\Client('wss://jetstream1.us-west.bsky.network/subscribe?requireHello=true' . $cursor);
				$this->client->setTimeout($timeout);
			} catch (\WebSocket\ConnectionException $e) {
				$this->logger->error('Error while trying to establish the connection', ['code' => $e->getCode(), 'message' => $e->getMessage()]);
				echo "Connection wasn't established.\n";
				exit(1);
			}
			$this->setOptions();
			$last_timeout = time();
			while (true) {
				try {
					$message = $this->client->receive();
					$data    = json_decode($message);
					if (is_object($data)) {
						$timestamp = $data->time_us;
						$this->route($data);
						$this->keyValue->set('jetstream_timestamp', $timestamp);
					} else {
						$this->logger->warning('Unexpected return value', ['data' => $data]);
						break;
					}
				} catch (\WebSocket\ConnectionException $e) {
					if ($e->getCode() == 1024) {
						$timeout_duration = time() - $last_timeout;
						if ($timeout_duration < $timeout_limit) {
							$this->logger->notice('Timeout - connection lost', ['duration' => $timeout_duration, 'timestamp' => $timestamp, 'code' => $e->getCode(), 'message' => $e->getMessage()]);
							break;
						}
						$this->logger->notice('Timeout', ['duration' => $timeout_duration, 'timestamp' => $timestamp, 'code' => $e->getCode(), 'message' => $e->getMessage()]);
					} else {
						$this->logger->error('Error', ['code' => $e->getCode(), 'message' => $e->getMessage()]);
						break;
					}
				}
				$last_timeout = time();
			}
			try {
				$this->client->close();
			} catch (\WebSocket\ConnectionException $e) {
				$this->logger->error('Error while trying to close the connection', ['code' => $e->getCode(), 'message' => $e->getMessage()]);
			}
		}
	}

	private function syncContacts()
	{
		$active_uids = $this->atprotocol->getUids();
		if (empty($active_uids)) {
			return;
		}

		foreach ($active_uids as $uid) {
			$this->actor->syncContacts($uid);
		}
	}

	private function setOptions()
	{
		$active_uids = $this->atprotocol->getUids();
		if (empty($active_uids)) {
			return;
		}

		$contacts = Contact::selectToArray(['uid', 'url'], ['uid' => $active_uids, 'network' => Protocol::BLUESKY, 'rel' => [Contact::FRIEND, Contact::SHARING]]);

		$self = [];
		foreach ($active_uids as $uid) {
			$did        = $this->atprotocol->getUserDid($uid);
			$contacts[] = ['uid' => $uid, 'url' => $did];
			$self[$did] = $uid;
		}
		$this->self = $self;

		$uids = [];
		foreach ($contacts as $contact) {
			$uids[$contact['url']][] = $contact['uid'];
		}
		$this->uids = $uids;

		$did_limit = $this->config->get('jetstream', 'did_limit');

		$dids = array_keys($uids);
		if (count($dids) > $did_limit) {
			$contacts = Contact::selectToArray(['url'], ['uid' => $active_uids, 'network' => Protocol::BLUESKY, 'rel' => [Contact::FRIEND, Contact::SHARING]], ['order' => ['last-item' => true]]);
			$dids     = $this->addDids($contacts, $uids, $did_limit, array_keys($self));
		}

		if (count($dids) < $did_limit) {
			$contacts = Contact::selectToArray(['url'], ['uid' => $active_uids, 'network' => Protocol::BLUESKY, 'rel' => Contact::FOLLOWER], ['order' => ['last-item' => true]]);
			$dids     = $this->addDids($contacts, $uids, $did_limit, $dids);
		}

		if (!$this->capped && count($dids) < $did_limit) {
			$contacts = Contact::selectToArray(['url'], ['uid' => 0, 'network' => Protocol::BLUESKY], ['order' => ['last-item' => true], 'limit' => $did_limit]);
			$dids     = $this->addDids($contacts, $uids, $did_limit, $dids);
		}

		$this->logger->debug('Selected DIDs', ['uids' => $active_uids, 'count' => count($dids), 'capped' => $this->capped]);
		$update = [
			'type'    => 'options_update',
			'payload' => [
				'wantedCollections'   => ['app.bsky.feed.post', 'app.bsky.feed.repost', 'app.bsky.feed.like', 'app.bsky.graph.block', 'app.bsky.actor.profile', 'app.bsky.graph.follow'],
				'wantedDids'          => $dids,
				'maxMessageSizeBytes' => 1000000
			]
		];
		try {
			$this->client->send(json_encode($update));
		} catch (\WebSocket\ConnectionException $e) {
			$this->logger->error('Error while trying to send options.', ['code' => $e->getCode(), 'message' => $e->getMessage()]);
		}
	}

	private function addDids(array $contacts, array $uids, int $did_limit, array $dids): array
	{
		foreach ($contacts as $contact) {
			if (in_array($contact['url'], $uids)) {
				continue;
			}
			$dids[] = $contact['url'];
			if (count($dids) >= $did_limit) {
				break;
			}
		}
		return $dids;
	}

	private function route(stdClass $data)
	{
		Item::incrementInbound(Protocol::BLUESKY);

		switch ($data->kind) {
			case 'account':
				if (!empty($data->identity->did)) {
					$this->processor->processAccount($data);
				}
				break;

			case 'identity':
				$this->processor->processIdentity($data);
				break;

			case 'commit':
				$this->routeCommits($data);
				break;
		}
	}

	private function routeCommits(stdClass $data)
	{
		$drift = max(0, round(time() - $data->time_us / 1000000));
		if ($drift > 60 && !$this->capped) {
			$this->capped = true;
			$this->setOptions();
			$this->logger->notice('Drift is too high, dids will be capped');
		} elseif ($drift == 0 && $this->capped) {
			$this->capped = false;
			$this->setOptions();
			$this->logger->notice('Drift is low enough, dids will be uncapped');
		}

		$this->logger->notice('Received commit', ['time' => date(DateTimeFormat::ATOM, $data->time_us / 1000000), 'drift' => $drift, 'capped' => $this->capped, 'did' => $data->did, 'operation' => $data->commit->operation, 'collection' => $data->commit->collection, 'timestamp' => $data->time_us]);
		$timestamp = microtime(true);

		switch ($data->commit->collection) {
			case 'app.bsky.feed.post':
				$this->routePost($data, $drift);
				break;

			case 'app.bsky.feed.repost':
				$this->routeRepost($data, $drift);
				break;

			case 'app.bsky.feed.like':
				$this->routeLike($data);
				break;

			case 'app.bsky.graph.block':
				$this->processor->performBlocks($data, $this->self[$data->did] ?? 0);
				break;

			case 'app.bsky.actor.profile':
				$this->routeProfile($data);
				break;

			case 'app.bsky.graph.follow':
				$this->routeFollow($data);
				break;

			case 'app.bsky.feed.generator':
			case 'app.bsky.feed.postgate':
			case 'app.bsky.feed.threadgate':
			case 'app.bsky.graph.list':
			case 'app.bsky.graph.listblock':
			case 'app.bsky.graph.listitem':
			case 'app.bsky.graph.starterpack':
				// Ignore these collections, since we can't really process them
				break;

			default:
				$this->storeCommitMessage($data);
				break;
		}
		if (microtime(true) - $timestamp > 2) {
			$this->logger->notice('Commit processed', ['duration' => round(microtime(true) - $timestamp, 3), 'time' => date(DateTimeFormat::ATOM, $data->time_us / 1000000), 'did' => $data->did, 'operation' => $data->commit->operation, 'collection' => $data->commit->collection]);
		}
	}

	private function routePost(stdClass $data, int $drift)
	{
		switch ($data->commit->operation) {
			case 'delete':
				$this->processor->deleteRecord($data);
				break;

			case 'create':
				$this->processor->createPost($data, $this->uids[$data->did] ?? [0], ($drift > 30));
				break;

			default:
				$this->storeCommitMessage($data);
				break;
		}
	}

	private function routeRepost(stdClass $data, int $drift)
	{
		switch ($data->commit->operation) {
			case 'delete':
				$this->processor->deleteRecord($data);
				break;

			case 'create':
				$this->processor->createRepost($data, $this->uids[$data->did] ?? [0], ($drift > 30));
				break;

			default:
				$this->storeCommitMessage($data);
				break;
		}
	}

	private function routeLike(stdClass $data)
	{
		switch ($data->commit->operation) {
			case 'delete':
				$this->processor->deleteRecord($data);
				break;

			case 'create':
				$this->processor->createLike($data);
				break;

			default:
				$this->storeCommitMessage($data);
				break;
		}
	}

	private function routeProfile(stdClass $data)
	{
		switch ($data->commit->operation) {
			case 'delete':
				$this->storeCommitMessage($data);
				break;

			case 'create':
				$this->actor->updateContactByDID($data->did);
				break;

			case 'update':
				$this->actor->updateContactByDID($data->did);
				break;

			default:
				$this->storeCommitMessage($data);
				break;
		}
	}

	private function routeFollow(stdClass $data)
	{
		switch ($data->commit->operation) {
			case 'delete':
				if ($this->processor->deleteFollow($data, $this->self)) {
					$this->syncContacts();
					$this->setOptions();
				}
				break;

			case 'create':
				if ($this->processor->createFollow($data, $this->self)) {
					$this->syncContacts();
					$this->setOptions();
				}
				break;

			default:
				$this->storeCommitMessage($data);
				break;
		}
	}

	private function storeCommitMessage(stdClass $data)
	{
		if ($this->config->get('debug', 'jetstream_log')) {
			$tempfile = tempnam(System::getTempPath(), 'at-proto.commit.' . $data->commit->collection . '.' . $data->commit->operation . '-');
			file_put_contents($tempfile, json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		}
	}
}
