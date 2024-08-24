<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Model;

use Friendica\Core\Addon;
use Friendica\Core\Config\Capability\IManageConfigValues;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Item;
use stdClass;

/**
 * Model interaction for the nodeinfo
 */
class Nodeinfo
{
	/**
	 * Updates the info about the current node
	 *
	 * @throws \Friendica\Network\HTTPException\InternalServerErrorException
	 */
	public static function update()
	{
		$config = DI::config();
		$logger = DI::logger();

		// If the addon 'statistics_json' is enabled then disable it and activate nodeinfo.
		if (Addon::isEnabled('statistics_json')) {
			$config->set('system', 'nodeinfo', true);
			Addon::uninstall('statistics_json');
		}

		if (empty($config->get('system', 'nodeinfo'))) {
			return;
		}

		$logger->info('User statistics - start');

		$userStats = User::getStatistics();

		DI::keyValue()->set('nodeinfo_total_users', $userStats['total_users']);
		DI::keyValue()->set('nodeinfo_active_users_halfyear', $userStats['active_users_halfyear']);
		DI::keyValue()->set('nodeinfo_active_users_monthly', $userStats['active_users_monthly']);
		DI::keyValue()->set('nodeinfo_active_users_weekly', $userStats['active_users_weekly']);

		$logger->info('user statistics - done', $userStats);

		$posts = DBA::count('post-thread', ["`uri-id` IN (SELECT `uri-id` FROM `post-user` WHERE NOT `deleted` AND `origin`)"]);
		$comments = DBA::count('post', ["NOT `deleted` AND `gravity` = ? AND `uri-id` IN (SELECT `uri-id` FROM `post-user` WHERE `origin`)", Item::GRAVITY_COMMENT]);
		DI::keyValue()->set('nodeinfo_local_posts', $posts);
		DI::keyValue()->set('nodeinfo_local_comments', $comments);

		$posts = DBA::count('post', ['deleted' => false, 'gravity' => Item::GRAVITY_COMMENT]);
		$comments = DBA::count('post', ['deleted' => false, 'gravity' => Item::GRAVITY_COMMENT]);
		DI::keyValue()->set('nodeinfo_total_posts', $posts);
		DI::keyValue()->set('nodeinfo_total_comments', $comments);

		$logger->info('Post statistics - done', ['posts' => $posts, 'comments' => $comments]);
	}

	/**
	 * Return the supported services
	 *
	 * @return Object with supported services
	 */
	public static function getUsage(bool $version2 = false)
	{
		$config = DI::config();

		$usage = new stdClass();
		$usage->users = new \stdClass;

		if (!empty($config->get('system', 'nodeinfo'))) {
			$usage->users->total = intval(DI::keyValue()->get('nodeinfo_total_users'));
			$usage->users->activeHalfyear = intval(DI::keyValue()->get('nodeinfo_active_users_halfyear'));
			$usage->users->activeMonth = intval(DI::keyValue()->get('nodeinfo_active_users_monthly'));
			$usage->localPosts = intval(DI::keyValue()->get('nodeinfo_local_posts'));
			$usage->localComments = intval(DI::keyValue()->get('nodeinfo_local_comments'));

			if ($version2) {
				$usage->users->activeWeek = intval(DI::keyValue()->get('nodeinfo_active_users_weekly'));
			}
		}

		return $usage;
	}

	/**
	 * Return the supported services
	 *
	 * @return array with supported services
	 */
	public static function getServices(): array
	{
		$services = [
			'inbound'  => [],
			'outbound' => [],
		];

		if (Addon::isEnabled('bluesky')) {
			$services['inbound'][] = 'bluesky';
			$services['outbound'][] = 'bluesky';
		}
		if (Addon::isEnabled('dwpost')) {
			$services['outbound'][] = 'dreamwidth';
		}
		if (Addon::isEnabled('statusnet')) {
			$services['inbound'][] = 'gnusocial';
			$services['outbound'][] = 'gnusocial';
		}
		if (Addon::isEnabled('ijpost')) {
			$services['outbound'][] = 'insanejournal';
		}
		if (Addon::isEnabled('libertree')) {
			$services['outbound'][] = 'libertree';
		}
		if (Addon::isEnabled('ljpost')) {
			$services['outbound'][] = 'livejournal';
		}
		if (Addon::isEnabled('pumpio')) {
			$services['inbound'][] = 'pumpio';
			$services['outbound'][] = 'pumpio';
		}

		$services['outbound'][] = 'smtp';

		if (Addon::isEnabled('tumblr')) {
			$services['outbound'][] = 'tumblr';
		}
		if (Addon::isEnabled('twitter')) {
			$services['outbound'][] = 'twitter';
		}
		if (Addon::isEnabled('wppost')) {
			$services['outbound'][] = 'wordpress';
		}

		return $services;
	}

	/**
	 * Gathers organization information and returns it as an array
	 *
	 * @param IManageConfigValues $config Configuration instance
	 * @return array Organization information
	 * @throws \Exception
	 */
	public static function getOrganization(IManageConfigValues $config): array
	{
		$administrator = User::getFirstAdmin(['username', 'email', 'nickname']);

		return [
			'name'    => $administrator['username'] ?? null,
			'contact' => $administrator['email']    ?? null,
			'account' => $administrator['nickname'] ?? '' ? DI::baseUrl() . '/profile/' . $administrator['nickname'] : null,
		];
	}
}
