<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Friendica\Addon\Event;

/**
 * Start an addon.
 */
final class AddonStartEvent
{
	private array $dependencies;

	public function __construct(array $dependencies)
	{
		$this->dependencies = $dependencies;
	}

	public function getDependencies(): array
	{
		return $this->dependencies;
	}
}
