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
		// Get all the Two Factor Authentication plugins.
		JPluginHelper::importPlugin('twofactorauth');

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
}
