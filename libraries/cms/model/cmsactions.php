<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Base Cms model.
 *
 * @package     Joomla.Libraries
 * @subpackage  Model
 * @since       3.4
 */
abstract class JModelCmsactions extends JModelCms
{
	/**
	 * The event to trigger after deleting the data.
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $event_after_delete = 'onContentAfterDelete';

	/**
	 * The event to trigger after saving the data.
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $event_after_save = 'onContentAfterSave';

	/**
	 * The event to trigger before deleting the data.
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $event_before_delete = 'onContentBeforeDelete';

	/**
	 * The event to trigger before saving the data.
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $event_before_save = 'onContentBeforeSave';

	/**
	 * The event to trigger after changing the published state of the data.
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $event_change_state = 'onContentChangeState';

	/**
	 * Constructor
	 *
	 * @param   array             $config      An array of configuration options. Must have view
	 *                                         and option keys.
	 * @param   JDatabaseDriver   $db          The database adpater.
	 * @param   JEventDispatcher  $dispatcher  The event dispatcher
	 *
	 * @since   3.4
	 */
	public function __construct(array $config, JDatabaseDriver $db = null, JEventDispatcher $dispatcher = null)
	{
		parent::__construct($config, $db, $dispatcher);

		if (isset($config['event_after_delete']))
		{
			$this->event_after_delete = $config['event_after_delete'];
		}

		if (isset($config['event_after_save']))
		{
			$this->event_after_save = $config['event_after_save'];
		}

		if (isset($config['event_before_delete']))
		{
			$this->event_before_delete = $config['event_before_delete'];
		}

		if (isset($config['event_before_save']))
		{
			$this->event_before_save = $config['event_before_save'];
		}

		if (isset($config['event_change_state']))
		{
			$this->event_change_state = $config['event_change_state'];
		}
	}

	/**
	 * Method to authorise the current user for an action.
	 * This method is intended to be overridden to allow for customized access rights
	 *
	 * @param   string  $action     ACL action string. e.g. 'core.create'.
	 * @param   string  $assetName  Asset name to check against.
	 * @param   JUser   $user       The user to check the action against
	 *
	 * @return bool
	 * @see JUser::authorise
	 */
	public function allowAction($action, $assetName = null)
	{
		// If we don't have an assetname use the component name by default
		$assetName = $assetName ? $assetName : $this->option;
		$user = JFactory::getUser();

		return $user->authorise($action, $assetName);
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
	 *
	 * @since   3.4
	 */
	protected function canDelete($record)
	{
		// If we can't find a record ID just return false
		if (!empty($record->id))
		{
			// The record is trashed and therefore already deleted!
			if ($record->published != -2)
			{
				return false;
			}

			return $this->allowAction('core.delete', $this->option);
		}

		return false;
	}

	/**
	 * Method to test whether a record can have its state changed. Proxies to allowAction.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since   3.4
	 */
	protected function canEditState($record)
	{
		return $this->allowAction('core.edit.state', $this->option);
	}


	/**
	 * Method to checkin a row.
	 *
	 * @param   integer  $pk  The numeric id of the primary key.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	public function checkin($pk = null)
	{
		// Only attempt to check the row in if it exists.
		if ($pk)
		{
			throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_CHECKIN_NO_KEY'));
		}

		$user = JFactory::getUser();

		// Get an instance of the row to checkin.
		$table = $this->getTable();

		if (!$table->load($pk))
		{
			throw new RuntimeException($table->getError());
		}

		// Check if this is the user has previously checked out the row.
		$isCheckedOut = ($table->checked_out > 0);
		$canCheckIn = ($table->checked_out == $user->get('id') && $user->authorise('core.admin', 'com_checkin'));

		if($isCheckedOut && !$canCheckIn)
		{
			throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_CHECKIN_USER_MISMATCH'));
		}

		// Attempt to check the row in.
		if (!$table->checkin($pk))
		{
			throw new RuntimeException($table->getError());
		}

		return true;
	}

	/**
	 * Method to check-out a row for editing.
	 *
	 * @param   integer  $pk  The numeric id of the primary key.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	public function checkout($pk = null)
	{
		// Only attempt to check the row in if it exists.
		if ($pk)
		{
			throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_CHECKOUT_NO_KEY'));
		}

		$user = JFactory::getUser();

		// Get an instance of the row to checkout.
		$table = $this->getTable();

		if (!$table->load($pk))
		{
			throw new RuntimeException($table->getError());
		}

		// Check if this is the user having previously checked out the row.
		if ($table->checked_out > 0 && $table->checked_out != $user->get('id'))
		{
			throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_CHECKOUT_USER_MISMATCH'));
		}

		// Attempt to check the row out.
		if (!$table->checkout($user->get('id'), $pk))
		{
			throw new RuntimeException($table->getError());
		}

		return true;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   mixed  $pks  A primary key or array of record primary keys.
	 *
	 * @return  integer  Number of rows affected if successful
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	public function delete($pks)
	{
		if (empty($pks))
		{
			throw new RuntimeException('No record selected', 404);
		}

		$table = $this->getTable();
		$tableName = $table->getTableName();
		$key = $table->getKeyName();
		$db = $this->getDb();
		$query = $db->getQuery(true);

		$query->delete($db->quoteName($tableName));

		if (!is_array($pks))
		{
			$query->where($db->setQuery($key) . ' = ' . $pks);
		}
		else
		{
			$pksImploded = implode(',', $pks);
			$query->where($db->quoteName($key) . ' IN (' . $pksImploded . ')');
		}

		$db->setQuery($query);

		try
		{
			$result = $db->execute();
		}
		catch (Exception $e)
		{
			throw new RuntimeException($e->getMessage(), 404);
		}

		if ($result && $db->getAffectedRows())
		{
			// Successful result. Return the number of rows affected
			return $db->getAffectedRows();
		}
		elseif ($result)
		{
			// We have no rows affected. Throw an exception.
			throw new RuntimeException('Record Not Found', 404);
		}

		// We don't have a result from the database. Throw an exception
		throw new RuntimeException('Delete failed', 500);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	public function save(&$data)
	{
		$table = $this->getTable();

		if ((!empty($data['tags']) && $data['tags'][0] != ''))
		{
			$table->newTags = $data['tags'];
		}

		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');

		// Load the row if saving an existing record.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		// Bind the data.
		if (!$table->bind($data))
		{
			throw new RuntimeException($table->getError());
		}

		// Prepare the row for saving
		$table = $this->prepareTable($table);

		// Check the data.
		if (!$table->check())
		{
			throw new RuntimeException($table->getError());
		}

		// If the dispatcher throws an exception abort here
		try
		{
			// Trigger the onContentBeforeSave event.
			$result = $this->dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, $table, $isNew));

			if (in_array(false, $result, true))
			{
				// Handle if the plugin is still using JError to set errors
				throw new RuntimeException($this->dispatcher->getError());
			}
		}
		catch (Exception $e)
		{
			throw new RuntimeException($e->getMessage());
		}

		// Store the data.
		if (!$table->store())
		{
			throw new RuntimeException($table->getError());
		}

		// Clean the cache.
		$this->cleanCache();

		// Trigger the onContentAfterSave event.
		$this->dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, $table, $isNew));

		return true;
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   JTableInterface  $table  A reference to a JTable object.
	 *
	 * @return  JTableInterface
	 *
	 * @since   3.4
	 */
	protected function prepareTable(JTableInterface $table)
	{
		// Derived class will provide its own implementation if required.
	}
}
