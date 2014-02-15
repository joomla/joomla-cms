<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Model for component configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       3.2
 */
class ConfigModelComponent extends ConfigModelForm
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 *
	 * @since	3.2
	 */
	protected function populateState()
	{
		$input = JFactory::getApplication()->input;

		// Set the component (option) we are dealing with.
		$component = $input->get('component');
		$state = $this->loadState();
		$state->set('component.option', $component);

		// Set an alternative path for the configuration file.
		if ($path = $input->getString('path'))
		{
			$path = JPath::clean(JPATH_SITE . '/' . $path);
			JPath::check($path);
			$state->set('component.path', $path);
		}

		$this->setState($state);
	}

	/**
	 * Method to get a form object.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since	3.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$state = $this->getState();

		if ($path = $state->get('component.path'))
		{
			// Add the search path for the admin component config.xml file.
			JForm::addFormPath($path);
		}
		else
		{
			// Add the search path for the admin component config.xml file.
			JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/' . $state->get('component.option'));
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
	 * @return	object
	 *
	 * @since	3.2
	 */
	public function getComponent()
	{
		$state = $this->getState();
		$option = $state->get('component.option');

		// Load common and local language files.
		$lang = JFactory::getLanguage();
		$lang->load($option, JPATH_BASE, null, false, true)
		|| $lang->load($option, JPATH_BASE . "/components/$option", null, false, true);

		$result = JComponentHelper::getComponent($option);

		return $result;
	}

	/**
	 * Method to save the configuration data.
	 *
	 * @param   array  $data  An array containing all global config data.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since	3.2
	 * @throws  RuntimeException
	 */
	public function save($data)
	{
		$table	= JTable::getInstance('extension');

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
				throw new RuntimeException($table->getError());
			}

			// We don't need this anymore
			unset($data['option']);
			unset($data['params']['rules']);
		}

		// Load the previous Data
		if (!$table->load($data['id']))
		{
			throw new RuntimeException($table->getError());
		}

		unset($data['id']);

		// Bind the data.
		if (!$table->bind($data))
		{
			throw new RuntimeException($table->getError());
		}

		// Check the data.
		if (!$table->check())
		{
			throw new RuntimeException($table->getError());
		}

		// Store the data.
		if (!$table->store())
		{
			throw new RuntimeException($table->getError());
		}

		// Clean the component cache.
		$this->cleanCache('_system', 0);
		$this->cleanCache('_system', 1);

		return true;
	}
}
