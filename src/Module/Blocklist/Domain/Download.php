<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Module\Blocklist\Domain;

use Friendica\App;
use Friendica\Core\L10n;
use Friendica\Core\System;
use Friendica\Moderation\DomainPatternBlocklist;
use Friendica\Module\Response;
use Friendica\Util\Profiler;
use Psr\Log\LoggerInterface;

class Download extends \Friendica\BaseModule
{
	/** @var DomainPatternBlocklist */
	private $blocklist;

	public function __construct(DomainPatternBlocklist $blocklist, L10n $l10n, App\BaseURL $baseUrl, App\Arguments $args, LoggerInterface $logger, Profiler $profiler, Response $response, array $server, array $parameters = [])
	{
		parent::__construct($l10n, $baseUrl, $args, $logger, $profiler, $response, $server, $parameters);

		$this->blocklist = $blocklist;
	}

	/**
	 * @param array $request
	 *
	 * @return void
	 * @throws \Exception
	 */
	protected function rawContent(array $request = [])
	{
		$hash = md5(json_encode($this->blocklist->get(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

		$etag = 'W/"' . $hash . '"';
		if (trim($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') == $etag) {
			header('HTTP/1.1 304 Not Modified');
			System::exit();
		}

		header('Content-Type: text/csv');
		header('Content-Transfer-Encoding: Binary');
		header('Content-disposition: attachment; filename="' . $this->baseUrl->getHost() . '_domain_blocklist_' . substr($hash, 0, 6) . '.csv"');
		header("Etag: $etag");

		$this->blocklist->exportToFile('php://output');

		System::exit();
	}
}
