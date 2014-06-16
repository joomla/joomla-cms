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
	 * @param   JDatabaseDriver   $db          The database adpater.
	 * @param   JEventDispatcher  $dispatcher  The event dispatcher
	 * @param   array             $config      An array of configuration options. Must have view
	 *                                         and option keys.
	 *
	 * @since   3.4
	 */
	public function __construct(JDatabaseDriver $db = null, JEventDispatcher $dispatcher = null, $config = array())
	{
		parent::__construct($db, $dispatcher, $config);

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

			return false;
		}

		$user = JFactory::getUser();

		// Get an instance of the row to checkin.
		$table = $this->getTable();

		if (!$table->load($pk))
		{
			throw new RuntimeException($table->getError());

			return false;
		}

		// Check if this is the user has previously checked out the row.
		if ($table->checked_out > 0 && $table->checked_out != $user->get('id') && !$user->authorise('core.admin', 'com_checkin'))
		{
			throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_CHECKIN_USER_MISMATCH'));

			return false;
		}

		// Attempt to check the row in.
		if (!$table->checkin($pk))
		{
			throw new RuntimeException($table->getError());

			return false;
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

			return false;
		}

		$user = JFactory::getUser();

		// Get an instance of the row to checkout.
		$table = $this->getTable();

		if (!$table->load($pk))
		{
			throw new RuntimeException($table->getError());

			return false;
		}

		// Check if this is the user having previously checked out the row.
		if ($table->checked_out > 0 && $table->checked_out != $user->get('id'))
		{
			throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_CHECKOUT_USER_MISMATCH'));

			return false;
		}

		// Attempt to check the row out.
		if (!$table->checkout($user->get('id'), $pk))
		{
			throw new RuntimeException($table->getError());

			return false;
		}

		return true;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   mixed  &$pks  A primary key or array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   3.4
	 */
	public function delete(&$pks)
	{
		if (empty($pks))
		{
			throw new RuntimeException('No record selected', 404);
		}

		$table = $this->getTable();
		$tableName = $table->getTableName();
		$key = $table->getKeyName();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->delete($db->quoteName($tableName));

		if (!is_array($pks))
		{
			$query->where($db->setQuery($key) . ' = ' . $pk);
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
			throw new RuntimeException('Error in query', 404);
		}

		if ($result && $db->getAffectedRows())
		{
			$app = JFactory::getApplication();
			$app->setHeader('status', '204 Deleted');

			return true;
		}
		elseif ($result)
		{
			throw new RuntimeException('Record Not Found', 404);
		}
		else
		{
			throw new RuntimeException('Delete failed', 500);
		}
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   3.4
	 */
	public function save(&$data)
	{
		$dispatcher = JEventDispatcher::getInstance();
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

		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
			}

			// Bind the data.
			if (!$table->bind($data))
			{
				$this->setError($table->getError());

				return false;
			}

			// Prepare the row for saving
			$this->prepareTable($table);

			// Check the data.
			if (!$table->check())
			{
				$this->setError($table->getError());
				return false;
			}

			// Trigger the onContentBeforeSave event.
			$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, $table, $isNew));

			if (in_array(false, $result, true))
			{
				$this->setError($table->getError());
				return false;
			}

			// Store the data.
			if (!$table->store())
			{
				$this->setError($table->getError());

				return false;
			}

			// Clean the cache.
			$this->cleanCache();

			// Trigger the onContentAfterSave event.
			$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, $table, $isNew));
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}
}
