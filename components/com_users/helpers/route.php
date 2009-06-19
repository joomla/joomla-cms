<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Users Route Helper
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @version		1.0
 */
class UsersHelperRoute
{
	/**
	 * Method to get the menu items for the component.
	 *
	 * @access	public
	 * @return	array		An array of menu items.
	 * @since	1.0
	 * @static
	 */
	function &getItems()
	{
		static $items;

		// Get the menu items for this component.
		if (!isset($items))
		{
			// Include the site app in case we are loading this from the admin.
			require_once JPATH_SITE.DS.'includes'.DS.'application.php';

			$menu	= &JSite::getMenu();
			$com	= &JComponentHelper::getComponent('com_users');
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
	 * @access	public
	 * @return	mixed		Integer menu id on success, null on failure.
	 * @since	1.0
	 * @static
	 */
	function getLoginRoute()
	{
		// Get the items.
		$items	= &UsersHelperRoute::getItems();
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
	 * @access	public
	 * @return	mixed		Integer menu id on success, null on failure.
	 * @since	1.0
	 * @static
	 */
	function getProfileRoute()
	{
		// Get the items.
		$items	= &UsersHelperRoute::getItems();
		$itemid	= null;

		// Search for a suitable menu id.
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
	 * @access	public
	 * @return	mixed		Integer menu id on success, null on failure.
	 * @since	1.0
	 * @static
	 */
	function getRegistrationRoute()
	{
		// Get the items.
		$items	= &UsersHelperRoute::getItems();
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
	 * @access	public
	 * @return	mixed		Integer menu id on success, null on failure.
	 * @since	1.0
	 * @static
	 */
	function getRemindRoute()
	{
		// Get the items.
		$items	= &UsersHelperRoute::getItems();
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
	 * @access	public
	 * @return	mixed		Integer menu id on success, null on failure.
	 * @since	1.0
	 * @static
	 */
	function getResendRoute()
	{
		// Get the items.
		$items	= &UsersHelperRoute::getItems();
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
	 * @access	public
	 * @return	mixed		Integer menu id on success, null on failure.
	 * @since	1.0
	 * @static
	 */
	function getResetRoute()
	{
		// Get the items.
		$items	= &UsersHelperRoute::getItems();
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