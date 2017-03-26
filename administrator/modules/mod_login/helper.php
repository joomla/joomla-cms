<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
				return strcmp($a['value'], $b['value']);
			}
		);

		// Fix wrongly set parentheses in RTL languages
		if (JFactory::getLanguage()->isRtl())
		{
			foreach ($languages as &$language)
			{
				$language['text'] = $language['text'] . '&#x200E;';
			}
		}

		array_unshift($languages, JHtml::_('select.option', '', JText::_('JDEFAULTLANGUAGE')));

		return JHtml::_('select.genericlist', $languages, 'lang', ' class="custom-select"', 'value', 'text', null);
	}

	/**
	 * Get the redirect URI after login.
	 *
	 * @return  string
	 */
	public static function getReturnUri()
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
	 *
	 * @deprecated  4.0  Use JAuthenticationHelper::getTwoFactorMethods() instead.
	 */
	public static function getTwoFactorMethods()
	{
		try
		{
			JLog::add(
				sprintf('%s() is deprecated, use JAuthenticationHelper::getTwoFactorMethods() instead.', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		return JAuthenticationHelper::getTwoFactorMethods();
	}
}
