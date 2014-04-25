<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Captcha
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JCmsModelData extends JCmsModelBase
{

	/**
	 * Method to get the database driver object
	 *
	 * @return  JDatabaseDriver
	 */
	public function getDbo()
	{
		return JFactory::getDbo();
	}

	/**
	 * Method to get the name of the primary key from table
	 * @See JTable::getKeyName
	 * @return string
	 */
	public function getKeyName($tableName = null, $tablePrefix = null, $config = array())
	{
		$table = $this->getTable($tableName, $tablePrefix, $config = array());
		return $table->getKeyName();
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function getTable($name = null, $prefix = null, $config = array())
	{
		$config = $this->config;

		if (count($config) == 0)
		{
			$config = $this->config;
		}
		else
		{
			//merge sent config to
			//make sure both subject and options
			//are always set.
			//Will not overwrite existing keys
			$config += $this->config;
		}

		if (empty($name))
		{
			$name = ucfirst($config['subject']);
		}

		if (empty($prefix))
		{
			$prefix = ucfirst(substr($config['option'], 4));
		}

		if ($table = $this->createTable($name, $prefix, $config))
		{
			return $table;
		}

		throw new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_TABLE_NAME_NOT_SUPPORTED', $prefix.'Table'.$name), 0);
	}

	/**
	 * Method to load and return a model object.
	 *
	 * @param   string  $name    The name of the view
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration settings to pass to JTable::getInstance
	 *
	 * @return  mixed   A JTable object or boolean false if failed
	 *
	 * @since   12.2
	 * @see     JTable::getInstance()
	 */
	protected function createTable($name, $prefix , $config = array())
	{
		// Clean the model name
		$name = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

		// Make sure we are returning a DBO object
		if (!array_key_exists('dbo', $config))
		{
			$config['dbo'] = $this->getDbo();
		}

		$className = $prefix.'Table'.$name;

		return new $className($config);
	}

	/**
	 * Method to load a row for editing from the version history table.
	 *
	 * @param   integer  $version_id  Key to the version history table.
	 * @param   JTable   $table      Content table object being loaded.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 * @throws ErrorException
	 * @since   12.2
	 */
	public function loadHistory($version_id, JTable $table)
	{
		// Only attempt to check the row in if it exists.
		if ($version_id)
		{
			$user = JFactory::getUser();

			// Get an instance of the row to checkout.
			$historyTable = JTable::getInstance('Contenthistory');

				
			if (!$historyTable->load($version_id))
			{
				throw new ErrorException($historyTable->getError());
				return false;
			}

			$rowArray = JArrayHelper::fromObject(json_decode($historyTable->version_data));

			$typeId = JTable::getInstance('Contenttype')->getTypeId($this->typeAlias);

			if ($historyTable->ucm_type_id != $typeId)
			{
				$key = $table->getKeyName();

				if (isset($rowArray[$key]))
				{
					$table->checkIn($rowArray[$key]);
				}

				throw ErrorException(JText::_('JLIB_APPLICATION_ERROR_HISTORY_ID_MISMATCH'));
				return false;
			}
		}

		$this->setState('save_date', $historyTable->save_date);
		$this->setState('version_note', $historyTable->version_note);

		return $table->bind($rowArray);
	}

	/**
	 * Method to lock a record for editing
	 * @param int $pk primary key of record
	 * @throws InvalidArgumentException
	 * @throws ErrorException
	 * @return boolean
	 * @see JCmsModelData::checkin
	 */
	public function checkout($pk)
	{
		$activeRecord = $this->getActiveRecord($pk);
		$user = JFactory::getUser();

		$activeRecord->checkout($user->id, $pk);

		return true;
	}

	/**
	 * Method to unlock a record
	 * @param int $pk primary key
	 * @throws InvalidArgumentException
	 * @throws ErrorException
	 * @return boolean
	 * @see JCmsModelData::checkout
	 */
	public function checkin($pk)
	{
		// Get an instance of the row to checkout.
		$activeRecord = $this->getActiveRecord($pk);

		$activeRecord->checkin($pk);

		return true;
	}

	/**
	 * Method to get a loaded active record.
	 * @param int $pk primary key
	 * @throws ErrorException
	 * @return JTable
	 */
	protected function getActiveRecord($pk)
	{
		// Get an instance of the row to checkout.
		$table = $this->getTable();

		if (!$table->load($pk))
		{
			throw new ErrorException($table->getError());
		}

		return $table;
	}

	/**
	 * Method to check if a table is lockable
	 * @param JTable $table
	 * @return boolean
	 */
	protected function isLockable(JTable $table)
	{
		$hasCheckedOut = (property_exists($table, 'checked_out'));
		$hasCheckedOutTime = (property_exists($table, 'checked_out_time'));
		// If there is no checked_out or checked_out_time field, just return true.

		if ($hasCheckedOut && $hasCheckedOutTime)
		{
			return true; // is lockable
		}

		return false; // is not lockable
	}


	/**
	 * Method to check if a record is locked
	 * @param JTable $activeRecord
	 * @return boolean
	 */
	protected function isLocked(JTable $activeRecord)
	{
		$user = JFactory::getUser();
		if ($this->isLockable($activeRecord))
		{
			$isCheckedOut = ($activeRecord->checked_out > 0);
			$isCurrentEditor = ($activeRecord->checked_out == $user->get('id'));
			$canOverride = ($user->authorise('core.admin', 'com_checkin'));

			if ($isCheckedOut && !$isCurrentEditor && !$canOverride)
			{
				return true; // record is locked
			}
		}
		false; // record is not locked
	}
}
