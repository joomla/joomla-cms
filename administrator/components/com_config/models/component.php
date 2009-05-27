<?php
/**
 * @version		$Id: component.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * @package		Joomla.Administrator
 * @subpackage	Config
 */
class ConfigModelComponent extends JModel
{
	/**
	 * Get the params for the configuration variables
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
}