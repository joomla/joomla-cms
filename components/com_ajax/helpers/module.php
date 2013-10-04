<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Module helper class for AJAX component.
 *
 * @package     Joomla.Site
 * @subpackage  com_ajax
 * @since       3.2
 */
class AjaxModuleHelper
{
	/**
	 * Check whether module available
	 *
	 * @param string $name - the module name in format "name" or "mod_name"
	 *
	 * @return bool
	 */
	public static function isModuleAvailable($name)
	{
		// Init variables
		$name 	  = strstr($name, 'mod_') ? $name : 'mod_' . $name;
		$app      = JFactory::getApplication();
		$clientId = $app->getClientId();
		$user     = JFactory::getUser();
		$groups   = implode(',', $user->getAuthorisedViewLevels());
		$db       = JFactory::getDbo();

		// Build query
		$query = $db->getQuery(true);
		$query->select('m.id');
		$query->from('#__modules AS m');
		$query->where('m.module = ' . $db->q($name));
		$query->where('m.published = 1');
		$query->where('m.access IN (' . $groups . ')');
		$query->where('m.client_id = ' . (int) $clientId);

		$date = JFactory::getDate();
		$now = $date->toSql();
		$nullDate = $db->getNullDate();
		$query->where('(m.publish_up = ' . $db->quote($nullDate) . ' OR m.publish_up <= ' . $db->quote($now) . ')');
		$query->where('(m.publish_down = ' . $db->quote($nullDate) . ' OR m.publish_down >= ' . $db->quote($now) . ')');

		// Set the query
		$db->setQuery($query);
		// Load available modules
		$modules = $db->loadObjectList();

		// Return true if at least the one module available
		return $modules && count($modules);
	}

	/**
	 * Call the AJAX method from the module helper
	 *
	 * @param string $name - the module name in format "name" or "mod_name"
	 *
	 * @return mixed
	 */
	public static function callModule($name)
	{
		if (!$name || !AjaxModuleHelper::isModuleAvailable($name))
		{
			// Module is not published or you do not have access to it
			throw new LogicException(sprintf('Module "%s" is not published or you do not have access to it', $name), 404);
		}

		// Init variables
		$app         = JFactory::getApplication();
		$name 	  	 = str_replace('mod_', '', $name);
		$class  	 = 'mod' . ucfirst($name) . 'Helper';
		$method 	 = $app->input->get('method', 'get') . 'Ajax';
		$helper_file = JPATH_BASE . '/modules/mod_' . $name . '/helper.php';

		if (!is_file($helper_file))
		{
			// The helper file does not exist
			throw new RuntimeException(sprintf('The file at %s does not exist', 'mod_' . $name . '/helper.php'), 404);
		}

		// Get module helper
		require_once $helper_file;

		if (!method_exists($class, $method))
		{
			// Method does not exist
			throw new LogicException(sprintf('Method %s does not exist', $method), 404);
		}

		return call_user_func($class . '::' . $method);
	}
}
