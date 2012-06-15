<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.database.table');

/**
 * Update table
 * Stores updates temporarily
 *
 * @package     Joomla.Platform
 * @subpackage  Table
 * @since       11.1
 */
class JTableUpdate extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabase  &$db  A database connector object
	 *
	 * @since   11.1
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__updates', 'update_id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True if the object is ok
	 *
	 * @see     JTable::check
	 * @since   11.1
	 */
	public function check()
	{
		// check for valid name
		if (trim($this->name) == '' || trim($this->element) == '')
		{
			$this->setError(JText::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_EXTENSION'));
			return false;
		}
		return true;
	}

	/**
	 * Overloaded bind function
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  An optional array or space separated list of properties
	 * to ignore while binding.
	 *
	 * @return  mixed  Null if operation was satisfactory, otherwise returns an error
	 *
	 * @see     JTable::bind
	 * @since   11.1
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		if (isset($array['control']) && is_array($array['control']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['control']);
			$array['control'] = (string) $registry;
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Method to create and execute a SELECT WHERE query.
	 *
	 * @param   array  $options  Array of options
	 *
	 * @return  JDatabase  Results of query
	 *
	 * @since   11.1
	 */
	public function find($options = array())
	{
		$where = array();
		foreach ($options as $col => $val)
		{
			$where[] = $col . ' = ' . $this->_db->Quote($val);
		}
		$query = $this->_db->getQuery(true);
		$query->select($this->_db->quoteName($this->_tbl_key));
		$query->from($this->_db->quoteName($this->_tbl));
		$query->where(implode(' AND ', $where));
		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}
}
