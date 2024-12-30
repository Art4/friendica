<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Friendica\Event;

/**
 * Allow Event listener to modify HTML.
 */
final class HtmlFilterEvent
{
	private string $name;

	private string $html;

	/**
	 * @param string|array $html
	 */
	public function __construct(string $name, string $html)
	{
		$this->name = $name;
		$this->html = $html;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getHtml(): string
	{
		return $this->html;
	}

	public function setHtml(string $html): void
	{
		$this->html = $html;
	}
}
