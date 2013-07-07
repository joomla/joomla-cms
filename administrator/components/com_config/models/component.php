<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Model for component configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       1.5
 */
class ConfigModelComponent extends JModelForm
{
	/**
	 * The event to trigger before saving the data.
	 *
	 * @var    string
	 * @since  3.1.0
	 */
	protected $event_before_save = 'onConfigurationBeforeSave';

	/**
	 * The event to trigger before deleting the data.
	 *
	 * @var    string
	 * @since  3.1.0
	 */
	protected $event_after_save = 'onConfigurationAfterSave';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		$input = JFactory::getApplication()->input;

		// Set the component (option) we are dealing with.
		$component = $input->get('component');
		$this->setState('component.option', $component);

		// Set an alternative path for the configuration file.
		if ($path = $input->getString('path'))
		{
			$path = JPath::clean(JPATH_SITE . '/' . $path);
			JPath::check($path);
			$this->setState('component.path', $path);
		}
	}

	/**
	 * Method to get a form object.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed    A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		if ($path = $this->getState('component.path'))
		{
			// Add the search path for the admin component config.xml file.
			JForm::addFormPath($path);
		}
		else
		{
			// Add the search path for the admin component config.xml file.
			JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/' . $this->getState('component.option'));
		}

		// Get the form.
		$form = $this->loadForm(
			'com_config.component',
			'config',
			array('control' => 'jform', 'load_data' => $loadData),
			false,
			'/config'
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Get the component information.
	 *
	 * @return  object
	 *
	 * @since   1.6
	 */
	function getComponent()
	{
		$option = $this->getState('component.option');

		// Load common and local language files.
		$lang = JFactory::getLanguage();
		$lang->load($option, JPATH_BASE, null, false, false)
		|| $lang->load($option, JPATH_BASE . "/components/$option", null, false, false)
		|| $lang->load($option, JPATH_BASE, $lang->getDefault(), false, false)
		|| $lang->load($option, JPATH_BASE . "/components/$option", $lang->getDefault(), false, false);

		$result = JComponentHelper::getComponent($option);

		$this->preprocessData('com_config.component', $result);

		return $result;
	}

	/**
	 * Method to save the configuration data.
	 *
	 * @param   array  $data  An array containing all global config data.
	 *
	 * @return  bool   True on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function save($data)
	{
		$dispatcher = JDispatcher::getInstance();
		$table = JTable::getInstance('extension');
		$isNew = true;

		// Save the rules.
		if (isset($data['params']) && isset($data['params']['rules']))
		{
			$rules = new JAccessRules($data['params']['rules']);
			$asset = JTable::getInstance('asset');

			if (!$asset->loadByName($data['option']))
			{
				$root = JTable::getInstance('asset');
				$root->loadByName('root.1');
				$asset->name = $data['option'];
				$asset->title = $data['option'];
				$asset->setLocation($root->id, 'last-child');
			}
			$asset->rules = (string) $rules;

			if (!$asset->check() || !$asset->store())
			{
				$this->setError($asset->getError());
				return false;
			}

			// We don't need this anymore
			unset($data['option']);
			unset($data['params']['rules']);
		}

		// Load the previous Data
		if (!$table->load($data['id']))
		{
			$this->setError($table->getError());
			return false;
		}

		unset($data['id']);

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());
			return false;
		}

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());
			return false;
		}

		// Trigger the onConfigurationBeforeSave event.
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

		// Clean the component cache.
		$this->cleanCache('_system');

		// Trigger the onConfigurationAfterSave event.
		$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, $table, $isNew));

		return true;
	}
}
