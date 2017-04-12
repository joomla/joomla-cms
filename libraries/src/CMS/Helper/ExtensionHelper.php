<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\Exception\MissingException;
use Joomla\Registry\Registry;
use Joomla\CMS\Dispatcher\DispatcherInterface;

/**
 * Extension helper class
 *
 * @since  __DEPLOY_VERSION__
 */
class ExtensionHelper
{
	/**
	 * The extension list cache
	 *
	 * @var    \stdClass[]
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $extensions = null;

	/**
	 * Returns the installed extensions.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getExtensions()
	{
		if (static::$extensions !== null)
		{
			return static::$extensions;
		}

		$loader = function ()
		{
			$db = \JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName(array('extension_id', 'type', 'element', 'folder', 'client_id')))
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('enabled') . ' = 1');
			$db->setQuery($query);

			return $db->loadObjectList();
		};

		/** @var \JCacheControllerCallback $cache */
		$cache = \JFactory::getCache('_system', 'callback');

		try
		{
			static::$extensions = $cache->get($loader, array(), __METHOD__);
		}
		catch (\JCacheException $e)
		{
			static::$extensions = $loader();
		}

		return static::$extensions;
	}
}
