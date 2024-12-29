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

	/** @var string|array */
	private $data;

	/**
	 * @param string|array $data
	 */
	public function __construct(string $name, $data)
	{
		$this->name = $name;
		$this->data = $data;
	}

	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return string|array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param string|array $data
	 *
	 * @throws \InvalidArgumentException If $data is a string but the original data was an array and vice versa.
	 */
	public function setData($data): void
	{
		if (is_string($this->data) && is_array($data)) {
			throw new \InvalidArgumentException('Argument #1 ($data) must be of type string, array given');
		}

		if (is_array($this->data) && is_string($data)) {
			throw new \InvalidArgumentException('Argument #1 ($data) must be of type array, string given');
		}

		$this->data = $data;
	}
}
