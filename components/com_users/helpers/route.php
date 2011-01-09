<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Users Route Helper
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @since		1.6
 */
class UsersHelperRoute
{
	/**
	 * Method to get the menu items for the component.
	 *
	 * @return	array		An array of menu items.
	 * @since	1.6
	 */
	public static function &getItems()
	{
		static $items;

		// Get the menu items for this component.
		if (!isset($items)) {
			// Include the site app in case we are loading this from the admin.
			require_once JPATH_SITE.'/includes/application.php';

			$app	= JFactory::getApplication();
			$menu	= $app->getMenu();
			$com	= JComponentHelper::getComponent('com_users');
			$items	= $menu->getItems('component_id', $com->id);

			// If no items found, set to empty array.
			if (!$items) {
				$items = array();
			}
		}

		return $items;
	}

	/**
	 * Method to get a route configuration for the login view.
	 *
	 * @return	mixed		Integer menu id on success, null on failure.
	 * @since	1.6
	 * @static
	 */
	public static function getLoginRoute()
	{
		// Get the items.
		$items	= self::getItems();
		$itemid	= null;

		// Search for a suitable menu id.
		foreach ($items as $item) {
			if (isset($item->query['view']) && $item->query['view'] === 'login') {
				$itemid = $item->id;
				break;
			}
		}

		return $itemid;
	}

	/**
	 * Method to get a route configuration for the profile view.
	 *
	 * @return	mixed		Integer menu id on success, null on failure.
	 * @since	1.6
	 */
	public static function getProfileRoute()
	{
		// Get the items.
		$items	= self::getItems();
		$itemid	= null;

		// Search for a suitable menu id.
		//Menu link can only go to users own profile.

		foreach ($items as $item) {
			if (isset($item->query['view']) && $item->query['view'] === 'profile') {
				$itemid = $item->id;
				break;
			}

		}
		return $itemid;
	}

	/**
	 * Method to get a route configuration for the registration view.
	 *
	 * @return	mixed		Integer menu id on success, null on failure.
	 * @since	1.6
	 */
	public static function getRegistrationRoute()
	{
		// Get the items.
		$items	= self::getItems();
		$itemid	= null;

		// Search for a suitable menu id.
		foreach ($items as $item) {
			if (isset($item->query['view']) && $item->query['view'] === 'registration') {
				$itemid = $item->id;
				break;
			}
		}

		return $itemid;
	}

	/**
	 * Method to get a route configuration for the remind view.
	 *
	 * @return	mixed		Integer menu id on success, null on failure.
	 * @since	1.6
	 */
	public static function getRemindRoute()
	{
		// Get the items.
		$items	= self::getItems();
		$itemid	= null;

		// Search for a suitable menu id.
		foreach ($items as $item) {
			if (isset($item->query['view']) && $item->query['view'] === 'remind') {
				$itemid = $item->id;
				break;
			}
		}

		return $itemid;
	}

	/**
	 * Method to get a route configuration for the resend view.
	 *
	 * @return	mixed		Integer menu id on success, null on failure.
	 * @since	1.6
	 */
	public static function getResendRoute()
	{
		// Get the items.
		$items	= self::getItems();
		$itemid	= null;

		// Search for a suitable menu id.
		foreach ($items as $item) {
			if (isset($item->query['view']) && $item->query['view'] === 'resend') {
				$itemid = $item->id;
				break;
			}
		}

		return $itemid;
	}

	/**
	 * Method to get a route configuration for the reset view.
	 *
	 * @return	mixed		Integer menu id on success, null on failure.
	 * @since	1.6
	 */
	function getResetRoute()
	{
		// Get the items.
		$items	= self::getItems();
		$itemid	= null;

		// Search for a suitable menu id.
		foreach ($items as $item) {
			if (isset($item->query['view']) && $item->query['view'] === 'reset') {
				$itemid = $item->id;
				break;
			}
		}

		return $itemid;
	}
}