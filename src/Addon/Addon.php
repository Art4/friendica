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

	public function __construct(AddonBootstrap $bootstrap)
	{
		$this->bootstrap = $bootstrap;
	}

	public function initAddon(): void
	{
		$event = new AddonStartEvent();

		$this->bootstrap->initAddon($event);
	}
}
