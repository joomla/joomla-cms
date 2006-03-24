<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Base class for a Joomla Controller
 *
 * @abstract
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.5
 */
class JController extends JObject {
	/**
	 * The Main Application [JApplication]
	 * @var	object
	  */
	var $_app = null;

	/**
	 * The Menu item object for the current $Itemid
	 * @var	object
	  */
	var $_menu = null;

	/**
	 * Array of class methods
	 * @var	array
	 */
	var $_methods 	= null;

	/**
	 * Array of class methods to call for a given task
	 * @var	array 
	 */
	var $_taskMap 	= null;

	/**
	 * Current task name
	 * @var	string
	 */
	var $_task 		= null;

	/**
	 * URL for redirection
	 * @var	string
	 */
	var $_redirect 	= null;

	/**
	 * Redirect message
	 * @var	string
	 */
	var $_message 	= null;

	/**
	 * ACO Section for the controller
	 * @var	string
	 */
	var $_acoSection 		= null;

	/**
	 * Default ACO Section value for the controller
	 * @var	string
	 */
	var $_acoSectionValue 	= null;

	/**
	 * View object
	 * @var	object
	 */
	var $_view = null;

	/**
	 * View file base path
	 * @var	string
	 */
	var $_viewPath = null;

	/**
	 * Name of the current view
	 * @var	string
	 */
	var $_viewName = null;

	/**
	 * Request option
	 * @var	string
	 */
	var $_viewOption = null;

	/**
	 * View name prefix
	 * @var	string
	 */
	var $_viewPrefix = null;

	/**
	 * Constructor
	 * 
	 * @access	protected
	 * @param	object	$app	The main application
	 * @param	string	$default	The default task [optional]
	 * @since	1.5
	 */
	function __construct( &$app, $default = null )
	{
		/*
		 * Initialize private variables
		 */
		$this->_redirect = null;
		$this->_message = null;
		$this->_taskMap = array();
		$this->_methods = array();

		// Iterate through methods and map tasks
		foreach (get_class_methods( get_class( $this ) ) as $method) {
			if (substr( $method, 0, 1 ) != '_') {
				$this->_methods[] = strtolower( $method );
				// auto register public methods as tasks
				$this->_taskMap[strtolower( $method )] = $method;
			}
		}
		// If the default task is set, register it as such
		if ($default) {
			$this->registerDefaultTask( $default );
		}
		// Register the main application to the controller
		$this->_app = & $app;
		
		// Build and set the menu item object from $Itemid
		$Itemid = JRequest::getVar( 'Itemid', 0, '', 'int' );
		if ($Itemid) {
			$menu = & JMenu::getInstance();
			$menu = & $menu->getItem($Itemid);
			$menu->parameters = & new JParameter($menu->params);
		} else {
			$menu = & new stdClass();
			$menu->parameters = & new JParameter();
		}
		$this->_menu = & $menu;
	}

	/**
	 * Get the system database object from the application
	 * 
	 * @access	public
	 * @return object
	 * @since	1.5
	 */
	function &getDBO()
	{
		return $this->_app->getDBO();
	}

	/**
	 * Authorization check
	 * 
	 * @access	public
	 * @param	string	$task	The ACO Section Value to check access on
	 * @return	boolean	True if authorized
	 * @since	1.5
	 */
	function authorize( $task )
	{
		// Only do access check if the aco section is set
		if ($this->_acoSection) {
			// If we have a section value set that trumps the passed task ???
			if ($this->_acoSectionValue) {
				// We have one, so set it and lets do the check
				$task = $this->_acoSectionValue;
			}
			// Get the JUser object for the current user and return the authorization boolean
			$user = & $this->_app->getUser();
			return $user->authorize( $this->_acoSection, $task );
		} else {
			// Nothing set, nothing to check... so obviously its ok :)
			return true;
		}
	}

	/**
	 * Sets the access control levels
	 * 
	 * @access	public
	 * @param string The ACO section (eg, the component)
	 * @param string The ACO section value (if using a constant value)
	 * @return	void
	 * @since	1.5
	 */
	function setAccessControl( $section, $value=null )
	{
		$this->_acoSection = $section;
		$this->_acoSectionValue = $value;
	}

	/**
	 * Set a URL to redirect the browser to
	 *
	 * @access	public
	 * @param	string	$url	URL to redirect to
	 * @param	string	$msg	Message to display on redirect
	 * @return	void
	 * @since	1.5
	 */
	function setRedirect( $url, $msg = null )
	{
		$this->_redirect = $url;
		if ($msg !== null) {
			$this->_message = $msg;
		}
	}

	/**
	 * Redirects the browser or returns false if no redirect is set.
	 * 
	 * @access	public
	 * @return	boolean	False if no redirect exists
	 * @since	1.5
	 */
	function redirect()
	{
		if ($this->_redirect) {
			josRedirect( $this->_redirect, $this->_message );
		}
	}

	/**
	 * Register (map) a task to a method in the class
	 * 
	 * @access	public
	 * @param	string	$task		The task
	 * @param	string	$method	The name of the method in the derived class to perform for this task
	 * @return	void
	 * @since	1.5
	 */
	function registerTask( $task, $method )
	{
		if (in_array( strtolower( $method ), $this->_methods )) {
			$this->_taskMap[strtolower( $task )] = $method;
		} else {
			JError::raiseError( 404, JText::_('Method '.$method.' not found') );
		}
	}

	/**
	 * Register the default task to perfrom if a mapping is not found
	 * 
	 * @access	public
	 * @param	string	$method	The name of the method in the derived class to perform if the task is not found
	 * @return	void
	 * @since	1.5
	 */
	function registerDefaultTask( $method )
	{
		$this->registerTask( '__default', $method );
	}

	/**
	 * Perform a task by triggering a method in the derived class
	 * 
	 * @access	public
	 * @param	string	$task	The task to perform
	 * @return	mixed	The value returned by the function
	 * @since	1.5
	 */
	function performTask( $task )
	{
		$this->_task = $task;

		$task = strtolower( $task );
		if (isset( $this->_taskMap[$task] )) {
			// We have a method in the map to this task
			$doTask = $this->_taskMap[$task];
		} else if (isset( $this->_taskMap['__default'] )) {
			// Didn't find the method, but we do have a default method
			$doTask = $this->_taskMap['__default'];
		} else {
			// Don't have a default method either...
			JError::raiseError( 404, JText::_('Task '.$task.' not found') );
			return false;
		}
		// Time to make sure we have access to do what we want to do...
		if ($this->authorize( $doTask )) {
			// Yep, lets do it already
			return call_user_func( array( &$this, $doTask ) );
		} else {
			// No access... better luck next time
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return false;
		}
	}

	/**
	 * Get the last task that was to be performed
	 * 
	 * @access	public
	 * @return	string	The task that was or is being performed
	 * @since	1.5
	 */
	function getTask()
	{
		return $this->_task;
	}

	/**
	 * Method to get the current view path
	 * 
	 * @access	public
	 * @return	string	View class file base directory
	 * @since	1.5
	 */
	function setViewPath( $path )
	{
		$this->_viewPath = JPath::clean( $path );
		return $this->_viewPath;
	}

	/**
	 * Method to set the current view path
	 * 
	 * @access	public
	 * @param	string	View class file base directory
	 * @return	string	The path
	 * @since	1.5
	 */
	function getViewPath()
	{
		return $this->_viewPath;
	}

	/**
	 * Method to set the current view.  Normally this would be done automatically, but this method is provided
	 * for maximum flexibility
	 * 
	 * @access	public
	 * @param	object	The view object to set
	 * @return	object	The view
	 * @since	1.5
	 */
	function &setView( &$view )
	{
		$this->_view = &$view;
		return $view;
	}

	/**
	 * Method to get the current view and load it if necessary..
	 * 
	 * @access	public
	 * @return	object	The view
	 * @since	1.5
	 */
	function &getView()
	{
		if (is_null( $this->_view )) {
			$view = $this->_loadView( $this->_viewName, $this->_viewOption, $this->_viewPrefix );
			$this->setView( $view );
		}
		return $this->_view;
	}

	/**
	 * Method to set the view name and options for loading the view class.
	 * 
	 * @access	public
	 * @param	string	$viewName	The name of the view
	 * @param	string	$option		The component subdirectory of the template folder to look in for an alternate
	 * @param	string	$prefix			Optional prefix for the view class name
	 * @return	void
	 * @since	1.5
	 */
	function setViewName( $viewName, $option, $prefix='' )
	{
		$this->_viewName	= $viewName;
		$this->_viewOption	= $option;
		$this->_viewPrefix	= $prefix;
	}

	/**
	 * Method to load and return a view object.  This method first looks in the current template directory for a match, and 
	 * failing that uses a default set path to load the view class file.
	 * 
	 * @access	private
	 * @param	string	$viewName	The name of the view
	 * @param	string	$option		The component subdirectory of the template folder to look in for an alternate
	 * @param	string	$prefix			Optional prefix for the view class name
	 * @return	mixed	View object or boolean false if failed
	 * @since	1.5
	 */
	function &_loadView( $viewName, $option, $prefix='' )
	{
		// Clean the view name
		$viewName = preg_replace( '#\W#', '', $viewName );
		
		// Get the current template name and path
		$tName = $this->_app->getTemplate();
		$tPath = JPATH_BASE.DS.'templates'.DS.$tName.DS.$option.DS.$viewName.'.php';

		// If a matching view exists in the current template folder we use that, otherwise we look for the default one
		if (file_exists( $tPath )) {
			require_once( $tPath );
			// Build the view class name
			// Alternate view classes must be postfixed with '_alt'
			$viewClass = 'JView'.$prefix.$viewName.'_alt';
			if (!class_exists( $viewClass )) {
				JError::raiseNotice( 0, 'View class '.$viewClass.' not found' );
			} else {
				return new $viewClass( $this );
			}
		} else {
			// Build the path to the default view based upon a supplied base path
			$path = $this->getViewPath().$viewName.DS.$viewName.'.php';

			// If the default view file exists include it and try to instantiate the object
			if (file_exists( $path )) {
				require_once( $path );
				// Build the view class name
				$viewClass = 'JView'.$prefix.$viewName;
				if (!class_exists( $viewClass )) {
					JError::raiseNotice( 0, 'View class ' . $viewClass . ' not found in file.' );
				} else {
					$view = & new $viewClass( $this );
					return $view;
				}
			} else {
				JError::raiseNotice( 0, 'View ' . $viewName . ' not supported. File not found.' );
			}
		}
		return false;
	}

	/**
	 * Typical view method for MVC based architecture
	 */
	function display()
	{
		$view = &$this->getView();
		$view->display();
	}
}
?>