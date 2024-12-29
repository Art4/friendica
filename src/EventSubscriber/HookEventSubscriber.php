<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types = 1);

namespace Friendica\EventSubscriber;

use Friendica\Event\DataFilterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Bridge between the EventDispatcher and the Hook class.
 */
final class HookEventSubscriber implements EventSubscriberInterface
{
	/**
	 * @return array<string, string>
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			DataFilterEvent::class => 'onDataFilterEvent',
		];
	}

	public function onDataFilterEvent(DataFilterEvent $event): void
	{
	}
}
