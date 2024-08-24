<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Test\src\Util\Emailer;

use Friendica\App\BaseURL;
use Friendica\Core\Config\Capability\IManageConfigValues;
use Friendica\Core\L10n;
use Friendica\Test\MockedTest;
use Friendica\Test\Util\VFSTrait;
use Friendica\Util\EMailer\MailBuilder;
use Friendica\Util\EMailer\SystemMailBuilder;
use Psr\Log\NullLogger;

class SystemMailBuilderTest extends MockedTest
{
	use VFSTrait;

	/** @var IManageConfigValues */
	private $config;
	/** @var L10n */
	private $l10n;
	/** @var BaseURL */
	private $baseUrl;

	protected function setUp(): void
	{
		parent::setUp();

		$this->setUpVfsDir();

		$this->config  = \Mockery::mock(IManageConfigValues::class);
		$this->config->shouldReceive('get')->with('config', 'admin_name')->andReturn('Admin');
		$this->l10n    = \Mockery::mock(L10n::class);
		$this->l10n->shouldReceive('t')->andReturnUsing(function ($msg) {
			return $msg;
		});
		$this->baseUrl = \Mockery::mock(BaseURL::class);
		$this->baseUrl->shouldReceive('getHost')->andReturn('friendica.local');
		$this->baseUrl->shouldReceive('__toString')->andReturn('http://friendica.local');
	}

	/**
	 * Test if the builder instance can get created
	 */
	public function testBuilderInstance()
	{
		$builder = new SystemMailBuilder($this->l10n, $this->baseUrl, $this->config, new NullLogger(), 'moreply@friendica.local', 'FriendicaSite');

		self::assertInstanceOf(MailBuilder::class, $builder);
		self::assertInstanceOf(SystemMailBuilder::class, $builder);
	}
}
