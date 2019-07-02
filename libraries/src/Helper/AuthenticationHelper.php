<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

\defined('JPATH_PLATFORM') or die;

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
	 * public function onUserLoginButtons(string $moduleId, string $usernameFieldId): array
	 *
	 * The onUserLoginButtons event handlers must return a simple array containing 0 or more button definitions.
	 *
	 * Each button definition is a hash array with the following keys:
	 *
	 * - label      The label of the button
	 * - onclick    The onclick attribute, used to fire a JavaScript event
	 * - icon       [optional] A CSS class or an image path for an optional icon displayed before the label
	 * - class      [optional] CSS class(es) to be added to the button
	 * - id         [optional] The ID of the button.
	 *
	 * @param   string  $moduleId         The HTML ID of the module container.
	 * @param   string  $usernameFieldId  The HTML ID of the login module's username field.
	 *
	 * @return  array  Button definitions.
	 *
	 * @since 4.0.0
	 */
	public static function getLoginButtons(string $moduleId, string $usernameFieldId): array
	{
		// Get all the User plugins.
		PluginHelper::importPlugin('user');

		// Trigger the onUserLoginButtons event and return the button definitions.
		$results = Factory::getApplication()->triggerEvent('onUserLoginButtons', [$moduleId, $usernameFieldId]);
		$buttons = [];

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
				$button = array_merge([
					'label'   => '',
					'icon'    => '',
					'class'   => '',
					'id'      => '',
					'onclick' => '',
				], $item);

				// Unset anything that doesn't conform to a button definition
				foreach (array_keys($button) as $key)
				{
					if (!in_array($key, ['label', 'icon', 'class', 'id', 'onclick']))
					{
						unset($button[$key]);
					}
				}

				// We need a label and an onclick handler at the bare minimum
				if (empty($button['label']) || empty($button['onclick']))
				{
					continue;
				}

				$buttons[] = $button;
			}
		}

		return $buttons;
	}
}
