<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica\Module\Media\Photo;

use Friendica\App;
use Friendica\BaseModule;
use Friendica\Core\L10n;
use Friendica\Core\Renderer;
use Friendica\Core\Session\Capability\IHandleUserSessions;
use Friendica\Model\Photo;
use Friendica\Module\Response;
use Friendica\Network\HTTPException\UnauthorizedException;
use Friendica\Util\Images;
use Friendica\Util\Profiler;
use Friendica\Util\Proxy;
use Friendica\Util\Strings;
use Psr\Log\LoggerInterface;

/**
 * Browser for Photos
 */
class Browser extends BaseModule
{
	/** @var IHandleUserSessions */
	protected $session;
	/** @var App */
	protected $app;

	public function __construct(L10n $l10n, App\BaseURL $baseUrl, App\Arguments $args, LoggerInterface $logger, Profiler $profiler, Response $response, IHandleUserSessions $session, App $app, array $server, array $parameters = [])
	{
		parent::__construct($l10n, $baseUrl, $args, $logger, $profiler, $response, $server, $parameters);

		$this->session = $session;
		$this->app     = $app;
	}

	protected function content(array $request = []): string
	{
		if (!$this->session->getLocalUserId()) {
			throw new UnauthorizedException($this->t('You need to be logged in to access this page.'));
		}

		// Needed to match the correct template in a module that uses a different theme than the user/site/default
		$theme = Strings::sanitizeFilePathItem($request['theme'] ?? '');
		if ($theme && is_file("view/theme/$theme/config.php")) {
			$this->app->setCurrentTheme($theme);
		}

		$album = $this->parameters['album'] ?? null;

		$photos = Photo::getBrowsablePhotosForUser($this->session->getLocalUserId(), $album);
		$albums = $album ? false : Photo::getBrowsableAlbumsForUser($this->session->getLocalUserId());

		$path = [
			'' => $this->t('Photos'),
		];
		if (!empty($album)) {
			$path[$album] = $album;
		}

		$photosArray = array_map([$this, 'map_files'], $photos);

		$tpl    = Renderer::getMarkupTemplate('media/browser.tpl');
		$output = Renderer::replaceMacros($tpl, [
			'$type'     => 'photo',
			'$path'     => $path,
			'$folders'  => $albums,
			'$files'    => $photosArray,
			'$cancel'   => $this->t('Cancel'),
			'$nickname' => $this->session->getLocalUserNickname(),
			'$upload'   => $this->t('Upload'),
		]);

		if (empty($request['mode'])) {
			$this->httpExit($output);
		}

		return $output;
	}

	protected function map_files(array $record): array
	{
		$ext        = Images::getExtensionByMimeType($record['type']);
		$filename_e = $record['filename'];

		// Take the largest picture that is smaller or equal 640 pixels
		$photo = Photo::selectFirst(
			['scale'],
			[
				"`resource-id` = ? AND `height` <= ? AND `width` <= ?",
				$record['resource-id'],
				Proxy::PIXEL_MEDIUM,
				Proxy::PIXEL_MEDIUM
			],
			['order' => ['scale']]);
		$scale = $photo['scale'] ?? $record['loq'];

		return [
			sprintf('%s/photos/%s/image/%s', $this->baseUrl, $this->session->getLocalUserNickname(), $record['resource-id']),
			$filename_e,
			sprintf('%s/photo/%s-%s%s', $this->baseUrl, $record['resource-id'], $scale, $ext),
			$record['desc'],
		];
	}
}
