<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Users Login Helper
 *
 * @package     Joomla.Site
 * @subpackage  com_users
 * @since       3.4
 */
class UsersHelperLogin
{
	/**
	 * Retrieve the url where the user should be returned after logging in
	 *
	 * @param   JRegistry  $params  view parameters
	 * @param   string     $type    return type
	 *
	 * @return string 
	 *
	 * @since  3.4
	 */
	public static function getReturnURL($params, $type)
	{
		$app	= JFactory::getApplication();
		$router = $app::getRouter();
		$url    = null;
		$itemid = $params->get($type);

		// B/C checks
		// @deprecated with 4.0
		// @note if we can break B/C You can remove $typeBC & $urlBc and the following if statement
		$typeBc = $type . '_redirect_url';
		$urlBc  = $params->get($typeBC);

		// If we have a old URL use it.
		if ($urlBc != '')
		{
			$itemid = $urlBc;
		}		

		if (is_numeric($itemid))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('link'))
				->from($db->quoteName('#__menu'))
				->where($db->quoteName('published') . '=1')
				->where($db->quoteName('id') . '=' . $db->quote($itemid));

			$db->setQuery($query);

			if ($link = $db->loadResult())
			{
				if ($router->getMode() == JROUTER_MODE_SEF)
				{
					$url = 'index.php?Itemid=' . $itemid;
				}
				else
				{
					$url = $link . '&Itemid=' . $itemid;
				}
			}
		}

		// For B/C reasons
		// The value in the param is not a number and not null
		// so the param store a old value like a URL and it will used.
		// @deprecated with 4.0
		// @note if we can break B/C You can remove this if statement
		if ($itemid != '')
		{
			$url = $itemid;
		}

		if (!$url)
		{
			// Stay on the same page
			$uri  = clone JUri::getInstance();
			$vars = $router->parse($uri);
			unset($vars['lang']);

			if ($router->getMode() == JROUTER_MODE_SEF)
			{
				if (isset($vars['Itemid']))
				{
					$itemid = $vars['Itemid'];
					$menu   = $app->getMenu();
					$item   = $menu->getItem($itemid);
					unset($vars['Itemid']);

					if (isset($item) && $vars == $item->query)
					{
						$url = 'index.php?Itemid=' . $itemid;
					}
					else
					{
						$url = 'index.php?' . JUri::buildQuery($vars) . '&Itemid=' . $itemid;
					}
				}
				else
				{
					$url = 'index.php?' . JUri::buildQuery($vars);
				}
			}
			else
			{
				$url = 'index.php?' . JUri::buildQuery($vars);
			}
		}

		// Set the current url to the B/C hidden field.
		// @deprecated with 4.0
		// @note if we can break B/C You can remove the next line.
		$params->set($typeBc, $url);

		return base64_encode($url);
	}

	/**
	 * Returns the current users type
	 *
	 * @return string
	 *
	 * @since  3.4
	 */
	public static function getType()
	{
		$user = JFactory::getUser();

		return (!$user->get('guest')) ? 'logout' : 'login';
	}

	/**
	 * Get list of available two factor methods
	 *
	 * @return array
	 *
	 * @since  3.4
	 */
	public static function getTwoFactorMethods()
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';

		return UsersHelper::getTwoFactorMethods();
	}
}
