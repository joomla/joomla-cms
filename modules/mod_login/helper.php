<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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

		$usersConfig = JComponentHelper::getParams('com_users');

		if ($item)
		{
			// Continue with another page
			$lang = '';

			if (JLanguageMultilang::isEnabled() && $item->language !== '*')
			{
				$lang = '&lang=' . $item->language;
			}

			// Build complete URL and switch scheme if "usesecure" is set
			$url = JUri::base() . 'index.php?Itemid=' . $item->id . $lang;
			$url = JUri::siteScheme($url, $usersConfig->get('usesecure'));
		}
		else
		{
			// Stay on the same page, but switch scheme if "usesecure" is set
			$url = JUri::siteScheme(JUri::getInstance(), $usersConfig->get('usesecure'))->toString();
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
