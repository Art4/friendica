<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Module\Debug;

use Friendica\BaseModule;
use Friendica\Core\Renderer;
use Friendica\DI;
use Friendica\Protocol\ActivityPub;
use Friendica\Util\JsonLD;

class ActivityPubConversion extends BaseModule
{
	protected function post(array $request = [])
	{
		// @todo check if POST is really used here
		$this->content($request);
	}

	protected function content(array $request = []): string
	{


		$results = [];
		if (!empty($_REQUEST['source'])) {
			try {
				$source = json_decode($_REQUEST['source'], true);
				$trust_source = true;
				$uid = DI::userSession()->getLocalUserId();
				$push = false;

				if (!$source) {
					throw new \Exception('Failed to decode source JSON');
				}

				$formatted = json_encode($source, JSON_PRETTY_PRINT);
				$results[] = [
					'title'   => DI::l10n()->t('Formatted'),
					'content' => $this->visible_whitespace(trim(var_export($formatted, true), "'")),
				];
				$results[] = [
					'title'   => DI::l10n()->t('Source'),
					'content' => $this->visible_whitespace(var_export($source, true))
				];
				$activity = JsonLD::compact($source);
				if (!$activity) {
					throw new \Exception('Failed to compact JSON');
				}
				$results[] = [
					'title'   => DI::l10n()->t('Activity'),
					'content' => $this->visible_whitespace(var_export($activity, true))
				];

				$type = JsonLD::fetchElement($activity, '@type');

				if (!$type) {
					throw new \Exception('Empty type');
				}

				if (!JsonLD::fetchElement($activity, 'as:object', '@id')) {
					throw new \Exception('Empty object');
				}

				if (!JsonLD::fetchElement($activity, 'as:actor', '@id')) {
					throw new \Exception('Empty actor');
				}

				// Don't trust the source if "actor" differs from "attributedTo". The content could be forged.
				if ($trust_source && ($type == 'as:Create') && is_array($activity['as:object'])) {
					$actor = JsonLD::fetchElement($activity, 'as:actor', '@id');
					$attributed_to = JsonLD::fetchElement($activity['as:object'], 'as:attributedTo', '@id');
					$trust_source = ($actor == $attributed_to);
					if (!$trust_source) {
						throw new \Exception('Not trusting actor: ' . $actor . '. It differs from attributedTo: ' . $attributed_to);
					}
				}

				// $trust_source is called by reference and is set to true if the content was retrieved successfully
				$object_data = ActivityPub\Receiver::prepareObjectData($activity, $uid, $push, $trust_source);
				if (empty($object_data)) {
					throw new \Exception('No object data found');
				}

				if (!$trust_source) {
					throw new \Exception('No trust for activity type "' . $type . '", so we quit now.');
				}

				if (!empty($body) && empty($object_data['raw'])) {
					$object_data['raw'] = $body;
				}

				// Internal flag for thread completion. See Processor.php
				if (!empty($activity['thread-completion'])) {
					$object_data['thread-completion'] = $activity['thread-completion'];
				}

				if (!empty($activity['completion-mode'])) {
					$object_data['completion-mode'] = $activity['completion-mode'];
				}

				$results[] = [
					'title'   => DI::l10n()->t('Object data'),
					'content' => $this->visible_whitespace(var_export($object_data, true))
				];

				$item = ActivityPub\Processor::createItem($object_data, true);

				$results[] = [
					'title'   => DI::l10n()->t('Result Item'),
					'content' => $this->visible_whitespace(var_export($item, true))
				];
			} catch (\Throwable $e) {
				$results[] = [
					'title'   => DI::l10n()->tt('Error', 'Errors', 1),
					'content' => $e->getMessage(),
				];
			}
		}

		$tpl = Renderer::getMarkupTemplate('debug/activitypubconversion.tpl');
		$o = Renderer::replaceMacros($tpl, [
			'$title'   => DI::l10n()->t('ActivityPub Conversion'),
			'$source'  => ['source', DI::l10n()->t('Source activity'), $_REQUEST['source'] ?? '', ''],
			'$results' => $results,
			'$submit' => DI::l10n()->t('Submit'),
		]);

		return $o;
	}

	private function visible_whitespace(string $s): string
	{
		return '<pre>' . htmlspecialchars($s) . '</pre>';
	}
}
