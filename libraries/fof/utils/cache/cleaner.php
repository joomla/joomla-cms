<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  utils
 * @copyright   Copyright (C) 2010 - 2015 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('FOF_INCLUDED') or die;

/**
 * A utility class to help you quickly clean the Joomla! cache
 */
class FOFUtilsCacheCleaner
{
	/**
	 * Clears the com_modules and com_plugins cache. You need to call this whenever you alter the publish state or
	 * parameters of a module or plugin from your code.
	 *
	 * @return  void
	 */
	public static function clearPluginsAndModulesCache()
	{
		self::clearPluginsCache();
		self::clearModulesCache();
	}

	/**
	 * Clears the com_plugins cache. You need to call this whenever you alter the publish state or parameters of a
	 * plugin from your code.
	 *
	 * @return  void
	 */
	public static function clearPluginsCache()
	{
		self::clearCacheGroups(array('com_plugins'), array(0,1));
	}

	/**
	 * Clears the com_modules cache. You need to call this whenever you alter the publish state or parameters of a
	 * module from your code.
	 *
	 * @return  void
	 */
	public static function clearModulesCache()
	{
		self::clearCacheGroups(array('com_modules'), array(0,1));
	}

	/**
	 * Clears the specified cache groups.
	 *
	 * @param   array $clearGroups    Which cache groups to clear. Usually this is com_yourcomponent to clear your
	 *                                component's cache.
	 * @param   array   $cacheClients Which cache clients to clear. 0 is the back-end, 1 is the front-end. If you do not
	 *                                specify anything, both cache clients will be cleared.
	 *
	 * @return  void
	 */
	public static function clearCacheGroups(array $clearGroups, array $cacheClients = array(0, 1))
	{
		$conf = JFactory::getConfig();

		foreach ($clearGroups as $group)
		{
			foreach ($cacheClients as $client_id)
			{
				try
				{
					$options = array(
						'defaultgroup' => $group,
						'cachebase' => ($client_id) ? JPATH_ADMINISTRATOR . '/cache' : $conf->get('cache_path', JPATH_SITE . '/cache')
					);

					$cache = JCache::getInstance('callback', $options);
					$cache->clean();
				}
				catch (Exception $e)
				{
					// suck it up
				}
			}
		}
	}
} 