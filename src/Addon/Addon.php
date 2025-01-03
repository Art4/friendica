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

	public function getRequiredDependencies(): array
	{
		return $this->bootstrap::getRequiredDependencies();
	}

	public function getProvidedDependencyRules(): array
	{
		if ($this->bootstrap instanceof DependencyProvider) {
			return $this->bootstrap::provideDependencyRules();
		}

		return [];
	}

	public function initAddon(array $dependencies): void
	{
		$event = new AddonStartEvent($dependencies);

		$this->bootstrap->initAddon($event);
	}
}
