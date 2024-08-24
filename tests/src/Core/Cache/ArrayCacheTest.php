<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Test\src\Core\Cache;

use Friendica\Core\Cache\Type\ArrayCache;

class ArrayCacheTest extends MemoryCacheTest
{
	protected function getInstance()
	{
		$this->cache = new ArrayCache('localhost');
		return $this->cache;
	}

	protected function tearDown(): void
	{
		$this->cache->clear(false);
		parent::tearDown();
	}

	/**
	 * @doesNotPerformAssertions
	 */
	public function testTTL()
	{
		// Array Cache doesn't support TTL
		self::markTestSkipped("Array Cache doesn't support TTL");
		return true;
	}
}
