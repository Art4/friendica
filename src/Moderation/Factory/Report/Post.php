<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Moderation\Factory\Report;

use Friendica\Capabilities\ICanCreateFromTableRow;

class Post extends \Friendica\BaseFactory implements ICanCreateFromTableRow
{
	public function createFromTableRow(array $row): \Friendica\Moderation\Entity\Report\Post
	{
		return new \Friendica\Moderation\Entity\Report\Post(
			$row['uri-id'],
			$row['status']
		);
	}
}
