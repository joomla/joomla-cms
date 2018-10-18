<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

/**
 * Users Route Helper
 *
 * @since       1.6
 * @deprecated  4.0
 */
class UsersHelperRoute
{
	/**
	 * Method to get the menu items for the component.
	 *
	 * @return  array  	An array of menu items.
	 *
	 * @since       1.6
	 * @deprecated  4.0
	 * @throws      \Exception
	 */
	public static function &getItems()
	{
		static $items;

		// Get the menu items for this component.
		if (!isset($items))
		{
			$component = ComponentHelper::getComponent('com_users');
			$items     = Factory::getApplication()->getMenu()->getItems('component_id', $component->id);

			// If no items found, set to empty array.
			if (!$items)
			{
				$items = array();
			}
		}

		return $items;
	}

	/**
	 * Method to get a route configuration for the login view.
	 *
	 * @return  mixed  	Integer menu id on success, null on failure.
	 *
	 * @since       1.6
	 * @deprecated  4.0
	 */
	public static function getLoginRoute()
	{
		// Get the items.
		$items  = self::getItems();

		// Search for a suitable menu id.
		foreach ($items as $item)
		{
			if (isset($item->query['view']) && $item->query['view'] === 'login')
			{
				return $item->id;
			}
		}

		return null;
	}

	/**
	 * Method to get a route configuration for the profile view.
	 *
	 * @return  mixed  	Integer menu id on success, null on failure.
	 *
	 * @since       1.6
	 * @deprecated  4.0
	 */
	public static function getProfileRoute()
	{
		// Get the items.
		$items  = self::getItems();

		// Search for a suitable menu id.
		// Menu link can only go to users own profile.

		foreach ($items as $item)
		{
			if (isset($item->query['view']) && $item->query['view'] === 'profile')
			{
				return $item->id;
			}
		}

		return null;
	}

	/**
	 * Method to get a route configuration for the registration view.
	 *
	 * @return  mixed  	Integer menu id on success, null on failure.
	 *
	 * @since       1.6
	 * @deprecated  4.0
	 */
	public static function getRegistrationRoute()
	{
		// Get the items.
		$items  = self::getItems();

		// Search for a suitable menu id.
		foreach ($items as $item)
		{
			if (isset($item->query['view']) && $item->query['view'] === 'registration')
			{
				return $item->id;
			}
		}

		return null;
	}

	/**
	 * Method to get a route configuration for the remind view.
	 *
	 * @return  mixed  	Integer menu id on success, null on failure.
	 *
	 * @since       1.6
	 * @deprecated  4.0
	 */
	public static function getRemindRoute()
	{
		// Get the items.
		$items  = self::getItems();

		// Search for a suitable menu id.
		foreach ($items as $item)
		{
			if (isset($item->query['view']) && $item->query['view'] === 'remind')
			{
				return $item->id;
			}
		}

		return null;
	}

	/**
	 * Method to get a route configuration for the resend view.
	 *
	 * @return  mixed  	Integer menu id on success, null on failure.
	 *
	 * @since       1.6
	 * @deprecated  4.0
	 */
	public static function getResendRoute()
	{
		// Get the items.
		$items  = self::getItems();

		// Search for a suitable menu id.
		foreach ($items as $item)
		{
			if (isset($item->query['view']) && $item->query['view'] === 'resend')
			{
				return $item->id;
			}
		}

		return null;
	}

	/**
	 * Method to get a route configuration for the reset view.
	 *
	 * @return  mixed  	Integer menu id on success, null on failure.
	 *
	 * @since       1.6
	 * @deprecated  4.0
	 */
	public static function getResetRoute()
	{
		// Get the items.
		$items  = self::getItems();

		// Search for a suitable menu id.
		foreach ($items as $item)
		{
			if (isset($item->query['view']) && $item->query['view'] === 'reset')
			{
				return $item->id;
			}
		}

		return null;
	}
}
