<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Module\Api\Friendica\Events;

use Friendica\Database\DBA;
use Friendica\Model\Event;
use Friendica\Module\BaseApi;
use Friendica\Network\HTTPException;

/**
 * API endpoint: /api/friendica/event_delete
 */


class Delete extends BaseApi
{
	protected function post(array $request = [])
	{
		$this->checkAllowedScope(self::SCOPE_WRITE);
		$uid = self::getCurrentUserID();

		$request = $this->getRequest([
			'id' => 0
		], $request);

		// params

		// error if no id specified
		if ($request['id'] == 0) {
			throw new HTTPException\BadRequestException('id not specified');
		}

		// error message if specified id is not in database
		if (!DBA::exists('event', ['uid' => $uid, 'id' => $request['id']])) {
			throw new HTTPException\BadRequestException('id not available');
		}

		// delete event
		$eventid = $request['id'];
		Event::delete($eventid);

		$success = ['id' => $eventid, 'status' => 'deleted'];
		$this->response->addFormattedContent('event_delete', ['$result' => $success], $this->parameters['extension'] ?? null);
	}
}
