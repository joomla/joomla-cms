<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JModelAdministrator extends JModelRecord
{
	/**
	 * Method to validate data and insert into db
	 *
	 * @param   array $data
	 * @param   array $ignore An optional array properties to ignore while binding.
	 *
	 * @return boolean
	 */
	public function create($data, $ignore = array())
	{
		$form = $this->getForm();
		$validData = $this->validate($form, $data);

		$table = $this->getTable();

		$this->observers->update('onBeforeCreate', array($this, $data));

		//Always ignore check out data
		$ignore[] = 'checked_out';
		$ignore[] = 'checked_out_time';

		$table->create($validData, $ignore);

		// Clean the cache.
		$this->cleanCache();

		$pkName = $table->getKeyName();
		if (isset($table->$pkName))
		{
			$this->setState($this->getContext() . '.id', $table->$pkName);
		}

		$this->observers->update('onAfterCreate', array($this, $data));
		return true;
	}

	/**
	 * Method to validate data and update into db
	 *
	 * @param array   $data
	 * @param  array  $ignore  An optional array properties to ignore while binding.
	 * @param  bool   $updateNulls Should null values be updated?
	 * @param  bool   $loadFirst   Should we load the record before updating?
	 *
	 * @throws ErrorException
	 * @return boolean
	 */
	public function update($data, $ignore = array(), $updateNulls = false, $loadFirst = false)
	{
		$this->observers->update('onBeforeUpdate', array($this, $data));

		$form      = $this->getForm();

		$validData = $this->validate($form, $data);
		$table = $this->getTable();

		//Always ignore check out data
		$ignore[] = 'checked_out';
		$ignore[] = 'checked_out_time';

		// Store the data.
		$table->update($validData, $ignore, $updateNulls, $loadFirst);

		// Clean the cache.
		$this->cleanCache();

		$pkName = $table->getKeyName();
		if (isset($table->$pkName))
		{
			$this->setState($this->getContext(). '.id', $table->$pkName);
		}

		$this->observers->update('onAfterUpdate', array($this, $data));
		return true;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param array $cid array of record primary keys.
	 *
	 * @throws ErrorException
	 * @return  boolean  True if successful, false if an error occurs.
	 */
	public function delete($cid)
	{
		$config = $this->config;
		$pks    = (array) $cid;
		$this->observers->update('onBeforeDelete', array($this, $cid));

		foreach ($pks AS $pk)
		{
			$table = $this->getActiveRecord($pk);

			if (!$this->allowAction('core.delete', $config['option'], $table))
			{
				$msg = JText::_('BABELU_LIB_ACL_ERROR_DELETE_NOT_PERMITTED');
				throw new ErrorException($msg);
			}

			$table->delete($pk);
		}

		$this->observers->update('onAfterDelete', array($this, $cid));

		// Clear the component's cache
		$this->cleanCache();
		return true;
	}

	/**
	 * Method to update one or more record states
	 *
	 * @param mixed  $cid  primary key or array of primary keys.
	 * @param string $type type of state change.
	 *
	 *
	 * @throws ErrorException
	 * @return boolean
	 */
	public function updateRecordState($cid, $type)
	{
		$stateChangeTypes = $this->getAvailableStates();

		if (!array_key_exists($type, $stateChangeTypes))
		{
			$msg = JText::_('BABELU_LIB_MODEL_ERROR_UNRECOGNIZED_STATE_CHANGE').':'.htmlspecialchars($type);
			throw new ErrorException($msg);
		}

		$newState = $stateChangeTypes[$type];

		$config = $this->config;
		$pks    = (array) $cid;

		foreach ($pks AS $i => $pk)
		{
			$activeRecord = $this->getActiveRecord($pk);

			if (!$this->allowAction('core.edit.state', $config['option'], $activeRecord))
			{
				//remove items we cannot edit.
				unset($cid[$i]);
				continue;
			}

			$stateField = $activeRecord->getStateField();

			$key = $activeRecord->getKeyName();
			$activeRecord->update(array($key => $pk, $stateField => $newState));
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to get an associative array of state types.
	 * This allows extensions to add additional states to their records by overriding this function.
	 *
	 * @return array $stateChangeTypes
	 */
	public function getAvailableStates()
	{
		$stateChangeTypes              = array();
		$stateChangeTypes['archived']   = 'ARCHIVED';
		$stateChangeTypes['draft']     = 'DRAFT';
		$stateChangeTypes['published']   = 'PUBLISHED';

		//@todo These states don't make sense for a default. Need to rethink.
		//$stateChangeTypes['reported']    = 'REPORTED';
		//$stateChangeTypes['trashed']     = 'TRASHED';
		return $stateChangeTypes;
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param   array   $order order values.
	 *
	 * @throws ErrorException
	 * @return  boolean
	 */
	public function saveorder($order = null)
	{
		$table = $this->getTable();

		if(!$table->supportsOrdering())
		{
			return false;
		}

		$reorderConditions = array();
		$pks        = (array) $order;

		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$newOrder = $i + 1;
			$keyName = $table->getKeyName();
			$orderField = $table->getOrderingField();
			$table->update(array($keyName => $pk, $orderField => $newOrder));

			// Remember to reorder within position and client_id
			$tempCondition = $table->getReorderConditions();

			if(!in_array($tempCondition, $reorderConditions))
			{
				$reorderConditions[] = $tempCondition;
			}

		}

		$this->reorder($table, $reorderConditions);

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to reorder records according to conditions.
	 *
	 * @param JTableCms $table
	 * @param array     $conditions
	 *
	 * @return bool
	 * @todo Group by reordering is broken. I need to work it out.
	 */
	protected function reorder($table, $conditions)
	{
		$dbo = $this->getDbo();
		$query = $dbo->getQuery(true);

		$primaryKey = $table->getKeyName();
		$orderField = $table->getOrderingField();


		$query->select(array($primaryKey, $orderField));
		$query->from($this->getTableName());
		$query->order($orderField);

		foreach($conditions AS $where)
		{
			$query->clear('where');
			$query->where($orderField . ' >= 0');

			if(!empty($where))
			{
				$query->where($where);
			}

			$dbo->setQuery($query);
			$rows = $dbo->loadObjectList();
			$ignore = array('checked_out','checked_out_time');

			foreach($rows AS $i => $row)
			{
				$isPositiveInt = ($row->$orderField >= 0);
				$shouldAdjust = ($row->$orderField != ($i + 1));

				if($isPositiveInt && $shouldAdjust)
				{
					$newOrder = ($i + 1);
					$table->update(array($primaryKey => $row->$primaryKey,$orderField => $newOrder), $ignore, false, true);
				}
			}
		}

		return true;
	}

	/**
	 * Add the component params by default
	 * @param string $ordering
	 * @param string $direction
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		if (!$this->stateIsSet)
		{
			$config = $this->config;
			$params = JComponentHelper::getParams($config['option']);
			$this->setState('params', $params);

			parent::populateState($ordering, $direction);
		}
	}
}