<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

defined('JPATH_PLATFORM') or die;

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
		\JPluginHelper::importPlugin('twofactorauth');

		// Trigger onUserTwofactorIdentify event and return the two factor enabled plugins.
		$identities = \JEventDispatcher::getInstance()->trigger('onUserTwofactorIdentify', array());

		// Generate array with two factor auth methods.
		$options = array(
			\JHtml::_('select.option', 'none', \JText::_('JGLOBAL_OTPMETHOD_NONE'), 'value', 'text'),
		);

		if (!empty($identities))
		{
			foreach ($identities as $identity)
			{
				if (!is_object($identity))
				{
					continue;
				}

				$options[] = \JHtml::_('select.option', $identity->method, $identity->title, 'value', 'text');
			}
		}

		return $options;
	}
}
