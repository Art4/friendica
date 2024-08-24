<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Privacy\Entity;

use Friendica\BaseEntity;

class AddressedReceivers extends BaseEntity
{
	protected array $to         = [];
	protected array $cc         = [];
	protected array $bcc        = [];
	protected array $audience   = [];
	protected array $attributed = [];

	public function __construct(array $to = [], array $cc = [], array $bcc = [], array $audience = [], array $attributed = [])
	{
		$this->to         = $to;
		$this->cc         = $cc;
		$this->bcc        = $bcc;
		$this->audience   = $audience;
		$this->attributed = $attributed;
	}

	public function isEmpty(): bool
	{
		return empty($this->to) && empty($this->cc) && empty($this->bcc) && empty($this->audience) && empty($this->attributed);
	}
}
