<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Application
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Component helper class
 *
 * @static
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.5
 */
class JComponentHelper
{
	/**
	 * Get the component info
	 *
	 * @access	public
	 * @param	string $name 	The component name
	 * @param 	boolean	$string	If set and a component does not exist, the enabled attribue will be set to false
	 * @return	object A JComponent object
	 */
	function &getComponent($name, $strict = false)
	{
		$result = null;
		$components = JComponentHelper::_load();

		if (isset($components[$name]))
		{
			$result = &$components[$name];
		}
		else
		{
			$result				= new stdClass();
			$result->enabled	= $strict ? false : true;
			$result->params		= null;
		}

		return $result;
	}

	/**
	 * Checks if the component is enabled
	 *
	 * @access	public
	 * @param	string	$component The component name
	 * @param 	boolean	$string	If set and a component does not exist, false will be returned
	 * @return	boolean
	 */
	function isEnabled($component, $strict = false)
	{
		global $mainframe;

		$result = &JComponentHelper::getComponent($component, $strict);
		return ($result->enabled | $mainframe->isAdmin());
	}

	/**
	 * Gets the parameter object for the component
	 *
	 * @access public
	 * @param string $name The component name
	 * @return object A JParameter object
	 */
	function &getParams($name)
	{
		static $instances;
		if (!isset($instances[$name]))
		{
			$component = &JComponentHelper::getComponent($name);
			$instances[$name] = new JParameter($component->params);
		}
		return $instances[$name];
	}

	function renderComponent($name = null, $params = array())
	{
		global $mainframe, $option;

		if (empty($name)) {
			// Throw 404 if no component
			JError::raiseError(404, JText::_("Component Not Found"));
			return;
		}

		$scope = $mainframe->scope; //record the scope
		$mainframe->scope = $name;  //set scope to component name

		// Build the component path
		$name = preg_replace('/[^A-Z0-9_\.-]/i', '', $name);
		$file = substr($name, 4);

		// Define component path
		define('JPATH_COMPONENT',					JPATH_BASE.DS.'components'.DS.$name);
		define('JPATH_COMPONENT_SITE',				JPATH_SITE.DS.'components'.DS.$name);
		define('JPATH_COMPONENT_ADMINISTRATOR',	JPATH_ADMINISTRATOR.DS.'components'.DS.$name);

		// get component path
		if ($mainframe->isAdmin() && file_exists(JPATH_COMPONENT.DS.'admin.'.$file.'.php')) {
			$path = JPATH_COMPONENT.DS.'admin.'.$file.'.php';
		} else {
			$path = JPATH_COMPONENT.DS.$file.'.php';
		}

		// If component disabled throw error
		if (!JComponentHelper::isEnabled($name) || !file_exists($path)) {
			JError::raiseError(404, JText::_('Component Not Found'));
		}

		$task = JRequest::getString('task');

		// Load common language files
		$lang = &JFactory::getLanguage();
		$lang->load($name);

		// Handle template preview outlining
		$contents = null;

		// Execute the component
		ob_start();
		require_once $path;
		$contents = ob_get_contents();
		ob_end_clean();

		// Build the component toolbar
		jimport('joomla.application.helper');
		if (($path = JApplicationHelper::getPath('toolbar')) && $mainframe->isAdmin()) {

			// Get the task again, in case it has changed
			$task = JRequest::getString('task');

			// Make the toolbar
			include_once($path);
		}

		$mainframe->scope = $scope; //revert the scope

		return $contents;
	}

	/**
	 * Load components
	 *
	 * @access	private
	 * @return	array
	 */
	function _load()
	{
		static $components;

		if (isset($components)) {
			return $components;
		}

		$db = &JFactory::getDbo();

		$query = 'SELECT *' .
				' FROM #__components' .
				' WHERE parent = 0';
		$db->setQuery($query);

		if (!($components = $db->loadObjectList('option'))) {
			JError::raiseWarning('SOME_ERROR_CODE', "Error loading Components: " . $db->getErrorMsg());
			return false;
		}

		return $components;

	}
}
