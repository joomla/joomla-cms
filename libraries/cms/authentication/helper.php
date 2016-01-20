<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Plugin helper class
 *
 * @since  1.5
 */
abstract class JAuthenticationHelper
{
	/**
	 * Get the Two Factor Authentication Methods available.
	 *
	 * @return  array  Two factor authentication methods.
	 *
	 * @since   3.x
	 */
	public static function getTwoFactorMethods()
	{
		$app = JFactory::getApplication();

		// Get all the Two Factor Authentication plugins.
		$twoFactorMethods = JPluginHelper::getPlugin('twofactorauth');
		$appSections      = $app->isSite() ? array(1, 3) : array(2, 3);

		// Remove from array the ones that are not activated in current app section (site, admin, both) in the plugin params.
		foreach($twoFactorMethods as $twoFactorMethodKey => $twoFactorMethod)
		{
			if (isset($twoFactorMethod->params))
			{
				$params = new Registry(json_decode($twoFactorMethod->params));
				if (!in_array((int) $params->get('section', 3), $appSections))
				{
					unset($twoFactorMethods[$twoFactorMethodKey]);
				}
			}
		}

		// For backward compatibility add a empty entry to the array.
		// We do this because in tmpl/default.php the two factor auth secret key only appear when two factor auth methods are more than 1.
		$twoFactorMethods[] = new stdClass();

		return $twoFactorMethods;
	}
}