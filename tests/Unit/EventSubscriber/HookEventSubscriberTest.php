<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types = 1);

namespace Friendica\Test\Unit\EventSubscriber;

use Friendica\Event\DataFilterEvent;
use Friendica\EventSubscriber\HookEventSubscriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HookEventSubscriberTest extends TestCase
{
	public function testCorrectImplementation(): void
	{
		$this->assertTrue(
			is_subclass_of(HookEventSubscriber::class, EventSubscriberInterface::class, true),
			HookEventSubscriber::class . ' does not implement ' . EventSubscriberInterface::class
		);
	}

	public function testGetSubscribedEvents(): void
	{
		$this->assertSame(
			[
				DataFilterEvent::class => 'onDataFilterEvent',
			],
			HookEventSubscriber::getSubscribedEvents()
		);
	}
}
