<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Friendica\Addon;

use Friendica\Addon\Event\AddonStartEvent;
use Friendica\EventSubscriber\StaticEventSubscriber;

/**
 * Interface an Addon has to implement.
 */
interface AddonBootstrap extends StaticEventSubscriber
{
	/**
	 * Returns an array with the FQCN of services.
	 */
	public static function getRequiredDependencies(): array;

	/**
	 * Init of the addon.
	 */
	public static function initAddon(AddonStartEvent $event): void;
}
