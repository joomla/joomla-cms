<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

\defined('JPATH_PLATFORM') or die;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * Authentication helper class
 *
 * @since  3.6.3
 */
abstract class AuthenticationHelper
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
		// Get all the Two Factor Authentication plugins.
		PluginHelper::importPlugin('twofactorauth');

		// Trigger onUserTwofactorIdentify event and return the two factor enabled plugins.
		$identities = Factory::getApplication()->triggerEvent('onUserTwofactorIdentify', array());

		// Generate array with two factor auth methods.
		$options = array(
			HTMLHelper::_('select.option', 'none', Text::_('JGLOBAL_OTPMETHOD_NONE'), 'value', 'text'),
		);

		if (!empty($identities))
		{
			foreach ($identities as $identity)
			{
				if (!\is_object($identity))
				{
					continue;
				}

				$options[] = HTMLHelper::_('select.option', $identity->method, $identity->title, 'value', 'text');
			}
		}

		return $options;
	}

	/**
	 * Get additional login buttons to add in a login module. These buttons can be used for authentication methods
	 * external to Joomla such as WebAuthn, login with social media providers, login with third party providers or even
	 * login with third party Single Sign On (SSO) services.
	 *
	 * Button definitions are returned by the onUserLoginButtons event handlers in plugins. By default, only system and
	 * user plugins are taken into account. The former because they are always loaded. The latter are explicitly loaded
	 * in this method.
	 *
	 * The onUserLoginButtons event handlers must conform to the following method definition:
	 *
	 * public function onUserLoginButtons(string $formId): array
	 *
	 * The onUserLoginButtons event handlers must return a simple array containing 0 or more button definitions.
	 *
	 * Each button definition is a hash array with the following keys:
	 *
	 * - label      The translation string used as the label and title of the button
	 * - onclick    The onclick attribute, used to fire a JavaScript event
	 * - id         The HTML ID of the button.
	 * - icon       [optional] A CSS class for an optional icon displayed before the label; has precedence over 'image'
	 * - image      [optional] An image path for an optional icon displayed before the label
	 * - class      [optional] CSS class(es) to be added to the button
	 *
	 * @param   string  $formId           The HTML ID of the login form container.
	 *
	 * @return  array  Button definitions.
	 *
	 * @since 4.0.0
	 */
	public static function getLoginButtons(string $formId): array
	{
		// Get all the User plugins.
		PluginHelper::importPlugin('user');

		// Trigger the onUserLoginButtons event and return the button definitions.
		try
		{
			/** @var CMSApplication $app */
			$app = Factory::getApplication();
		}
		catch (Exception $e)
		{
			return [];
		}

		$results        = $app->triggerEvent('onUserLoginButtons', [$formId]);
		$buttons        = [];

		foreach ($results as $result)
		{
			// Did we get garbage back from the plugin?
			if (!is_array($result) || empty($result))
			{
				continue;
			}

			// Did the developer accidentally return a single button definition instead of an array?
			if (array_key_exists('label', $result))
			{
				$result = [$result];
			}

			// Process each button, making sure it conforms to the required definition
			foreach ($result as $item)
			{
				// Force mandatory fields
				$defaultButtonDefinition = [
					'label'   => '',
					'icon'    => '',
					'image'   => '',
					'class'   => '',
					'id'      => '',
					'onclick' => '',
				];

				$button = array_merge($defaultButtonDefinition, $item);

				// Unset anything that doesn't conform to a button definition
				foreach (array_keys($button) as $key)
				{
					if (!in_array($key, ['label', 'icon', 'image', 'class', 'id', 'onclick']))
					{
						unset($button[$key]);
					}
				}

				// We need a label and an onclick handler at the bare minimum
				if (empty($button['label']) || empty($button['onclick']) || empty($button['id']))
				{
					continue;
				}

				$buttons[] = $button;
			}
		}

		return $buttons;
	}
}
