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
use Friendica\Addon\DependencyProvider;
use Friendica\Addon\Event\AddonInstallEvent;
use Friendica\Addon\Event\AddonStartEvent;
use Friendica\Addon\Event\AddonUninstallEvent;
use Friendica\Event\HtmlFilterEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class HelloAddon implements AddonBootstrap, DependencyProvider
{
	private LoggerInterface $logger;

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

	/**
	 * Return an array of events to subscribe to.
	 *
	 * The keys MUST be the event name.
	 * The values MUST be the method of the implementing class to call.
	 *
	 * Example:
	 *
	 * ```php
	 * return [Event::NAME => 'onEvent'];
	 * ```
	 *
	 * @return array<string, string>
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			HtmlFilterEvent::PAGE_END => 'onPageEnd',
		];
	}

	/**
	 * Returns an array of Dice rules.
	 */
	public static function provideDependencyRules(): array
	{
		// or return require($path_to_dependencies_file);
		return [
			LoggerInterface::class => [
				'instanceOf' => NullLogger::class,
				'call' => null,
			],
		];
	}

	/**
	 * Returns an array of strategy rules.
	 */
	public static function provideStrategyRules(): array
	{
		// or return require($path_to_strategies_file);
		return [];
	}

	public function initAddon(AddonStartEvent $event): void
	{
		// $dependencies containts an array of services defined in getRequiredDependencies().
		// The keys are the FQCN of the services.
		// The values are the instances of the services.
		$dependencies = $event->getDependencies();

		$this->logger = $dependencies[LoggerInterface::class];

		$this->logger->info('Hello from HelloAddon');
	}

	public function install(AddonInstallEvent $event): void
	{
		// do something on install
	}

	public function uninstall(AddonUninstallEvent $event): void
	{
		// do something on uninstall
	}

	public function onPageEnd(HtmlFilterEvent $event): void
	{
		$event->setHtml($event->getHtml() . '<p>Hello, World!</p>');
	}
}
