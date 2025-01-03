<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Friendica\Service\Addon;

/**
 * Interface to community with an addon.
 */
interface Addon
{
	public function getRequiredDependencies(): array;

	public function getSubscribedEvents(): array;

	public function getProvidedDependencyRules(): array;

	public function initAddon(array $dependencies): void;
}
