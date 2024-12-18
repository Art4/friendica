<?php

// Copyright (C) 2010-2024, the Friendica project
// SPDX-FileCopyrightText: 2010-2024 the Friendica project
//
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace Friendica;

use Friendica\App\Arguments;
use Friendica\App\BaseURL;
use Friendica\App\Mode;
use Friendica\App\Page;
use Friendica\App\Request;
use Friendica\App\Router;
use Friendica\Capabilities\ICanCreateResponses;
use Friendica\Content\Nav;
use Friendica\Core\Config\Factory\Config;
use Friendica\Core\Session\Capability\IHandleUserSessions;
use Friendica\Database\Definition\DbaDefinition;
use Friendica\Database\Definition\ViewDefinition;
use Friendica\Module\Maintenance;
use Friendica\Security\Authentication;
use Friendica\Core\Config\Capability\IManageConfigValues;
use Friendica\Core\PConfig\Capability\IManagePersonalConfigValues;
use Friendica\Core\L10n;
use Friendica\Core\System;
use Friendica\Module\Special\HTTPException as ModuleHTTPException;
use Friendica\Network\HTTPException;
use Friendica\Protocol\ATProtocol\DID;
use Friendica\Security\OpenWebAuth;
use Friendica\Util\DateTimeFormat;
use Friendica\Util\HTTPInputData;
use Friendica\Util\HTTPSignature;
use Friendica\Util\Profiler;
use Psr\Log\LoggerInterface;

/**
 * Our main application structure for the life of this page.
 *
 * Primarily deals with the URL that got us here
 * and tries to make some sense of it, and
 * stores our page contents and config storage
 * and anything else that might need to be passed around
 * before we spit the page out.
 *
 */
class App
{
	const PLATFORM = 'Friendica';
	const CODENAME = 'Yellow Archangel';
	const VERSION  = '2024.12-dev';

	/**
	 * @var Mode The Mode of the Application
	 */
	private $mode;

	/**
	 * @var BaseURL
	 */
	private $baseURL;

	/** @var string */
	private $requestId;

	/** @var Authentication */
	private $auth;

	/**
	 * @var IManageConfigValues The config
	 */
	private $config;

	/**
	 * @var LoggerInterface The logger
	 */
	private $logger;

	/**
	 * @var Profiler The profiler of this app
	 */
	private $profiler;

	/**
	 * @var L10n The translator
	 */
	private $l10n;

	/**
	 * @var App\Arguments
	 */
	private $args;

	/**
	 * @var IHandleUserSessions
	 */
	private $session;

	/**
	 * @var AppHelper $appHelper
	 */
	private $appHelper;

	public function __construct(
		Request $request,
		Authentication $auth,
		IManageConfigValues $config,
		Mode $mode,
		BaseURL $baseURL,
		LoggerInterface $logger,
		Profiler $profiler,
		L10n $l10n,
		Arguments $args,
		IHandleUserSessions $session,
		DbaDefinition $dbaDefinition,
		ViewDefinition $viewDefinition
	) {
		$this->requestId = $request->getRequestId();
		$this->auth      = $auth;
		$this->config    = $config;
		$this->mode      = $mode;
		$this->baseURL   = $baseURL;
		$this->profiler  = $profiler;
		$this->logger    = $logger;
		$this->l10n      = $l10n;
		$this->args      = $args;
		$this->session   = $session;
		$this->appHelper = DI::appHelper();

		$this->load($dbaDefinition, $viewDefinition);
	}

	/**
	 * Load the whole app instance
	 */
	protected function load(DbaDefinition $dbaDefinition, ViewDefinition $viewDefinition)
	{
		if ($this->config->get('system', 'ini_max_execution_time') !== false) {
			set_time_limit((int)$this->config->get('system', 'ini_max_execution_time'));
		}

		if ($this->config->get('system', 'ini_pcre_backtrack_limit') !== false) {
			ini_set('pcre.backtrack_limit', (int)$this->config->get('system', 'ini_pcre_backtrack_limit'));
		}

		// Normally this constant is defined - but not if "pcntl" isn't installed
		if (!defined('SIGTERM')) {
			define('SIGTERM', 15);
		}

		// Ensure that all "strtotime" operations do run timezone independent
		date_default_timezone_set('UTC');

		$this->profiler->reset();

		if ($this->mode->has(Mode::DBAVAILABLE)) {
			Core\Hook::loadHooks();
			$loader = (new Config())->createConfigFileManager($this->appHelper->getBasePath(), $_SERVER);
			Core\Hook::callAll('load_config', $loader);

			// Hooks are now working, reload the whole definitions with hook enabled
			$dbaDefinition->load(true);
			$viewDefinition->load(true);
		}

		$this->loadDefaultTimezone();
		// Register template engines
		Core\Renderer::registerTemplateEngine('Friendica\Render\FriendicaSmartyEngine');
	}

	/**
	 * Loads the default timezone
	 *
	 * Include support for legacy $default_timezone
	 *
	 * @global string $default_timezone
	 */
	private function loadDefaultTimezone()
	{
		if ($this->config->get('system', 'default_timezone')) {
			$timezone = $this->config->get('system', 'default_timezone', 'UTC');
		} else {
			global $default_timezone;
			$timezone = $default_timezone ?? '' ?: 'UTC';
		}

		$this->appHelper->setTimeZone($timezone);
	}

	/**
	 * Frontend App script
	 *
	 * The App object behaves like a container and a dispatcher at the same time, including a representation of the
	 * request and a representation of the response.
	 *
	 * This probably should change to limit the size of this monster method.
	 *
	 * @param Router                      $router
	 * @param IManagePersonalConfigValues $pconfig
	 * @param Authentication              $auth       The Authentication backend of the node
	 * @param Page                        $page       The Friendica page printing container
	 * @param ModuleHTTPException         $httpException The possible HTTP Exception container
	 * @param HTTPInputData               $httpInput  A library for processing PHP input streams
	 * @param float                       $start_time The start time of the overall script execution
	 * @param array                       $server     The $_SERVER array
	 *
	 * @throws HTTPException\InternalServerErrorException
	 * @throws \ImagickException
	 */
	public function runFrontend(
		Router $router,
		IManagePersonalConfigValues $pconfig,
		Authentication $auth,
		Page $page,
		Nav $nav,
		ModuleHTTPException $httpException,
		HTTPInputData $httpInput,
		float $start_time,
		array $server
	) {
		$requeststring = ($server['REQUEST_METHOD'] ?? '') . ' ' . ($server['REQUEST_URI'] ?? '') . ' ' . ($server['SERVER_PROTOCOL'] ?? '');
		$this->logger->debug('Request received', ['address' => $server['REMOTE_ADDR'] ?? '', 'request' => $requeststring, 'referer' => $server['HTTP_REFERER'] ?? '', 'user-agent' => $server['HTTP_USER_AGENT'] ?? '']);
		$request_start = microtime(true);
		$request = $_REQUEST;

		$this->profiler->set($start_time, 'start');
		$this->profiler->set(microtime(true), 'classinit');

		$moduleName = $this->args->getModuleName();
		$page->setLogging($this->args->getMethod(), $this->args->getModuleName(), $this->args->getCommand());

		try {
			// Missing DB connection: ERROR
			if ($this->mode->has(Mode::LOCALCONFIGPRESENT) && !$this->mode->has(Mode::DBAVAILABLE)) {
				throw new HTTPException\InternalServerErrorException($this->l10n->t('Apologies but the website is unavailable at the moment.'));
			}

			if (!$this->mode->isInstall()) {
				// Force SSL redirection
				if ($this->config->get('system', 'force_ssl') &&
					(empty($server['HTTPS']) || $server['HTTPS'] === 'off') &&
					(empty($server['HTTP_X_FORWARDED_PROTO']) || $server['HTTP_X_FORWARDED_PROTO'] === 'http') &&
					!empty($server['REQUEST_METHOD']) &&
					$server['REQUEST_METHOD'] === 'GET') {
					System::externalRedirect($this->baseURL . '/' . $this->args->getQueryString());
				}
				Core\Hook::callAll('init_1');
			}

			DID::routeRequest($this->args->getCommand(), $server);

			if ($this->mode->isNormal() && !$this->mode->isBackend()) {
				$requester = HTTPSignature::getSigner('', $server);
				if (!empty($requester)) {
					OpenWebAuth::addVisitorCookieForHandle($requester);
				}
			}

			// ZRL
			if (!empty($_GET['zrl']) && $this->mode->isNormal() && !$this->mode->isBackend() && !$this->session->getLocalUserId()) {
				// Only continue when the given profile link seems valid.
				// Valid profile links contain a path with "/profile/" and no query parameters
				if ((parse_url($_GET['zrl'], PHP_URL_QUERY) == '') &&
					strpos(parse_url($_GET['zrl'], PHP_URL_PATH) ?? '', '/profile/') !== false) {
					$this->auth->setUnauthenticatedVisitor($_GET['zrl']);
					OpenWebAuth::zrlInit();
				} else {
					// Someone came with an invalid parameter, maybe as a DDoS attempt
					// We simply stop processing here
					$this->logger->debug('Invalid ZRL parameter.', ['zrl' => $_GET['zrl']]);
					throw new HTTPException\ForbiddenException();
				}
			}

			if (!empty($_GET['owt']) && $this->mode->isNormal()) {
				$token = $_GET['owt'];
				OpenWebAuth::init($token);
			}

			if (!$this->mode->isBackend()) {
				$auth->withSession();
			}

			if ($this->session->isUnauthenticated()) {
				header('X-Account-Management-Status: none');
			}

			/*
			 * check_config() is responsible for running update scripts. These automatically
			 * update the DB schema whenever we push a new one out. It also checks to see if
			 * any addons have been added or removed and reacts accordingly.
			 */

			// in install mode, any url loads install module
			// but we need "view" module for stylesheet
			if ($this->mode->isInstall() && $moduleName !== 'install') {
				$this->baseURL->redirect('install');
			} else {
				Core\Update::check($this->appHelper->getBasePath(), false);
				Core\Addon::loadAddons();
				Core\Hook::loadHooks();
			}

			// Compatibility with Hubzilla
			if ($moduleName == 'rpost') {
				$this->baseURL->redirect('compose');
			}

			// Compatibility with the Android Diaspora client
			if ($moduleName == 'stream') {
				$this->baseURL->redirect('network?order=post');
			}

			if ($moduleName == 'conversations') {
				$this->baseURL->redirect('message');
			}

			if ($moduleName == 'commented') {
				$this->baseURL->redirect('network?order=comment');
			}

			if ($moduleName == 'liked') {
				$this->baseURL->redirect('network?order=comment');
			}

			if ($moduleName == 'activity') {
				$this->baseURL->redirect('network?conv=1');
			}

			if (($moduleName == 'status_messages') && ($this->args->getCommand() == 'status_messages/new')) {
				$this->baseURL->redirect('bookmarklet');
			}

			if (($moduleName == 'user') && ($this->args->getCommand() == 'user/edit')) {
				$this->baseURL->redirect('settings');
			}

			if (($moduleName == 'tag_followings') && ($this->args->getCommand() == 'tag_followings/manage')) {
				$this->baseURL->redirect('search');
			}

			// Initialize module that can set the current theme in the init() method, either directly or via App->setProfileOwner
			$page['page_title'] = $moduleName;

			// The "view" module is required to show the theme CSS
			if (!$this->mode->isInstall() && !$this->mode->has(Mode::MAINTENANCEDISABLED) && $moduleName !== 'view') {
				$module = $router->getModule(Maintenance::class);
			} else {
				// determine the module class and save it to the module instance
				// @todo there's an implicit dependency due SESSION::start(), so it has to be called here (yet)
				$module = $router->getModule();
			}

			// Display can change depending on the requested language, so it shouldn't be cached whole
			header('Vary: Accept-Language', false);

			// Processes data from GET requests
			$httpinput = $httpInput->process();
			$input     = array_merge($httpinput['variables'], $httpinput['files'], $request);

			// Let the module run its internal process (init, get, post, ...)
			$timestamp = microtime(true);
			$response  = $module->run($httpException, $input);
			$this->profiler->set(microtime(true) - $timestamp, 'content');

			// Wrapping HTML responses in the theme template
			if ($response->getHeaderLine(ICanCreateResponses::X_HEADER) === ICanCreateResponses::TYPE_HTML) {
				$response = $page->run($this, $this->session, $this->baseURL, $this->args, $this->mode, $response, $this->l10n, $this->profiler, $this->config, $pconfig, $nav, $this->session->getLocalUserId());
			}

			$this->logger->debug('Request processed sucessfully', ['response' => $response->getStatusCode(), 'address' => $server['REMOTE_ADDR'] ?? '', 'request' => $requeststring, 'referer' => $server['HTTP_REFERER'] ?? '', 'user-agent' => $server['HTTP_USER_AGENT'] ?? '', 'duration' => number_format(microtime(true) - $request_start, 3)]);
			$this->logSlowCalls(microtime(true) - $request_start, $response->getStatusCode(), $requeststring, $server['HTTP_USER_AGENT'] ?? '');
			System::echoResponse($response);
		} catch (HTTPException $e) {
			$this->logger->debug('Request processed with exception', ['response' => $e->getCode(), 'address' => $server['REMOTE_ADDR'] ?? '', 'request' => $requeststring, 'referer' => $server['HTTP_REFERER'] ?? '', 'user-agent' => $server['HTTP_USER_AGENT'] ?? '', 'duration' => number_format(microtime(true) - $request_start, 3)]);
			$this->logSlowCalls(microtime(true) - $request_start, $e->getCode(), $requeststring, $server['HTTP_USER_AGENT'] ?? '');
			$httpException->rawContent($e);
		}
		$page->logRuntime($this->config, 'runFrontend');
	}

	/**
	 * Log slow page executions
	 *
	 * @param float $duration
	 * @param integer $code
	 * @param string $request
	 * @param string $agent
	 * @return void
	 */
	private function logSlowCalls(float $duration, int $code, string $request, string $agent)
	{
		$logfile  = $this->config->get('system', 'page_execution_logfile');
		$loglimit = $this->config->get('system', 'page_execution_log_limit');
		if (empty($logfile) || empty($loglimit) || ($duration < $loglimit)) {
			return;
		}

		@file_put_contents(
			$logfile,
			DateTimeFormat::utcNow() . "\t" . round($duration, 3) . "\t" .
			$this->requestId . "\t" . $code . "\t" .
			$request . "\t" . $agent . "\n",
			FILE_APPEND
		);
	}
}
