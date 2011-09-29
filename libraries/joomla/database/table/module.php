<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

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
	 * Contructor.
	 *
	 * @param   database  &$db  A database connector object
	 *
	 * @return  JTableModule
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
	 * @see     JTable::check()
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
			// Swap the dates.
			$temp = $this->publish_up;
			$this->publish_up = $this->publish_down;
			$this->publish_down = $temp;
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
	 * @see     JTable:bind
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
}
