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
 * Base Cms Model Class for actions (e.g. CRUD functions)
 *
 * @package     Joomla.Libraries
 * @subpackage  Model
 * @since       3.4
 */
abstract class JModelActions extends JModelAdministrator
{
	/**
	 * Method to validate data and insert into db
	 *
	 * @param array $data
	 *
	 * @return boolean
	 *
	 * @since  3.4
	 * @throws RuntimeException
	 */
	public function create($data)
	{
		$form      = $this->getForm($data, false);
		$validData = $this->validate($form, $data);
		$table     = $this->getTable();

		if ((!empty($validData['tags']) && $validData['tags'][0] != ''))
		{
			$table->newTags = $validData['tags'];
		}

		// Prepare the table for store
		$table->bind($validData);
		$table->check();

		// Get dispatcher and include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');
		$dispatcher = $this->dispatcher;
		$context    = $this->getContext();

		$result = $dispatcher->trigger('onContentBeforeSave', array($context, $table, true));

		if (in_array(false, $result, true))
		{
			throw new RuntimeException($table->getError());
		}

		// Store the data.
		if (!$table->store())
		{
			throw new RuntimeException($table->getError());
		}

		// Clean the cache.
		$this->cleanCache();

		// Trigger the onContentAfterSave event.
		$dispatcher->trigger('onContentAfterSave', array($context, $table, true));

		$pkName = $table->getKeyName();

		if (isset($table->$pkName))
		{
			$context = $this->getContext();
			$this->state->set($context . '.id', $table->$pkName);
		}

		return true;
	}

	/**
	 * Method to validate data and update into db
	 *
	 * @param array $data
	 *
	 * @return boolean
	 *
	 * @since  3.4
	 * @throws RuntimeException
	 */
	public function update($data)
	{
		$form      = $this->getForm($data, false);
		$validData = $this->validate($form, $data);
		$table     = $this->getTable();

		if ((!empty($validData['tags']) && $validData['tags'][0] != ''))
		{
			$table->newTags = $validData['tags'];
		}

		//prepare the table for store
		$pk = $data[$table->getKeyName()];
		$table->load($pk);
		$table->bind($validData);
		$table->check();

		// Get dispatcher and include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');
		$dispatcher = $this->dispatcher;

		$result = $dispatcher->trigger('onContentBeforeSave', array($this->option . '.' . $this->getName(), $table, false));

		if (in_array(false, $result, true))
		{
			throw new RuntimeException($table->getError());
		}

		// Store the data.
		if (!$table->store())
		{
			throw new RuntimeException($table->getError());
		}

		// Clean the cache.
		$this->cleanCache();

		// Trigger the onContentAfterSave event.
		$dispatcher->trigger('onContentAfterSave', array($this->option . '.' . $this->getName(), $table, false));

		$pkName = $table->getKeyName();

		if (isset($table->$pkName))
		{
			$this->state->set($this->getName() . '.id', $table->$pkName);
		}

		return true;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param array $cid array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since     3.4
	 * @throws    RuntimeException
	 * @internal  param  array  $pks
	 */
	public function delete($cid)
	{
		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');
		$dispatcher = $this->dispatcher;

		// Get the id's of the items to delete
		$pks = (array) $cid;

		foreach ($pks as $pk)
		{
			$context      = $this->option . '.' . $this->getName();
			$activeRecord = $this->getActiveRecord($pk);

			if ($this->canDelete('core.delete', $activeRecord))
			{
				// Trigger the onContentBeforeDelete event.
				$result = $dispatcher->trigger('onContentBeforeDelete', array($context, $activeRecord));

				if (in_array(false, $result, true))
				{
					throw new RuntimeException($activeRecord->getError());
				}

				$activeRecord->delete($pk);

				// Trigger the onContentAfterDelete event.
				$dispatcher->trigger('onContentAfterDelete', array($context, $activeRecord));
			}
			else
			{
				throw new RuntimeException($this->text_prefix . '_ACL_ERROR_DELETE_NOT_PERMITTED');

			}
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	public function publish($pks, $value = 1)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$user       = JFactory::getUser();
		$table      = $this->getTable();
		$pks        = (array) $pks;

		// Include the content plugins for the change of state event.
		JPluginHelper::importPlugin('content');

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			$table->reset();

			if ($table->load($pk))
			{
				if (!$this->canEditState($table))
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JLog::add(JText::_($this->text_prefix . '_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), JLog::WARNING, 'jerror');

					return false;
				}
			}
		}

		// Attempt to change the state of the records.
		if (!$table->publish($pks, $value, $user->get('id')))
		{
			throw new RuntimeException($table->getError());

			return false;
		}

		$context = $this->option . '.' . $this->name;

		// Trigger the onContentChangeState event.
		$result = $dispatcher->trigger($this->event_change_state, array($context, $pks, $value));

		if (in_array(false, $result, true))
		{
			throw new RuntimeException($dispatcher->getError());

			return false;
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   string  $action  The action to check
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. 
	 *                   Defaults to the permission set in the component.
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	protected function canDelete($action = 'core.delete', $record)
	{
		if (!empty($record->id))
		{
			// We can only delete records that have been trashed
			if ($record->published != -2)
			{
				return false;
			}

			return parent::allowAction($action, $this->option, $record);
		}

		throw new RuntimeException('An invalid record was passed');
	}

	/**
	 * Method to test whether a record can have its state changed.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since   3.4
	 */
	protected function canEditState($record)
	{
		return parent::allowAction('core.edit.state', $this->option, $record);
	}

	/**
	 * Method to reorder one or more records
	 *
	 * @param  array   $cid
	 * @param  string  $direction up or down
	 *
	 * @since  3.4
	 * @throws RuntimeException
	 * @return boolean
	 */
	public function reorder($cid, $direction)
	{
		$direction = strtoupper($direction);

		if ($direction == 'UP')
		{
			$delta = -1;
		}
		elseif ($direction == 'DOWN')
		{
			$delta = 1;
		}
		else
		{
			$delta = null;
		}

		$pks = (array) $cid;

		foreach ($pks as $pk)
		{
			$activeRecord = $this->getActiveRecord($pk);

			if ($this->allowAction('core.edit.state', $this->option, $activeRecord))
			{
				$where = $activeRecord->getReorderConditions($activeRecord);
				$activeRecord->moveOrder($pk, $delta, $where);
			}
			else
			{
				throw new RuntimeException($this->text_prefix . '_LIB_ACL_ERROR_EDIT_STATE_NOT_PERMITTED');
			}
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param   array   $cid    An array of primary key ids.
	 * @param   integer $order  +1 or -1
	 *
	 * @return  mixed
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	public function saveorder($cid = null, $order = null)
	{
		if (empty($cid))
		{
			throw new RuntimeException(JText::_($this->text_prefix . '_LIB_MODEL_ERROR_NO_ITEMS_SELECTED'));
		}


		/**
		 *  This is something that needs to be worked out once changes to JTable are completed
		 *  Commented out because I haven't really studied the implementation, so this code might not be correct.
		 * $table          = $this->getTable();
		 * $tableClassName = get_class($table);
		 * $contentType    = new JUcmType;
		 * $type           = $contentType->getTypeByTable($tableClassName);
		 * $typeAlias      = $type->type_alias;
		 * $tagsObserver   = $table->getObserverOfClass('JTableObserverTags');
		*/

		$conditions = array();
		$pks       = (array) $cid;

		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$activeRecord = $this->getActiveRecord($pk);

			// Access checks.
			if ($this->allowAction('core.edit.state', $this->option, $activeRecord))
			{
				$activeRecord->ordering = $order[$i];

				// Store the data.
				if (!$activeRecord->store())
				{
					throw new RuntimeException($activeRecord->getError());
				}

				// Remember to reorder within position and client_id
				$condition = $activeRecord->getReorderConditions($activeRecord);
				$found     = false;

				foreach ($conditions as $cond)
				{
					if ($cond[1] == $condition)
					{
						$found = true;

						break;
					}
				}

				if (!$found)
				{
					$key          = $activeRecord->getKeyName();
					$conditions[] = array($activeRecord->$key, $condition);
				}
			}
		}

		// Execute reorder for each condition.
		foreach ($conditions as $cond)
		{
			$table = $this->getTable();
			$table->load($cond[0]);
			$table->reorder($cond[1]);
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}
}
