<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Friendica\Test\Unit\Event;

use Friendica\Event\HtmlFilterEvent;
use PHPUnit\Framework\TestCase;

class HtmlFilterEventTest extends TestCase
{
	public function testGetNameReturnsName(): void
	{
		$event = new HtmlFilterEvent('test', '');

		$this->assertSame('test', $event->getName());
	}

	public function testGetHtmlReturnsCorrectString(): void
	{
		$data = 'original';

		$event = new HtmlFilterEvent('test', $data);

		$this->assertSame($data, $event->getHtml());
	}

	public function testSetHtmlUpdatesHtml(): void
	{
		$event = new HtmlFilterEvent('test', 'original');

		$expected = 'updated';

		$event->setHtml($expected);

		$this->assertSame($expected, $event->getHtml());
	}
}
