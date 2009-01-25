<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Application
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// No direct access
defined('JPATH_BASE') or die();

/**
 * Component helper class
 *
 * @static
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.5
 */
abstract class JComponentHelper
{

	/**
	 * Get the component info
	 *
	 * @access	public
	 * @param	string $name 	The component name
	 * @param 	boolean	$string	If set and a component does not exist, the enabled attribue will be set to false
	 * @return	object A JComponent object
	 */
	public static function &getComponent( $name, $strict = false )
	{
		$result = null;
		$components = JComponentHelper::_load();

		if (isset( $components[$name] ))
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
	public static function isEnabled( $component, $strict = false )
	{
		$appl = JFactory::getApplication();

		$result = &JComponentHelper::getComponent( $component, $strict );
		return ($result->enabled | $appl->isAdmin());
	}

	/**
	 * Gets the parameter object for the component
	 *
	 * @access public
	 * @param string $name The component name
	 * @return object A JParameter object
	 */
	public static function &getParams( $name )
	{
		static $instances;
		if (!isset( $instances[$name] ))
		{
			$component = &JComponentHelper::getComponent( $name );
			$instances[$name] = new JParameter($component->params);
		}
		return $instances[$name];
	}

	/**
	 * Render a component
	 *
	 * @param	string $name Name of the component
	 * @param	array $params
	 * @return	string Output from rendering the component
	 * @since	1.5
	 */
	public static function renderComponent($name = null, $params = array())
	{
		$component	= JApplicationHelper::getComponentName();
		$appl	= JFactory::getApplication();

		//needed for backwards compatibility
		// @todo if legacy ...
		$mainframe =& $appl;

		if(empty($name)) {
			// Throw 404 if no component
			JError::raiseError(404, JText::_("Component Not Found"));
			return;
		}

		$scope = $appl->scope; //record the scope
		$appl->scope = $name;  //set scope to component name

		// Build the component path
		$name = preg_replace('/[^A-Z0-9_\.-]/i', '', $name);
		$file = substr( $name, 4 );

		// Define component path
		define( 'JPATH_COMPONENT',					JPATH_BASE.DS.'components'.DS.$name);
		define( 'JPATH_COMPONENT_SITE',				JPATH_SITE.DS.'components'.DS.$name);
		define( 'JPATH_COMPONENT_ADMINISTRATOR',	JPATH_ADMINISTRATOR.DS.'components'.DS.$name);

		// get component path
		if ( $appl->isAdmin() && file_exists(JPATH_COMPONENT.DS.'admin.'.$file.'.php') ) {
			$path = JPATH_COMPONENT.DS.'admin.'.$file.'.php';
		} else {
			$path = JPATH_COMPONENT.DS.$file.'.php';
		}

		// If component disabled throw error
		if (!JComponentHelper::isEnabled( $name ) || !file_exists($path)) {
			JError::raiseError( 404, JText::_( 'Component Not Found' ) );
		}

		$task = JRequest::getString( 'task' );

		// Load common language files
		$lang =& JFactory::getLanguage();
		// 1.5 3PD or Core files
		$lang->load($name);
		// 1.6 3PD
		$lang->load('joomla', JPATH_COMPONENT);

		// @todo if ( $legacy )
		$option = $component;

		// Handle template preview outlining
		$contents = null;

		// Execute the component
		ob_start();
		require_once $path;
		$contents = ob_get_contents();
		ob_end_clean();

		// Build the component toolbar
		jimport( 'joomla.application.helper' );
		if (($path = JApplicationHelper::getPath( 'toolbar' )) && $appl->isAdmin()) {

			// Get the task again, in case it has changed
			$task = JRequest::getString( 'task' );

			// Make the toolbar
			include_once( $path );
		}

		$appl->scope = $scope; //revert the scope

		return $contents;
	}

	/**
	 * Load components
	 *
	 * @access	private
	 * @return	array
	 */
	protected static function _load()
	{
		static $components;

		if (isset($components)) {
			return $components;
		}

		$db = &JFactory::getDBO();

		$query = 'SELECT *' .
				' FROM #__components' .
				' WHERE parent = 0';
		$db->setQuery( $query );

		try {
			$components = $db->loadObjectList( 'option' );
		} catch (JException $e) {
			JError::raiseWarning( 'SOME_ERROR_CODE', "Error loading Components: " . $db->getErrorMsg());
			return false;
		}

		return $components;

	}
}

