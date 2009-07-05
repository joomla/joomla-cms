<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_config
 */
class ConfigModelComponent extends JModelForm
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * @since	1.6
	 */
	protected function _populateState()
	{
		// Set the component (option) we are dealing with.
		$component = JRequest::getCmd('component');
		$this->setState('component.option', $component);

		// Set an alternative path for the configuration file.
		if ($path = JRequest::getString('path'))
		{
			$path = JPath::clean(JPATH_SITE.DS.$path);
			JPath::check($path);
			$this->setState('component.path', $path);
		}
	}

	/**
	 * Method to get a form object.
	 *
	 * @return	mixed		A JForm object on success, false on failure.
	 * @since	1.6
	 */
	public function getForm()
	{
		$option = $this->getState('component.option');

		jimport('joomla.form.form');

		// Add the search path for the admin component config.xml file.
		JForm::addFormPath(JPATH_ADMINISTRATOR.'/components/'.$option);

		// Get the form.
		$form = parent::getForm('config', 'com_config.component', array('array' => 'jform', 'event' => 'onPrepareForm'));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return false;
		}

		return $form;
	}

	/**
	 * Get the component information.
	 *
	 * @return	object
	 * @since	1.6
	 */
	function getComponent()
	{
		// Initialise variables.
		$option = $this->getState('component.option');

		// Load common and local language files.
		$lang = &JFactory::getLanguage();
		$lang->load($option);
		$lang->load($option, JPATH_COMPONENT);

		$result = JComponentHelper::getComponent($option);

		return $result;
	}

	/**
	 * Method to save the configuration data.
	 *
	 * @param	array	An array containing all global config data.
	 * @return	bool	True on success, false on failure.
	 * @since	1.6
	 */
	public function save($data)
	{
		$table = &JTable::getInstance('component');

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

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());
			return false;
		}

		// Clean the cache.
		$cache = &JFactory::getCache('com_content');
		$cache->clean();

		return true;
	}

}