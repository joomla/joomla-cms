<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Legacy routing rules class from com_users
 *
 * @since       3.6
 * @deprecated  4.0
 */
class UsersRouterRulesLegacy implements JComponentRouterRulesInterface
{
	/**
	 * Constructor for this legacy router
	 *
	 * @param   JComponentRouterAdvanced  $router  The router this rule belongs to
	 *
	 * @since       3.6
	 * @deprecated  4.0
	 */
	public function __construct($router)
	{
		$this->router = $router;
	}

	/**
	 * Preprocess the route for the com_users component
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  void
	 *
	 * @since       3.6
	 * @deprecated  4.0
	 */
	public function preprocess(&$query)
	{
	}

	/**
	 * Build the route for the com_users component
	 *
	 * @param   array  &$query     An array of URL arguments
	 * @param   array  &$segments  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @return  void
	 *
	 * @since       3.6
	 * @deprecated  4.0
	 */
	public function build(&$query, &$segments)
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

		// Get the relevant menu items if not loaded.
		if (empty($items))
		{
			// Get all relevant menu items.
			$items = $this->router->menu->getItems('component', 'com_users');

			// Build an array of serialized query strings to menu item id mappings.
			foreach ($items as $item)
			{
				if (empty($item->query['view']))
				{
					continue;
				}

				// Check to see if we have found the resend menu item.
				if (empty($resend) && $item->query['view'] === 'resend')
				{
					$resend = $item->id;

					continue;
				}

				// Check to see if we have found the reset menu item.
				if (empty($reset) && $item->query['view'] === 'reset')
				{
					$reset = $item->id;

					continue;
				}

				// Check to see if we have found the remind menu item.
				if (empty($remind) && $item->query['view'] === 'remind')
				{
					$remind = $item->id;

					continue;
				}

				// Check to see if we have found the login menu item.
				if (empty($login) && $item->query['view'] === 'login' && (empty($item->query['layout']) || $item->query['layout'] === 'default'))
				{
					$login = $item->id;

					continue;
				}

				// Check to see if we have found the registration menu item.
				if (empty($registration) && $item->query['view'] === 'registration')
				{
					$registration = $item->id;

					continue;
				}

				// Check to see if we have found the profile menu item.
				if (empty($profile) && $item->query['view'] === 'profile')
				{
					$profile = $item->id;
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
						unset($query['view']);
					}
					else
					{
						$query['Itemid'] = $default;
					}
					break;

				case 'resend':
					if ($query['Itemid'] = $resend)
					{
						unset($query['view']);
					}
					else
					{
						$query['Itemid'] = $default;
					}
					break;

				case 'remind':
					if ($query['Itemid'] = $remind)
					{
						unset($query['view']);
					}
					else
					{
						$query['Itemid'] = $default;
					}
					break;

				case 'login':
					if ($query['Itemid'] = $login)
					{
						unset($query['view']);
					}
					else
					{
						$query['Itemid'] = $default;
					}
					break;

				case 'registration':
					if ($query['Itemid'] = $registration)
					{
						unset($query['view']);
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

					unset($query['view']);

					if ($query['Itemid'] = $profile)
					{
						unset($query['view']);
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

					unset($query['user_id']);

					break;
			}
		}

		$total = count($segments);

		for ($i = 0; $i < $total; $i++)
		{
			$segments[$i] = str_replace(':', '-', $segments[$i]);
		}
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 * @param   array  &$vars      The URL attributes to be used by the application.
	 *
	 * @return  void
	 *
	 * @since       3.6
	 * @deprecated  4.0
	 */
	public function parse(&$segments, &$vars)
	{
		$total = count($segments);

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

			return;
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
	}
}
