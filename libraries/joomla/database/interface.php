<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla Platform Database Interface
 *
 * @since  1.7.0
*/
interface JDatabaseInterface
{
	/**
	 * Test to see if the connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.7.0
	 */
	public static function isSupported();
}
