<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Test;

use Asika\SimpleConsole\Console;
use Friendica\Test\MockedTestCase;
use Friendica\Test\Util\Intercept;

abstract class ConsoleTestCase extends MockedTestCase
{
	/**
	 * @var array The default argv for a Console Instance
	 */
	protected $consoleArgv = [ 'consoleTest.php' ];

	protected function setUp() : void
	{
		parent::setUp();

		Intercept::setUp();
	}

	/**
	 * Dumps the execution of an console output to a string and returns it
	 *
	 * @param Console $console The current console instance
	 *
	 * @return string the output of the execution
	 */
	protected function dumpExecute(Console $console)
	{
		Intercept::reset();
		$console->execute();
		$returnStr = Intercept::$cache;
		Intercept::reset();

		return $returnStr;
	}
}
