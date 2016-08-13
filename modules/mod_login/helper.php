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

		// Stay on the same page
		$url = JUri::getInstance()->toString();

		if ($item)
		{
			$lang = '';

			if (JLanguageMultilang::isEnabled() && $item->language !== '*')
			{
				$lang = '&lang=' . $item->language;
			}

			$url = 'index.php?Itemid=' . $item->id . $lang;
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
	 *
	 * @deprecated  4.0  Use JAuthenticationHelper::getTwoFactorMethods() instead.
	 */
	public static function getTwoFactorMethods()
	{
		JLog::add(__METHOD__ . ' is deprecated, use JAuthenticationHelper::getTwoFactorMethods() instead.', JLog::WARNING, 'deprecated');

		return JAuthenticationHelper::getTwoFactorMethods();
	}
}
