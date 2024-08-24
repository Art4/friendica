<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Test\src\Core\Cache;

use Exception;
use Friendica\Core\Cache\Type\RedisCache;
use Friendica\Core\Config\Capability\IManageConfigValues;
use Mockery;

/**
 * @requires extension redis
 * @group REDIS
 */
class RedisCacheTest extends MemoryCacheTest
{
	protected function getInstance()
	{
		$configMock = Mockery::mock(IManageConfigValues::class);

		$host = $_SERVER['REDIS_HOST'] ?? 'localhost';
		$port = $_SERVER['REDIS_PORT'] ?? 6379;

		$configMock
			->shouldReceive('get')
			->with('system', 'redis_host')
			->andReturn($host);
		$configMock
			->shouldReceive('get')
			->with('system', 'redis_port')
			->andReturn($port);

		$configMock
			->shouldReceive('get')
			->with('system', 'redis_db', 0)
			->andReturn(0);
		$configMock
			->shouldReceive('get')
			->with('system', 'redis_password')
			->andReturn(null);

		try {
			$this->cache = new \Friendica\Core\Cache\Type\RedisCache($host, $configMock);
		} catch (Exception $e) {
			static::markTestSkipped('Redis is not available. Failure: ' . $e->getMessage());
		}
		return $this->cache;
	}

	protected function tearDown(): void
	{
		$this->cache->clear(false);
		parent::tearDown();
	}
}
