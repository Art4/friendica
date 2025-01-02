<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Friendica\Test\Unit\Addon;

use Friendica\Addon\AddonManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AddonManagerTest extends TestCase
{
	public function testLoadAddon(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())->method('info')->with('Addon "helloaddon" loaded.');

		$manager = new AddonManager(
			dirname(__DIR__, 2) . '/Util',
			$logger
		);

		$manager->bootstrapAddons(['helloaddon' => []]);
	}
}
