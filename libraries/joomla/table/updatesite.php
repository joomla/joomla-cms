<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Update site table
 * Stores the update sites for extensions
 *
 * @package     Joomla.Platform
 * @subpackage  Table
 * @since       3.4
 */
class JTableUpdatesite extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  Database driver object.
	 *
	 * @since   3.4
	 */
	public function __construct($db)
	{
		parent::__construct('#__update_sites', 'update_site_id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True if the object is ok
	 *
	 * @see     JTable::check()
	 * @since   3.4
	 */
	public function check()
	{
		// Check for valid name
		if (trim($this->name) == '' || trim($this->location) == '')
		{
			$this->setError(JText::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_EXTENSION'));

			return false;
		}
		return true;
	}
}
