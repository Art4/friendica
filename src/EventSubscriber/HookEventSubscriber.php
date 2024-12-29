<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types = 1);

namespace Friendica\EventSubscriber;

use Friendica\Core\Hook;
use Friendica\Event\DataFilterEvent;

/**
 * Bridge between the EventDispatcher and the Hook class.
 */
final class HookEventSubscriber implements StaticEventSubscriber
{
	/**
	 * @return array<string, string>
	 */
	public static function getStaticSubscribedEvents(): array
	{
		return [
			DataFilterEvent::class => 'onDataFilterEvent',
		];
	}

	public static function onDataFilterEvent(DataFilterEvent $event): void
	{
		$data = $event->getData();

		Hook::callAll($event->getName(), $data);

		$event->setData($data);
	}
}
