<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_login
 *
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @since       1.5
 */
class ModLoginHelper
{
	/**
	 * Retrieve the url where the user should be returned after logging in
	 *
	 * @param   \Joomla\Registry\Registry  $params  module parameters
	 * @param   string                     $type    return type
	 *
	 * @return string
	 */
	public static function getReturnUrl($params, $type)
	{
		$app  = JFactory::getApplication();
		$item = $app->getMenu()->getItem($params->get($type));

		if ($item)
		{
			$url = 'index.php?Itemid=' . $item->id;
		}
		else
		{
			// Stay on the same page
			$uri = JUri::getInstance();
			$uConfig = JComponentHelper::getParams('com_users');
			if ($uConfig->get('usesecure'))
			{
				// Encrypted login form activated
				// login: switch to HTTPS, logout: switch to HTTP
				$config = JFactory::getConfig();
				if ($type == 'login')
				{
					$uri->setScheme('https');
					$uri->setPort($config->get('https_port'));
				}
				else
				{
					$uri->setScheme('http');
					$uri->setPort($config->get('http_port'));
				}
			}
			$url = $uri->toString();
		}

		return base64_encode($url);
	}

	/**
	 * Returns the current users type
	 *
	 * @return string
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
	 */
	public static function getTwoFactorMethods()
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';

		return UsersHelper::getTwoFactorMethods();
	}
}
