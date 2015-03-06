<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JModelData extends JModelCms
{
	/**
	 * DBO object
	 * @var JDatabaseDriver
	 */
	protected $dbo;

	public function __construct($config = array())
	{
		parent::__construct($config);

		if(isset($config['dbo']) && ($config['dbo'] instanceof JDatabaseDriver))
		{
			$this->dbo = $config['dbo'];
		}
	}

	/**
	 * Method to get the database driver object
	 *
	 * @return  JDatabaseDriver
	 */
	public function getDbo()
	{
		if(!($this->dbo instanceof JDatabaseDriver))
		{
			$this->dbo = JFactory::getDbo();
		}

		return $this->dbo;
	}

	/**
	 * Method to get a default table name
	 *
	 * Default Format: strtolower('#__'.substr($config['option'], 4).'_'.$config['resource'])
	 * This is intended to be overridden, if you use a different naming system
	 *
	 * @param array $config
	 *
	 * @return string
	 */
	public function getTableName($config = array())
	{
		//make sure we have all the configuration vars
		$config += $this->config;

		$prefix  = '#__' . substr($config['option'], 4);

		$postfix = '_' . $config['resource'];

		return strtolower($prefix.$postfix);
	}

	/**
	 * Method to get the primary key name
	 *
	 * Default Format: strtolower($config['resource'].'_id')
	 * * This is intended to be overridden, if you use a different naming system
	 *
	 * @param array $config
	 *
	 * @return string
	 */
	public function getPrimaryKey($config = array())
	{
		//make sure we have all the configuration vars
		$config += $this->config;

		return strtolower($config['resource'].'_id');
	}

	/**
	 * Method to get the name of the primary key from table
	 *
	 * @param string $tablePrefix
	 * @param string $tableName
	 * @param array  $config
	 *
	 * @return string
	 */
	public function getKeyName($tablePrefix = null, $tableName = null, $config = array())
	{
		$table = $this->getTable($tablePrefix, $tableName, $config);

		return $table->getKeyName();
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string $prefix The class prefix. Optional.
	 * @param   string $name   The table name. Optional.
	 * @param   array  $config Configuration array for model. Optional.
	 *
	 * @return  JTableCms  the table object
	 */
	public function getTable($prefix = null, $name = null, $config = array())
	{
		$config += $this->config;

		if (empty($prefix))
		{
			$prefix = ucfirst(substr($config['option'], 4));
		}

		if (empty($name))
		{
			$name = ucfirst($config['resource']);
		}

		//create it if it does not already exist
		return $this->createTable($prefix, $name, $config);
	}

	/**
	 * Method to load and return a model object.
	 *
	 * @param   string $prefix The class prefix. Optional.
	 * @param   string $name   The name of the view
	 * @param   array  $config Configuration settings to pass to JTable::getInstance
	 *
	 * @throws ErrorException
	 *
	 * @return  JTableCms   A table object
	 */
	protected function createTable($prefix, $name, $config = array())
	{
		// Clean the model name
		$prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);
		$name   = preg_replace('/[^A-Z0-9_]/i', '', $name);

		// Make sure we are returning a DBO object
		if (!array_key_exists('dbo', $config))
		{
			$config['dbo'] = $this->getDbo();
		}

		if(!isset($config['table']['name']))
		{
			$config['table']['name'] = $this->getTableName($config);
		}

		if(!isset($config['table']['key']))
		{
			$config['table']['name'] = $this->getPrimaryKey($config);
		}

		$className = $prefix . 'Table' . $name;

		if(!class_exists($className))
		{
			$msg = JText::_('BABELU_LIB_MODEL_ERROR_TABLE_NAME_NOT_SUPPORTED');
			throw new ErrorException($msg . ': ' .$className);
		}

		return new $className($config);
	}

	/**
	 * Method to lock a record for editing
	 *
	 * @param int $pk primary key of record
	 *
	 * @throws InvalidArgumentException
	 * @throws ErrorException
	 *
	 * @return boolean
	 */
	public function checkout($pk)
	{
		$activeRecord = $this->getActiveRecord($pk);

		if(!$this->isLockable($activeRecord))
		{
			return true;
		}

		if(!$this->isLocked($activeRecord))
		{
			$userId = JFactory::getUser()->id;
			$now = new JDate();
			$key = $activeRecord->getKeyName();

			$activeRecord->update(array($key => (int)$pk, 'checked_out' => $userId, 'checked_out_time' => $now->toSql()));
			return true;
		}

		$msg = JText::_('BABELU_LIB_MODEL_ERROR_CHECKIN_USER_MISMATCH');
		throw new ErrorException($msg);
	}

	/**
	 * Method to unlock a record
	 *
	 * @param int $pk primary key
	 *
	 * @return boolean
	 */
	public function checkin($pk)
	{
		// Get an instance of the row to checkout.
		$activeRecord = $this->getActiveRecord($pk);

		if($this->isLockable($activeRecord) && !$this->isLocked($activeRecord))
		{
			$userId = 'NULL';
			$nullDate = JFactory::getDbo()->getNullDate();
			$key = $activeRecord->getKeyName();
			$activeRecord->update(array($key => (int)$pk,'checked_out' => $userId, 'checked_out_time' => $nullDate));
		}

		return true;
	}

	/**
	 * Method to get a loaded active record.
	 *
	 * @param int $pk primary key
	 *
	 * @return JTableCms
	 */
	protected function getActiveRecord($pk)
	{
		$table = $this->getTable();
		$table->load($pk);
		return $table;
	}

	/**
	 * Method to check if a table is lockable
	 *
	 * @param JTableCms $table
	 *
	 * @return boolean
	 */
	protected function isLockable($table)
	{
		$hasCheckedOut     = (property_exists($table, 'checked_out'));
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
	 *
	 * @param JTableCms $activeRecord
	 *
	 * @return boolean
	 */
	protected function isLocked($activeRecord)
	{
		$isCheckedOut = ($activeRecord->checked_out > 0);

		$user            = JFactory::getUser();
		$isCurrentEditor = ($activeRecord->checked_out == $user->get('id'));
		$canOverride     = ($user->authorise('core.admin', 'com_checkin'));

		if ($isCheckedOut && !$isCurrentEditor && !$canOverride)
		{
			return true; // record is locked
		}

		return false; // record is not locked
	}
}
