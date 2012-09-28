<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_version
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_version
 *
 * @package     Joomla.Administrator
 * @subpackage  mod_version
 * @since       1.6
 */
abstract class modVersionHelper
{
	/**
	 * Get the member items of the submenu.
	 *
	 * @return	mixed	An arry of menu items, or false on error.
	 */
	public static function getVersion(&$params)
	{
		$format = $params->get('format', 'short');
		$product = $params->get('product', 0);
		$method = 'get' . ucfirst($format) . "Version";

		// Get the joomla version
		$instance = new JVersion;
		$version = call_user_func(array($instance, $method));//$instance->{$method};

		if ($format == 'short' && !empty($product))
		{
			//add the product name to short format only (in long format it's included)
			$version = $instance->PRODUCT . ' ' . $version;
		}
		return $version;
	}
}
