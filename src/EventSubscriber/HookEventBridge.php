<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Friendica\EventSubscriber;

use Friendica\Core\Hook;
use Friendica\Event\DataFilterEvent;
use Friendica\Event\HtmlFilterEvent;

/**
 * Bridge between the EventDispatcher and the Hook class.
 */
final class HookEventBridge implements StaticEventSubscriber
{
	/**
	 * @return array<string, string>
	 */
	public static function getStaticSubscribedEvents(): array
	{
		return [
			DataFilterEvent::class => 'onDataFilterEvent',
			HtmlFilterEvent::HEAD => 'onHtmlFilterEvent',
			HtmlFilterEvent::FOOTER => 'onHtmlFilterEvent',
			HtmlFilterEvent::PAGE_CONTENT_TOP => 'onHtmlFilterEvent',
			HtmlFilterEvent::PAGE_END => 'onHtmlFilterEvent',
		];
	}

	public static function onDataFilterEvent(DataFilterEvent $event): void
	{
		$data = $event->getData();

		Hook::callAll($event->getName(), $data);

		$event->setData($data);
	}

	public static function onHtmlFilterEvent(HtmlFilterEvent $event): void
	{
		$html = $event->getHtml();

		// Hook::callAll($event->getName(), $html);

		$event->setHtml($html);
	}
}
