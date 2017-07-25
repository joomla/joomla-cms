<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_version
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_version
 *
 * @since  1.6
 */
abstract class ModVersionHelper
{
	/**
	 * Get the member items of the submenu.
	 *
	 * @return  string  String containing the current Joomla version.
	 */
	public static function getVersion()
	{
		$version = new JVersion;

		return $version::PRODUCT . ' ' . $version->getShortVersion();
	}
}
