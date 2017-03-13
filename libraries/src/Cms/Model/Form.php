<?php
/**
 * @package     Joomla.Model
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Model;

defined('JPATH_PLATFORM') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Prototype form model.
 *
 * @see    \JForm
 * @see    \JFormField
 * @see    \JFormRule
 * @since  1.6
 */
abstract class Form extends Model
{
	/**
	 * Array of form objects.
	 *
	 * @var    \JForm[]
	 * @since  1.6
	 */
	protected $_forms = array();

	/**
	 * Maps events to plugin groups.
	 *
	 * @var    array
	 * @since  3.6
	 */
	protected $events_map = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     \JModelLegacy
	 * @since   3.6
	 */
	public function __construct($config = array())
	{
		$config['events_map'] = isset($config['events_map']) ? $config['events_map'] : array();

		$this->events_map = array_merge(
			array(
				'validate' => 'content',
			),
			$config['events_map']
		);

		parent::__construct($config);
	}

	/**
	 * Method to checkin a row.
	 *
	 * @param   integer  $pk  The numeric id of the primary key.
	 *
	 * @return  boolean  False on failure or error, true otherwise.
	 *
	 * @since   1.6
	 */
	public function checkin($pk = null)
	{
		// Only attempt to check the row in if it exists.
		if ($pk)
		{
			$user = \JFactory::getUser();

			// Get an instance of the row to checkin.
			$table = $this->getTable();

			if (!$table->load($pk))
			{
				$this->setError($table->getError());

				return false;
			}

			$checkedOutField = $table->getColumnAlias('checked_out');
			$checkedOutTimeField = $table->getColumnAlias('checked_out_time');

			// If there is no checked_out or checked_out_time field, just return true.
			if (!property_exists($table, $checkedOutField) || !property_exists($table, $checkedOutTimeField))
			{
				return true;
			}

			// Check if this is the user having previously checked out the row.
			if ($table->{$checkedOutField} > 0 && $table->{$checkedOutField} != $user->get('id') && !$user->authorise('core.admin', 'com_checkin'))
			{
				$this->setError(\JText::_('JLIB_APPLICATION_ERROR_CHECKIN_USER_MISMATCH'));

				return false;
			}

			// Attempt to check the row in.
			if (!$table->checkIn($pk))
			{
				$this->setError($table->getError());

				return false;
			}
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
	 * @since   1.6
	 */
	public function checkout($pk = null)
	{
		// Only attempt to check the row in if it exists.
		if ($pk)
		{
			// Get an instance of the row to checkout.
			$table = $this->getTable();

			if (!$table->load($pk))
			{
				$this->setError($table->getError());

				return false;
			}

			$checkedOutField = $table->getColumnAlias('checked_out');
			$checkedOutTimeField = $table->getColumnAlias('checked_out_time');

			// If there is no checked_out or checked_out_time field, just return true.
			if (!property_exists($table, $checkedOutField) || !property_exists($table, $checkedOutTimeField))
			{
				return true;
			}

			$user = \JFactory::getUser();

			// Check if this is the user having previously checked out the row.
			if ($table->{$checkedOutField} > 0 && $table->{$checkedOutField} != $user->get('id'))
			{
				$this->setError(\JText::_('JLIB_APPLICATION_ERROR_CHECKOUT_USER_MISMATCH'));

				return false;
			}

			// Attempt to check the row out.
			if (!$table->checkOut($user->get('id'), $pk))
			{
				$this->setError($table->getError());

				return false;
			}
		}

		return true;
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  \JForm|boolean  A \JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	abstract public function getForm($data = array(), $loadData = true);

	/**
	 * Method to get a form object.
	 *
	 * @param   string   $name     The name of the form.
	 * @param   string   $source   The form source. Can be XML string if file flag is set to false.
	 * @param   array    $options  Optional array of options for the form creation.
	 * @param   boolean  $clear    Optional argument to force load a new form.
	 * @param   string   $xpath    An optional xpath to search for the fields.
	 *
	 * @return  \JForm|boolean  \JForm object on success, false on error.
	 *
	 * @see     \JForm
	 * @since   1.6
	 */
	protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{
		// Handle the optional arguments.
		$options['control'] = ArrayHelper::getValue((array) $options, 'control', false);

		// Create a signature hash.
		$hash = md5($source . serialize($options));

		// Check if we can use a previously loaded form.
		if (isset($this->_forms[$hash]) && !$clear)
		{
			return $this->_forms[$hash];
		}

		// Get the form.
		\JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		\JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		\JForm::addFormPath(JPATH_COMPONENT . '/model/form');
		\JForm::addFieldPath(JPATH_COMPONENT . '/model/field');

		// In case namespace model, use resources folder as agreed elsewhere.
		if ($this->namespace)
		{
			// Frontend model
			if (strpos($this->namespace, '\\Admin'))
			{
				\JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/' . $this->option . '/resources/forms');
				\JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/' . $this->option . '/resources/fields');
			}

			// Backend model
			if (strpos($this->namespace, '\\Site'))
			{
				\JForm::addFormPath(JPATH_ROOT . '/components/' . $this->option . '/resources/forms');
				\JForm::addFormPath(JPATH_ROOT . '/components/' . $this->option . '/resources/fields');
			}
		}

		try
		{
			$form = \JForm::getInstance($name, $source, $options, false, $xpath);

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
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Store the form for later.
		$this->_forms[$hash] = $form;

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array  The default data is an empty array.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		return array();
	}

	/**
	 * Method to allow derived classes to preprocess the data.
	 *
	 * @param   string  $context  The context identifier.
	 * @param   mixed   &$data    The data to be processed. It gets altered directly.
	 * @param   string  $group    The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function preprocessData($context, &$data, $group = 'content')
	{
		// Get the dispatcher and load the users plugins.
		\JPluginHelper::importPlugin($group);

		// Trigger the data preparation event.
		\JFactory::getApplication()->triggerEvent('onContentPrepareData', array($context, &$data));
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   \JForm  $form   A \JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @see     \JFormField
	 * @since   1.6
	 * @throws  \Exception if there is an error in the form event.
	 */
	protected function preprocessForm(\JForm $form, $data, $group = 'content')
	{
		// Import the appropriate plugin group.
		\JPluginHelper::importPlugin($group);

		// Trigger the form preparation event.
		\JFactory::getApplication()->triggerEvent('onContentPrepareForm', array($form, $data));
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   \JForm  $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  array|boolean  Array of filtered data if valid, false otherwise.
	 *
	 * @see     \JFormRule
	 * @see     \JFilterInput
	 * @since   1.6
	 */
	public function validate($form, $data, $group = null)
	{
		// Include the plugins for the delete events.
		\JPluginHelper::importPlugin($this->events_map['validate']);

		\JFactory::getApplication()->triggerEvent('onUserBeforeDataValidation', array($form, &$data));

		// Filter and validate the form data.
		$data = $form->filter($data);
		$return = $form->validate($data, $group);

		// Check for an error.
		if ($return instanceof \Exception)
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
}
