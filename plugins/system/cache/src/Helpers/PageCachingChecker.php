<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cache
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Cache\Helpers;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/**
 * Joomla! Page Cache Plugin - PageCachingChecker.
 *
 * @since  __DEPLOY_VERSION__
 */
final class PageCachingChecker
{
	/**
	 * Check if the page can be cached according to all conditions.
	 *
	 * @param   string $cacheKey	Cache Key
	 *
	 * @return   boolean  True if the page can be cached
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	public static function canPageBeCached(string $cacheKey): bool
	{
		$app = Factory::getApplication();

		// Only GET requests are cached.
		if ($app->input->getMethod() !== 'GET')
		{
			return false;
		}

		// Run only when we're on Site Application side
		if (!$app->isClient('site'))
		{
			return false;
		}

		// If the site is offline, don't do anything
		if ((bool) $app->get('offline', '0'))
		{
			return false;
		}

		// The user is authenticated
		if (!$app->getIdentity()->guest) {
			return false;
		}

		// If there are messages in the queue, don't cache the page
		if (!empty($app->getMessageQueue()))
		{
			return false;
		}

		if (self::isExcludedPage($cacheKey)) {
			return false;
		}

		return true;
	}


	/**
	 * Check if the page is excluded from the cache or not.
	 *
	 * @param   string $cacheKey	Cache Key
	 *
	 * @return   boolean  True if the page is excluded else false
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	public static function isExcludedPage(string $cacheKey): bool
	{
		$app = Factory::getApplication();
		$plugin = PluginHelper::getPlugin('system', 'cache');
		$params = new Registry($plugin->params);

		// Check if menu items have been excluded.
		if ($exclusions = $params->get('exclude_menu_items', array()))
		{
			// Get the current menu item.
			$active = $app->getMenu()->getActive();

			if ($active && $active->id && in_array((int) $active->id, (array) $exclusions))
			{
				return true;
			}
		}

		// Check if regular expressions are being used.
		if ($exclusions = $params->get('exclude', ''))
		{
			// Normalize line endings.
			$exclusions = str_replace(array("\r\n", "\r"), "\n", $exclusions);

			// Split them.
			$exclusions = explode("\n", $exclusions);

			// Gets internal URI.
			$internalUri = '/index.php?' . Uri::getInstance()->buildQuery($app->getRouter()->getVars());

			// Loop through each pattern.
			if ($exclusions)
			{
				foreach ($exclusions as $exclusion)
				{
					// Make sure the exclusion has some content
					if ($exclusion !== '')
					{
						// Test both external and internal URI
						if (preg_match('#' . $exclusion . '#i', $cacheKey . ' ' . $internalUri, $match))
						{
							return true;
						}
					}
				}
			}
		}

		// If any pagecache plugins return true for onPageCacheIsExcluded, exclude.
		PluginHelper::importPlugin('pagecache');

		$results = $app->triggerEvent('onPageCacheIsExcluded');

		return in_array(true, $results, true);
	}
}
