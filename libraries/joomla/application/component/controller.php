<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Base class for a Joomla Controller
 *
 * Controller (controllers are where you put all the actual code) Provides basic
 * functionality, such as rendering views (aka displaying templates).
 *
 * @abstract
 * @package		Joomla.Framework
 * @subpackage	Application
 * @author		Andrew Eddie
 * @since		1.5
 */
class JController extends JObject
{
	/**
	 * The name of the controller
	 *
	 * @var		array
	 * @access protected
	 */
	var $_name = null;

	/**
	 * Array of class methods
	 *
	 * @var	array
	 * @access protected
	 */
	var $_methods 	= null;

	/**
	 * Array of class methods to call for a given task
	 *
	 * @var	array
	 * @access protected
	 */
	var $_taskMap 	= null;

	/**
	 * Requested task to be preformed
	 *
	 * @var	string
	 * @access protected
	 */
	var $_task 		= null;

	/**
	 * The mapped task that was performed
	 *
	 * @var	string
	 * @access protected
	 */
	var $_doTask 	= null;

    /**
	 * The set of search directories for resources (views or models)
	 *
	 * @var array
	 * @access protected
	 */
	var $_path = array(
		'model' => array(),
		'view'  => array()
	);

	/**
	 * URL for redirection
	 *
	 * @var	string
	 * @access protected
	 */
	var $_redirect 	= null;

	/**
	 * Redirect message
	 *
	 * @var	string
	 * @access protected
	 */
	var $_message 	= null;

	/**
	 * Redirect message type
	 *
	 * @var	string
	 * @access protected
	 */
	var $_messageType 	= null;

	/**
	 * ACO Section for the controller
	 *
	 * @var	string
	 * @access protected
	 */
	var $_acoSection 		= null;

	/**
	 * Default ACO Section value for the controller
	 *
	 * @var	string
	 * @access protected
	 */
	var $_acoSectionValue 	= null;

	/**
	 * View object
	 *
	 * @var	object
	 * @access protected
	 */
	var $_view = null;

	/**
	 * Name of the current view
	 *
	 * @var	string
	 * @access protected
	 */
	var $_viewName = null;

	/**
	 * Type of the current view
	 *
	 * @var	string
	 * @access protected
	 */
	var $_viewType = null;

	/**
	 * View name prefix
	 *
	 * @var	string
	 * @access protected
	 */
	var $_viewPrefix = null;

	/**
	 * An error message
	 *
	 * @var string
	 * @access protected
	 */
	var $_error;

	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	string	$default	The default task [optional]
	 * @since	1.5
	 */
	function __construct( $config = array() )
	{
		//Initialize private variables
		$this->_redirect	= null;
		$this->_message		= null;
		$this->_messageType = 'message';
		$this->_taskMap		= array();
		$this->_methods		= array();
		$this->_data		= array();

		// Get the methods only for the final controller class
		$thisMethods	= get_class_methods( get_class( $this ) );
		$baseMethods	= get_class_methods( 'JController' );
		$methods		= array_diff( $thisMethods, $baseMethods );

		// Add default display method
		$methods[] = 'display';

		// Iterate through methods and map tasks
		foreach ($methods as $method)
		{
			if (substr( $method, 0, 1 ) != '_')
			{
				$this->_methods[] = strtolower( $method );
				// auto register public methods as tasks
				$this->_taskMap[strtolower( $method )] = $method;
			}
		}

		//Set the controller name
		if (empty( $this->_name ))
		{
			if (isset($config['name']))  {
				$this->_name = $config['name'];
			}
			else
			{
				$r = null;
				if (!preg_match('/Controller(.*)/i', get_class($this), $r)) {
					JError::raiseError (500, "JController::__construct() : Can't get or parse class name.");
				}
				$this->_name = strtolower( $r[1] );
			}
		}

		// If the default task is set, register it as such
		if (isset($config['default_task'])) {
			$this->registerDefaultTask( $config['default_task'] );
		} else {
			$this->registerDefaultTask('display' );
		}

		// set the default model search path
		if (isset($config['model_path'])) {
			// user-defined dirs
			$this->_setPath('model', $config['model_path']);
		} else {
			$this->setModelPath(null);
		}

		// set the default view search path
		if (isset($config['view_path'])) {
			// user-defined dirs
			$this->_setPath('view', $config['view_path']);
		} else {
			$this->setViewPath(null);
		}
	}

	/**
	 * Execute a task by triggering a method in the derived class
	 *
	 * @access	public
	 * @param	string	$task	The task to perform
	 * @return	mixed	The value returned by the function
	 * @since	1.5
	 */
	function execute( $task )
	{
		$this->_task = $task;

		$task = strtolower( $task );
		if (isset( $this->_taskMap[$task] ))
		{
			// We have a method in the map to this task
			$doTask = $this->_taskMap[$task];
		}
		else if (isset( $this->_taskMap['__default'] ))
		{
			// Didn't find the method, but we do have a default method
			$doTask = $this->_taskMap['__default'];
		}
		else
		{
			// Don't have a default method either...
			JError::raiseError( 404, JText::_('Task ['.$task.'] not found') );
			return false;
		}

		// Record the actual task being fired
		$this->_doTask = $doTask;

		// Time to make sure we have access to do what we want to do...
		if ($this->authorize( $doTask ))
		{
			// Yep, lets do it already
			return $this->$doTask();
		}
		else
		{
			// No access... better luck next time
			JError::raiseError( 403, JText::_('Access Forbidden') );
			return false;
		}
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
		if ($this->_acoSection)
		{
			// If we have a section value set that trumps the passed task ???
			if ($this->_acoSectionValue)
			{
				// We have one, so set it and lets do the check
				$task = $this->_acoSectionValue;
			}
			// Get the JUser object for the current user and return the authorization boolean
			$user = & JFactory::getUser();
			return $user->authorize( $this->_acoSection, $task );
		}
		else
		{
			// Nothing set, nothing to check... so obviously its ok :)
			return true;
		}
	}

	/**
	 * Typical view method for MVC based architecture
	 *
	 */
	function display()
	{
		$view = &$this->getView();
		$view->display();
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
		if ($this->_redirect)
		{
			global $mainframe;
			$mainframe->redirect( $this->_redirect, $this->_message, $this->_messageType );
		}
	}

	/**
	 * Method to get a model object, load it if necessary..
	 *
	 * @access	public
	 * @param	string The model name
	 * @param	string The class prefix
	 * @return	object	The model
	 * @since	1.5
	 */
	function &getModel($name, $prefix='')
	{
		if ($model = &$this->_createModel( $name, $prefix ))
		{
			// task is a reserved state
			$model->setState( 'task', $this->_doTask );
		}
		return $model;
	}

	/**
     * Resets the stack of controller model paths.
     *
     * To clear all paths, use JController::setModelPath(null).
     *
     * @param string|array The directory (-ies) to set as the path.
     * @return void
     */
	function setModelPath( $path )
	{
		$this->_setPath('model', $path);
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
	 * Method to get the current view and load it if necessary..
	 *
	 * @access	public
	 * @return	object	The view
	 * @since	1.5
	 */
	function &getView($name='', $prefix='', $type='')
	{
		if (is_null( $this->_view ))
		{
			if (empty($name)) {
				$name = $this->_viewName;
			}

			if (empty($prefix)) {
				$prefix = $this->_viewPrefix;
			}

			if (empty($type)) {
				$type = $this->_viewType;
			}

			$view =& $this->_createView( $name, $prefix, $type );
			$this->setView( $view );
		}
		return $this->_view;
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
	 * Method to get the current view path
	 *
	 * @access	public
	 * @return	string	View class file base directory
	 * @since	1.5
	 */
	function setViewPath( $path )
	{
		$this->_setPath('view', $path);
	}

	/**
	 * Method to set the view name and options for loading the view class.
	 *
	 * @access	public
	 * @param	string	$viewName	The name of the view
	 * @param	string	$prefix		Optional prefix for the view class name
	 * @return	void
	 * @since	1.5
	 */
	function setViewName( $viewName, $prefix = null, $type = null )
	{
		$this->_viewName = $viewName;

		if ($prefix !== null) {
			$this->_viewPrefix = $prefix;
		}

		if ($prefix !== null) {
			$this->_viewType = $type;
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
	function registerDefaultTask( $method ) {
		$this->registerTask( '__default', $method );
	}

	/**
	 * Get the error message
	 * @return string The error message
	 * @since 1.5
	 */
	function getError() {
		return $this->_error;
	}

	/**
	 * Sets the error message
	 * @param string The error message
	 * @return string The new error message
	 * @since 1.5
	 */
	function setError( $value )
	{
		$this->_error = $value;
		return $this->_error;
	}

	/**
	 * Set a URL to redirect the browser to
	 *
	 * @access	public
	 * @param	string	$url	URL to redirect to
	 * @param	string	$msg	Message to display on redirect
	 * @param	string	$type	Message type
	 * @return	void
	 * @since	1.5
	 */
	function setRedirect( $url, $msg = null, $type = 'message' )
	{
		$this->_redirect	= $url;
		$this->_message		= $msg;
		$this->_messageType	= $type;
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
	 * Method to load and return a model object.
	 *
	 * @access	private
	 * @param	string	$modelName	The name of the view
	 * @return	mixed	Model object or boolean false if failed
	 * @since	1.5
	 */
	function &_createModel( $name, $prefix = '')
	{
		$false = false;

		// Clean the model name
		$modelName   = preg_replace( '#\W#', '', $name );
		$classPrefix = preg_replace( '#\W#', '', $prefix );

		// Build the model class name
		$modelClass = $classPrefix.$modelName;

		if (!class_exists( $modelClass ))
		{
			// If the model file exists include it and try to instantiate the object
			if ($path = $this->_findFile('model', strtolower($modelName).'.php'))
			{
				require( $path );
				if (!class_exists( $modelClass ))
				{
					JError::raiseWarning( 0, 'Model class ' . $modelClass . ' not found in file.' );
					return $false;
				}
			}
			else
			{
				JError::raiseWarning( 0, 'Model ' . $modelName . ' not supported. File not found.' );
				return $false;
			}
		}

		$model = new $modelClass();
		return $model;
	}

	/**
	 * Method to load and return a view object.  This method first looks in the current template directory for a match, and
	 * failing that uses a default set path to load the view class file.
	 *
	 * @access	private
	 * @param	string	The name of the view
	 * @param	string	Optional prefix for the view class name
	 * @return	mixed	View object or boolean false if failed
	 * @since	1.5
	 */
	function &_createView( $name, $prefix = '', $type = '' )
	{
		$false = false;

		// Clean the view name
		$viewName	 = preg_replace( '#\W#', '', $name );
		$classPrefix = preg_replace( '#\W#', '', $prefix );
		$viewType	 = preg_replace( '#\W#', '', $type );

		if (!empty($type)) {
			$type = '.'.$type;
		}

		$view		= null;

		// Build the view class name
		$viewClass = $classPrefix.$viewName;

		if (!class_exists( $viewClass ))
		{
			// If the default view file exists include it and try to instantiate the object
			if ($path = $this->_findFile('view', strtolower($viewName).DS.'view'.$type.'.php'))
			{
				require_once( $path );

				if (!class_exists( $viewClass ))
				{
					JError::raiseWarning( 0, 'View class ' . $viewClass . ' not found in file.' );
					return $false;
				}
			}
			else
			{
				JError::raiseWarning( 0, 'View ' . $viewName . ' not supported. File not found.' );
				return $false;
			}
		}

		$view = new $viewClass();
		return $view;
	}

   /**
	* Sets an entire array of search paths for resources.
	*
	* @access protected
	* @param string $type The type of path to set, typically 'view' or 'model.
	* @param string|array $path The new set of search paths.  If null or
	* false, resets to the current directory only.
	*/
	function _setPath($type, $path)
	{
		global $mainframe, $option;

		// clear out the prior search dirs
		$this->_path[$type] = array();

		// always add the fallback directories as last resort
		switch (strtolower($type))
		{
			case 'view':
				// the current directory
				$this->_addPath($type, JPATH_COMPONENT.DS.'views');
				break;

			case 'model':
				// the current directory
				$this->_addPath($type, JPATH_COMPONENT.DS.'models');
				break;
		}

		// actually add the user-specified directories
		$this->_addPath($type, $path);
	}

   /**
	* Adds to the search path for templates and resources.
	*
	* @access protected
	* @param string|array $path The directory or stream to search.
	*/
	function _addPath($type, $path)
	{
		// convert from path string to array of directories
		if (is_string($path) && ! strpos($path, '://'))
		{
			// the path config is a string, and it's not a stream
			// identifier (the "://" piece). add it as a path string.
			$path = explode(PATH_SEPARATOR, $path);

			// typically in path strings, the first one is expected
			// to be searched first. however, JView uses a stack,
			// so the first would be last.  reverse the path string
			// so that it behaves as expected with path strings.
			$path = array_reverse($path);
		}
		else
		{
			// just force to array
			settype($path, 'array');
		}

		// loop through the path directories
		foreach ($path as $dir)
		{
			// no surrounding spaces allowed!
			$dir = trim($dir);

			// add trailing separators as needed
			if (strpos($dir, '://') && substr($dir, -1) != '/') {
				// stream
				$dir .= '/';
			} elseif (substr($dir, -1) != DIRECTORY_SEPARATOR) {
				// directory
				$dir .= DIRECTORY_SEPARATOR;
			}

			// add to the top of the search dirs
			array_unshift($this->_path[$type], $dir);
		}
	}

   /**
	* Searches the directory paths for a given file.
	*
	* @access protected
	* @param array $type The type of path to search (template or resource).
	* @param string $file The file name to look for.
	*
	* @return string|bool The full path and file name for the target file,
	* or boolean false if the file is not found in any of the paths.
	*/
	function _findFile($type, $file)
	{
		// get the set of paths
		$set = $this->_path[$type];

		// start looping through the path set
		foreach ($set as $path)
		{
			// get the path to the file
			$fullname = $path . $file;

			// is the path based on a stream?
			if (strpos($path, '://') === false)
			{
				// not a stream, so do a realpath() to avoid directory
				// traversal attempts on the local file system.
				$path = realpath($path); // needed for substr() later
				$fullname = realpath($fullname);
			}

			// the substr() check added to make sure that the realpath()
			// results in a directory registered with Savant so that
			// non-registered directores are not accessible via directory
			// traversal attempts.
			if (file_exists($fullname) && is_readable($fullname) &&
				substr($fullname, 0, strlen($path)) == $path)
			{
				return $fullname;
			}
		}

		// could not find the file in the set of paths
		return false;
	}
}
?>