<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Update table
 * Stores updates temporarily
 *
 * @since  1.7.0
 */
class Update extends Table
{
	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver  $db  Database driver object.
	 *
	 * @since   1.7.0
	 */
	public function __construct($db)
	{
		parent::__construct('#__updates', 'update_id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True if the object is ok
	 *
	 * @see     Table::check()
	 * @since   1.7.0
	 */
	public function check()
	{
		// Check for valid name
		if (trim($this->name) == '' || trim($this->element) == '')
		{
			$this->setError(\JText::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_EXTENSION'));

			return false;
		}

		if (!$this->update_id && !$this->data)
		{
			$this->data = '';
		}

		return true;
	}

	/**
	 * Overloaded bind function
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  An optional array or space separated list of properties
	 *                          to ignore while binding.
	 *
	 * @return  mixed  Null if operation was satisfactory, otherwise returns an error
	 *
	 * @see     Table::bind()
	 * @since   1.7.0
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new Registry($array['params']);
			$array['params'] = (string) $registry;
		}

		if (isset($array['control']) && is_array($array['control']))
		{
			$registry = new Registry($array['control']);
			$array['control'] = (string) $registry;
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Method to create and execute a SELECT WHERE query.
	 *
	 * @param   array  $options  Array of options
	 *
	 * @return  string  Results of query
	 *
	 * @since   1.7.0
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
