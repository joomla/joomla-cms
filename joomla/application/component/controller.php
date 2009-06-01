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
class JController extends JObject
{
	/**
	 * The base path of the controller
	 *
	 * @var		string
	 * @access 	protected
	 */
	var $_basePath = null;

	/**
	 * The name of the controller
	 *
	 * @var		array
	 * @access	protected
	 */
	var $_name = null;

	/**
	 * Array of class methods
	 *
	 * @var	array
	 * @access	protected
	 */
	var $_methods 	= null;

	/**
	 * Array of class methods to call for a given task.
	 *
	 * @var	array
	 * @access	protected
	 */
	var $_taskMap 	= null;

	/**
	 * Current or most recent task to be performed.
	 *
	 * @var	string
	 * @access	protected
	 */
	var $_task 		= null;

	/**
	 * The mapped task that was performed.
	 *
	 * @var	string
	 * @access	protected
	 */
	var $_doTask 	= null;

	/**
	 * The set of search directories for resources (views).
	 *
	 * @var array
	 * @access	protected
	 */
	var $_path = array(
		'view'	=> array()
	);

	/**
	 * URL for redirection.
	 *
	 * @var	string
	 * @access	protected
	 */
	var $_redirect 	= null;

	/**
	 * Redirect message.
	 *
	 * @var	string
	 * @access	protected
	 */
	var $_message 	= null;

	/**
	 * Redirect message type.
	 *
	 * @var	string
	 * @access	protected
	 */
	var $_messageType 	= null;

	/**
	 * ACO Section for the controller.
	 *
	 * @var	string
	 * @access	protected
	 */
	var $_acoSection 		= null;

	/**
	 * Default ACO Section value for the controller.
	 *
	 * @var	string
	 * @access	protected
	 */
	var $_acoSectionValue 	= null;

	/**
	 * Method to get a singleton controller instance.
	 *
	 * @param	string		$name		The prefix for the controller.
	 * @param	array		$config		An array of optional constructor options.
	 * @return	mixed		JController derivative class or JException on error.
	 * @since	1.6
	 */
	public static function &getInstance($prefix, $config = array())
	{
		static $instance;

		if (!empty($instance)) {
			return $instance;
		}

		// Get the environment configuration.
		$basePath	= array_key_exists('base_path', $config) ? $config['base_path'] : JPATH_COMPONENT;
		$protocol	= JRequest::getWord('protocol');
		$command	= JRequest::getCmd('task', 'display');

		// Check for a controller.task command.
		if (strpos($command, '.') !== false)
		{
			// Explode the controller.task command.
			list($type, $task) = explode('.', $command);

			// Define the controller filename and path.
			$file	= self::_createFileName('controller', array('name' => $type, 'protocol' => $protocol));
			$path	= $basePath.DS.'controllers'.DS.$file;

			// Reset the task without the contoller context.
			JRequest::setVar('task', $task);
		}
		else
		{
			// Base controller.
			$type	= null;
			$task	= $command;

			// Define the controller filename and path.
			$file	= self::_createFileName('controller', array('name' => 'controller', 'protocol' => $protocol));
			$path	= $basePath.DS.$file;
		}

		// Get the controller class name.
		$class = ucfirst($prefix).'Controller'.ucfirst($type);

		// Include the class if not present.
		if (!class_exists($class))
		{
			// If the controller file path exists, include it.
			if (file_exists($path)) {
				require_once $path;
			} else {
				throw new JException(JText::sprintf('INVALID CONTROLLER', $type), 1056, E_ERROR, $type, true);
			}
		}

		// Instantiate the class.
		if (class_exists($class)) {
			$instance = new $class($config);
		} else {
			throw new JException(JText::sprintf('INVALID CONTROLLER CLASS', $class), 1057, E_ERROR, $class, true);
		}

		return $instance;
	}


	/**
	 * Constructor.
	 *
	 * @access	protected
	 * @param	array An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'default_task', 'model_path', and
	 * 'view_path' (this list is not meant to be comprehensive).
	 * @since	1.5
	 */
	function __construct($config = array())
	{
		//Initialize private variables
		$this->_redirect	= null;
		$this->_message		= null;
		$this->_messageType = 'message';
		$this->_taskMap		= array();
		$this->_methods		= array();
		$this->_data		= array();

		// Get the methods only for the final controller class
		$thisMethods	= get_class_methods(get_class($this));
		$baseMethods	= get_class_methods('JController');
		$methods		= array_diff($thisMethods, $baseMethods);

		// Add default display method
		$methods[] = 'display';

		// Iterate through methods and map tasks
		foreach ($methods as $method)
		{
			if (substr($method, 0, 1) != '_') {
				$this->_methods[] = strtolower($method);
				// auto register public methods as tasks
				$this->_taskMap[strtolower($method)] = $method;
			}
		}

		//set the view name
		if (empty($this->_name))
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
		if (array_key_exists('default_task', $config)) {
			$this->registerDefaultTask($config['default_task']);
		} else {
			$this->registerDefaultTask('display');
		}

		// set the default model search path
		if (array_key_exists('model_path', $config)) {
			// user-defined dirs
			$this->addModelPath($config['model_path']);
		} else {
			$this->addModelPath($this->_basePath.DS.'models');
		}

		// set the default view search path
		if (array_key_exists('view_path', $config)) {
			// user-defined dirs
			$this->_setPath('view', $config['view_path']);
		} else {
			$this->_setPath('view', $this->_basePath.DS.'views');
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
	function execute($task)
	{
		$this->_task = $task;

		$task = strtolower($task);
		if (isset($this->_taskMap[$task])) {
			$doTask = $this->_taskMap[$task];
		} elseif (isset($this->_taskMap['__default'])) {
			$doTask = $this->_taskMap['__default'];
		} else {
			return JError::raiseError(404, JText::_('Task ['.$task.'] not found'));
		}

		// Record the actual task being fired
		$this->_doTask = $doTask;

		// Make sure we have access
		if ($this->authorize($doTask))
		{
			$retval = $this->$doTask();
			return $retval;
		}
		else
		{
			return JError::raiseError(403, JText::_('Access Forbidden'));
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
	function authorize($task)
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
			return $user->authorize($this->_acoSection, $task);
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
	function display($cachable=false)
	{
		$document = &JFactory::getDocument();

		$viewType	= $document->getType();
		$viewName	= JRequest::getCmd('view', $this->getName());
		$viewLayout	= JRequest::getCmd('layout', 'default');

		$view = & $this->getView($viewName, $viewType, '', array('base_path'=>$this->_basePath));

		// Get/Create the model
		if ($model = & $this->getModel($viewName)) {
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		// Set the layout
		$view->setLayout($viewLayout);

		// Display the view
		if ($cachable && $viewType != 'feed') {
			global $option;
			$cache = &JFactory::getCache($option, 'view');
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
	function redirect()
	{
		if ($this->_redirect) {
			global $mainframe;
			$mainframe->redirect($this->_redirect, $this->_message, $this->_messageType);
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
	function &getModel($name = '', $prefix = '', $config = array())
	{
		if (empty($name)) {
			$name = $this->getName();
		}

		if (empty($prefix)) {
			$prefix = $this->getName() . 'Model';
		}

		if ($model = & $this->_createModel($name, $prefix, $config))
		{
			// task is a reserved state
			$model->setState('task', $this->_task);

			// Lets get the application object and set menu information if its available
			$app	= &JFactory::getApplication();
			$menu	= &$app->getMenu();
			if (is_object($menu))
			{
				if ($item = $menu->getActive())
				{
					$params	= &$menu->getParams($item->id);
					// Set Default State Data
					$model->setState('parameters.menu', $params);
				}
			}
		}
		return $model;
	}

	/**
	 * Adds to the stack of model paths in LIFO order.
	 *
	 * @static
	 * @param	string|array The directory (string), or list of directories
	 *                       (array) to add.
	 * @return	void
	 */
	function addModelPath($path)
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
	function getTasks()
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
	function getTask()
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
	function getName()
	{
		$name = $this->_name;

		if (empty($name))
		{
			$r = null;
			if (!preg_match('/(.*)Controller/i', get_class($this), $r)) {
				JError::raiseError(500, "JController::getName() : Cannot get or parse class name.");
			}
			$name = strtolower($r[1]);
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
	function &getView($name = '', $type = '', $prefix = '', $config = array())
	{
		static $views;

		if (!isset($views)) {
			$views = array();
		}

		if (empty($name)) {
			$name = $this->getName();
		}

		if (empty($prefix)) {
			$prefix = $this->getName() . 'View';
		}

		if (empty($views[$name]))
		{
			if ($view = & $this->_createView($name, $prefix, $type, $config)) {
				$views[$name] = & $view;
			} else {
				$result = JError::raiseError(
					500, JText::_('View not found [name, type, prefix]:')
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
	function addViewPath($path)
	{
		$this->_addPath('view', $path);
	}

	/**
	 * Register (map) a task to a method in the class.
	 *
	 * @access	public
	 * @param	string	The task.
	 * @param	string	The name of the method in the derived class to perform
	 *                  for this task.
	 * @return	void
	 * @since	1.5
	 */
	function registerTask($task, $method)
	{
		if (in_array(strtolower($method), $this->_methods)) {
			$this->_taskMap[strtolower($task)] = $method;
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
	function registerDefaultTask($method)
	{
		$this->registerTask('__default', $method);
	}

	/**
	 * Sets the internal message that is passed with a redirect
	 *
	 * @access	public
	 * @param	string	The message
	 * @return	string	Previous message
	 * @since	1.5
	 */
	function setMessage($text)
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
	function setRedirect($url, $msg = null, $type = 'message')
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
	function setAccessControl($section, $value = null)
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
	function &_createModel($name, $prefix = '', $config = array())
	{
		$result = null;

		// Clean the model name
		$modelName	 = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$classPrefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

		$result = &JModel::getInstance($modelName, $classPrefix, $config);
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
	function &_createView($name, $prefix = '', $type = '', $config = array())
	{
		$result = null;

		// Clean the view name
		$viewName	 = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$classPrefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);
		$viewType	 = preg_replace('/[^A-Z0-9_]/i', '', $type);

		// Build the view class name
		$viewClass = $classPrefix . $viewName;

		if (!class_exists($viewClass))
		{
			jimport('joomla.filesystem.path');
			$path = JPath::find(
				$this->_path['view'],
				$this->_createFileName('view', array('name' => $viewName, 'type' => $viewType))
			);
			if ($path) {
				require_once $path;

				if (!class_exists($viewClass)) {
					$result = JError::raiseError(
						500, JText::_('View class not found [class, file]:')
						. ' ' . $viewClass . ', ' . $path);
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
	function _setPath($type, $path)
	{
		// clear out the prior search dirs
		$this->_path[$type] = array();

		// actually add the user-specified directories
		$this->_addPath($type, $path);
	}

	/**
	* Adds to the search path for templates and resources.
	*
	* @access	protected
	* @param	string The path type (e.g. 'model', 'view'.
	* @param	string|array The directory or stream to search.
	* @return	void
	*/
	function _addPath($type, $path)
	{
		// just force path to array
		settype($path, 'array');

		// loop through the path directories
		foreach ($path as $dir)
		{
			// no surrounding spaces allowed!
			$dir = trim($dir);

			// add trailing separators as needed
			if (substr($dir, -1) != DIRECTORY_SEPARATOR) {
				// directory
				$dir .= DIRECTORY_SEPARATOR;
			}

			// add to the top of the search dirs
			array_unshift($this->_path[$type], $dir);
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
	function _createFileName($type, $parts = array())
	{
		$filename = '';

		switch ($type)
		{
			case 'controller':
				if (!empty($parts['protocol'])) {
					$parts['protocol'] = '.'.$parts['protocol'];
				}

				$filename = strtolower($parts['name']).$parts['protocol'].'.php';
				break;

			case 'view':
				if (!empty($parts['type'])) {
					$parts['type'] = '.'.$parts['type'];
				}

				$filename = strtolower($parts['name']).DS.'view'.$parts['type'].'.php';
			break;
		}
		return $filename;
	}
}