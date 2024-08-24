<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Module\Api\Friendica;

use Friendica\App;
use Friendica\Core\L10n;
use Friendica\Factory\Api\Friendica\Photo as FriendicaPhoto;
use Friendica\Module\BaseApi;
use Friendica\Model\Contact;
use Friendica\Module\Api\ApiResponse;
use Friendica\Network\HTTPException;
use Friendica\Util\Profiler;
use Psr\Log\LoggerInterface;

class Photo extends BaseApi
{
	/** @var FriendicaPhoto */
	private $friendicaPhoto;


	public function __construct(FriendicaPhoto $friendicaPhoto, \Friendica\Factory\Api\Mastodon\Error $errorFactory, App $app, L10n $l10n, App\BaseURL $baseUrl, App\Arguments $args, LoggerInterface $logger, Profiler $profiler, ApiResponse $response, array $server, array $parameters = [])
	{
		parent::__construct($errorFactory, $app, $l10n, $baseUrl, $args, $logger, $profiler, $response, $server, $parameters);

		$this->friendicaPhoto = $friendicaPhoto;
	}

	protected function rawContent(array $request = [])
	{
		$this->checkAllowedScope(BaseApi::SCOPE_READ);
		$uid  = BaseApi::getCurrentUserID();
		$type = $this->getRequestValue($this->parameters, 'extension', 'json');

		if (empty($request['photo_id'])) {
			throw new HTTPException\BadRequestException('No photo id.');
		}

		$scale    = (!empty($request['scale']) ? intval($request['scale']) : null);
		$photo_id = $request['photo_id'];

		// prepare json/xml output with data from database for the requested photo
		$data = ['photo' => $this->friendicaPhoto->createFromId($photo_id, $scale, $uid, $type)];

		$this->response->addFormattedContent('statuses', $data, $this->parameters['extension'] ?? null, Contact::getPublicIdByUserId($uid));
	}
}
