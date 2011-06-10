<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.application.component.modelform');

/**
 * Prototype admin model.
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       11.1
 */
abstract class JModelAdmin extends JModelForm
{
	/**
	 * @var    string	The prefix to use with controller messages.
	 * @since  11.1
	 */
	protected $text_prefix = null;

	/**
	 * @var    string	The event to trigger after deleting the data.
	 * @since  11.1
	 */
	protected $event_after_delete = null;

	/**
	 * @var    string	The event to trigger after saving the data.
	 * @since  11.1
	 */
	protected $event_after_save = null;

	/**
	 * @var    string	The event to trigger before deleting the data.
	 * @since  11.1
	 */
	protected $event_before_delete = null;

	/**
	 * @var    string	The event to trigger before saving the data.
	 * @since  11.1
	 */
	protected $event_before_save = null;

	/**
	 * @var    string	The event to trigger after changing the published state of the data.
	 * @since  11.1
	 */
	protected $event_change_state = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config	An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since  11.1
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		if (isset($config['event_after_delete'])) {
			$this->event_after_delete = $config['event_after_delete'];
		} else  if (empty($this->event_after_delete)) {
			$this->event_after_delete = 'onContentAfterDelete';
		}

		if (isset($config['event_after_save'])) {
			$this->event_after_save = $config['event_after_save'];
		} else  if (empty($this->event_after_save)) {
			$this->event_after_save = 'onContentAfterSave';
		}

		if (isset($config['event_before_delete'])) {
			$this->event_before_delete = $config['event_before_delete'];
		} else  if (empty($this->event_before_delete)) {
			$this->event_before_delete = 'onContentBeforeDelete';
		}

		if (isset($config['event_before_save'])) {
			$this->event_before_save = $config['event_before_save'];
		} else  if (empty($this->event_before_save)) {
			$this->event_before_save = 'onContentBeforeSave';
		}

		if (isset($config['event_change_state'])) {
			$this->event_change_state = $config['event_change_state'];
		} else  if (empty($this->event_change_state)) {
			$this->event_change_state = 'onContentChangeState';
		}

		// Guess the JText message prefix. Defaults to the option.
		if (isset($config['text_prefix'])) {
			$this->text_prefix = strtoupper($config['text_prefix']);
		} else  if (empty($this->text_prefix)) {
			$this->text_prefix = strtoupper($this->option);
		}
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object   $record	A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 * @since   11.1
	 */
	protected function canDelete($record)
	{
		$user = JFactory::getUser();
		return $user->authorise('core.delete', $this->option);
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object   $record	A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 * @since   11.1
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();
		return $user->authorise('core.edit.state', $this->option);
	}

	/**
	 * Method override to check-in a record or an array of record
	 *
	 * @param   integer|array	$pks	The ID of the primary key or an array of IDs
	 *
	 * @return  mixed    Boolean false if there is an error, otherwise the count of records checked in.
	 * @since   11.1
	 */
	public function checkin($pks = array())
	{
		// Initialise variables.
		$user		= JFactory::getUser();
		$pks		= (array) $pks;
		$table		= $this->getTable();
		$count		= 0;

		if (empty($pks)) {
			$pks = array((int) $this->getState($this->getName().'.id'));
		}

		// Check in all items.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk)) {

				if ($table->checked_out > 0) {
					if (!parent::checkin($pk)) {
						return false;
					}
					$count++;
				}
			}
			else {
				$this->setError($table->getError());

				return false;
			}
		}

		return $count;
	}

	/**
	 * Method override to check-out a record.
	 *
	 * @param   integer  $pk	The ID of the primary key.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 * @since   11.1
	 */
	public function checkout($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState($this->getName().'.id');

		return parent::checkout($pk);
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array    $pks	An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 * @since   11.1
	 */
	public function delete(&$pks)
	{
		// Initialise variables.
		$dispatcher	= JDispatcher::getInstance();
		$user		= JFactory::getUser();
		$pks		= (array) $pks;
		$table		= $this->getTable();

		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk) {

			if ($table->load($pk)) {

				if ($this->canDelete($table)) {

					$context = $this->option.'.'.$this->name;

					// Trigger the onContentBeforeDelete event.
					$result = $dispatcher->trigger($this->event_before_delete, array($context, $table));
					if (in_array(false, $result, true)) {
						$this->setError($table->getError());
						return false;
					}

					if (!$table->delete($pk)) {
						$this->setError($table->getError());
						return false;
					}

					// Trigger the onContentAfterDelete event.
					$dispatcher->trigger($this->event_after_delete, array($context, $table));

				} else {

					// Prune items that you can't change.
					unset($pks[$i]);
					$error = $this->getError();
					if ($error) {
						JError::raiseWarning(500, $error);
					}
					else {
						JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));
					}
				}

			} else {
				$this->setError($table->getError());
				return false;
			}
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk	The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 * @since   11.1
	 */
	public function getItem($pk = null)
	{
		// Initialise variables.
		$pk		= (!empty($pk)) ? $pk : (int) $this->getState($this->getName().'.id');
		$table	= $this->getTable();

		if ($pk > 0) {
			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError()) {
				$this->setError($table->getError());
				return false;
			}
		}

		// Convert to the JObject before adding other data.
		$properties = $table->getProperties(1);
		$item = JArrayHelper::toObject($properties, 'JObject');

		if (property_exists($item, 'params')) {
			$registry = new JRegistry;
			$registry->loadJSON($item->params);
			$item->params = $registry->toArray();
		}

		return $item;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   object  $table	A JTable object.
	 *
	 * @return  array  An array of conditions to add to ordering queries.
	 * @since   11.1
	 */
	protected function getReorderConditions($table)
	{
		return array();
	}

	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 * @since   11.1
	 */
	protected function populateState()
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');
		$table = $this->getTable();
		$key = $table->getKeyName();

		// Get the pk of the record from the request.
		$pk = JRequest::getInt($key);
		$this->setState($this->getName().'.id', $pk);

		// Load the parameters.
		$value = JComponentHelper::getParams($this->option);
		$this->setState('params', $value);
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   JTable	$table	A reference to a JTable object.
	 *
	 * @return  void
	 * @since   11.1
	 */
	protected function prepareTable(&$table)
	{
		// Derived class will provide its own implentation if required.
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    $pks	A list of the primary keys to change.
	 * @param   integer  $value	The value of the published state.
	 *
	 * @return  boolean  True on success.
	 * @since   11.1
	 */
	function publish(&$pks, $value = 1)
	{
		// Initialise variables.
		$dispatcher	= JDispatcher::getInstance();
		$user		= JFactory::getUser();
		$table		= $this->getTable();
		$pks		= (array) $pks;

		// Include the content plugins for the change of state event.
		JPluginHelper::importPlugin('content');

		// Access checks.
		foreach ($pks as $i => $pk) {
			$table->reset();

			if ($table->load($pk)) {
				if (!$this->canEditState($table)) {
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
					return false;
				}
			}
		}

		// Attempt to change the state of the records.
		if (!$table->publish($pks, $value, $user->get('id'))) {
			$this->setError($table->getError());
			return false;
		}

		$context = $this->option.'.'.$this->name;

		// Trigger the onContentChangeState event.
		$result = $dispatcher->trigger($this->event_change_state, array($context, $pks, $value));

		if (in_array(false, $result, true)) {
			$this->setError($table->getError());
			return false;
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to adjust the ordering of a row.
	 *
	 * Returns NULL if the user did not have edit
	 * privileges for any of the selected primary keys.
	 *
	 * @param   integer  $pks    The ID of the primary key to move.
	 * @param   integer  $delta  Increment, usually +1 or -1
	 *
	 * @return  boolean|null	False on failure or error, true on success.
	 * @since   11.1
	 */
	public function reorder($pks, $delta = 0)
	{
		// Initialise variables.
		$user	= JFactory::getUser();
		$table	= $this->getTable();
		$pks	= (array) $pks;
		$result	= true;

		$allowed = true;

		foreach ($pks as $i => $pk) {
			$table->reset();

			if ($table->load($pk) && $this->checkout($pk)) {
				// Access checks.
				if (!$this->canEditState($table)) {
					// Prune items that you can't change.
					unset($pks[$i]);
					$this->checkin($pk);
					JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
					$allowed = false;
					continue;
				}

				$where = array();
				$where = $this->getReorderConditions($table);

				if (!$table->move($delta, $where)) {
					$this->setError($table->getError());
					unset($pks[$i]);
					$result = false;
				}

				$this->checkin($pk);
			} else {
				$this->setError($table->getError());
				unset($pks[$i]);
				$result = false;
			}
		}

		if ($allowed === false && empty($pks)) {
			$result = null;
		}

		// Clear the component's cache
		if ($result == true) {
			$this->cleanCache();
		}

		return $result;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data	The form data.
	 *
	 * @return  boolean  True on success.
	 * @since   11.1
	 */
	public function save($data)
	{
		// Initialise variables;
		$dispatcher = JDispatcher::getInstance();
		$table		= $this->getTable();
		$key			= $table->getKeyName();
		$pk			= (!empty($data[$key])) ? $data[$key] : (int)$this->getState($this->getName().'.id');
		$isNew		= true;

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');

		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			if ($pk > 0) {
				$table->load($pk);
				$isNew = false;
			}

			// Bind the data.
			if (!$table->bind($data)) {
				$this->setError($table->getError());
				return false;
			}

			// Prepare the row for saving
			$this->prepareTable($table);

			// Check the data.
			if (!$table->check()) {
				$this->setError($table->getError());
				return false;
			}

			// Trigger the onContentBeforeSave event.
			$result = $dispatcher->trigger($this->event_before_save, array($this->option.'.'.$this->name, &$table, $isNew));
			if (in_array(false, $result, true)) {
				$this->setError($table->getError());
				return false;
			}

			// Store the data.
			if (!$table->store()) {
				$this->setError($table->getError());
				return false;
			}

			// Clean the cache.
			$this->cleanCache();

			// Trigger the onContentAfterSave event.
			$dispatcher->trigger($this->event_after_save, array($this->option.'.'.$this->name, &$table, $isNew));
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$pkName = $table->getKeyName();

		if (isset($table->$pkName)) {
			$this->setState($this->getName().'.id', $table->$pkName);
		}
		$this->setState($this->getName().'.new', $isNew);

		return true;
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param   array    $pks	An array of primary key ids.
	 * @param   integer  $order	+/-1
	 *
	 * @return  mixed
	 * @since   11.1
	 */
	function saveorder($pks = null, $order = null)
	{
		// Initialise variables.
		$table		= $this->getTable();
		$conditions	= array();
		$user = JFactory::getUser();

		if (empty($pks)) {
			return JError::raiseWarning(500, JText::_($this->text_prefix.'_ERROR_NO_ITEMS_SELECTED'));
		}

		// update ordering values
		foreach ($pks as $i => $pk) {
			$table->load((int) $pk);

			// Access checks.
			if (!$this->canEditState($table)) {
				// Prune items that you can't change.
				unset($pks[$i]);
				JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
			} else if ($table->ordering != $order[$i]) {
				$table->ordering = $order[$i];

				if (!$table->store()) {
					$this->setError($table->getError());
					return false;
				}

				// Remember to reorder within position and client_id
				$condition = $this->getReorderConditions($table);
				$found = false;

				foreach ($conditions as $cond) {
					if ($cond[1] == $condition) {
						$found = true;
						break;
					}
				}

				if (!$found) {
					$key = $table->getKeyName();
					$conditions[] = array ($table->$key, $condition);
				}
			}
		}

		// Execute reorder for each category.
		foreach ($conditions as $cond) {
			$table->load($cond[0]);
			$table->reorder($cond[1]);
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}
}