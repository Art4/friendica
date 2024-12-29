<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types = 1);

namespace Friendica\Event;

/**
 * Allow Event listener to modify data.
 */
final class DataFilterEvent
{
	private string $name;

	private array $data;

	public function __construct(string $name, array $data)
	{
		$this->name = $name;
		$this->data = $data;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getData(): array
	{
		return $this->data;
	}

	public function setData(array $data): void
	{
		$this->data = $data;
	}
}
