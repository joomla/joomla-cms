<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  database
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file is adapted from the Joomla! Platform. It is used to iterate a database cursor returning FOFTable objects
 * instead of plain stdClass objects
 */

// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

if (!interface_exists('JDatabaseInterface'))
{
	/**
	 * Joomla Platform Database Interface
	 *
	 * @since  11.2
	 */
	interface JDatabaseInterface
	{
		/**
		 * Test to see if the connector is available.
		 *
		 * @return  boolean  True on success, false otherwise.
		 *
		 * @since   11.2
		 */
		public static function isSupported();
	}
}

interface FOFDatabaseInterface extends JDatabaseInterface
{
}
