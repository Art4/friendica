<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Friendica\Test\Unit\Service\Addon;

use Friendica\Event\HtmlFilterEvent;
use Friendica\Service\Addon\Addon;
use Friendica\Service\Addon\AddonLoader;
use Friendica\Service\Addon\AddonManager;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AddonManagerTest extends TestCase
{
	public function testBootstrapAddonsLoadsTheAddon(): void
	{
		$loader = $this->createMock(AddonLoader::class);
		$loader->expects($this->once())->method('getAddons')->willReturn(['helloaddon' => $this->createMock(Addon::class)]);

		$manager = new AddonManager($loader);

		$manager->bootstrapAddons(['helloaddon' => []]);
	}

	public function testGetAllSubscribedEventsReturnsEvents(): void
	{
		$addon = $this->createMock(Addon::class);
		$addon->expects($this->once())->method('getSubscribedEvents')->willReturn([[HtmlFilterEvent::PAGE_END, [Addon::class, 'onPageEnd']]]);

		$loader = $this->createMock(AddonLoader::class);
		$loader->expects($this->once())->method('getAddons')->willReturn(['helloaddon' => $addon]);

		$manager = new AddonManager($loader);

		$manager->bootstrapAddons(['helloaddon' => []]);

		$this->assertSame(
			[[HtmlFilterEvent::PAGE_END, [Addon::class, 'onPageEnd']]],
			$manager->getAllSubscribedEvents()
		);
	}
}
