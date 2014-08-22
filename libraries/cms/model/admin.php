<?php
/**
 * @package     Joomla.CMS
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_PLATFORM') or die();

/**
 * Prototype admin model.
 *
 * @package Joomla.CMS
 * @subpackage Model
 */
class JCmsModelAdmin extends JCmsModel
{

	/**
	 * Context, used to get user session data and also trigger plugin
	 *
	 * @var string
	 */
	protected $context;

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var string
	 */
	protected $languagePrefix = null;

	/**
	 * Array of form objects.
	 *
	 * @var array
	 */
	protected $forms = array();

	/**
	 * Need to process checkin on this model or not
	 *
	 * @var boolean
	 */
	public $checkin = false;

	/**
	 * The event to trigger after deleting the data.
	 *
	 * @var string
	 */
	protected $eventAfterDelete = null;

	/**
	 * The event to trigger after saving the data.
	 *
	 * @var string
	 */
	protected $eventAfterSave = null;

	/**
	 * The event to trigger before deleting the data.
	 *
	 * @var string
	 */
	protected $eventBeforeDelete = null;

	/**
	 * The event to trigger before saving the data.
	 *
	 * @var string
	 */
	protected $eventBeforeSave = null;

	/**
	 * The event to trigger after changing the published state of the data.
	 *
	 * @var string
	 */
	protected $eventChangeState = null;

	/**
	 * Name of plugin group which will be loaded to process the triggered event.
	 * Default is component name
	 *
	 * @var string
	 */
	protected $pluginGroup = null;

	/**
	 * Constructor.
	 *
	 * @param array $config An optional associative array of configuration settings.
	 *        	
	 * @see JCmsModel
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		//Insert the default model states for admin
		$this->state->insert('id', 'int', 0)->insert('cid', 'array', array());
		
		//Initialize the events
		if (isset($config['event_after_delete']))
		{
			$this->eventAfterDelete = $config['event_after_delete'];
		}
		elseif (empty($this->eventAfterDelete))
		{
			$this->eventAfterDelete = 'onContentAfterDelete';
		}
		
		if (isset($config['event_after_save']))
		{
			$this->eventAfterSave = $config['event_after_save'];
		}
		elseif (empty($this->eventAfterSave))
		{
			$this->eventAfterSave = 'onContentAfterSave';
		}
		
		if (isset($config['event_before_delete']))
		{
			$this->eventBeforeDelete = $config['event_before_delete'];
		}
		elseif (empty($this->eventBeforeDelete))
		{
			$this->eventBeforeDelete = 'onContentBeforeDelete';
		}
		
		if (isset($config['event_before_save']))
		{
			$this->eventBeforeSave = $config['event_before_save'];
		}
		elseif (empty($this->eventBeforeSave))
		{
			$this->eventBeforeSave = 'onContentBeforeSave';
		}
		
		if (isset($config['event_change_state']))
		{
			$this->eventChangeState = $config['event_change_state'];
		}
		elseif (empty($this->eventChangeState))
		{
			$this->eventChangeState = 'onContentChangeState';
		}
		
		// JText message prefix. Defaults to the name of component.
		if (isset($config['language_prefix']))
		{
			$this->languagePrefix = strtoupper($config['language_prefix']);
		}
		elseif (empty($this->languagePrefix))
		{
			$this->languagePrefix = strtoupper(substr($this->option, 4));
		}
		
		if (isset($config['plugin_group']))
		{
			$this->pluginGroup = $config['plugin_group'];
		}
		elseif (empty($this->pluginGroup))
		{
			//Plugin group should default to component name
			$this->pluginGroup = substr($this->option, 4);
		}
		//Check to see whether we will process checkin for this model or not		
		$fields = array_keys($this->db->getTableColumns($this->table));
		if (in_array('checked_out', $fields) && in_array('checked_out_time', $fields))
		{
			$this->checkin = true;
		}
		
		$this->context = $this->option . '.' . $this->name;
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @param array $data Data for the form.
	 *        	
	 * @param boolean $loadData True if the form is to load its own data (default case), false if not.
	 *        	
	 * @return mixed A JForm object on success, false on failure
	 *
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm($this->context, $this->name, array('control' => 'jform', 'load_data' => $loadData));
		
		if (empty($form))
		{
			return false;
		}
		// We don't allows change ordering on the form
		$form->setFieldAttribute('ordering', 'filter', 'unset');
		
		if (empty($data))
		{
			$data = $this->loadFormData();
		}
		if (!$this->canEditState((object) $data))
		{
			// Disable fields for display.
			$form->setFieldAttribute('published', 'disabled', 'true');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}
		
		return $form;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param JForm $form The form to validate against.
	 *        	
	 * @param array $data The data to validate.
	 *        	
	 * @param string $group The name of the field group to validate.
	 *        	
	 * @return mixed Array of filtered data if valid, false otherwise.
	 *
	 * @see JFormRule
	 * @see JFilterInput
	 */
	public function validate($form, $data, $group = null)
	{
		// Filter and validate the form data.
		$data = $form->filter($data);
		$return = $form->validate($data, $group);
		
		// Check for an error.
		if ($return instanceof Exception)
		{
			$this->setError($return->getMessage());
			return false;
		}
		
		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message)
			{
				$this->setError($message);
			}
			
			return false;
		}
		
		// Tags B/C break at 3.1.2
		if (isset($data['metadata']['tags']) && !isset($data['tags']))
		{
			$data['tags'] = $data['metadata']['tags'];
		}
		
		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 */
	public function getItem($pk = null)
	{
		$pk = empty($pk) ? (int) $this->state->id : $pk;
		$table = $this->getTable();
		if ($pk > 0)
		{
			// Attempt to load the row.
			$return = $table->load($pk);
			
			// Check for a table object error.
			if ($return === false && $table->getError())
			{
				$this->setError($table->getError());
				return false;
			}
		}
		
		// Convert to the JObject before adding other data. ? Why ?
		$properties = $table->getProperties(1);
		$item = JArrayHelper::toObject($properties, 'JObject');
		
		if (property_exists($item, 'params'))
		{
			$registry = new JRegistry();
			$registry->loadString($item->params);
			$item->params = $registry->toArray();
		}
		
		return $item;
	}

	/**
	 * Method to save an object
	 * 
	 * @param array $data
	 * @param JInput $input
	 * @return boolean
	 */
	public function save($data, $input = null)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$table = $this->getTable();
		$id = $this->state->id;
		$isNew = true;
		if ($input)
		{
			$task = $input->getCmd('task', '');
		}
		else
		{
			$task = '';
		}
		// Include the plugins for the on save events.
		JPluginHelper::importPlugin($this->pluginGroup);
		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			if ($id > 0)
			{
				$table->load($id);
				$isNew = false;
			}
			// Bind the data.
			if (!$table->bind($data))
			{
				$this->setError($table->getError());
				return false;
			}
			// Prepare the row for saving
			$this->prepareTable($table, $task);
			// Check the data.
			if (!$table->check())
			{
				$this->setError($table->getError());
				return false;
			}
			// Trigger the onContentBeforeSave event.
			$result = $dispatcher->trigger($this->eventBeforeSave, array($this->context, $table, $isNew));
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
			$dispatcher->trigger($this->eventAfterSave, array($this->context, $table, $isNew));
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		$this->state->id = $table->id;
		return true;
	}

	/**
	 * Method to check-in a record or an array of record
	 *
	 * @param mixed $pks The ID of the primary key or an array of IDs
	 *        	
	 * @return mixed Boolean false if there is an error, otherwise the count of records checked in.
	 */
	public function checkin($pks = array())
	{
		$pks = (array) $pks;
		$table = $this->getTable();
		$count = 0;
		// Check in all items.
		foreach ($pks as $pk)
		{
			if ($table->load($pk))
			{
				if ($this->canCheckin($table))
				{
					if ($table->checked_out > 0)
					{
						if (!$table->checkin())
						{
							$this->setError($table->getError());
							return false;
						}
						$count++;
					}
				}
				else
				{
					$this->setError(JText::_('JLIB_APPLICATION_ERROR_CHECKIN_USER_MISMATCH'));
					return false;
				}
			}
			else
			{
				$this->setError($table->getError());
				return false;
			}
		}
		
		return $count;
	}

	/**
	 * Method to check-out a record.
	 *
	 * @param integer $pk The ID of the primary key.
	 *        		
	 * @return boolean True if successful, false if an error occurs.
	 *        
	 */
	public function checkout($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->state->id;
		$table = $this->getTable();
		if (!$table->load($pk))
		{
			$this->setError($table->getError());
			return false;
		}
		
		// If there is no checked_out or checked_out_time field, checkout is not needed and we just return true,
		if (!$this->checkin)
		{
			return true;
		}
		
		$user = JFactory::getUser();
		
		// Check if this is the user having previously checked out the row.
		if (!$this->canCheckout($table))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_CHECKOUT_USER_MISMATCH'));
			return false;
		}
		// Attempt to check the row out.
		if (!$table->checkout($user->get('id'), $pk))
		{
			$this->setError($table->getError());
			return false;
		}
		
		return true;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param array &$pks An array of record primary keys.
	 *        	
	 *        	
	 * @return boolean True if successful, false if an error occurs.
	 *        
	 */
	public function delete($pks)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$pks = (array) $pks;
		$table = $this->getTable();
		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin($this->pluginGroup);
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				if (!$this->canDelete($table))
				{
					$this->setError(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));
					return false;
				}
				// Trigger the onBeforeDelete event.
				$result = $dispatcher->trigger($this->eventBeforeDelete, array($this->context, $table));
				
				if (in_array(false, $result, true))
				{
					$this->setError($table->getError());
					return false;
				}
				
				if (!$table->delete($pk))
				{
					$this->setError($table->getError());
					return false;
				}
				// Trigger the onAfterDelete event.
				$dispatcher->trigger($this->eventAfterDelete, array($this->context, $table));
			}
			else
			{
				$this->setError($table->getError());
				return false;
			}
		}
		// Clear the component's cache
		$this->cleanCache();
		return true;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param array $pks A list of the primary keys to change.
	 *        	
	 * @param integer $value The value of the published state.
	 *        	
	 * @return boolean True on success.
	 */
	public function publish($pks, $value = 1)
	{
		$dispatcher = JEventDispatcher::getInstance();
		$userId = JFactory::getUser()->get('id');
		$table = $this->getTable();
		$pks = (array) $pks;
		foreach ($pks as $pk)
		{
			if ($table->load($pk))
			{
				if (!$this->canEditState($table))
				{
					$this->setError(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
					return false;
				}
			}
			else
			{
				$this->setError($table->getError());
				return false;
			}
		}
		// Attempt to change the state of the records.
		if (!$table->publish($pks, $value, $userId))
		{
			$this->setError($table->getError());
			return false;
		}
		JPluginHelper::importPlugin($this->pluginGroup);
		// Trigger the onContentChangeState event.
		$result = $dispatcher->trigger($this->eventChangeState, array($this->context, $pks, $value));
		if (in_array(false, $result, true))
		{
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
	 * @param integer $pks The ID of the primary key to move.
	 *        	
	 * @param integer $delta Increment, usually +1 or -1
	 *        	
	 * @return mixed False on failure or error, true on success, null if the $pk is empty (no items selected).
	 *        
	 */
	public function reorder($pks, $delta = 0)
	{
		$table = $this->getTable();
		$pks = (array) $pks;
		$result = true;
		
		$allowed = true;
		
		foreach ($pks as $i => $pk)
		{
			$table->reset();
			
			if ($table->load($pk) && $this->checkout($pk))
			{
				$where = $this->getReorderConditions($table);
				if (!$table->move($delta, $where))
				{
					$this->setError($table->getError());
					unset($pks[$i]);
					$result = false;
				}
				
				$this->checkin($pk);
			}
			else
			{
				$this->setError($table->getError());
				unset($pks[$i]);
				$result = false;
			}
		}
		
		if ($allowed === false && empty($pks))
		{
			$result = null;
		}
		
		// Clear the component's cache
		if ($result == true)
		{
			$this->cleanCache();
		}
		
		return $result;
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param array $pks An array of primary key ids.
	 *        	
	 * @param integer $order +1 or -1
	 *        	
	 * @return mixed
	 *
	 */
	public function saveorder($pks = null, $order = null)
	{
		$table = $this->getTable();
		$conditions = array();
		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$table->load((int) $pk);
			if ($table->ordering != $order[$i])
			{
				$table->ordering = $order[$i];
				if (!$table->store())
				{
					$this->setError($table->getError());
					return false;
				}
				// Remember to reorder within position and client_id
				$condition = $this->getReorderConditions($table);
				$found = false;
				
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
					$conditions[] = array($table->id, $condition);
				}
			}
		}
		
		// Execute reorder for each category.
		foreach ($conditions as $cond)
		{
			$table->load($cond[0]);
			$table->reorder($cond[1]);
		}
		
		// Clear the component's cache
		$this->cleanCache();
		
		return true;
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param array $data An array of input data.
	 *        	        	
	 * @return boolean
	 *
	 * @since 3.5
	 */
	public function canAdd(array $data = array())
	{
		$user = JFactory::getUser();
		return ($user->authorise('core.create', $this->option) || count($user->getAuthorisedCategories($this->option, 'core.create')));
	}

	/**
	 * Method to check if you can edit an existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param int $id ID of the record which you want to edit
	 *        	
	 * @return boolean
	 *
	 * @since 3.5
	 */
	public function canEdit($id)
	{
		return JFactory::getUser()->authorise('core.edit', $this->option);
	}

	/**
	 * Method to check if you can save a record
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param array $data Form data
	 *        	
	 *        	
	 * @return boolean
	 *
	 * @since 3.5
	 */
	public function canSave($data)
	{
		$id = $data['id'];
		if ($id > 0)
		{
			return $this->canEdit($id);
		}
		else
		{
			return $this->canAdd();
		}
	}

	/**
	 * Method to test whether a record can be changed state by the current user.
	 *
	 * @param object $record The record being edited 
	 *        	
	 * @return boolean True if allowed to change the state of the record. Defaults to the permission for the component.
	 */
	protected function canEditState($record)
	{
		return JFactory::getUser()->authorise('core.edit.state', $this->option);
	}

	/**
	 * Method to test whether a record can be checked in by the current user.
	 *
	 * @param object $record A record object.
	 *        	
	 * @return boolean True if allowed to check in the record.
	 */
	protected function canCheckin($record)
	{
		$user = JFactory::getUser();
		if ($record->checked_out > 0 && $record->checked_out != $user->get('id') && !$user->authorise('core.admin', 'com_checkin'))
		{
			return false;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to test whether a record can be checked out by the current user.
	 *
	 * @param object $record A record object.
	 *        	
	 * @return boolean True if allowed to checkout the record.
	 */
	protected function canCheckout($record)
	{
		if ($record->checked_out > 0 && $record->checked_out != JFactory::getUser()->get('id'))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Method to check whether the current user is allowed to delete a record
	 *
	 * @param object $record the record being deleted
	 *        	
	 * @return boolean True if allowed to delete the record. Defaults to the permission for the component.
	 *        
	 */
	protected function canDelete($record)
	{
		return JFactory::getUser()->authorise('core.delete', $this->option);
	}

	/**
	 * Method to change the title & alias.
	 *
	 * @param integer $categoryId The id of the category.
	 *        	
	 * @param string $alias The alias.
	 *        	
	 * @param string $title The title.
	 *        	
	 * @return array Contains the modified title and alias.
	 */
	protected function generateNewTitle($categoryId, $alias, $title)
	{
		// Alter the title & alias
		$table = $this->getTable();
		while ($table->load(array('alias' => $alias, 'catid' => $categoryId)))
		{
			$title = JString::increment($title);
			$alias = JString::increment($alias, 'dash');
		}
		
		return array($title, $alias);
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param JTable $table A JTable object.
	 *        	
	 * @return array An array of conditions to add to ordering queries.
	 *        
	 */
	protected function getReorderConditions($table)
	{
		return array();
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param JTable $table A reference to a JTable object.
	 *        	
	 * @return void
	 *
	 */
	protected function prepareTable($table)
	{
	}

	/**
	 * Method to get a form object.
	 *
	 * @param string $name The name of the form.
	 *
	 * @param string $source The form source. Can be XML string if file flag is set to false.
	 *
	 * @param array $options Optional array of options for the form creation.
	 *
	 * @param boolean $clear Optional argument to force load a new form.
	 *
	 * @param string $xpath An optional xpath to search for the fields.
	 *
	 * @return mixed JForm object on success, False on error.
	 *
	 * @see JForm
	 */
	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{
		// Handle the optional arguments.
		$options['control'] = JArrayHelper::getValue($options, 'control', false);
		
		// Create a signature hash.
		$hash = md5($source . serialize($options));
		
		// Check if we can use a previously loaded form.
		if (isset($this->forms[$hash]) && !$clear)
		{
			return $this->forms[$hash];
		}
		
		// Get the form.
		JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/' . $this->option . '/model/forms');
		JForm::addFieldPath(JPATH_ADMINISTRATOR . '/components/' . $this->option . '/model/fields');
		
		try
		{
			$form = JForm::getInstance($name, $source, $options, false, $xpath);
			
			if (isset($options['load_data']) && $options['load_data'])
			{
				// Get the data for the form.
				$data = $this->loadFormData();
			}
			else
			{
				$data = array();
			}
			
			// Allow for additional modification of the form, and events to be triggered.
			// We pass the data because plugins may require it.
			$this->preprocessForm($form, $data);
			
			// Load the data into the form after the plugins have operated.
			$form->bind($data);
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}
		
		// Store the form for later.
		$this->forms[$hash] = $form;
		
		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return array The default data is an empty array.
	 *
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState($this->context . '.data', array());
		
		if (empty($data))
		{
			$data = $this->getItem();
		}
		
		return $data;
	}

	/**
	 * Method to allow derived classes to preprocess the data.
	 *
	 * @param string $context The context identifier.
	 *
	 * @param mixed &$data The data to be processed. It gets altered directly.
	 *
	 * @return void
	 */
	protected function preprocessData($context, &$data)
	{
		// Get the dispatcher and load the users plugins.
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin($this->pluginGroup);
		// Trigger the data preparation event.
		$results = $dispatcher->trigger('onContentPrepareData', array($context, $data));
		
		// Check for errors encountered while preparing the data.
		if (count($results) > 0 && in_array(false, $results, true))
		{
			$this->setError($dispatcher->getError());
		}
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param JForm $form A JForm object.
	 *
	 * @param mixed $data The data expected for the form.
	 *
	 * @param string $group The name of the plugin group to import (defaults to "content").
	 *
	 * @return void
	 *
	 * @see JFormField
	 * @throws Exception if there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		// Import the appropriate plugin group.
		JPluginHelper::importPlugin($group);
		
		// Get the dispatcher.
		$dispatcher = JEventDispatcher::getInstance();
		
		// Trigger the form preparation event.
		$results = $dispatcher->trigger('onContentPrepareForm', array($form, $data));
		
		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true))
		{
			// Get the last error.
			$error = $dispatcher->getError();
			
			if (!($error instanceof Exception))
			{
				throw new Exception($error);
			}
		}
	}
}
