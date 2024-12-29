<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types = 1);

namespace Friendica\Test\Unit\Event;

use Friendica\Event\DataFilterEvent;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class DataFilterEventTest extends TestCase
{
	public function testGetNameReturnsName(): void
	{
		$event = new DataFilterEvent('test', []);

		$this->assertSame('test', $event->getName());
	}

	public function testGetDataReturnsArray(): void
	{
		$data = ['data' => 'original'];

		$event = new DataFilterEvent('test', $data);

		$this->assertSame($data, $event->getData());
	}

	public function testGetDataReturnsString(): void
	{
		$data = 'original';

		$event = new DataFilterEvent('test', $data);

		$this->assertSame($data, $event->getData());
	}

	public function testSetDataUpdatesData(): void
	{
		$data = ['data' => 'original'];

		$event = new DataFilterEvent('test', $data);

		$expected = ['data' => 'updated'];

		$event->setData($expected);

		$this->assertSame($expected, $event->getData());
	}

	public function testSetDataWithStringThrowsException(): void
	{
		$data = ['data' => 'original'];

		$event = new DataFilterEvent('test', $data);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Argument #1 ($data) must be of type array, string given');

		$event->setData('try to set a string instead of an array');
	}

	public function testSetDataWithArrayThrowsException(): void
	{
		$data = 'original data';

		$event = new DataFilterEvent('test', $data);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Argument #1 ($data) must be of type string, array given');

		$event->setData(['try to set an array instead of a string']);
	}
}
