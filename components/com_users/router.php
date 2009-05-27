<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

/**
 * Function to build a Users URL route.
 *
 * @param	array	The array of query string values for which to build a route.
 * @return	array	The URL route with segments represented as an array.
 * @version	1.0
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

	// Initialize variables.
	$segments = array();

	// Get the relevant menu items if not loaded.
	if (empty($items))
	{
		// Get all relevant menu items.
		$menu	= & JSite::getMenu();
		$items	= $menu->getItems('component', 'com_users');

		// Build an array of serialized query strings to menu item id mappings.
		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			// Check to see if e have found the resend menu item.
			if (empty($resend) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'resend')) {
				$resend = $items[$i]->id;
			}

			// Check to see if e have found the reset menu item.
			if (empty($reset) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'reset')) {
				$reset = $items[$i]->id;
			}

			// Check to see if e have found the remind menu item.
			if (empty($remind) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'remind')) {
				$remind = $items[$i]->id;
			}

			// Check to see if e have found the login menu item.
			if (empty($login) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'login')) {
				$login = $items[$i]->id;
			}

			// Check to see if e have found the registration menu item.
			if (empty($registration) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'registration')) {
				$registration = $items[$i]->id;
			}

			// Check to see if e have found the profile menu item.
			if (empty($profile) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'profile')) {
				$profile = $items[$i]->id;
			}
		}

		// Set the default menu item to use for com_users if possible.
		if ($profile) {
			$default = $profile;
		}
		elseif ($registration) {
			$default = $registration;
		}
		elseif ($login) {
			$default = $login;
		}
	}

	if (!empty($query['view']))
	{
		switch ($query['view'])
		{
			case 'reset':
				unset ($query['view']);
				$query['Itemid'] = ($reset) ? $reset : $default;
				break;

			case 'resend':
				unset ($query['view']);
				$query['Itemid'] = ($resend) ? $resend : $default;
				break;

			case 'remind':
				unset ($query['view']);
				$query['Itemid'] = ($remind) ? $remind : $default;
				break;

			case 'login':
				unset ($query['view']);
				$query['Itemid'] = ($login) ? $login : $default;
				break;

			case 'registration':
				unset ($query['view']);
				$query['Itemid'] = ($registration) ? $registration : $default;
				break;

			default:
			case 'profile':
				unset ($query['view']);

				// Only append the member id if not "me".
				$user = & JFactory::getUser();
				if (!empty($query['member_id']) && ($query['member_id'] != $user->id)) {
					$segments[] = $query['member_id'];
				}
				unset ($query['member_id']);

				if (!empty($query['layout'])) {
					$segments[] = $query['layout'];
				}
				unset ($query['layout']);

				$query['Itemid'] = ($profile) ? $profile : $default;
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
 * @version	1.0
 */
function UsersParseRoute($segments)
{
	// Initialize variables.
	$vars = array();

	// Only run routine if there are segments to parse.
	if (count($segments) < 1) {
		return;
	}

	// Get the package from the route segments.
	$member = array_pop($segments);

	if (!is_numeric($member)) {
		$vars['view'] = 'profile';
		$vars['layout'] = $member;
		return $vars;
	}

	// Get the package id from the packages table by alias.
	$db = & JFactory::getDbo();
	$db->setQuery(
		'SELECT `id`' .
		' FROM `#__users`' .
		' WHERE `id` = '.$db->quote($member)
	);
	$memberId = $db->loadResult();

	// Set the package id if present.
	if ($memberId)
	{
		// Set the package id.
		$vars['member_id'] = intval($memberId);

		// Set the view to package if not already set.
		if (empty($vars['view'])) {
			$vars['view'] = 'profile';
		}
	}
	else {
		JError::raiseError(404, JText::_('Resource not found.'));
	}

	return $vars;
}
