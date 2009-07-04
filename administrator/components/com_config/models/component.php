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

jimport('joomla.application.component.model');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_config
 */
class ConfigModelComponent extends JModel
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * @since	1.6
	 */
	protected function _populateState()
	{
		$component = JRequest::getCmd('component');
		$this->setState('component.option', $component);
	}

	/**
	 * Get the component information.
	 *
	 * @param	string	An optional 'option'.
	 *
	 * @return	object
	 * @since	1.6
	 */
	function getComponent($option = null)
	{
		if (empty($option)) {
			$option = $this->getState('component.option');
		}

		// Load common language files
		$lang = &JFactory::getLanguage();
		// 1.5 3PD or Core files
		$lang->load($option);
		// 1.6 3PD
		$lang->load($option, JPATH_COMPONENT);

		return JComponentHelper::getComponent($option);
	}

	/**
	 * Get the params for the configuration variables.
	 *
	 * @return	JParameter
	 */
	function &getParams()
	{
		static $instance;

		if ($instance == null)
		{
			$component	= JRequest::getCmd('component');

			$table = &JTable::getInstance('component');
			$table->loadByOption($component);

			// work out file path
			if ($path = JRequest::getString('path'))
			{
				$path = JPath::clean(JPATH_SITE.DS.$path);
				JPath::check($path);
			}
			else
			{
				$option	= preg_replace('#\W#', '', $table->option);
				$path	= JPATH_ADMINISTRATOR.DS.'components'.DS.$option.DS.'config.xml';
			}

			if (file_exists($path)) {
				$instance = new JParameter($table->params, $path);
			}
			else {
				$instance = new JParameter($table->params);
			}
		}
		return $instance;
	}
}