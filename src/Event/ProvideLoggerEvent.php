<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Friendica\Event;

use Psr\Log\LoggerInterface;

/**
 * Allows a listener to provide a different Logger implementation.
 */
final class ProvideLoggerEvent implements NamedEvent
{
	/**
	 * Friendica is initialized.
	 */
	public const NAME = 'friendica.container.logger';

	private string $name;

	private LoggerInterface $logger;

	public function __construct(LoggerInterface $logger)
	{
		$this->name = self::NAME;
		$this->logger = $logger;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getLogger(): LoggerInterface
	{
		return $this->logger;
	}

	public function setLogger(LoggerInterface $logger): void
	{
		$this->logger = $logger;
	}
}
