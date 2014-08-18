<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Base Cms Model Class for data
 *
 * @package     Joomla.Libraries
 * @subpackage  Model
 * @since       3.4
 */
abstract class JModelData extends JModelCms
{
	/**
	 * Array of JTables
	 * @var array
	 */
	protected $tables = array();

	/**
	 * Method to get the name of the primary key from table
	 *
	 * @param string $tablePrefix
	 * @param string $tableName
	 * @param array  $config
	 *
	 * @return string
	 * @See JTable::getKeyName
	 */
	public function getKeyName( $tablePrefix = null, $tableName = null, $config = array())
	{
		$table = $this->getTable($tablePrefix, $tableName, $config);

		return $table->getKeyName();
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  JTableInterface  A JTableInterface object
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	public function getTable($name = null, $prefix = null, $options = array())
	{
		if (!$name)
		{
			$name = ucfirst($this->getName());
		}

		if (!$prefix)
		{
			$prefix = ucfirst(substr($this->option, 4)) . 'Table';
		}

		// Make sure we are giving a JDatabaseDriver object to the table
		if (!array_key_exists('dbo', $options))
		{
			$options['dbo'] = $this->getDb();
		}

		// Try and get table instance
		$table = JTable::getInstance($name, $prefix, $options);

		if ($table instanceof JTableInterface)
		{
			return $table;
		}

		// If the table isn't a instance of JTableInterface throw an exception
		throw new RuntimeException(JText::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $name), 0);
	}

	/**
	 * Method to lock a record for editing
	 *
	 * @param int $pk primary key of record
	 *
	 * @return boolean
	 * @see JCmsModelData::checkin
	 */
	public function checkout($pk)
	{
		$activeRecord = $this->getActiveRecord($pk);
		$user         = JFactory::getUser();

		$activeRecord->checkout($user->id, $pk);

		return true;
	}

	/**
	 * Method to unlock a record
	 *
	 * @param  int  $pk  Integer primary key or array of primary keys
	 *
	 * @return  boolean
	 * @see JCmsModelData::checkout
	 */
	public function checkin($pk)
	{
		if (is_integer($pk))
		{
			$pk = array($pk);
		}

		JArrayHelper::toInteger($pk);

		foreach ($pk as $primaryKey)
		{
			// Get an instance of the row to checkout.
			$activeRecord = $this->getActiveRecord($primaryKey);

			$activeRecord->checkin($primaryKey);
		}

		return true;
	}

	/**
	 * Method to get a loaded active record.
	 *
	 * @param int $pk primary key
	 *
	 * @throws RuntimeException
	 * @return JTable
	 */
	protected function getActiveRecord($pk)
	{
		// Get an instance of the row to checkout.
		$table = $this->getTable();

		if (!$table->load($pk))
		{
			throw new RuntimeException($table->getError());
		}

		return $table;
	}

	/**
	 * Method to check if a table is lockable
	 *
	 * @param JTable $table
	 *
	 * @return boolean
	 */
	protected function isLockable($table)
	{
		$hasCheckedOut     = (property_exists($table, 'checked_out'));
		$hasCheckedOutTime = (property_exists($table, 'checked_out_time'));

		// If there is no checked_out or checked_out_time field or it is empty, return true.
		if ($hasCheckedOut && $hasCheckedOutTime)
		{
			return true;
		}

		// Is not lockable
		return false;
	}


	/**
	 * Method to check if a record is locked
	 *
	 * @param JTable $activeRecord
	 *
	 * @return boolean
	 */
	protected function isLocked($activeRecord)
	{
		if ($this->isLockable($activeRecord))
		{
			$isCheckedOut = ($activeRecord->checked_out > 0);

			$user            = JFactory::getUser();
			$isCurrentEditor = ($activeRecord->checked_out == $user->get('id'));
			$canOverride     = ($user->authorise('core.admin', 'com_checkin'));

			// Record is locked
			if ($isCheckedOut && !$isCurrentEditor && !$canOverride)
			{
				return true;
			}
		}

		// Record is not locked
		return false;
	}
}
