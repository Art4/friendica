<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Friendica\Addon;

use Friendica\Addon\Event\AddonStartEvent;

/**
 * Proxy object for an addon.
 */
final class Addon
{
	private AddonBootstrap $bootstrap;

	private array $dependencies;

	public function __construct(AddonBootstrap $bootstrap, array $dependencies = [])
	{
		$this->bootstrap = $bootstrap;
		$this->dependencies = $dependencies;
	}

	public function initAddon(): void
	{
		$event = new AddonStartEvent($this->dependencies);

		$this->bootstrap->initAddon($event);
	}
}
