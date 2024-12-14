<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Test;

/**
 * Parent class for test cases requiring fixtures
 */
abstract class FixtureTest extends MockedTestCase
{
	use FixtureTestTrait;

	protected function setUp(): void
	{
		parent::setUp();

		$this->setUpFixtures();
	}

	protected function tearDown(): void
	{
		$this->tearDownFixtures();

		parent::tearDown();
	}
}
