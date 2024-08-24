<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Module\Api\Mastodon;

use Friendica\App;
use Friendica\Core\L10n;
use Friendica\Core\System;
use Friendica\Model\Contact;
use Friendica\Module\Api\ApiResponse;
use Friendica\Module\BaseApi;
use Friendica\Network\HTTPException;
use Friendica\Util\Profiler;
use Psr\Log\LoggerInterface;

/**
 * @see https://docs.joinmastodon.org/methods/accounts/reports/
 */
class Reports extends BaseApi
{
	/** @var \Friendica\Moderation\Factory\Report */
	private $reportFactory;
	/** @var \Friendica\Moderation\Repository\Report */
	private $reportRepo;

	public function __construct(\Friendica\Moderation\Repository\Report $reportRepo, \Friendica\Moderation\Factory\Report $reportFactory, \Friendica\Factory\Api\Mastodon\Error $errorFactory, App $app, L10n $l10n, App\BaseURL $baseUrl, App\Arguments $args, LoggerInterface $logger, Profiler $profiler, ApiResponse $response, array $server, array $parameters = [])
	{
		parent::__construct($errorFactory, $app, $l10n, $baseUrl, $args, $logger, $profiler, $response, $server, $parameters);

		$this->reportFactory = $reportFactory;
		$this->reportRepo    = $reportRepo;
	}

	public function post(array $request = [])
	{
		$this->checkAllowedScope(self::SCOPE_WRITE);

		$request = $this->getRequest([
			'account_id' => '',      // ID of the account to report
			'status_ids' => [],      // Array of Statuses to attach to the report, for context
			'comment'    => '',      // Reason for the report (default max 1000 characters)
			'category'   => 'other', // Specify if the report is due to spam, violation of enumerated instance rules, or some other reason.
			'rule_ids'   => [],      // For violation category reports, specify the ID of the exact rules broken.
			'forward'    => false,   // If the account is remote, should the report be forwarded to the remote admin?
		], $request);

		$contact = Contact::getById($request['account_id'], ['id', 'gsid']);
		if (empty($contact)) {
			throw new HTTPException\NotFoundException('Account ' . $request['account_id'] . ' not found');
		}

		$report = $this->reportFactory->createFromReportsRequest(
			System::getRules(),
			Contact::getPublicIdByUserId(self::getCurrentUserID()),
			$contact['id'],
			$contact['gsid'],
			$request['comment'],
			$request['category'],
			$request['forward'],
			$request['status_ids'],
			$request['rule_ids'],
			self::getCurrentUserID()
		);

		$this->reportRepo->save($report);

		$this->jsonExit([]);
	}
}
