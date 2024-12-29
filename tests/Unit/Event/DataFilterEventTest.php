<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types = 1);

namespace Friendica\Test\Unit\Event;

use Friendica\Event\DataFilterEvent;
use PHPUnit\Framework\TestCase;

class DataFilterEventTest extends TestCase
{
	public function testGetNameReturnsName(): void
	{
		$data = ['data' => 'original'];

		$event = new DataFilterEvent('test', $data);

		$this->assertEquals('test', $event->getName());
	}

	public function testGetDataReturnsData(): void
	{
		$data = ['data' => 'original'];

		$event = new DataFilterEvent('test', $data);

		$this->assertEquals($data, $event->getData());
	}

	public function testSetDataUpdatesData(): void
	{
		$data = ['data' => 'original'];

		$event = new DataFilterEvent('test', $data);

		$expected = ['data' => 'updated'];

		$event->setData($expected);

		$this->assertEquals($expected, $event->getData());
	}
}
