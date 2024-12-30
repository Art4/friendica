<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Friendica\Test\Unit\EventSubscriber;

use Friendica\Event\DataFilterEvent;
use Friendica\EventSubscriber\HookEventBridge;
use Friendica\EventSubscriber\StaticEventSubscriber;
use PHPUnit\Framework\TestCase;

class HookEventBridgeTest extends TestCase
{
	public function testCorrectImplementation(): void
	{
		$this->assertTrue(
			is_subclass_of(HookEventBridge::class, StaticEventSubscriber::class, true),
			HookEventBridge::class . ' does not implement ' . StaticEventSubscriber::class
		);
	}

	public function testGetStaticSubscribedEventsReturnsStaticMethods(): void
	{
		$expected = [
			DataFilterEvent::class => 'onDataFilterEvent',
		];

		$this->assertSame(
			$expected,
			HookEventBridge::getStaticSubscribedEvents()
		);

		foreach ($expected as $methodName) {
			$this->assertTrue(method_exists(HookEventBridge::class, $methodName));

			$this->assertTrue(
				(new \ReflectionMethod(HookEventBridge::class, $methodName))->isStatic(),
				$methodName . ' is not static'
			);
		}
	}
}
