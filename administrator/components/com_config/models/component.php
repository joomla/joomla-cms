<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

/**
 * @package		Joomla.Administrator
 * @subpackage	Config
 */
class ConfigModelComponent extends JModel
{
	/**
	 * Get the params for the configuration variables
	 */
	public function &getParams()
	{
		static $instance;

		if ($instance == null) {
			$component = $this->getState('component');

			$table =& JTable::getInstance('component');
			$table->loadByOption($component);

			// work out file path
			if ($path = JRequest::getString('path')) {
				$path = JPath::clean(JPATH_SITE.DS.$path);
				JPath::check($path);
			} else {
				$option	= preg_replace('#\W#', '', $table->option);
				$path	= JPATH_ADMINISTRATOR.DS.'components'.DS.$option.DS.'config.xml';
			}

			if (file_exists($path)) {
				$instance = new JParameter($table->params, $path);
			} else {
				$instance = new JParameter($table->params);
			}
		}
		return $instance;
	}
	
	public function getComponent($componentName = null)
	{
		if (is_null($componentName)) {
			$componentName = $this->getState('component');
		}
	
		if (empty($componentName)) {
			JError::raiseError(500, 'Not a valid component');
			return false;
		}
		// load the component's language file
		$lang = & JFactory::getLanguage();
		// 1.5 or core
		$lang->load($componentName);
		// 1.6 support for component specific languages
		$lang->load($componentName, JPATH_ADMINISTRATOR.DS.'components'.DS.$componentName);		
		
		$component = JComponentHelper::getComponent($componentName);
		
		return $component;
	}
}