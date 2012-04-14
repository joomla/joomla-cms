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
jimport('joomla.database.tableasset');

/**
 * Module table
 *
 * @package     Joomla.Platform
 * @subpackage  Table
 * @since       11.1
 */
class JTableModule extends JTable
{
	/**
	 * Constructor.
	 *
	 * @param   JDatabase  &$db  A database connector object
	 *
	 * @since   11.1
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__modules', 'id', $db);

		$this->access = (int) JFactory::getConfig()->get('access');
	}

	/**
	 * Overloaded check function.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @see     JTable::check
	 * @since   11.1
	 */
	public function check()
	{
		// check for valid name
		if (trim($this->title) == '')
		{
			$this->setError(JText::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_MODULE'));
			return false;
		}

		// Check the publish down date is not earlier than publish up.
		if (intval($this->publish_down) > 0 && $this->publish_down < $this->publish_up)
		{
			$this->setError(JText::_('JGLOBAL_START_PUBLISH_AFTER_FINISH'));
			return false;
		}

		return true;
	}

	/**
	 * Overloaded bind function.
	 *
	 * @param   array  $array   Named array.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
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

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded store function to avoid NOT NULL constraint violation.
	 *
	 * @param	boolean	True to update fields even if they are null.
	 * @return	boolean	True on success, false on failure.
	 * @since	1.6
	 */
	public function store($updateNulls = false)
	{
		// Set publish_up to null date if not set
		if (!$this->publish_up)
		{
			$this->publish_up = $this->_db->getNullDate();
		}

		// Set publish_down to null date if not set
		if (!$this->publish_down)
		{
			$this->publish_down = $this->_db->getNullDate();
		}

		// Set content to empty string if not set
		if (!$this->content)
		{
			$this->content = '';
		}

		// Attempt to store the data.
		return parent::store($updateNulls);
	}
}
