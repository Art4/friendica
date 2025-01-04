<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Friendica\Service\Addon;

/**
 * Proxy object for a legacy addon.
 */
final class LegacyAddonProxy implements Addon
{
	private string $name;

	private string $path;

	private bool $isInit = false;

	public function __construct(string $name, string $path)
	{
		$this->name = $name;
		$this->path = $path;
	}

	public function getRequiredDependencies(): array
	{
		return [];
	}

	public function getSubscribedEvents(): array
	{
		return [];
	}

	public function getProvidedDependencyRules(): array
	{
		return [];
	}

	public function initAddon(array $dependencies): void
	{
		if ($this->isInit) {
			return;
		}

		$this->isInit = true;

		include_once($this->path . '/' . $this->name . '/' . $this->name . '.php');
	}

	public function installAddon(): void
	{
		if (function_exists($this->name . '_install')) {
			call_user_func($this->name . '_install');
		}
	}

	public function uninstallAddon(): void
	{
		if (function_exists($this->name . '_uninstall')) {
			call_user_func($this->name . '_uninstall');
		}
	}
}
