<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_login
 *
 * @since  1.6
 */
abstract class ModLoginHelper
{
	/**
	 * Get an HTML select list of the available languages.
	 *
	 * @return  string
	 */
	public static function getLanguageList()
	{
		$languages = JLanguageHelper::createLanguageList(null, JPATH_ADMINISTRATOR, false, true);

		if (count($languages) <= 1)
		{
			return '';
		}

		usort(
			$languages,
			function ($a, $b)
			{
				return strcmp($a["value"], $b["value"]);
			}
		);

		// Fix wrongly set parentheses in RTL languages
		if (JFactory::getLanguage()->isRTL())
		{
			foreach ($languages as &$language)
			{
				$language['text'] = $language['text'] . '&#x200E;';
			}
		}

		array_unshift($languages, JHtml::_('select.option', '', JText::_('JDEFAULTLANGUAGE')));

		return JHtml::_('select.genericlist', $languages, 'lang', ' class="advancedSelect"', 'value', 'text', null);
	}

	/**
	 * Get the redirect URI after login.
	 *
	 * @return  string
	 */
	public static function getReturnURI()
	{
		$uri    = JUri::getInstance();
		$return = 'index.php' . $uri->toString(array('query'));

		if ($return != 'index.php?option=com_login')
		{
			return base64_encode($return);
		}
		else
		{
			return base64_encode('index.php');
		}
	}

	/**
	 * Creates a list of two factor authentication methods used in com_users
	 * on user view
	 *
	 * @return  array
	 */
	public static function getTwoFactorMethods()
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';

		return UsersHelper::getTwoFactorMethods();
	}
}
