<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Application
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * Prototype admin model.
 *
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.6
 */
abstract class JModelAdmin extends JModelForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = null;

	/**
	 * @var		string	The event to trigger after saving the data.
	 * @since	1.6
	 */
	protected $event_after_save = null;

	/**
	 * @var		string	The event to trigger after before the data.
	 * @since	1.6
	 */
	protected $event_before_save = null;

	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		if (isset($config['event_after_save'])) {
			$this->eventAfterSave = $config['event_after_save'];
		} else  if (empty($this->event_after_save)) {
			$this->event_after_save = 'onContentAfterSave';
		}

		if (isset($config['event_before_save'])) {
			$this->eventAfterSave = $config['event_before_save'];
		} else  if (empty($this->event_before_save)) {
			$this->event_before_save = 'onContentBeforeSave';
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
	 * @param	object	A record object.
	 * @return	boolean	True if allowed to delete the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canDelete($record)
	{
		$user = JFactory::getUser();
		return $user->authorise('core.delete', $this->option);
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	A record object.
	 * @return	boolean	True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();
		return $user->authorise('core.edit.state', $this->option);
	}

	/**
	 * Method override to check-in a record.
	 *
	 * @param	integer	The ID of the primary key.
	 * @return	boolean
	 * @since	1.6
	 */
	public function checkin($pk = null)
	{
		// Initialise variables.
		$pk	= (!empty($pk)) ? $pk : (int) $this->getState($this->getName().'.id');

		return parent::checkin($pk);
	}

	/**
	 * Method override to check-out a record.
	 *
	 * @param	int		The ID of the primary key.
	 * @return	boolean	True if successful, false if an error occurs.
	 * @since	1.6
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
	 * @param	array	An array of record primary keys.
	 * @return	boolean	True if successful, false if an error occurs.
	 * @since	1.6
	 */
	public function delete(&$pks)
	{
		// Typecast variable.
		$pks = (array) $pks;
		$user = JFactory::getUser();

		// Get a row instance.
		$table = $this->getTable();

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk) {
			if ($table->load($pk)) {
				if ($this->canDelete($table)) {
					if (!$table->delete($pk)) {
						$this->setError($table->getError());
						return false;
					}
				} else {
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDIT_STATE_NOT_PERMITTED'));
				}
			} else {
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
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
		$item = JArrayHelper::toObject($table->getProperties(1), 'JObject');

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
	 * @param	object	A record object.
	 * @return	array	An array of conditions to add to add to ordering queries.
	 * @since	1.6
	 */
	protected function getReorderConditions($record = null)
	{
		return array();
	}

	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('administrator');

		// Load the User state.
		if (!($pk = (int) $app->getUserState($this->option.'.edit.'.$this->getName().'.id'))) {
			$pk = (int) JRequest::getInt('id');
		}
		$this->setState($this->getName().'.id', $pk);

		// Load the parameters.
		$value	= JComponentHelper::getParams($this->option);
		$this->setState('params', $value);
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param	JTable	A JTable object.
	 * @since	1.6
	 */
	protected function prepareTable($table)
	{
		// Derived class will provide its own implentation if required.
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param	array	A list of the primary keys to change.
	 * @param	int		The value of the published state.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	function publish(&$pks, $value = 1)
	{
		// Initialise variables.
		$user	= JFactory::getUser();
		$table	= $this->getTable();
		$pks	= (array) $pks;

		// Access checks.
		foreach ($pks as $i => $pk) {
			if ($table->load($pk)) {
				if (!$this->canEditState($table)) {
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDIT_STATE_NOT_PERMITTED'));
				}
			}
		}

		// Attempt to change the state of the records.
		if (!$table->publish($pks, $value, $user->get('id'))) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

	/**
	 * Method to adjust the ordering of a row.
	 *
	 * @param	int		The ID of the primary key to move.
	 * @param	integer	Increment, usually +1 or -1
	 * @return	boolean	False on failure or error, true otherwise.
	 * @since	1.6
	 */
	public function reorder($pks, $delta = 0)
	{
		// Initialise variables.
		$user	= JFactory::getUser();
		$table	= $this->getTable();
		$pks	= (array) $pks;
		$result	= true;

		foreach ($pks as $i => $pk) {
			$table->reset();
			if ($table->load($pk) && $this->checkout($pk)) {
				// Access checks.
				if (!$this->canEditState($table)) {
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JERROR_CORE_EDIT_STATE_NOT_PERMITTED'));
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

		return $result;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
		// Initialise variables;
		$dispatcher = JDispatcher::getInstance();
		$table		= $this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int)$this->getState($this->getName().'.id');
		$isNew		= true;

		// Include the content plugins for the onSave events.
		JPluginHelper::importPlugin('content');

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
		$cache = JFactory::getCache($this->option);
		$cache->clean();

		// Trigger the onContentAfterSave event.
		$dispatcher->trigger($this->event_after_save, array($this->option.'.'.$this->name, &$table, $isNew));

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
	 * @param	array	An array of primary key ids.
	 * @param	int		+/-1
	 * @since	1.6
	 */
	function saveorder($pks, $order)
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
				JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDIT_STATE_NOT_PERMITTED'));
			} else if ($table->ordering != $order[$i]) {
				$table->ordering = $order[$i];
				if (!$table->store()) {
					$this->setError($table->getError());
					return false;
				}
				// remember to reorder within position and client_id
				$condition = $this->getReorderConditions($table);
				$found = false;
				foreach ($conditions as $cond) {
					if ($cond[1] == $condition) {
						$found = true;
						break;
					}
				}
				if (!$found) {
					$conditions[] = array ($table->id, $condition);
				}
			}
		}

		// Execute reorder for each category.
		foreach ($conditions as $cond) {
			$table->load($cond[0]);
			$table->reorder($cond[1]);
		}

		// Clear the component's cache
		$cache = JFactory::getCache($this->option);
		$cache->clean();

		return true;
	}
}