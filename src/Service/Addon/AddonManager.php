<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Friendica\Service\Addon;

/**
 * Manager for all addons.
 */
final class AddonManager
{
	private AddonLoader $addonFactory;

	/** @var Addon[] */
	private array $addons = [];

	public function __construct(AddonLoader $addonFactory)
	{
		$this->addonFactory = $addonFactory;
	}

	public function bootstrapAddons(array $addonNames): void
	{
		$this->addons = $this->addonFactory->getAddons($addonNames);
	}

	public function getAllRequiredDependencies(): array
	{
		$dependencies = [];

		foreach ($this->addons as $addon) {
			// @TODO Here we can filter or deny dependencies from addons
			$dependencies = array_merge($dependencies, $addon->getRequiredDependencies());
		}

		return array_unique($dependencies);
	}

	public function getRequiredDependencies(): array
	{
		$dependencies = [];

		foreach ($this->addons as $addon) {
			// @TODO Here we can filter or deny dependencies from addons
			$dependencies[$addon->getId()] =  $addon->getRequiredDependencies();
		}

		return $dependencies;
	}

	public function getProvidedDependencyRules(): array
	{
		$dependencyRules = [];

		foreach ($this->addons as $addon) {
			// @TODO At this point we can handle duplicate rules and handle possible conflicts
			$dependencyRules = array_merge($dependencyRules, $addon->getProvidedDependencyRules());
		}

		return $dependencyRules;
	}

	public function getAllSubscribedEvents(): array
	{
		$events = [];

		foreach ($this->addons as $addon) {
			$events = array_merge($events, $addon->getSubscribedEvents());
		}

		return $events;
	}

	public function initAddons(array $dependencies): void
	{
		foreach ($this->addons as $addon) {
			$required          = $addon->getRequiredDependencies();
			$addonDependencies = [];

			foreach ($required as $dependency) {
				if (!array_key_exists($dependency, $dependencies)) {
					throw new \RuntimeException(sprintf('Dependency "%s" required by addon "%s" not found.', $dependency, $addon));
				}

				$addonDependencies[$dependency] = $dependencies[$dependency];
			}

			$addon->initAddon($addonDependencies);
		}
	}
}
