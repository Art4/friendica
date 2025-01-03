<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Friendica\Test\Unit\Addon;

use Friendica\Addon\AddonBootstrap;
use Friendica\Addon\Event\AddonStartEvent;
use Friendica\Service\Addon\Addon;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AddonTest extends TestCase
{
	public function testCreateWithAddonBootstrap(): void
	{
		$bootstrap = $this->createMock(AddonBootstrap::class);

		$addon = new Addon($bootstrap);

		$this->assertInstanceOf(Addon::class, $addon);
	}

	public function testInitAddonCallsBootstrap(): void
	{
		$bootstrap = $this->createMock(AddonBootstrap::class);
		$bootstrap->expects($this->once())->method('initAddon')->willReturnCallback(function($event) {
			$this->assertInstanceOf(AddonStartEvent::class, $event);
		});

		$addon = new Addon($bootstrap);

		$addon->initAddon([]);
	}

	public function testInitAddonCallsBootstrapWithDependencies(): void
	{
		$bootstrap = $this->createMock(AddonBootstrap::class);

		$bootstrap->expects($this->once())->method('initAddon')->willReturnCallback(function(AddonStartEvent $event) {
			$dependencies = $event->getDependencies();

			$this->assertArrayHasKey(LoggerInterface::class, $dependencies);
			$this->assertInstanceOf(LoggerInterface::class, $dependencies[LoggerInterface::class]);
		});

		$addon = new Addon($bootstrap);

		$addon->initAddon(
			[LoggerInterface::class => $this->createMock(LoggerInterface::class)]
		);
	}
}
