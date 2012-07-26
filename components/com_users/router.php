<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Function to build a Users URL route.
 *
 * @param	array	The array of query string values for which to build a route.
 * @return	array	The URL route with segments represented as an array.
 * @since	1.5
 */
function UsersBuildRoute(&$query)
{
	// Declare static variables.
	static $items;
	static $default;
	static $registration;
	static $profile;
	static $login;
	static $remind;
	static $resend;
	static $reset;

	// Initialise variables.
	$segments = array();

	// Get the relevant menu items if not loaded.
	if (empty($items)) {
		// Get all relevant menu items.
		$app	= JFactory::getApplication();
		$menu	= $app->getMenu();
		$items	= $menu->getItems('component', 'com_users');

		// Build an array of serialized query strings to menu item id mappings.
		for ($i = 0, $n = count($items); $i < $n; $i++) {
			// Check to see if we have found the resend menu item.
			if (empty($resend) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'resend')) {
				$resend = $items[$i]->id;
			}

			// Check to see if we have found the reset menu item.
			if (empty($reset) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'reset')) {
				$reset = $items[$i]->id;
			}

			// Check to see if we have found the remind menu item.
			if (empty($remind) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'remind')) {
				$remind = $items[$i]->id;
			}

			// Check to see if we have found the login menu item.
			if (empty($login) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'login')) {
				$login = $items[$i]->id;
			}

			// Check to see if we have found the registration menu item.
			if (empty($registration) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'registration')) {
				$registration = $items[$i]->id;
			}

			// Check to see if we have found the profile menu item.
			if (empty($profile) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'profile')) {
			$profile = $items[$i]->id;
			}
		}

		// Set the default menu item to use for com_users if possible.
		if ($profile) {
			$default = $profile;
		} elseif ($registration) {
			$default = $registration;
		} elseif ($login) {
			$default = $login;
		}
	}

	if (!empty($query['view'])) {
		switch ($query['view']) {
			case 'reset':
				if ($query['Itemid'] = $reset) {
					unset ($query['view']);
				} else {
					$query['Itemid'] = $default;
				}
				break;

			case 'resend':
				if ($query['Itemid'] = $resend) {
					unset ($query['view']);
				} else {
					$query['Itemid'] = $default;
				}
				break;

			case 'remind':
				if ($query['Itemid'] = $remind) {
					unset ($query['view']);
				} else {
					$query['Itemid'] = $default;
				}
				break;

			case 'login':
				if ($query['Itemid'] = $login) {
					unset ($query['view']);
				} else {
					$query['Itemid'] = $default;
				}
				break;

			case 'registration':
				if ($query['Itemid'] = $registration) {
					unset ($query['view']);
				} else {
					$query['Itemid'] = $default;
				}
				break;

			default:
			case 'profile':
				if (!empty($query['view'])) {
					$segments[] = $query['view'];
				}
				unset ($query['view']);
				if ($query['Itemid'] = $profile) {
					unset ($query['view']);
				} else {
					$query['Itemid'] = $default;
				}

				// Only append the user id if not "me".
				$user = JFactory::getUser();
				if (!empty($query['user_id']) && ($query['user_id'] != $user->id)) {
					$segments[] = $query['user_id'];
				}
				unset ($query['user_id']);

				break;
		}
	}

	return $segments;
}

/**
 * Function to parse a Users URL route.
 *
 * @param	array	The URL route with segments represented as an array.
 * @return	array	The array of variables to set in the request.
 * @since	1.5
 */
function UsersParseRoute($segments)
{
	// Initialise variables.
	$vars = array();

	// Only run routine if there are segments to parse.
	if (count($segments) < 1) {
		return;
	}

	// Get the package from the route segments.
	$userId = array_pop($segments);

	if (!is_numeric($userId)) {
		$vars['view'] = 'profile';
		return $vars;
	}

	if (is_numeric($userId)) {
		// Get the package id from the packages table by alias.
		$db = JFactory::getDbo();
		$db->setQuery(
			'SELECT '.$db->quoteName('id') .
			' FROM '.$db->quoteName('#__users') .
			' WHERE '.$db->quoteName('id').' = '.(int) $userId
		);
		$userId = $db->loadResult();
	}

	// Set the package id if present.
	if ($userId) {
		// Set the package id.
		$vars['user_id'] = (int) $userId;

		// Set the view to package if not already set.
		if (empty($vars['view'])) {
			$vars['view'] = 'profile';
		}
	} else {
		JError::raiseError(404, JText::_('JGLOBAL_RESOURCE_NOT_FOUND'));
	}

	return $vars;
}
