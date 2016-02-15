<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Routing class from com_users
 *
 * @since  3.2
 */
class UsersRouter extends JComponentRouterBase
{
	/**
	 * Build the route for the com_users component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.3
	 */
	public function build(&$query)
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

		$segments = array();

		// Get the relevant menu items if not loaded.
		if (empty($items))
		{
			// Get all relevant menu items.
			$items = $this->menu->getItems('component', 'com_users');

			// Build an array of serialized query strings to menu item id mappings.
			for ($i = 0, $n = count($items); $i < $n; $i++)
			{
				// Check to see if we have found the resend menu item.
				if (empty($resend) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'resend'))
				{
					$resend = $items[$i]->id;
				}

				// Check to see if we have found the reset menu item.
				if (empty($reset) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'reset'))
				{
					$reset = $items[$i]->id;
				}

				// Check to see if we have found the remind menu item.
				if (empty($remind) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'remind'))
				{
					$remind = $items[$i]->id;
				}

				// Check to see if we have found the login menu item.
				if (empty($login) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'login'))
				{
					$login = $items[$i]->id;
				}

				// Check to see if we have found the registration menu item.
				if (empty($registration) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'registration'))
				{
					$registration = $items[$i]->id;
				}

				// Check to see if we have found the profile menu item.
				if (empty($profile) && !empty($items[$i]->query['view']) && ($items[$i]->query['view'] == 'profile'))
				{
					$profile = $items[$i]->id;
				}
			}

			// Set the default menu item to use for com_users if possible.
			if ($profile)
			{
				$default = $profile;
			}
			elseif ($registration)
			{
				$default = $registration;
			}
			elseif ($login)
			{
				$default = $login;
			}
		}

		if (!empty($query['view']))
		{
			switch ($query['view'])
			{
				case 'reset':
					if ($query['Itemid'] = $reset)
					{
						unset ($query['view']);
					}
					else
					{
						$query['Itemid'] = $default;
					}
					break;

				case 'resend':
					if ($query['Itemid'] = $resend)
					{
						unset ($query['view']);
					}
					else
					{
						$query['Itemid'] = $default;
					}
					break;

				case 'remind':
					if ($query['Itemid'] = $remind)
					{
						unset ($query['view']);
					}
					else
					{
						$query['Itemid'] = $default;
					}
					break;

				case 'login':
					if ($query['Itemid'] = $login)
					{
						unset ($query['view']);
					}
					else
					{
						$query['Itemid'] = $default;
					}
					break;

				case 'registration':
					if ($query['Itemid'] = $registration)
					{
						unset ($query['view']);
					}
					else
					{
						$query['Itemid'] = $default;
					}
					break;

				default:
				case 'profile':
					if (!empty($query['view']))
					{
						$segments[] = $query['view'];
					}

					unset ($query['view']);

					if ($query['Itemid'] = $profile)
					{
						unset ($query['view']);
					}
					else
					{
						$query['Itemid'] = $default;
					}

					// Only append the user id if not "me".
					$user = JFactory::getUser();

					if (!empty($query['user_id']) && ($query['user_id'] != $user->id))
					{
						$segments[] = $query['user_id'];
					}

					unset ($query['user_id']);

					break;
			}
		}

		$total = count($segments);

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   3.3
	 */
	public function parse(&$segments)
	{
		$total = count($segments);
		$vars = array();

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
		}

		// Only run routine if there are segments to parse.
		if (count($segments) < 1)
		{
			return;
		}

		// Get the package from the route segments.
		$userId = array_pop($segments);

		if (!is_numeric($userId))
		{
			$vars['view'] = 'profile';

			return $vars;
		}

		if (is_numeric($userId))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__users'))
				->where($db->quoteName('id') . ' = ' . (int) $userId);
			$db->setQuery($query);
			$userId = $db->loadResult();
		}

		// Set the package id if present.
		if ($userId)
		{
			// Set the package id.
			$vars['user_id'] = (int) $userId;

			// Set the view to package if not already set.
			if (empty($vars['view']))
			{
				$vars['view'] = 'profile';
			}
		}
		else
		{
			JError::raiseError(404, JText::_('JGLOBAL_RESOURCE_NOT_FOUND'));
		}

		return $vars;
	}
}

/**
 * Users router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  REQUEST query
 *
 * @return  array  Segments of the SEF url
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function usersBuildRoute(&$query)
{
	$router = new UsersRouter;

	return $router->build($query);
}

/**
 * Convert SEF URL segments into query variables
 *
 * @param   array  $segments  Segments in the current URL
 *
 * @return  array  Query variables
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function usersParseRoute($segments)
{
	$router = new UsersRouter;

	return $router->parse($segments);
}
