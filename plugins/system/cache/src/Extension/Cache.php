<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.cache
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Cache\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Cache\CacheController;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Document\FactoryInterface as DocumentFactoryInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Profiler\Profiler;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Event;
use Joomla\Event\Priority;
use Joomla\Event\SubscriberInterface;

/**
 * Joomla! Page Cache Plugin.
 *
 * @since  1.5
 */
final class Cache extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Application object.
	 *
	 * @var    CMSApplication
	 * @since  3.8.0
	 */
	protected $app;

	/**
	 * Cache instance.
	 *
	 * @var    CacheController
	 * @since  1.5
	 */
	private $cache;

	/**
	 * The application's document factory interface
	 *
	 * @var   DocumentFactoryInterface
	 * @since __DEPLOY_VERSION__
	 */
	private $documentFactory;

	/**
	 * Cache controller factory interface
	 *
	 * @var CacheControllerFactoryInterface
	 * @since __DEPLOY_VERSION__
	 */
	private $cacheControllerFactory;

	/**
	 * Constructor
	 *
	 * @param   DispatcherInterface              $subject                 The object to observe
	 * @param   array                            $config                  An optional associative
	 *                                                                    array of configuration
	 *                                                                    settings. Recognized key
	 *                                                                    values include 'name',
	 *                                                                    'group', 'params',
	 *                                                                    'language'
	 *                                                                    (this list is not meant
	 *                                                                    to be comprehensive).
	 * @param   DocumentFactoryInterface         $documentFactory         The application's
	 *                                                                    document factory
	 * @param   CacheControllerFactoryInterface  $cacheControllerFactory  Cache controller factory
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(&$subject, $config,
		DocumentFactoryInterface $documentFactory,
		CacheControllerFactoryInterface $cacheControllerFactory
	)
	{
		parent::__construct($subject, $config);

		$this->documentFactory = $documentFactory;
		$this->cacheControllerFactory = $cacheControllerFactory;
	}

	/**
	 * Returns an array of CMS events this plugin will listen to and the respective handlers.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		/**
		 * Note that onAfterRender and onAfterRespond must be the last handlers to run for this
		 * plugin to operate as expected. These handlers put pages into cache. We must make sure
		 * that a. the page SHOULD be cached and b. we are caching the complete page, as it's
		 * output to the browser.
		 */
		return [
			'onAfterRoute'   => 'onAfterRoute',
			'onAfterRender'  => ['onAfterRender', Priority::LOW],
			'onAfterRespond' => ['onAfterRespond', Priority::LOW],
		];
	}

	/**
	 * Returns a cached page if the current URL exists in the cache.
	 *
	 * @param   Event  $event  The Joomla event being handled
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onAfterRoute(Event $event)
	{
		if (!$this->appStateSupportsCaching())
		{
			return;
		}

		// If any `pagecache` plugins return false for onPageCacheSetCaching, do not use the cache.
		PluginHelper::importPlugin('pagecache');

		$results = $this->app->triggerEvent('onPageCacheSetCaching');

		$this->getCacheController()->setCaching(!in_array(false, $results, true));

		$data = $this->getCacheController()->get($this->getCacheKey());

		if ($data === false)
		{
			// No cached data.
			return;
		}

		// Set the page content from the cache and output it to the browser.
		$this->app->setBody($data);

		echo $this->app->toString((bool) $this->app->get('gzip'));

		// Mark afterCache in debug and run debug onAfterRespond events, e.g. show Joomla Debug Console if debug is active.
		if (JDEBUG)
		{
			// Create a document instance and load it into the application.
			$document = $this->documentFactory
				->createDocument($this->app->input->get('format', 'html'));
			$this->app->loadDocument($document);

			Profiler::getInstance('Application')->mark('afterCache');
			$this->app->triggerEvent('onAfterRespond');
		}

		// Closes the application.
		$this->app->close();
	}

	/**
	 * After Render Event. Check whether the current page is excluded from cache.
	 *
	 * @param   Event  $event  The CMS event we are handling.
	 *
	 * @return  void
	 *
	 * @since   3.9.12
	 */
	public function onAfterRender(Event $event)
	{
		if (!$this->appStateSupportsCaching() || $this->getCacheController()->getCaching() === false)
		{
			return;
		}

		if ($this->isExcluded() === true)
		{
			$this->getCacheController()->setCaching(false);

			return;
		}

		// Disable compression before caching the page.
		$this->app->set('gzip', false);
	}

	/**
	 * After Respond Event. Stores page in cache.
	 *
	 * @param   Event  $event  The application event we are handling.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function onAfterRespond(Event $event)
	{
		if (!$this->appStateSupportsCaching() || $this->getCacheController()->getCaching() === false)
		{
			return;
		}

		// Saves current page in cache.
		$this->getCacheController()->store($this->app->getBody(), $this->getCacheKey());
	}

	/**
	 * Does the current application state allow for caching?
	 *
	 * The following conditions must be met:
	 * * This is the frontend application. This plugin does not apply to other applications.
	 * * This is a GET request. This plugin does not apply to POST, PUT etc.
	 * * There is no currently logged in user (pages might have user–specific content).
	 * * The message queue is empty.
	 *
	 * The first two tests are cached to make early returns possible; these conditions cannot change
	 * throughout the lifetime of the request.
	 *
	 * The other two tests MUST NOT be cached because auto–login plugins may fire anytime within
	 * the application lifetime logging in a user and messages can be generated anytime within the
	 * application's lifetime.
	 *
	 * @return  boolean
	 * @since   __DEPLOY_VERSION__
	 */
	private function appStateSupportsCaching(): bool
	{
		static $isSite = null;
		static $isGET = null;

		if ($isSite === null)
		{
			$isSite = ($this->app instanceof CMSApplicationInterface) && $this->app->isClient('site');
			$isGET  = $this->app->input->getMethod() === 'GET';
		}

		// Boolean short–circuit evaluation means this returns fast false when $isSite is false.
		return $isSite
			&& $isGET
			&& $this->app->getIdentity()->guest
			&& empty($this->app->getMessageQueue());
	}

	/**
	 * Get a cache key for the current page based on the url and possible other factors.
	 *
	 * @return  string
	 *
	 * @since   3.7
	 */
	private function getCacheKey(): string
	{
		static $key;

		if (!$key)
		{
			PluginHelper::importPlugin('pagecache');

			$parts   = $this->app->triggerEvent('onPageCacheGetKey');
			$parts[] = Uri::getInstance()->toString();

			$key = md5(serialize($parts));
		}

		return $key;
	}

	/**
	 * Check if the page is excluded from the cache or not.
	 *
	 * @return   boolean  True if the page is excluded else false
	 *
	 * @since    3.5
	 */
	private function isExcluded(): bool
	{
		// Check if menu items have been excluded.
		$excludedMenuItems = $this->params->get('exclude_menu_items', []);

		if ($excludedMenuItems)
		{
			// Get the current menu item.
			$active = $this->app->getMenu()->getActive();

			if ($active && $active->id && in_array((int) $active->id, (array) $excludedMenuItems))
			{
				return true;
			}
		}

		// Check if regular expressions are being used.
		$exclusions = $this->params->get('exclude', '');

		if ($exclusions)
		{
			// Convert the exclusions into a normalised array
			$exclusions       = str_replace(["\r\n", "\r"], "\n", $exclusions);
			$exclusions       = explode("\n", $exclusions);
			$filterExpression = function ($x)
			{
				return $x !== '';
			};
			$exclusions       = array_filter($exclusions, $filterExpression);

			// Gets the internal (non-SEF) and the external (possibly SEF) URIs.
			$internalUrl = '/index.php?' . Uri::getInstance()->buildQuery($this->app->getRouter()->getVars());
			$externalUrl = Uri::getInstance()->toString();

			$reduceCallback = function (bool $carry, string $exclusion) use ($internalUrl, $externalUrl)
			{
				// Test both external and internal URIs
				return $carry && preg_match('#' . $exclusion . '#i', $externalUrl . ' ' . $internalUrl, $match);
			};
			$excluded = array_reduce($exclusions, $reduceCallback, false);

			if ($excluded)
			{
				return true;
			}
		}

		// If any pagecache plugins return true for onPageCacheIsExcluded, exclude.
		PluginHelper::importPlugin('pagecache');

		$results = $this->app->triggerEvent('onPageCacheIsExcluded');

		return in_array(true, $results, true);
	}

	/**
	 * Get the cache controller
	 *
	 * @return  CacheController
	 * @since   __DEPLOY_VERSION__
	 */
	private function getCacheController(): CacheController
	{
		if (!empty($this->cache))
		{
			return $this->cache;
		}

		// Set the cache options.
		$options = [
			'defaultgroup' => 'page',
			'browsercache' => $this->params->get('browsercache', 0),
			'caching'      => false,
		];

		// Instantiate cache with previous options.
		$this->cache = $this->cacheControllerFactory->createCacheController('page', $options);

		return $this->cache;
	}
}
