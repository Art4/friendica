<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Test\src\Factory\Api\Mastodon;

use Friendica\Core\Hook;
use Friendica\DI;
use Friendica\Model\Post;
use Friendica\Test\FixtureTest;

class StatusTest extends FixtureTest
{
	protected $status;

	protected function setUp(): void
	{
		parent::setUp();

		DI::config()->set('system', 'no_smilies', false);
		$this->status = DI::mstdnStatus();

		Hook::register('smilie', 'tests/Util/SmileyWhitespaceAddon.php', 'add_test_unicode_smilies');
		Hook::loadHooks();
	}

	public function testSimpleStatus()
	{
		$post = Post::selectFirst([], ['id' => 13]);
		$this->assertNotNull($post);
		$result = $this->status->createFromUriId($post['uri-id']);
		$this->assertNotNull($result);
	}

	public function testSimpleEmojiStatus()
	{
		$post = Post::selectFirst([], ['id' => 14]);
		$this->assertNotNull($post);
		$result = $this->status->createFromUriId($post['uri-id'])->toArray();
		$this->assertEquals(':like: :friendica: no <code>:dislike</code> :p: :embarrassed: 🤗 ❤ :smileyheart333: 🔥', $result['content']);
		$emojis = array_fill_keys(['like', 'friendica', 'p', 'embarrassed', 'smileyheart333'], true);
		$this->assertEquals(count($emojis), count($result['emojis']));
		foreach ($result['emojis'] as $emoji) {
			$this->assertTrue(array_key_exists($emoji['shortcode'], $emojis));
			$this->assertEquals(0, strpos($emoji['url'], 'http'));
		}
	}
}
