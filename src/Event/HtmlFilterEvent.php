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
	public const HEAD = 'html.head';

	public const FOOTER = 'html.footer';

	public const PAGE_CONTENT_TOP = 'html.page_content_top';

	public const PAGE_END = 'html.page_end';

	private string $name;

	private string $html;

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
