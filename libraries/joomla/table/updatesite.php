<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
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

	/**
	 * Method to create and execute a SELECT WHERE query.
	 *
	 * @param   array  $options  Array of options
	 *
	 * @return  string  Results of query
	 *
	 * @since   3.4
	 */
	public function find($options = array())
	{
		$where = array();

		foreach ($options as $col => $val)
		{
			$where[] = $col . ' = ' . $this->_db->quote($val);
		}

		$query = $this->_db->getQuery(true)
			->select($this->_db->quoteName($this->_tbl_key))
			->from($this->_db->quoteName($this->_tbl))
			->where(implode(' AND ', $where));
		$this->_db->setQuery($query);

		return $this->_db->loadResult();
	}
}
