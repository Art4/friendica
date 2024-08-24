<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Worker;

use Friendica\Core\Hook;
use Friendica\Core\Logger;
use Friendica\Core\Worker;
use Friendica\Database\DBA;
use Friendica\DI;
use Friendica\Model\Item;

/**
 * Expires old item entries
 */
class Expire
{
	public static function execute($param = '', $hook_function = '')
	{
		$a = DI::app();

		Hook::loadHooks();

		if (intval($param) > 0) {
			$user = DBA::selectFirst('user', ['uid', 'username', 'expire'], ['uid' => $param]);
			if (DBA::isResult($user)) {
				Logger::info('Expire items', ['user' => $user['uid'], 'username' => $user['username'], 'interval' => $user['expire']]);
				Item::expire($user['uid'], $user['expire']);
				Logger::info('Expire items done', ['user' => $user['uid'], 'username' => $user['username'], 'interval' => $user['expire']]);
			}
			return;
		} elseif ($param == 'hook' && !empty($hook_function)) {
			foreach (Hook::getByName('expire') as $hook) {
				if ($hook[1] == $hook_function) {
					Logger::info('Calling expire hook', ['hook' => $hook[1]]);
					Hook::callSingle('expire', $hook, $data);
				}
			}
			return;
		}

		Logger::notice('start expiry');

		$r = DBA::select('user', ['uid', 'username'], ["`expire` != ?", 0]);
		while ($row = DBA::fetch($r)) {
			Logger::info('Calling expiry', ['user' => $row['uid'], 'username' => $row['username']]);
			Worker::add(['priority' => $a->getQueueValue('priority'), 'created' => $a->getQueueValue('created'), 'dont_fork' => true],
				'Expire', (int)$row['uid']);
		}
		DBA::close($r);

		Logger::notice('calling hooks');
		foreach (Hook::getByName('expire') as $hook) {
			Logger::info('Calling expire', ['hook' => $hook[1]]);
			Worker::add(['priority' => $a->getQueueValue('priority'), 'created' => $a->getQueueValue('created'), 'dont_fork' => true],
				'Expire', 'hook', $hook[1]);
		}

		Logger::notice('calling hooks done');

		return;
	}
}
