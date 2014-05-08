<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JModelAdministrator extends JModelUcm
{
	/**
	 * Array of form objects.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $forms = array();

	/**
	 * Method to validate data and insert into db
	 *
	 * @param array $data
	 *
	 * @throws ErrorException
	 * @return boolean
	 */
	public function create($data)
	{
		$form = $this->getForm($data, false);

		$validData = $this->validate($form, $data);

		$table = $this->getTable();

		if ((!empty($validData['tags']) && $validData['tags'][0] != ''))
		{
			$table->newTags = $validData['tags'];
		}

		//prepare the table for store
		$table->bind($validData);
		$table->check();

		// Get dispatcher and include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');
		$dispatcher = $this->getDispatcher();
		$context    = $this->getContext();

		$result = $dispatcher->trigger('onContentBeforeSave', array($context, $table, true));

		if (in_array(false, $result, true))
		{
			throw new ErrorException($table->getError());
		}

		// Store the data.
		if (!$table->store())
		{
			throw new ErrorException($table->getError());
		}

		// Clean the cache.
		$this->cleanCache();

		// Trigger the onContentAfterSave event.
		$dispatcher->trigger('onContentAfterSave', array($context, $table, true));

		$pkName = $table->getKeyName();

		if (isset($table->$pkName))
		{
			$context = $this->getContext();
			$this->setState($context . '.id', $table->$pkName);
		}

		return true;
	}

	/**
	 * method for getting the form from the model.
	 *
	 * @param   array   $data     Data for the form.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   12.2
	 */
	public function getForm($data = array(), $loadData = false)
	{
		$config = $this->config;
		$form   = $this->loadForm($config['option'] . '.' . $config['subject'], $config['subject'], array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		if (!empty($data) && $loadData == false)
		{
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Method to get a form object.
	 *
	 * @param   string    $name    The name of the form.
	 * @param   string    $source  The form source. Can be XML string if file flag is set to false.
	 * @param   array     $config Optional array of options for the form creation.
	 * @param   boolean   $clear   Optional argument to force load a new form.
	 * @param bool|string $xpath   An optional xpath to search for the fields.
	 *
	 * @return  mixed  JForm object on success, False on error.
	 *
	 * @see     JForm
	 * @since   12.2
	 */
	protected function loadForm($name, $source = null, $config = array(), $clear = false, $xpath = false)
	{
		// Handle the optional arguments.
		$config['control'] = JArrayHelper::getValue($config, 'control', false);

		// Create a signature hash.
		$hash = md5($source . serialize($config));

		// Check if we can use a previously loaded form.
		if (isset($this->forms[$hash]) && !$clear)
		{
			return $this->forms[$hash];
		}

		// Get the form.
		JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		JForm::addFormPath(JPATH_COMPONENT . '/model/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/model/fields');

		$form = JForm::getInstance($name, $source, $config, false, $xpath);

		if (isset($config['load_data']) && $config['load_data'])
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


		// Store the form for later.
		$this->forms[$hash] = $form;

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @param string $context user state variables context prefix
	 * @return  array    The default data is an empty array.
	 *
	 * @since   12.2
	 */
	protected function loadFormData($context = null)
	{
		if(is_null($context))
		{
			$context = $this->getContext();
		}

		$data = JFactory::getApplication()->getUserState($context . '.jform.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   JForm  $form  A JForm object.
	 * @param   mixed  $data  The data expected for the form.
	 * @param   string $group The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @see     JFormField
	 * @since   12.2
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm($form, $data, $group = 'content')
	{
		// Import the appropriate plugin group.
		JPluginHelper::importPlugin($group);

		$dispatcher = $this->getDispatcher();

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

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm  $form  The form to validate against.
	 * @param   array  $data  The data to validate.
	 * @param   string $group The name of the field group to validate.
	 *
	 * @throws ErrorException
	 * @return  mixed  Array of filtered data if valid
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		// Filter and validate the form data.
		$data = $form->filter($data);

		try
		{
			$return = $form->validate($data, $group);
		}
		catch (Exception $e)
		{
			throw new ErrorException($e->getMessage());
		}

		// Check the validation results.
		if ($return === false)
		{
			$msg = '';
			$i   = 0;

			// Get the validation messages from the form.
			foreach ($form->getErrors() as $e)
			{
				if ($i != 0)
				{
					$msg .= '<br/>';
				}

				$msg .= $e->getMessage();
				$i++;
			}

			throw new ErrorException($msg);
		}

		// Tags B/C break at 3.1.2
		if (isset($data['metadata']['tags']) && !isset($data['tags']))
		{
			$data['tags'] = $data['metadata']['tags'];
		}

		return $data;
	}

	/**
	 * Method to validate data and update into db
	 *
	 * @param array $data
	 *
	 * @throws ErrorException
	 * @return boolean
	 */
	public function update($data)
	{

		$form = $this->getForm($data, false);
		$validData = $this->validate($form, $data);

		$table = $this->getTable();

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
		$dispatcher = $this->getDispatcher();
		$config     = $this->config;

		$result = $dispatcher->trigger('onContentBeforeSave', array($config['option'] . '.' . $config['subject'], $table, false));

		if (in_array(false, $result, true))
		{
			throw new ErrorException($table->getError());
		}

		// Store the data.
		if (!$table->store())
		{
			throw new ErrorException($table->getError());
		}

		// Clean the cache.
		$this->cleanCache();

		// Trigger the onContentAfterSave event.
		$dispatcher->trigger('onContentAfterSave', array($config['option'] . '.' . $config['subject'], $table, false));

		$pkName = $table->getKeyName();

		if (isset($table->$pkName))
		{
			$this->setState($config['subject'] . '.id', $table->$pkName);
		}

		return true;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param array $cid array of record primary keys.
	 *
	 * @throws ErrorException
	 * @internal param array $pks
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since    12.2
	 */
	public function delete($cid)
	{
		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');
		$dispatcher = $this->getDispatcher();

		$config = $this->config;
		$pks    = (array) $cid;

		foreach ($pks AS $pk)
		{
			$context = $config['option'] . '.' . $config['subject'];

			$activeRecord = $this->getActiveRecord($pk);

			if ($this->allowAction('core.delete', $config['option'], $activeRecord))
			{
				// Trigger the onContentBeforeDelete event.
				$result = $dispatcher->trigger('onContentBeforeDelete', array($context, $activeRecord));

				if (in_array(false, $result, true))
				{
					throw new ErrorException($activeRecord->getError());
				}

				$activeRecord->delete($pk);

				// Trigger the onContentAfterDelete event.
				$dispatcher->trigger('onContentAfterDelete', array($context, $activeRecord));
			}
			else
			{
				throw new ErrorException('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED');

			}
		}

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
	 * @see JCmsModelAdministrator::getStateTypes
	 * @throws ErrorException
	 * @return boolean
	 */
	public function updateRecordState($cid, $type)
	{
		$stateChangeTypes = $this->getStateTypes();

		if (!array_key_exists($type, $stateChangeTypes))
		{
			throw new ErrorException('JLIB_APPLICATION_ERROR_UNRECOGNIZED_STATE_CHANGE');
		}

		$newState = $stateChangeTypes[$type];

		$user = JFactory::getUser();
		$config = $this->config;
		$pks    = (array) $cid;

		foreach ($pks AS $i => $pk)
		{
			$activeRecord = $this->getActiveRecord($pk);

			if ($this->allowAction('core.edit.state', $config['option'], $activeRecord))
			{
				$activeRecord->updateRecordState($pk, $newState, $user->id);
			}
			else
			{
				//remove items we cannot edit.
				unset($cid[$i]);
			}
		}

		// Include the content plugins for the change of state event.
		JPluginHelper::importPlugin('content');
		$dispatcher = $this->getDispatcher();

		$context = $this->getContext();

		//trigger 'onContentChangeState'
		$result = $dispatcher->trigger('onContentChangeState', array($context, $cid, $newState));

		if (!in_array(false, $result, true))
		{
			// Clear the component's cache
			$this->cleanCache();
		}

		return true;
	}

	/**
	 * Method to get an associative array of state types.
	 * Default array has these values 'publish' => 1, 'unpublish' => 0,'archive' => 2,'trash' => -2,'report' => -3
	 * This allows extensions to add additional states to their records by overloading this function.
	 * @return array $stateChangeTypes
	 */
	protected function getStateTypes()
	{
		$stateChangeTypes              = array();
		$stateChangeTypes['publish']   = 1;
		$stateChangeTypes['unpublish'] = 0;
		$stateChangeTypes['archive']   = 2;
		$stateChangeTypes['trash']     = -2;
		$stateChangeTypes['report']    = -3;

		return $stateChangeTypes;
	}

	/**
	 * Method to reorder one or more records
	 *
	 * @param array  $cid
	 * @param string $direction up or down
	 *
	 * @throws ErrorException
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

		$config = $this->config;
		$pks    = (array) $cid;

		foreach ($pks AS $pk)
		{
			$activeRecord = $this->getActiveRecord($pk);

			if ($this->allowAction('core.edit.state', $config['option'], $activeRecord))
			{
				$where = $activeRecord->getReorderConditions($activeRecord);
				$activeRecord->moveOrder($pk, $delta, $where);
			}
			else
			{
				throw new ErrorException('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED');
			}
		}

		// Clear the component's cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param   array   $cid   An array of primary key ids.
	 * @param   integer $order +1 or -1
	 *
	 * @throws ErrorException
	 * @return  mixed
	 *
	 * @since   12.2
	 */
	public function saveorder($cid = null, $order = null)
	{
		if (empty($cid))
		{
			throw new ErrorException(JText::_('JLIB_APPLICATION_ERROR_NO_ITEMS_SELECTED'));
		}


		/*
		 *  This is something that needs to be worked out once changes to JTable are completed
		 *  Commented out because I haven't really studied the implementation, so this code might not be correct.
		 * $table          = $this->getTable();
		 * $tableClassName = get_class($table);
		 * $contentType    = new JUcmType;
		 * $type           = $contentType->getTypeByTable($tableClassName);
		 * $typeAlias      = $type->type_alias;
		 * $tagsObserver   = $table->getObserverOfClass('JTableObserverTags');
		*/

		$conditions     = array();
		$config = $this->config;
		$pks    = (array) $cid;
		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$activeRecord = $this->getActiveRecord($pk);
			// Access checks.
			if ($this->allowAction('core.edit.state', $config['option'], $activeRecord))
			{
				$activeRecord->ordering = $order[$i];

				// Store the data.
				if (!$activeRecord->store())
				{
					throw new ErrorException($activeRecord->getError());
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

	/**
	 * Method to import one or more files.
	 *
	 * This method is intended to be overridden by child classes.
	 *
	 * @param array $data  post data from the input
	 * @param array $files files data from the input
	 *
	 * @throws ErrorException
	 */
	public function import($data, $files)
	{
		throw new ErrorException(JText::_('JLIB_APPLICATION_ERROR_IMPORT_NOT_SUPPORTED'));
	}

	/**
	 * Method to allow derived classes to preprocess the data.
	 *
	 * @param   string $context The context identifier.
	 * @param   mixed  &$data   The data to be processed. It gets altered directly.
	 *
	 * @throws Exception
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function preprocessData($context, &$data)
	{
		// Get the dispatcher and load the users plugins.
		$dispatcher = $this->getDispatcher();
		JPluginHelper::importPlugin('content');

		// Trigger the data preparation event.
		$results = $dispatcher->trigger('onContentPrepareData', array($context, $data));

		// Check for errors encountered while preparing the data.
		if (count($results) > 0 && in_array(false, $results, true))
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