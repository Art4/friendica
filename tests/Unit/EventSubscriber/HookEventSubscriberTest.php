<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Friendica\Test\Unit\EventSubscriber;

use Friendica\Event\DataFilterEvent;
use Friendica\EventSubscriber\HookEventSubscriber;
use Friendica\EventSubscriber\StaticEventSubscriber;
use PHPUnit\Framework\TestCase;

class HookEventSubscriberTest extends TestCase
{
	public function testCorrectImplementation(): void
	{
		$this->assertTrue(
			is_subclass_of(HookEventSubscriber::class, StaticEventSubscriber::class, true),
			HookEventSubscriber::class . ' does not implement ' . StaticEventSubscriber::class
		);
	}

	public function testGetStaticSubscribedEventsReturnsStaticMethods(): void
	{
		$expected = [
			DataFilterEvent::class => 'onDataFilterEvent',
		];

		$this->assertSame(
			$expected,
			HookEventSubscriber::getStaticSubscribedEvents()
		);

		foreach ($expected as $methodName) {
			$this->assertTrue(method_exists(HookEventSubscriber::class, $methodName));

			$this->assertTrue(
				(new \ReflectionMethod(HookEventSubscriber::class, $methodName))->isStatic(),
				$methodName . ' is not static'
			);
		}
	}
}
