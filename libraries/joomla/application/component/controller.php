<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Application
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// No direct access
defined('JPATH_BASE') or die();

/**
 * Base class for a Joomla Controller
 *
 * Controller (controllers are where you put all the actual code) Provides basic
 * functionality, such as rendering views (aka displaying templates).
 *
 * @abstract
 * @package		Joomla.Framework
 * @subpackage	Application
 * @since		1.5
 */
abstract class JController extends JObject
{
	/**
	 * The base path of the controller
	 *
	 * @var		string
	 * @access 	protected
	 */
	protected $_basePath = null;

	/**
	 * The name of the controller
	 *
	 * @var		array
	 * @access	protected
	 */
	protected $_name = null;

	/**
	 * Array of class methods
	 *
	 * @var	array
	 * @access	protected
	 */
	protected $_methods 	= null;

	/**
	 * Array of class methods to call for a given task.
	 *
	 * @var	array
	 * @access	protected
	 */
	protected $_taskMap 	= null;

	/**
	 * Current or most recent task to be performed.
	 *
	 * @var	string
	 * @access	protected
	 */
	protected $_task 		= null;

	protected $_data = null;

	/**
	 * The mapped task that was performed.
	 *
	 * @var	string
	 * @access	protected
	 */
	protected $_doTask 	= null;

	/**
	 * The set of search directories for resources (views).
	 *
	 * @var array
	 * @access	protected
	 */
	protected $_path = array(
		'view'	=> array()
	);

	/**
	 * URL for redirection.
	 *
	 * @var	string
	 * @access	protected
	 */
	protected $_redirect 	= null;

	/**
	 * Redirect message.
	 *
	 * @var	string
	 * @access	protected
	 */
	protected $_message 	= null;

	/**
	 * Redirect message type.
	 *
	 * @var	string
	 * @access	protected
	 */
	protected $_messageType 	= null;

	/**
	 * ACO Section for the controller.
	 *
	 * @var	string
	 * @access	protected
	 */
	protected $_acoSection 		= null;

	/**
	 * Default ACO Section value for the controller.
	 *
	 * @var	string
	 * @access	protected
	 */
	protected $_acoSectionValue 	= null;

	protected $_params = null;

	/**
	 * Constructor.
	 *
	 * @access	protected
	 * @param	array An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @since	1.5
	 */
	public function __construct( $config = array() )
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
		foreach ( $methods as $method )
		{
			if ( substr( $method, 0, 1 ) != '_' ) {
				$this->_methods[] = strtolower( $method );
				// auto register public methods as tasks
				$this->_taskMap[strtolower( $method )] = $method;
			}
		}

		//set the view name
		if (empty( $this->_name ))
		{
			if (array_key_exists('name', $config))  {
				$this->_name = $config['name'];
			} else {
				$this->_name = $this->getName();
			}
		}

		// Set a base path for use by the controller
		if (array_key_exists('base_path', $config)) {
			$this->_basePath	= $config['base_path'];
		} else {
			$this->_basePath	= JPATH_COMPONENT;
		}

		// If the default task is set, register it as such
		if ( array_key_exists( 'default_task', $config ) ) {
			$this->registerDefaultTask( $config['default_task'] );
		} else {
			$this->registerDefaultTask( 'display' );
		}

		// set the default model search path
		if ( array_key_exists( 'model_path', $config ) ) {
			// user-defined dirs
			$this->addModelPath($config['model_path']);
		} else {
			$this->addModelPath($this->_basePath.DS.'models');
		}

		// set the default view search path
		if ( array_key_exists( 'view_path', $config ) ) {
			// user-defined dirs
			$this->_setPath( 'view', $config['view_path'] );
		} else {
			$this->_setPath( 'view', $this->_basePath.DS.'views' );
		}
	}

	/**
	 * Execute a task by triggering a method in the derived class.
	 *
	 * @access	public
	 * @param	string The task to perform. If no matching task is found, the
	 * '__default' task is executed, if defined.
	 * @return	mixed|false The value returned by the called method, false in
	 * error case.
	 * @since	1.5
	 */
	public function execute( $task )
	{
		$this->_task = $task;

		$task = strtolower( $task );
		if (isset( $this->_taskMap[$task] )) {
			$doTask = $this->_taskMap[$task];
		} elseif (isset( $this->_taskMap['__default'] )) {
			$doTask = $this->_taskMap['__default'];
		} else {
			return JError::raiseError( 404, JText::_('Task ['.$task.'] not found') );
		}

		// Record the actual task being fired
		$this->_doTask = $doTask;

		// Make sure we have access
		if ($this->authorize( $doTask ))
		{
			$retval = $this->$doTask();
			return $retval;
		}
		else
		{
			return JError::raiseError( 403, JText::_('Access Forbidden') );
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
	public function authorize( $task )
	{
		// Only do access check if the aco section is set
		if ($this->_acoSection)
		{
			// If we have a section value set that trumps the passed task ???
			if ($this->_acoSectionValue) {
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
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @access	public
	 * @param	string	$cachable	If true, the view output will be cached
	 * @since	1.5
	 */
	public function display($cachable=false)
	{
		$document =& JFactory::getDocument();

		$viewType	= $document->getType();
		$viewName	= JRequest::getCmd( 'view', $this->getName() );
		$viewLayout	= JRequest::getCmd( 'layout', 'default' );

		$view = & $this->getView( $viewName, $viewType, '', array( 'base_path'=>$this->_basePath));

		// Get/Create the model
		if ($model = & $this->getModel($viewName)) {
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		// Set the layout
		$view->setLayout($viewLayout);

		// Display the view
		if ($cachable && $viewType != 'feed') {
			$component	= JApplicationHelper::getComponentName();
			$cache		= JFactory::getCache($component, 'view');
			$cache->get($view, 'display');
		} else {
			$view->display();
		}
	}

	/**
	 * Redirects the browser or returns false if no redirect is set.
	 *
	 * @access	public
	 * @return	boolean	False if no redirect exists.
	 * @since	1.5
	 */
	public function redirect()
	{
		if ($this->_redirect) {
			$appl = JFactory::getApplication();
			$appl->redirect( $this->_redirect, $this->_message, $this->_messageType );
		}
		return false;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @access	public
	 * @param	string	The model name. Optional.
	 * @param	string	The class prefix. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	object	The model.
	 * @since	1.5
	 */
	public function &getModel( $name = '', $prefix = '', $config = array() )
	{
		if ( empty( $name ) ) {
			$name = $this->getName();
		}

		if ( empty( $prefix ) ) {
			$prefix = $this->getName() . 'Model';
		}

		if ( $model = & $this->_createModel( $name, $prefix, $config ) )
		{
			// task is a reserved state
			$model->setState( 'task', $this->_task );

			// Lets get the application object and set menu information if its available
			$app	= &JFactory::getApplication();
			$menu	= &$app->getMenu();
			if (is_object( $menu ))
			{
				if ($item = $menu->getActive())
				{
					$params	=& $menu->getParams($item->id);
					// Set Default State Data
					$model->setState( 'parameters.menu', $params );
				}
			}
		}
		return $model;
	}

	/**
	 * Adds to the stack of model paths in LIFO order.
	 *
	 * @static
	 * @param	string|array The directory (string), or list of directories (array) to add.
	 * @return	void
	 */
	public function addModelPath( $path )
	{
		jimport('joomla.application.component.model');
		JModel::addIncludePath($path);
	}

	/**
	 * Gets the available tasks in the controller.
	 * @access	public
	 * @return	array Array[i] of task names.
	 * @since	1.5
	 */
	public function getTasks()
	{
		return $this->_methods;
	}

	/**
	 * Get the last task that is or was to be performed.
	 *
	 * @access	public
	 * @return	 string The task that was or is being performed.
	 * @since	1.5
	 */
	public function getTask()
	{
		return $this->_task;
	}

	/**
	 * Method to get the controller name
	 *
	 * The dispatcher name by default parsed using the classname, or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @access	public
	 * @return	string The name of the dispatcher
	 * @since	1.5
	 */
	public function getName()
	{
		$name = $this->_name;

		if (empty( $name ))
		{
			$r = null;
			if ( !preg_match( '/(.*)Controller/i', get_class( $this ), $r ) ) {
				throw new JException('Cannot get or parse class name', 500, E_ERROR, $name, true);
			}
			$name = strtolower( $r[1] );
		}

		return $name;
	}

	/**
	 * Method to get a reference to the current view and load it if necessary.
	 *
	 * @access	public
	 * @param	string	The view name. Optional, defaults to the controller
	 * name.
	 * @param	string	The view type. Optional.
	 * @param	string	The class prefix. Optional.
	 * @param	array	Configuration array for view. Optional.
	 * @return	object	Reference to the view or an error.
	 * @since	1.5
	 */
	public function &getView( $name = '', $type = '', $prefix = '', $config = array() )
	{
		static $views;

		if ( !isset( $views ) ) {
			$views = array();
		}

		if ( empty( $name ) ) {
			$name = $this->getName();
		}

		if ( empty( $prefix ) ) {
			$prefix = $this->getName() . 'View';
		}

		if ( empty( $views[$name] ) )
		{
			if ( $view = & $this->_createView( $name, $prefix, $type, $config ) ) {
				$views[$name] = & $view;
			} else {
				$result = JError::raiseError(
					500, JText::_( 'View not found [name, type, prefix]:' )
						. ' ' . $name . ',' . $type . ',' . $prefix
				);
				return $result;
			}
		}

		return $views[$name];
	}

	/**
	 * Add one or more view paths to the controller's stack, in LIFO order.
	 *
	 * @static
	 * @param	string|array The directory (string), or list of directories
	 * (array) to add.
	 * @return	void
	 */
	public function addViewPath( $path )
	{
		$this->_addPath( 'view', $path );
	}

	/**
	 * Register (map) a task to a method in the class.
	 *
	 * @access	public
	 * @param	string	The task.
	 * @param	string	The name of the method in the derived class to perform for this task.
	 * @return	void
	 * @since	1.5
	 */
	public function registerTask( $task, $method )
	{
		if ( in_array( strtolower( $method ), $this->_methods ) ) {
			$this->_taskMap[strtolower( $task )] = $method;
		}
	}

	/**
	 * Register the default task to perform if a mapping is not found.
	 *
	 * @access	public
	 * @param	string The name of the method in the derived class to perform if
	 * a named task is not found.
	 * @return	void
	 * @since	1.5
	 */
	public function registerDefaultTask( $method )
	{
		$this->registerTask( '__default', $method );
	}

	/**
	 * Sets the internal message that is passed with a redirect
	 *
	 * @access	public
	 * @param	string	The message
	 * @return	string	Previous message
	 * @since	1.5
	 */
	public function setMessage( $text )
	{
		$previous		= $this->_message;
		$this->_message = $text;
		return $previous;
	}

	/**
	 * Set a URL for browser redirection.
	 *
	 * @access	public
	 * @param	string URL to redirect to.
	 * @param	string	Message to display on redirect. Optional, defaults to
	 * 			value set internally by controller, if any.
	 * @param	string	Message type. Optional, defaults to 'message'.
	 * @return	void
	 * @since	1.5
	 */
	public function setRedirect( $url, $msg = null, $type = 'message' )
	{
		$this->_redirect = $url;
		if ($msg !== null) {
			// controller may have set this directly
			$this->_message	= $msg;
		}
		$this->_messageType	= $type;
	}

	/**
	 * Sets the access control levels.
	 *
	 * @access	public
	 * @param	string The ACO section (eg, the component).
	 * @param	string The ACO section value (if using a constant value).
	 * @return	void
	 * @since	1.5
	 */
	public function setAccessControl( $section, $value = null )
	{
		$this->_acoSection = $section;
		$this->_acoSectionValue = $value;
	}

	/**
	 * Method to load and return a model object.
	 *
	 * @access	private
	 * @param	string  The name of the model.
	 * @param	string	Optional model prefix.
	 * @param	array	Configuration array for the model. Optional.
	 * @return	mixed	Model object on success; otherwise null
	 * failure.
	 * @since	1.5
	 */
	private function &_createModel( $name, $prefix = '', $config = array())
	{
		$result = null;

		// Clean the model name
		$modelName	 = preg_replace( '/[^A-Z0-9_]/i', '', $name );
		$classPrefix = preg_replace( '/[^A-Z0-9_]/i', '', $prefix );

		$result =& JModel::getInstance($modelName, $classPrefix, $config);
		return $result;
	}

	/**
	 * Method to load and return a view object. This method first looks in the
	 * current template directory for a match, and failing that uses a default
	 * set path to load the view class file.
	 *
	 * Note the "name, prefix, type" order of parameters, which differs from the
	 * "name, type, prefix" order used in related public methods.
	 *
	 * @access	private
	 * @param	string	The name of the view.
	 * @param	string	Optional prefix for the view class name.
	 * @param	string	The type of view.
	 * @param	array	Configuration array for the view. Optional.
	 * @return	mixed	View object on success; null or error result on failure.
	 * @since	1.5
	 */
	private function &_createView( $name, $prefix = '', $type = '', $config = array() )
	{
		$result = null;

		// Clean the view name
		$viewName	 = preg_replace( '/[^A-Z0-9_]/i', '', $name );
		$classPrefix = preg_replace( '/[^A-Z0-9_]/i', '', $prefix );
		$viewType	 = preg_replace( '/[^A-Z0-9_]/i', '', $type );

		// Build the view class name
		$viewClass = $classPrefix . $viewName;

		if ( !class_exists( $viewClass ) )
		{
			jimport( 'joomla.filesystem.path' );
			$path = JPath::find(
				$this->_path['view'],
				$this->_createFileName( 'view', array( 'name' => $viewName, 'type' => $viewType) )
			);
			if ($path) {
				require_once $path;

				if ( !class_exists( $viewClass ) ) {
					$result = JError::raiseError(
						500, JText::_( 'View class not found [class, file]:' )
						. ' ' . $viewClass . ', ' . $path );
					return $result;
				}
			} else {
				return $result;
			}
		}

		$result = new $viewClass($config);
		return $result;
	}

	/**
	* Sets an entire array of search paths for resources.
	*
	* @access	protected
	* @param	string	The type of path to set, typically 'view' or 'model'.
	* @param	string|array	The new set of search paths. If null or false,
	* resets to the current directory only.
	*/
	protected function _setPath( $type, $path )
	{
		// clear out the prior search dirs
		$this->_path[$type] = array();

		// actually add the user-specified directories
		$this->_addPath( $type, $path );
	}

	/**
	* Adds to the search path for templates and resources.
	*
	* @access	protected
	* @param	string The path type (e.g. 'model', 'view'.
	* @param	string|array The directory or stream to search.
	* @return	void
	*/
	protected function _addPath( $type, $path )
	{
		// just force path to array
		settype( $path, 'array' );

		// loop through the path directories
		foreach ( $path as $dir )
		{
			// no surrounding spaces allowed!
			$dir = trim( $dir );

			// add trailing separators as needed
			if ( substr( $dir, -1 ) != DIRECTORY_SEPARATOR ) {
				// directory
				$dir .= DIRECTORY_SEPARATOR;
			}

			// add to the top of the search dirs
			array_unshift( $this->_path[$type], $dir );
		}
	}

	/**
	 * Create the filename for a resource.
	 *
	 * @access	private
	 * @param	string	The resource type to create the filename for.
	 * @param	array	An associative array of filename information. Optional.
	 * @return	string	The filename.
	 * @since	1.5
	 */
	private function _createFileName( $type, $parts = array() )
	{
		$filename = '';

		switch ( $type )
		{
			case 'view':
				if ( !empty( $parts['type'] ) ) {
					$parts['type'] = '.'.$parts['type'];
				}

				$filename = strtolower($parts['name']).DS.'view'.$parts['type'].'.php';
			break;
		}
		return $filename;
	}
}

