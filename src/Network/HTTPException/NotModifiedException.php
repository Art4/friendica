<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Network\HTTPException;

use Friendica\Network\HTTPException;

class NotModifiedException extends HTTPException
{
	protected $code     = 304;
	protected $httpdesc = 'Not Modified';
}
