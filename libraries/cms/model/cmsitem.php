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
 * Prototype item model.
 *
 * @package     Joomla.Model
 * @subpackage  Model
 * @since       3.4
 */
class JModelCmsitem extends JModelCmsactions implements JModelFormInterface
{
	/**
	 * An item.
	 *
	 * @var    array
	 */
	protected $item = null;

	/**
	 * Array of form objects.
	 *
	 * @var    array
	 * @since  3.4
	 */
	protected $forms = array();

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   3.4
	 */
	public function getForm($data = array(), $loadData = true)
	{
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   3.4
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		return md5($id);
	}

	/**
	 * Method to get an object. This only works for content types registered in the content_type table.
	 *
	 * @param   integer  $id  The id of the object to get.
	 *
	 * @return  mixed  Object on success, false on failure.
	 */
	public function getItem($id = null)
	{
		if (empty($this->item) && $this->getTable())
		{
			$table = $this->getTable();
			$tableClassName = get_class($table);

			$this->item = false;
			$contentType = new JUcmType;

			if ($tableClassName != 'JTableCorecontent')
			{
				$type = $contentType->getTypeByTable($tableClassName);
			}
			elseif (!empty($id))
			{
				$table->load($id);
				$type = $contentType->getType($table->core_type_id);

			}
			elseif (empty($id))
			// Deal with examples where there is no row in type table?
			{
				$type = null;
			}

			if (!empty($type))
			{
				$prefix = $this->state->get($table, 'prefix');
				$typeTable = $type->table;
				$typeTable = json_decode($typeTable);

				// Check to see if special exists .. if it doesn't use common
				if (!empty($typeTable->special) && $tableClassName != 'JTableCorecontent')
				{
					$table = JTable::getInstance($typeTable->special->type, $typeTable->special->prefix);
				}
				else
				{
					if (empty($typeTable->common))
					{
						// Should there be an exception here? or should we load ucm_content?
						return;
					}
					$table = JTable::getInstance($typeTable->common->type, $typeTable->common->prefix);
					// Get the special field mapping here
				}
			}

			// Attempt to load the row.
			if ($table->load($id))
			{
				// Convert the JTable to a clean object.
				$properties = $table->getProperties(1);
				$this->item = JArrayHelper::toObject($properties);
			}
			elseif ($error = $table->getError())
			{
				$this->setError($error);
			}
		}

		return $this->item;
	}

	/**
	 * Method to increment the hit counter for the weblink
	 *
	 * @param   integer  $id  Optional ID of the weblink.
	 *
	 * @return  boolean  True on success
	 *
	 * @since  3.4
	 */
	public function hit($id = null)
	{
		if (empty($id))
		{
			$id = $this->getState($type . 'id');
		}

		$item = $this->getTable($type, $prefix);
		return $item->hit($id);
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   JTable  $table  A reference to a JTable object.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function prepareTable($table)
	{
		// Derived class will provide its own implementation if required.
	}

	/**
	 * Method to get a form object.
	 *
	 * @param   string   $name     The name of the form.
	 * @param   string   $source   The form source. Can be XML string if file flag is set to false.
	 * @param   array    $options  Optional array of options for the form creation.
	 * @param   boolean  $clear    Optional argument to force load a new form.
	 * @param   string   $xpath    An optional xpath to search for the fields.
	 *
	 * @return  mixed  JForm object on success, False on error.
	 *
	 * @see     JForm
	 * @since   3.4
	 */
	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{
		// Handle the optional arguments.
		$options['control'] = JArrayHelper::getValue($options, 'control', false);

		// Create a signature hash.
		$hash = sha1($source . serialize($options));

		// Check if we can use a previously loaded form.
		if (isset($this->_forms[$hash]) && !$clear)
		{
			return $this->_forms[$hash];
		}

		// Get the form.
		// Register the paths for the form -- failing here
		$paths = new SplPriorityQueue;
		$paths->insert(JPATH_COMPONENT . '/model/form', 'normal');
		$paths->insert(JPATH_COMPONENT . '/model/field', 'normal');
		$paths->insert(JPATH_COMPONENT . '/model/rule', 'normal');

		//Legacy support to be removed in 4.0.  -- failing here
		$paths->insert(JPATH_COMPONENT . '/models/forms', 'normal');
		$paths->insert(JPATH_COMPONENT . '/models/fields', 'normal');
		$paths->insert(JPATH_COMPONENT . '/models/rules', 'normal');

		// test -- prob with previous -- tempory solution
		JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		JForm::addFormPath(JPATH_COMPONENT . '/model/form');
		JForm::addFieldPath(JPATH_COMPONENT . '/model/field');

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

			$app = JFactory::getApplication();
			$app->enqueueMessage($e->getMessage());

			return false;
		}

		// Store the form for later.
		$this->_forms[$hash] = $form;

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array    The default data is an empty array.
	 *
	 * @since   3.4
	 */
	public function loadFormData()
	{
		return array();
	}

	/**
	 * Method to allow derived classes to preprocess the data.
	 *
	 * @param   string  $context  The context identifier.
	 * @param   mixed   &$data    The data to be processed. It gets altered directly.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function preprocessData($context, &$data)
	{
		// Get the dispatcher and load the plugins.
		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');

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
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @see     JFormField
	 * @since   3.4
	 * @throws  Exception if there is an error in the form event.
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

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   3.4
	 */
	public function validate($form, $data, $group = null)
	{
		// Filter and validate the form data.
		$data = $form->filter($data);
		$return = $form->validate($data, $group);

		// Check for an error.
		if ($return instanceof Exception)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage($return->getMessage());

			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message)
			{
				$app = JFactory::getApplication();
				$app->enqueueMessage($message);

			}

			return false;
		}

		return $data;
	}

	/**
	 * Method to change the title & alias.
	 *
	 * @param   integer  $category_id  The id of the category.
	 * @param   string   $alias        The alias.
	 * @param   string   $title        The title.
	 *
	 * @return	array  Contains the modified title and alias.
	 *
	 * @since	3.4
	 */
	protected function generateNewTitle($category_id, $alias, $title)
	{
		// Alter the title & alias
		$table = $this->getTable();
		while ($table->load(array('alias' => $alias, 'catid' => $category_id)))
		{
			$title = JString::increment($title);
			$alias = JString::increment($alias, 'dash');
		}

		return array($title, $alias);
	}
}
