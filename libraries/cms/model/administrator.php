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
 * Base Cms Model Class for administrative functions
 *
 * @package     Joomla.Libraries
 * @subpackage  Model
 * @since       3.4
 */
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
	 * method for getting the form from the model.
	 *
	 * @param   array   $data     Data for the form.
	 * @param   boolean $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   12.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$config = $this->config;
		$form   = $this->loadForm($config['option'] . '.' . $this->getName(), $this->getName(), array('control' => 'jform', 'load_data' => $loadData));

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
	public function loadForm($name, $source = null, $config = array(), $clear = false, $xpath = false)
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
		if(JFactory::getApplication()->isSite())
		{
			JForm::addFormPath(JPATH_COMPONENT . '/model/forms');
			JForm::addFieldPath(JPATH_COMPONENT . '/model/fields');
			JForm::addFormPath(JPATH_COMPONENT . '/model/form');
			JForm::addFieldPath(JPATH_COMPONENT . '/model/field');
		}
		else
		{
			JForm::addFormPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/forms');
			JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/fields');
			JForm::addFormPath(JPATH_COMPONENT_ADMINISTRATOR . '/model/form');
			JForm::addFieldPath(JPATH_COMPONENT_ADMINISTRATOR . '/model/field');
		}

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
	 *
	 * @throws  RuntimeException if there is an error in the form event.
	 */
	protected function preprocessForm($form, $data, $group = 'content')
	{
		// Import the appropriate plugin group.
		JPluginHelper::importPlugin($group);

		$dispatcher = $this->dispatcher;

		// Trigger the form preparation event.
		$results = $dispatcher->trigger('onContentPrepareForm', array($form, $data));

		// Check for errors encountered while preparing the form.
		if (count($results) && in_array(false, $results, true))
		{
			// Get the last error.
			$error = $dispatcher->getError();

			if (!($error instanceof Exception))
			{
				throw new RuntimeException($error);
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
	 * @return  mixed  Array of filtered data if valid
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 * @throws  RuntimeException
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
			throw new RuntimeException($e->getMessage());
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

			throw new RuntimeException($msg);
		}

		// Tags B/C break at 3.1.2
		if (isset($data['metadata']['tags']) && !isset($data['tags']))
		{
			$data['tags'] = $data['metadata']['tags'];
		}

		return $data;
	}

	/**
	 * Method to allow derived classes to preprocess the data.
	 *
	 * @param   string $context The context identifier.
	 * @param   mixed  &$data   The data to be processed. It gets altered directly.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 * @throws  RuntimeException
	 */
	protected function preprocessData($context, &$data)
	{
		// Get the dispatcher and load the users plugins.
		$dispatcher = $this->dispatcher;
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
				throw new RuntimeException($error);
			}
		}
	}

	protected function populateState($ordering = null, $direction = null)
	{
		if (!$this->stateIsSet)
		{
			$params = JComponentHelper::getParams($this->option);
			$this->state->set('params', $params);

			parent::populateState($ordering, $direction);
		}
	}
}