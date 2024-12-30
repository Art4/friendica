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
	 * This allows us to mock the Hook call in tests.
	 *
	 * @var \Closure|null
	 */
	private static $callHook = null;

	/**
	 * This maps the event names to the legacy Hook names.
	 */
	private static array $eventMapper = [
		HtmlFilterEvent::HEAD => 'head',
		HtmlFilterEvent::FOOTER => 'footer',
		HtmlFilterEvent::PAGE_CONTENT_TOP => 'page_content_top',
		HtmlFilterEvent::PAGE_END => 'page_end',
	];

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
		$event->setData(
			static::callHook($event->getName(), $event->getData())
		);
	}

	public static function onHtmlFilterEvent(HtmlFilterEvent $event): void
	{
		$name = $event->getName();

		$name = static::$eventMapper[$name] ?? $name;

		$event->setHtml(
			static::callHook($name, $event->getHtml())
		);
	}

	/**
	 * @param string|array $data
	 *
	 * @return string|array
	 */
	private static function callHook(string $name, $data)
	{
		// Little hack to allow mocking the Hook call in tests.
		if (static::$callHook instanceof \Closure) {
			return (static::$callHook)->__invoke($name, $data);
		}

		Hook::callAll($name, $data);

		return $data;
	}
}
