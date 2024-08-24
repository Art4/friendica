<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Core\Addon\Exception;

use Throwable;

/**
 * Exception in case one or more config files of the addons are invalid
 */
class AddonInvalidConfigFileException extends \RuntimeException
{
	public function __construct($message = '', $code = 0, Throwable $previous = null)
	{
		parent::__construct($message, 500, $previous);
	}
}
