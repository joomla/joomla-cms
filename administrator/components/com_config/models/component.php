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