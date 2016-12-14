<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Authentication
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Authentication helper class
 *
 * @since  3.6.3
 */
abstract class JAuthenticationHelper
{
	/**
	 * Get the Two Factor Authentication Methods available.
	 *
	 * @return  array  Two factor authentication methods.
	 *
	 * @since   3.6.3
	 */
	public static function getTwoFactorMethods()
	{
		/**
		 * Get all the Two Factor Authentication plugins.
		 *
		 * We also load the authentication plugins because they can register custom two factor authentication handlers.
		 * For example, when you have a social login you must not check two factor authentication since you are no
		 * longer submitting a username and password but making an API call to a remote server.
		 */
		JPluginHelper::importPlugin('twofactorauth');
		JPluginHelper::importPlugin('authentication');

		// Trigger onUserTwofactorIdentify event and return the two factor enabled plugins.
		$identities = JEventDispatcher::getInstance()->trigger('onUserTwofactorIdentify', array());

		// Generate array with two factor auth methods.
		$options = array(
			JHtml::_('select.option', 'none', JText::_('JGLOBAL_OTPMETHOD_NONE'), 'value', 'text'),
		);

		if (!empty($identities))
		{
			foreach ($identities as $identity)
			{
				if (!is_object($identity))
				{
					continue;
				}

				$options[] = JHtml::_('select.option', $identity->method, $identity->title, 'value', 'text');
			}
		}

		return $options;
	}

	/**
	 * Get the additional login form field definitions.
	 *
	 * @param   string  $loginUrl    The URL or menu item ID to return to after successfully logging in
	 * @param   string  $failureUrl  The URL or menu item ID to return to after failing to log in
	 *
	 * @return  array  Additional login form field definitions
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getUserLoginFormFields($loginUrl = null, $failureUrl = null)
	{
		$loginUrl   = self::cleanUpReturnUrl($loginUrl);
		$failureUrl = self::cleanUpReturnUrl($failureUrl);

		/**
		 * All three types of plugins can define custom login fields.
		 *
		 * While custom login fields are primarily used for social login, opening up the implementation to Two Factor
		 * Authentication and User plugins lets us implement richer login interactions.
		 */
		JPluginHelper::importPlugin('twofactorauth');
		JPluginHelper::importPlugin('authentication');
		JPluginHelper::importPlugin('user');

		$fieldDefinitions = JEventDispatcher::getInstance()->trigger('onUserLoginFormFields', array($loginUrl, $failureUrl));

		$fields = array();

		if (!empty($fieldDefinitions))
		{
			foreach ($fieldDefinitions as $fieldDefinition)
			{
				if (!is_array($fieldDefinition))
				{
					continue;
				}

				foreach ($fieldDefinition as $field)
				{
					if (!is_object($field) || !($field instanceof JAuthenticationFieldInterface))
					{
						continue;
					}

					$fields[] = $field;
				}
			}
		}

		return $fields;
	}

	/**
	 * Cleans up a return URL. If it's a URL it makes sure it's an internal URL. If it's a menu item ID it creates the
	 * absolute URL pointing to it. If the URL is empty or non-internal the current URL is used instead.
	 *
	 * @param   string  $return  The return URL to clean up
	 *
	 * @return  string  The cleaned up URL
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected static function cleanUpReturnUrl($return)
	{
		// Get the default URL (current page)
		$defaultUrl = JUri::getInstance()->toString();

		// Is the return URL base64-encoded by any chance?
		$returnDecoded = @base64_decode($return);

		if ($returnDecoded !== false)
		{
			$return = $returnDecoded;
		}

		// Do we have a URL (as opposed to a menu item)?
		if (!is_numeric($return))
		{
			// Don't redirect to an external URL.
			if (!JUri::isInternal($return))
			{
				return $defaultUrl;
			}

			if (strpos($return, 'index.php?') !== false)
			{
				$return = JRoute::_($return);
			}

			return empty($return) ? $defaultUrl : $return;
		}

		// We have a menu item. We must translate it to a URL. For this, we need the language ID suffix.
		$lang = '';

		if (JLanguageMultilang::isEnabled())
		{

			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
						->select('language')
						->from($db->quoteName('#__menu'))
						->where('client_id = 0')
						->where('id =' . $return);

			$db->setQuery($query);

			try
			{
				$language = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				return $defaultUrl;
			}

			if ($language !== '*')
			{
				$lang = '&lang=' . $language;
			}
		}

		// Construct and return the URL
		return JRoute::_('index.php?Itemid=' . $return . $lang);
	}
}
