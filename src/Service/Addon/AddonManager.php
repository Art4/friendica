<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Friendica\Service\Addon;

use Friendica\Addon\AddonBootstrap;
use Psr\Log\LoggerInterface;

/**
 * Manager for all addons.
 */
final class AddonManager
{
	private string $addonPath;

	private LoggerInterface $logger;

	/** @var Addon[] */
	private array $addons = [];

	public function __construct(string $addonPath, LoggerInterface $logger)
	{
		$this->addonPath = $addonPath;
		$this->logger    = $logger;
	}

	public function bootstrapAddons(array $addonNames): void
	{
		foreach ($addonNames as $addonName => $addonDetails) {
			try {
				$this->bootstrapAddon($addonName);
			} catch (\Throwable $th) {
				// @TODO Here we can check if we have a Legacy addon and try to load it
				// throw $th;
			}
		}
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

	public function getProvidedDependencyRules(): array
	{
		$dependencyRules = [];

		foreach ($this->addons as $addon) {
			$dependencyRules = array_merge($dependencyRules, $addon->getProvidedDependencyRules());
		}

		return $dependencyRules;
	}

	private function bootstrapAddon(string $addonName): void
	{
		$bootstrapFile = sprintf('%s/%s/bootstrap.php', $this->addonPath, $addonName);

		if (!file_exists($bootstrapFile)) {
			throw new \RuntimeException(sprintf('Bootstrap file for addon "%s" not found.', $addonName));
		}

		try {
			$bootstrap = require $bootstrapFile;
		} catch (\Throwable $th) {
			throw new \RuntimeException(sprintf('Something went wrong loading the Bootstrap file for addon "%s".', $addonName), $th->getCode(), $th);
		}

		if (!($bootstrap instanceof AddonBootstrap)) {
			throw new \RuntimeException(sprintf('Bootstrap file for addon "%s" MUST return an instance of AddonBootstrap.', $addonName));
		}

		$this->addons[$addonName] = new AddonProxy($bootstrap);

		$this->logger->info(sprintf('Addon "%s" loaded.', $addonName));
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