<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JTableCms extends JTable
{
	protected $_config;

	/**
	 * Name of the primary key fields in the table.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $_tbl_keys = array();

	public function __construct($config = array())
	{
		if (!isset($config['table']['name']))
		{
			$config['table']['name'] = $this->buildTableName($config);
		}

		if (!isset($config['table']['key']))
		{
			$config['table']['key'] = $config['subject'].'_id';
		}

		if (!isset($config['dbo']))
		{
			$config['dbo'] = JFactory::getDbo();
		}

		$table = $config['table']['name'];
		$key = $config['table']['key'];
		$db = $config['dbo'];

		$this->_config = $config;

		parent::__construct($table, $key, $db);

		//fix for 2.5 compatiblity
		if (count($this->_tbl_keys) == 0)
		{
			// Set the key to be an array.
			if (is_string($key))
			{
				$key = array($key);
			}
			elseif (is_object($key))
			{
				$key = (array) $key;
			}
				
			$this->_tbl_keys = $key;
		}

		$this->_config = $config;
	}

	protected function buildTableName($config)
	{
		$prefix = '#__'.substr($config['option'], 4);
		$postfix = '_'.$config['subject'];

		$tableName = strtolower($prefix.$postfix);
		return $tableName;
	}

	/**
	 * Method to update the record state
	 * @param   mixed    $pks     An optional array of primary key values to update.
	 * @param integer $newState The new publishing state.
	 * @param integer  $userId  The user id of the user performing the operation.
	 * @return boolean
	 */
	public function updateRecordState($pk, $newState, $userId = 0)
	{
		$pks = $this->getValidPk($pk);

		$userId = (int) $userId;
		$newState  = (int) $newState;

		foreach ($pks AS $pk)
		{

			$activeRecord = $this->load($pk);
				
			// Update the publishing state for rows with the given primary keys.
			$query = $this->_db->getQuery(true)
			->update($this->_tbl)
			->set('state = ' . $newState);
				
			$checkin = false;
			if ($this->isLockable($this))
			{
				if(!$this->isLocked($this))
				{
					$this->checkout($userId, $pk);
					$checkin = true;
				}

				$query->where('(checked_out = 0 OR checked_out = ' . $userId . ')');
			}
				
			// Build the WHERE clause for the primary keys.
			$this->appendPrimaryKeys($query, $pk);
				
			$this->_db->setQuery($query);
			$this->_db->execute();
				
			//Check in what we checked out
			if ($checkin)
			{
				$this->checkin($pk);
			}
		}

		//if no exceptions were thrown
		//we are good to go.
		return true;
	}

	public function checkout($userId, $pk = null)
	{
		$pk = $this->getValidPk($pk);

		if (!$this->isLockable($this))
		{
			return true;
		}

		if ($this->isLocked($this))
		{
			throw new ErrorException(JText::_('JLIB_DATABASE_ERROR_CHECKIN_USER_MISMATCH'));
			return false;
		}

		// Get the current time in the database format.
		$time = JFactory::getDate()->toSql();

		// Check the row out by primary key.
		$query = $this->_db->getQuery(true)
		->update($this->_tbl)
		->set($this->_db->quoteName('checked_out') . ' = ' . (int) $userId)
		->set($this->_db->quoteName('checked_out_time') . ' = ' . $this->_db->quote($time));
		$this->appendPrimaryKeys($query, $pk);
		$this->_db->setQuery($query);
		$this->_db->execute();

		// Set table values in the object.
		$this->checked_out      = (int) $userId;
		$this->checked_out_time = $time;

		return true;
	}

	public function checkin($pk = null)
	{
		$pk = $this->getValidPk($pk);

		if (!$this->isLockable($this))
		{
			return true;
		}

		if ($this->isLocked($this))
		{
			throw new ErrorException(JText::_('JLIB_DATABASE_ERROR_CHECKIN_USER_MISMATCH'));
			return false;
		}

		// Check the row in by primary key.
		$query = $this->_db->getQuery(true)
		->update($this->_tbl)
		->set($this->_db->quoteName('checked_out') . ' = 0')
		->set($this->_db->quoteName('checked_out_time') . ' = ' . $this->_db->quote($this->_db->getNullDate()));
		$this->appendPrimaryKeys($query, $pk);

		$this->_db->setQuery($query);


		// Check for a database error.
		$this->_db->execute();

		// Set table values in the object.
		$this->checked_out      = 0;
		$this->checked_out_time = '';

		return true;
	}

	public function delete($pk = null)
	{
		$pk = $this->getValidPk($pk);

		if ($this->isCompatible('3.0'))
		{
			// Implement JObservableInterface: Pre-processing by observers
			$this->_observers->update('onBeforeDelete', array($pk));
		}

		// If tracking assets, remove the asset first.
		if ($this->_trackAssets)
		{
			// Get the asset name
			$name  = $this->_getAssetName();
			$asset = self::getInstance('Asset');

			if ($asset->loadByName($name))
			{
				if (!$asset->delete())
				{
					throw new ErrorException($asset->getError());
					return false;
				}
			}
		}

		// Delete the row by primary key.
		$query = $this->_db->getQuery(true)
		->delete($this->_tbl);
		$this->appendPrimaryKeys($query, $pk);

		$this->_db->setQuery($query);

		// Check for a database error.
		$this->_db->execute();

		if ($this->isCompatible('3.0'))
		{
			// Implement JObservableInterface: Post-processing by observers
			$this->_observers->update('onAfterDelete', array($pk));
		}

		return true;
	}

	public function getValidPk($pk = null)
	{
		if (is_null($pk))
		{
			$pk = array();

			foreach ($this->_tbl_keys AS $key)
			{
				$pk[$key] = $this->$key;
			}
		}
		elseif (!is_array($pk))
		{
			$pk = array($this->_tbl_key => $pk);
		}

		foreach ($this->_tbl_keys AS $key)
		{
			$pk[$key] = is_null($pk[$key]) ? $this->$key : $pk[$key];

			if ($pk[$key] === null)
			{
				throw new UnexpectedValueException(JText::_('JLIB_DATABASE_ERROR_NULL_PRIMARY_KEYS_NOT_ALLOWED'));
				return false;
			}
		}
		return $pk;
	}

	/**
	 * Method to append the primary keys for this table to a query.
	 *
	 * @param   JDatabaseQuery  $query  A query object to append.
	 * @param   mixed           $pk     Optional primary key parameter.
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function appendPrimaryKeys($query, $pk = null)
	{
		if (is_null($pk))
		{
			foreach ($this->_tbl_keys as $k)
			{
				$query->where($this->_db->quoteName($k) . ' = ' . $this->_db->quote($this->$k));
			}
		}
		else
		{
			if (!is_object($pk) && !is_array($pk))
			{
				$pk = array($this->_tbl_key => $pk);
			}

			$pk = (object) $pk;

			foreach ($this->_tbl_keys AS $k)
			{
				$query->where($this->_db->quoteName($k) . ' = ' . $this->_db->quote($pk->$k));
			}
		}
	}

	/**
	 * Method to check if a table has lockable rows.
	 * A table that has both the "checked_out" and "checked_out_time" fields has lockable rows
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
	 * Method ot check if a table row is locked.
	 * A row is locked if it is lockable and has a checked_out value greater than 0
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

	public function moveOrder($pk, $userId, $delta, $where = null)
	{
		// if no ordering not supported then just return true
		if (!$this->supportsOrdering($this))
		{
			return true;
		}

		// If the change is none, do nothing.
		if (empty($delta))
		{
			return true;
		}

		$checkin = false;
		if ($this->isLockable($this))
		{
			if ($this->isLocked($this))
			{
				throw new ErrorException(JText::_('JLIB_DATABASE_ERROR_CHECKIN_USER_MISMATCH'));
				return false;
			}
			$this->checkout($userId);
			$checkin = true;
		}

		$k     = $this->_tbl_key;
		$row   = null;
		$query = $this->_db->getQuery(true);

		// Select the primary key and ordering values from the table.
		$query->select(implode(',', $this->_tbl_keys) . ', ordering')
		->from($this->_tbl);

		// If the movement delta is negative move the row up.
		if ($delta < 0)
		{
			$query->where('ordering < ' . (int) $this->ordering)
			->order('ordering DESC');
		}
		// If the movement delta is positive move the row down.
		elseif ($delta > 0)
		{
			$query->where('ordering > ' . (int) $this->ordering)
			->order('ordering ASC');
		}

		// Add the custom WHERE clause if set.
		if ($where)
		{
			$query->where($where);
		}

		// Select the first row with the criteria.
		$this->_db->setQuery($query, 0, 1);
		$row = $this->_db->loadObject();

		// If a row is found, move the item.
		if (!empty($row))
		{
			// Update the ordering field for this instance to the row's ordering value.
			$query->clear()
			->update($this->_tbl)
			->set('ordering = ' . (int) $row->ordering);
			$this->appendPrimaryKeys($query);
			$this->_db->setQuery($query);
			$this->_db->execute();

			// Update the ordering field for the row to this instance's ordering value.
			$query->clear()
			->update($this->_tbl)
			->set('ordering = ' . (int) $this->ordering);
			$this->appendPrimaryKeys($query, $row);
			$this->_db->setQuery($query);
			$this->_db->execute();

			// Update the instance value.
			$this->ordering = $row->ordering;
		}
		else
		{
			// Update the ordering field for this instance.
			$query->clear()
			->update($this->_tbl)
			->set('ordering = ' . (int) $this->ordering);
			$this->appendPrimaryKeys($query);
			$this->_db->setQuery($query);
			$this->_db->execute();
		}

		if ($checkin)
		{
			$this->checkin($pk);
		}

		return true;
	}

	protected function supportsOrdering(JTable $table)
	{
		if (property_exists($table, 'ordering'))
		{
			return true;
		}
		return false;
	}

	public function getReorderConditions(JTable $activeRecord)
	{
		return '';
	}

	/**
	 * (non-PHPdoc)
	 * @see JTable::check()
	 */
	public function check()
	{
		if (property_exists($this, 'ordering') && $this->id == 0)
		{
			$this->ordering = self::getNextOrder();
		}
		return parent::check();
	}

	/**
	 * (non-PHPdoc)
	 * @see JTable::bind()
	 */
	public function bind($src, $ignore = array())
	{
		$array = $src;

		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string)$registry;
		}

		//TODO figure out how to implement rules without JRules

		if (isset($array['rules']) && is_array($array['rules']))
		{
			$rules = new JAccessRules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the JTable instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 *
	 * @link    http://docs.joomla.org/JTable/load
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 * @throws  RuntimeException
	 * @throws  UnexpectedValueException
	 */
	public function load($keys = null, $reset = true)
	{
		$version = new JVersion();
		if ($this->isCompatible('3.0'))
		{
			// Implement JObservableInterface: Pre-processing by observers
			$this->_observers->update('onBeforeLoad', array($keys, $reset));
		}

		if (empty($keys))
		{
			$empty = true;
			$keys  = array();

			// If empty, use the value of the current key
			foreach ($this->_tbl_keys as $key)
			{
				$empty      = $empty && empty($this->$key);
				$keys[$key] = $this->$key;
			}

			// If empty primary key there's is no need to load anything
			if ($empty)
			{
				return true;
			}
		}
		elseif (!is_array($keys))
		{
			// Load by primary key.
			$keyCount = count($this->_tbl_keys);

			if ($keyCount)
			{
				if ($keyCount > 1)
				{
					throw new InvalidArgumentException('JLIB_DATABASE_ERROR_INCOMPLETE_COMPOUND_PRIMARY_KEY');
				}
				$keys = array($this->getKeyName() => $keys);
			}
			else
			{
				throw new RuntimeException('JLIB_DATABASE_NO_PRIMARY_KEY_DEFINED');
			}
		}

		if ($reset)
		{
			$this->reset();
		}

		// Initialise the query.
		$query = $this->_db->getQuery(true)
		->select('*')
		->from($this->_tbl);
		$fields = array_keys($this->getProperties());

		foreach ($keys as $field => $value)
		{
			// Check that $field is in the table.
			if (!in_array($field, $fields))
			{
				throw new UnexpectedValueException(sprintf('Missing field in database: %s &#160; %s.', get_class($this), $field));
			}
			// Add the search tuple to the query.
			$query->where($this->_db->quoteName($field) . ' = ' . $this->_db->quote($value));
		}

		$this->_db->setQuery($query);

		$row = $this->_db->loadAssoc();

		// Check that we have a result.
		if (empty($row))
		{
			$result = false;
		}
		else
		{
			// Bind the object with the row and return.
			$result = $this->bind($row);
		}

		if ($this->isCompatible('3.0'))
		{
			// Implement JObservableInterface: Post-processing by observers
			$this->_observers->update('onAfterLoad', array(&$result, $row));
		}


		return $result;
	}

	/**
	 * Check if the version compatible
	 * @param string $minimum The minimum version of the Joomla which is compatible.
	 * @see JVersion::isCompatible
	 * @return boolean
	 */
	protected function isCompatible($minimum)
	{
		$version = new JVersion();
		if ($version->isCompatible($minimum))
		{
			return true;
		}
		return false;
	}
}

