<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Test\src\Model\Post;

use Friendica\Test\MockedTestCase;

class MediaTest extends MockedTestCase
{
	/**
	 * Test the api_get_attachments() function.
	 *
	 * @return void
	 */
	public function testApiGetAttachments()
	{
		self::markTestIncomplete('Needs Model\Post\Media refactoring first.');

		// $body = 'body';
		// self::assertEmpty(api_get_attachments($body, 0));
	}

	/**
	 * Test the api_get_attachments() function with an img tag.
	 *
	 * @return void
	 */
	public function testApiGetAttachmentsWithImage()
	{
		self::markTestIncomplete('Needs Model\Post\Media refactoring first.');

		// $body = '[img]http://via.placeholder.com/1x1.png[/img]';
		// self::assertIsArray(api_get_attachments($body, 0));
	}

	/**
	 * Test the api_get_attachments() function with an img tag and an AndStatus user agent.
	 *
	 * @return void
	 */
	public function testApiGetAttachmentsWithImageAndAndStatus()
	{
		self::markTestIncomplete('Needs Model\Post\Media refactoring first.');

		// $_SERVER['HTTP_USER_AGENT'] = 'AndStatus';
		// $body                       = '[img]http://via.placeholder.com/1x1.png[/img]';
		// self::assertIsArray(api_get_attachments($body, 0));
	}
}
