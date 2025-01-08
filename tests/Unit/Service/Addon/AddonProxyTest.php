<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Friendica\Test\Unit\Addon;

use Friendica\Addon\AddonBootstrap;
use Friendica\Addon\DependencyProvider;
use Friendica\Addon\Event\AddonStartEvent;
use Friendica\Addon\InstallableAddon;
use Friendica\Service\Addon\Addon;
use Friendica\Service\Addon\AddonProxy;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Helper interface to combine AddonBootstrap and DependencyProvider.
 */
interface CombinedAddonBootstrapDependencyProvider extends AddonBootstrap, DependencyProvider
{
}

/**
 * Helper interface to combine AddonBootstrap and InstallableAddon.
 */
interface CombinedAddonBootstrapInstallableAddon extends AddonBootstrap, InstallableAddon
{
}

class AddonProxyTest extends TestCase
{
	public function testCreateWithAddonBootstrap(): void
	{
		$bootstrap = $this->createStub(AddonBootstrap::class);

		$addon = new AddonProxy('id', $bootstrap);

		$this->assertInstanceOf(Addon::class, $addon);
	}

	public function testGetIdReturnsId(): void
	{
		$bootstrap = $this->createStub(AddonBootstrap::class);

		$addon = new AddonProxy('id', $bootstrap);

		$this->assertSame('id', $addon->getId());
	}

	public function testGetRequiredDependenciesCallsBootstrap(): void
	{
		$bootstrap = $this->createMock(AddonBootstrap::class);
		$bootstrap->expects($this->once())->method('getRequiredDependencies')->willReturn([]);

		$addon = new AddonProxy('id', $bootstrap);

		$addon->getRequiredDependencies();
	}

	public function testGetProvidedDependencyRulesCallsBootstrap(): void
	{
		$bootstrap = $this->createMock(CombinedAddonBootstrapDependencyProvider::class);
		$bootstrap->expects($this->once())->method('provideDependencyRules')->willReturn([]);

		$addon = new AddonProxy('id', $bootstrap);

		$addon->getProvidedDependencyRules();
	}

	public function testGetSubscribedEventsCallsBootstrap(): void
	{
		$bootstrap = $this->createMock(AddonBootstrap::class);
		$bootstrap->expects($this->once())->method('getSubscribedEvents')->willReturn(['foo' => 'bar']);

		$addon = new AddonProxy('id', $bootstrap);

		$this->assertSame(
			[
				['foo', [$bootstrap, 'bar']],
			],
			$addon->getSubscribedEvents()
		);

	}

	public function testInitAddonCallsBootstrap(): void
	{
		$bootstrap = $this->createMock(AddonBootstrap::class);
		$bootstrap->expects($this->once())->method('initAddon')->willReturnCallback(function ($event) {
			$this->assertInstanceOf(AddonStartEvent::class, $event);
		});

		$addon = new AddonProxy('id', $bootstrap);

		$addon->initAddon([]);
	}

	public function testInitAddonMultipleTimesWillCallBootstrapOnce(): void
	{
		$bootstrap = $this->createMock(AddonBootstrap::class);
		$bootstrap->expects($this->once())->method('initAddon')->willReturnCallback(function ($event) {
			$this->assertInstanceOf(AddonStartEvent::class, $event);
		});

		$addon = new AddonProxy('id', $bootstrap);

		$addon->initAddon([]);
		$addon->initAddon([]);
	}

	public function testInitAddonCallsBootstrapWithDependencies(): void
	{
		$bootstrap = $this->createMock(AddonBootstrap::class);

		$bootstrap->expects($this->once())->method('initAddon')->willReturnCallback(function (AddonStartEvent $event) {
			$dependencies = $event->getDependencies();

			$this->assertArrayHasKey(LoggerInterface::class, $dependencies);
			$this->assertInstanceOf(LoggerInterface::class, $dependencies[LoggerInterface::class]);
		});

		$addon = new AddonProxy('id', $bootstrap);

		$addon->initAddon(
			[LoggerInterface::class => $this->createStub(LoggerInterface::class)]
		);
	}

	public function testInstallAddonCallsBootstrap(): void
	{
		$bootstrap = $this->createMock(CombinedAddonBootstrapInstallableAddon::class);
		$bootstrap->expects($this->once())->method('install');

		$addon = new AddonProxy('id', $bootstrap);

		$addon->initAddon([]);
		$addon->installAddon();
	}

	public function testUninstallAddonCallsBootstrap(): void
	{
		$bootstrap = $this->createMock(CombinedAddonBootstrapInstallableAddon::class);
		$bootstrap->expects($this->once())->method('uninstall');

		$addon = new AddonProxy('id', $bootstrap);

		$addon->initAddon([]);
		$addon->uninstallAddon();
	}
}
