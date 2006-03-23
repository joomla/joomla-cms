<?php
/**
* @version $Id: app.php 1534 2005-12-22 01:38:31Z Jinx $
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
* Acts as a Factory class for application specific objects and
* provides many supporting API functions.
*
* @abstract
* @package		Joomla.Framework
* @subpackage	Application
* @since		1.1
*/
class JController extends JObject {
	/** @var array An array of the class methods to call for a task */
	var $_taskMap 	= null;
	/** @var string The name of the current task*/
	var $_task 		= null;
	/** @var array An array of the class methods*/
	var $_methods 	= null;
	/** @var string A url to redirect to */
	var $_redirect 	= null;
	/** @var string A message about the operation of the task */
	var $_message 	= null;

	// action based access control

	/** @var string The ACO Section */
	var $_acoSection 		= null;
	/** @var string The ACO Section value */
	var $_acoSectionValue 	= null;

	/** object The App */
	var $_app = null;

	/** @var object */
	var $_view = null;
	/** @var string */
	var $_viewPath = null;

	var $_viewName = null;
	var $_viewOption = null;
	var $_viewPrefix = null;

	/**
	 * Constructor
	 * @param string Set the default task
	 */
	function __construct( $default='' ) {
		$this->_taskMap = array();
		$this->_methods = array();
		foreach (get_class_methods( get_class( $this ) ) as $method) {
			if (substr( $method, 0, 1 ) != '_') {
				$this->_methods[] = strtolower( $method );
				// auto register public methods as tasks
				$this->_taskMap[strtolower( $method )] = $method;
			}
		}
		$this->_redirect = '';
		$this->_message = '';
		if ($default) {
			$this->registerDefaultTask( $default );
		}

		global $mainframe;
		$this->setApp( $mainframe );
	}

	/**
	 * Sets the access control levels
	 * @param string The ACO section (eg, the component)
	 * @param string The ACO section value (if using a constant value)
	 */
	function setAccessControl( $section, $value=null ) {
		$this->_acoSection = $section;
		$this->_acoSectionValue = $value;
	}
	/**
	 * Access control check
	 */
	function accessCheck( $task ) {
		global $acl, $my;

		// only check if the derived class has set these values
		if ($this->_acoSection) {
			// ensure user has access to this function
			if ($this->_acoSectionValue) {
				// use a 'constant' task for this task handler
				$task = $this->_acoSectionValue;
			}
			return $acl->acl_check( $this->_acoSection, $task, 'users', $my->usertype );
		} else {
			return true;
		}
	}

	/**
	 * Set a URL to redirect the browser to
	 * @param string A URL
	 */
	function setRedirect( $url, $msg = null ) {
		$this->_redirect = $url;
		if ($msg !== null) {
			$this->_message = $msg;
		}
	}
	/**
	 * Redirects the browser
	 */
	function redirect() {
		if ($this->_redirect) {
			mosRedirect( $this->_redirect, $this->_message );
		}
	}
	/**
	 * Register (map) a task to a method in the class
	 * @param string The task
	 * @param string The name of the method in the derived class to perform for this task
	 */
	function registerTask( $task, $method ) {
		if (in_array( strtolower( $method ), $this->_methods )) {
			$this->_taskMap[strtolower( $task )] = $method;
		} else {
			$this->methodNotFound( $method );
		}
	}
	/**
	 * Register the default task to perfrom if a mapping is not found
	 * @param string The name of the method in the derived class to perform if the task is not found
	 */
	function registerDefaultTask( $method ) {
		$this->registerTask( '__default', $method );
	}
	/**
	 * Perform a task by triggering a method in the derived class
	 * @param string The task to perform
	 * @return mixed The value returned by the function
	 */
	function performTask( $task ) {
		$this->_task = $task;

		$task = strtolower( $task );
		if (isset( $this->_taskMap[$task] )) {
			$doTask = $this->_taskMap[$task];
		} else if (isset( $this->_taskMap['__default'] )) {
			$doTask = $this->_taskMap['__default'];
		} else {
			return $this->taskNotFound( $this->_task );
		}

		if ($this->accessCheck( $doTask )) {
			return call_user_func( array( &$this, $doTask ) );
		} else {
			return $this->notAllowed( $task );
		}
	}
	/**
	 * Get the last task that was to be performed
	 * @return string The task that was or is being performed
	 */
	function getTask() {
		return $this->_task;
	}
	/**
	 * Basic method if the task is not found
	 * @param string The task
	 * @return null
	 */
	function taskNotFound( $task ) {
		echo 'Task ' . $task . ' not found';
		return null;
	}
	/**
	 * Basic method if the registered method is not found
	 * @param string The name of the method in the derived class
	 * @return null
	 */
	function methodNotFound( $name ) {
		echo 'Method ' . $name . ' not found';
		return null;
	}
	/**
	 * Basic method if access is not permitted to the task
	 * @param string The name of the method in the derived class
	 * @return null
	 */
	function notAllowed( $name ) {
		echo _NOT_AUTH;

		return null;
	}

	//
	// THE APPLICATION
	//

	function &setApp( $app ) {
		$this->_app = &$app;
		return $app;
	}

	function &getApp() {
		return $this->_app;
	}

	/**
	 * Get the system database object
	 * @return object
	 */
	function &getDBO() {
		return $this->_app->getDBO();
	}

	//
	// VIEWS
	//

	function setViewPath( $path ) {
		$this->_viewPath = JPath::clean( $path );
		return $this->_viewPath;
	}

	function getViewPath() {
		return $this->_viewPath;
	}

	function &setView( &$view ) {
		$this->_view = &$view;
		return $view;
	}

	function &getView() {
		if (is_null( $this->_view ))
		{
			$view = $this->_loadView( $this->_viewName, $this->_viewOption, $this->_viewPrefix );
			$this->setView( $view );
		}
		return $this->_view;
	}

	/**
	 * @param string The name of the view
	 * @param string The name of the component used in the request (option)
	 * @param string A prefix used for the view name, eg, JViewPrefixName
	 */
	function setViewName( $viewName, $option, $prefix='' ) {
		$this->_viewName = $viewName;
		$this->_viewOption = $option;
		$this->_viewPrefix = $prefix;
	}

	/**
	 */
	function &_loadView( $viewName, $option, $prefix='' ) {
		$mainframe = $this->getApp();

		$viewName = preg_replace( '#\W#', '', $viewName );

		// check template path
		$tName = $mainframe->getTemplate();

		$tPath = JPATH_SITE . DS . 'templates' . DS . $tName . DS . $option . DS . $viewName . '.php';

		if (file_exists( $tPath ))
		{
			// load the extended template view
			// the class must be prefixed with _alt

			$viewClass = 'JView' . $prefix . $viewName . '_alt';
			if (!class_exists( $viewClass )) {
				JError::raiseNotice( 0, 'View class ' . $viewClass . ' not found' );
			} else {
				$view = new $viewClass();
				$view->setController( $this );
				return $view;
			}
		}
		else
		{
			$path = $this->getViewPath() . $viewName . DS . $viewName . '.php';

			if (file_exists( $path ))
			{
				require_once( $path );

				$viewClass = 'JView' . $prefix . $viewName;

				if (!class_exists( $viewClass ))
				{
					JError::raiseNotice( 0, 'View class ' . $viewClass . ' not found in file.' );
				} else {
					$view = new $viewClass();
					$view->setController( $this );
					return $view;
				}
			} else {
				JError::raiseNotice( 0, 'View ' . $viewName . ' not supported. File not found.' );
			}
		}

		$null = null;
		return $null;
	}


	/**
	 * Typical view method for MVC based architecture
	 */
	function display() {
		$view = &$this->getView();
		$view->display();
	}


}
?>