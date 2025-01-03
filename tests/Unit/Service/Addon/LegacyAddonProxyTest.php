<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Friendica\Test\Unit\Addon;

use Friendica\Service\Addon\Addon;
use Friendica\Service\Addon\LegacyAddonProxy;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LegacyAddonProxyTest extends TestCase
{
	public function testCreateWithNameAndPath(): void
	{
		$root = vfsStream::setup('addons', 0777, ['helloaddon' => []]);

		$addon = new LegacyAddonProxy('helloaddon', $root->url());

		$this->assertInstanceOf(Addon::class, $addon);
	}

	public function testGetRequiredDependenciesReturnsEmptyArray(): void
	{
		$root = vfsStream::setup('addons', 0777, ['helloaddon' => []]);

		$addon = new LegacyAddonProxy('helloaddon', $root->url());

		$this->assertSame(
			[],
			$addon->getRequiredDependencies()
		);
	}

	public function testGetProvidedDependencyRulesReturnsEmptyArray(): void
	{
		$root = vfsStream::setup('addons', 0777, ['helloaddon' => []]);

		$addon = new LegacyAddonProxy('helloaddon', $root->url());

		$this->assertSame(
			[],
			$addon->getProvidedDependencyRules()
		);
	}

	public function testGetSubscribedEventsReturnsEmptyArray(): void
	{
		$root = vfsStream::setup('addons', 0777, ['helloaddon' => []]);

		$addon = new LegacyAddonProxy('helloaddon', $root->url());

		$this->assertSame(
			[],
			$addon->getSubscribedEvents()
		);

	}

	public function testInitAddonIncludesAddonFile(): void
	{
		$root = vfsStream::setup('addons', 0777, ['helloaddon' => []]);

		vfsStream::newFile('helloaddon.php')
				->at($root->getChild('helloaddon'))
				->setContent('<?php throw new \Exception("Addon loaded");');

		$addon = new LegacyAddonProxy('helloaddon', $root->url());

		try {
			$addon->initAddon([]);
		} catch (\Throwable $th) {
			$this->assertSame(
				'Addon loaded',
				$th->getMessage()
			);
		}
	}

	public function testInitAddonMultipleTimesWillIncludeFileOnlyOnce(): void
	{
		$root = vfsStream::setup('addons', 0777, ['helloaddon' => []]);

		vfsStream::newFile('helloaddon.php')
				->at($root->getChild('helloaddon'))
				->setContent('<?php throw new \Exception("Addon loaded");');

		$addon = new LegacyAddonProxy('helloaddon', $root->url());

		try {
			$addon->initAddon([]);
		} catch (\Throwable $th) {
			$this->assertSame(
				'Addon loaded',
				$th->getMessage()
			);
		}

		$addon->initAddon([]);
		$addon->initAddon([]);
	}
}
