<?php

// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * Name: Hello Addon
 * Description: For testing purpose only
 * Version: 1.0
 * Author: Artur Weigandt <dont-mail-me@example.com>
 */

declare(strict_types=1);

namespace FriendicaAddons\HelloAddon;

use Friendica\Addon\AddonBootstrap;
use Friendica\Addon\Event\AddonInstallEvent;
use Friendica\Addon\Event\AddonStartEvent;
use Friendica\Addon\Event\AddonUninstallEvent;
use Friendica\Event\HtmlFilterEvent;
use Friendica\Event\ProvideLoggerEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class HelloAddon implements AddonBootstrap
{
	/** @var LoggerInterface */
	private static $logger;

	/**
	 * Returns an array of services that are required by this addon.
	 *
	 * The array should contain FQCN of the required services.
	 *
	 * The dependencies will be passed to the initAddon() method via AddonStartEvent::getDependencies().
	 */
	public static function getRequiredDependencies(): array
	{
		return [
			LoggerInterface::class,
		];
	}

	public static function getStaticSubscribedEvents(): array
	{
		return [
			HtmlFilterEvent::PAGE_END => 'onPageEnd',
			ProvideLoggerEvent::NAME => 'onProvideLoggerEvent',
		];
	}

	public function initAddon(AddonStartEvent $event): void
	{
		// $dependencies containts an array of services defined in getRequiredDependencies().
		// The keys are the FQCN of the services.
		// The values are the instances of the services.
		$dependencies = $event->getDependencies();

		static::$logger = $dependencies[LoggerInterface::class];

		static::$logger->info('Hello from HelloAddon');
	}

	public static function install(AddonInstallEvent $event): void
	{
		// do something on install
	}

	public static function uninstall(AddonUninstallEvent $event): void
	{
		// do something on uninstall
	}

	public static function onPageEnd(HtmlFilterEvent $event): void
	{
		$event->setHtml($event->getHtml() . '<p>Hello, World!</p>');
	}

	public static function onProvideLoggerEvent(ProvideLoggerEvent $event): void
	{
		$event->setLogger(new NullLogger());
	}
}
