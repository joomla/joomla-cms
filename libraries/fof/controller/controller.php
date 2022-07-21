<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  controller
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @note        This file has been modified by the Joomla! Project and no longer reflects the original work of its author.
 */

// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * FrameworkOnFramework controller class. FOF is based on the thin controller
 * paradigm, where the controller is mainly used to set up the model state and
 * spawn the view.
 *
 * @package  FrameworkOnFramework
 * @since    1.0
 */
class FOFController extends FOFUtilsObject
{
	/**
	 * @var int Bit mask to enable Routing on redirects.
	 * 0 = never
	 * 1 = frontend only
	 * 2 = backend  only
	 * 3 = always
	 */
	protected $autoRouting = 0;

	/**
	 * The current component's name without the com_ prefix
	 *
	 * @var    string
	 */
	protected $bareComponent = 'foobar';

	/**
	 * The base path of the controller
	 *
	 * @var    string
	 */
	protected $basePath;

	/**
	 * The tasks for which caching should be enabled by default
	 *
	 * @var array
	 */
	protected $cacheableTasks = array('browse', 'read');

	/**
	 * The current component's name; you can override it in the configuration
	 *
	 * @var    string
	 */
	protected $component = 'com_foobar';

	/**
	 * A cached copy of the class configuration parameter passed during initialisation
	 *
	 * @var    array
	 */
	protected $config = array();

	/**
	 * An instance of FOFConfigProvider to provision configuration overrides
	 *
	 * @var    FOFConfigProvider
	 */
	protected $configProvider = null;

	/**
	 * Set to true to enable CSRF protection on selected tasks. The possible
	 * values are:
	 * 0	Disabled; no token checks are performed
	 * 1	Enabled; token checks are always performed
	 * 2	Only on HTML requests and backend; token checks are always performed in the back-end and in the front-end only when format is 'html'
	 * 3	Only on back-end; token checks are performer only in the back-end
	 *
	 * @var    integer
	 */
	protected $csrfProtection = 2;

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 */
	protected $default_view;

	/**
	 * The mapped task that was performed.
	 *
	 * @var    string
	 */
	protected $doTask;

	/**
	 * The input object for this MVC triad; you can override it in the configuration
	 *
	 * @var    FOFInput
	 */
	protected $input = array();

	/**
	 * Redirect message.
	 *
	 * @var    string
	 */
	protected $message;

	/**
	 * Redirect message type.
	 *
	 * @var    string
	 */
	protected $messageType;

	/**
	 * The current layout; you can override it in the configuration
	 *
	 * @var    string
	 */
	protected $layout = null;

	/**
	 * Array of class methods
	 *
	 * @var    array
	 */
	protected $methods;

	/**
	 * The prefix of the models
	 *
	 * @var    string
	 */
	protected $model_prefix;

	/**
	 * Overrides the name of the view's default model
	 *
	 * @var    string
	 */
	protected $modelName = null;

	/**
	 * The set of search directories for resources (views).
	 *
	 * @var    array
	 */
	protected $paths;

	/**
	 * URL for redirection.
	 *
	 * @var    string
	 */
	protected $redirect;

	/**
	 * Current or most recently performed task.
	 *
	 * @var    string
	 */
	protected $task;

	/**
	 * Array of class methods to call for a given task.
	 *
	 * @var    array
	 */
	protected $taskMap;

	/**
	 * The name of the controller
	 *
	 * @var    array
	 */
	protected $name;

	/**
	 * The current view name; you can override it in the configuration
	 *
	 * @var    string
	 */
	protected $view = '';

	/**
	 * Overrides the name of the view's default view
	 *
	 * @var    string
	 */
	protected $viewName = null;

	/**
	 * A copy of the FOFView object used in this triad
	 *
	 * @var    FOFView
	 */
	private $_viewObject = null;

	/**
	 * A cache for the view item objects created in this controller
	 *
	 * @var   array
	 */
	protected $viewsCache = array();

	/**
	 * A copy of the FOFModel object used in this triad
	 *
	 * @var    FOFModel
	 */
	private $_modelObject = null;

	/**
	 * Does this tried have a FOFForm which will be used to render it?
	 *
	 * @var    boolean
	 */
	protected $hasForm = false;

	/**
	 * Gets a static (Singleton) instance of a controller class. It loads the
	 * relevant controller file from the component's directory or, if it doesn't
	 * exist, creates a new controller object out of thin air.
	 *
	 * @param   string  $option  Component name, e.g. com_foobar
	 * @param   string  $view    The view name, also used for the controller name
	 * @param   array   $config  Configuration parameters
	 *
	 * @return  FOFController
	 */
	public static function &getAnInstance($option = null, $view = null, $config = array())
	{
		static $instances = array();

		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array) $config;
		}
		elseif (!is_array($config))
		{
			$config = array();
		}

		$hash = $option . $view;

		if (!array_key_exists($hash, $instances))
		{
			$instances[$hash] = self::getTmpInstance($option, $view, $config);
		}

		return $instances[$hash];
	}

	/**
	 * Gets a temporary instance of a controller object. A temporary instance is
	 * not a Singleton and can be disposed off after use.
	 *
	 * @param   string  $option  The component name, e.g. com_foobar
	 * @param   string  $view    The view name, e.g. cpanel
	 * @param   array   $config  Configuration parameters
	 *
	 * @return  \FOFController  A disposable class instance
	 */
	public static function &getTmpInstance($option = null, $view = null, $config = array())
	{
		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array) $config;
		}
		elseif (!is_array($config))
		{
			$config = array();
		}

		// Get an input object
		if (array_key_exists('input', $config))
		{
			$input = $config['input'];
		}
		else
		{
			$input = null;
		}

		if (array_key_exists('input_options', $config))
		{
			$input_options = $config['input_options'];
		}
		else
		{
			$input_options = array();
		}

		if (!($input instanceof FOFInput))
		{
			$input = new FOFInput($input, $input_options);
		}

		// Determine the option (component name) and view
		$config['option'] = !is_null($option) ? $option : $input->getCmd('option', 'com_foobar');
		$config['view'] = !is_null($view) ? $view : $input->getCmd('view', 'cpanel');

		// Get the class base name, e.g. FoobarController
		$classBaseName = ucfirst(str_replace('com_', '', $config['option'])) . 'Controller';

		// Get the class name suffixes, in the order to be searched for: plural, singular, 'default'
		$classSuffixes = array(
			FOFInflector::pluralize($config['view']),
			FOFInflector::singularize($config['view']),
			'default'
		);

		// Get the path names for the component
		$componentPaths = FOFPlatform::getInstance()->getComponentBaseDirs($config['option']);
        $filesystem     = FOFPlatform::getInstance()->getIntegrationObject('filesystem');

		// Look for the best classname match
		foreach ($classSuffixes as $suffix)
		{
			$className = $classBaseName . ucfirst($suffix);

			if (class_exists($className))
			{
				// The class is already loaded. We have a match!
				break;
			}

			// The class is not already loaded. Try to find and load it.
			$searchPaths = array(
				$componentPaths['main'] . '/controllers',
				$componentPaths['admin'] . '/controllers'
			);

			// If we have a searchpath in the configuration please search it first

			if (array_key_exists('searchpath', $config))
			{
				array_unshift($searchPaths, $config['searchpath']);
			}
			else
			{
				$configProvider = new FOFConfigProvider;
				$searchPath = $configProvider->get($config['option'] . '.views.' . FOFInflector::singularize($config['view']) . '.config.searchpath', null);

				if ($searchPath)
				{
					array_unshift($searchPaths, $componentPaths['admin'] . '/' . $searchPath);
					array_unshift($searchPaths, $componentPaths['main'] . '/' . $searchPath);
				}
			}

			/**
			 * Try to find the path to this file. First try to find the
			 * format-specific controller file, e.g. foobar.json.php for
			 * format=json, then the regular one-size-fits-all controller
			 */

			$format = $input->getCmd('format', 'html');
			$path = null;

			if (!empty($format))
			{
				$path = $filesystem->pathFind(
					$searchPaths, strtolower($suffix) . '.' . strtolower($format) . '.php'
				);
			}

			if (!$path)
			{
				$path = $filesystem->pathFind(
						$searchPaths, strtolower($suffix) . '.php'
				);
			}

			// The path is found. Load the file and make sure the expected class name exists.

			if ($path)
			{
				require_once $path;

				if (class_exists($className))
				{
					// The class was loaded successfully. We have a match!
					break;
				}
			}
		}

		if (!class_exists($className))
		{
			// If no specialised class is found, instantiate the generic FOFController
			$className = 'FOFController';
		}

		$instance = new $className($config);

		return $instance;
	}

	/**
	 * Public constructor of the Controller class
	 *
	 * @param   array  $config  Optional configuration parameters
	 */
	public function __construct($config = array())
	{
		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array) $config;
		}
		elseif (!is_array($config))
		{
			$config = array();
		}

		$this->methods = array();
		$this->message = null;
		$this->messageType = 'message';
		$this->paths = array();
		$this->redirect = null;
		$this->taskMap = array();

		// Cache the config
		$this->config = $config;

		// Get the input for this MVC triad

		if (array_key_exists('input', $config))
		{
			$input = $config['input'];
		}
		else
		{
			$input = null;
		}

		if (array_key_exists('input_options', $config))
		{
			$input_options = $config['input_options'];
		}
		else
		{
			$input_options = array();
		}

		if ($input instanceof FOFInput)
		{
			$this->input = $input;
		}
		else
		{
			$this->input = new FOFInput($input, $input_options);
		}

		// Load the configuration provider
		$this->configProvider = new FOFConfigProvider;

		// Determine the methods to exclude from the base class.
		$xMethods = get_class_methods('FOFController');

		// Some methods must always be considered valid tasks
		$iMethods = array('accesspublic', 'accessregistered', 'accessspecial',
			'add', 'apply', 'browse', 'cancel', 'copy', 'edit', 'orderdown',
			'orderup', 'publish', 'read', 'remove', 'save', 'savenew',
			'saveorder', 'unpublish', 'display', 'archive', 'trash', 'loadhistory');

		// Get the public methods in this class using reflection.
		$r = new ReflectionClass($this);
		$rMethods = $r->getMethods(ReflectionMethod::IS_PUBLIC);

		foreach ($rMethods as $rMethod)
		{
			$mName = $rMethod->getName();

			// If the developer screwed up and declared one of the helper method public do NOT make them available as
			// tasks.
			if ((substr($mName, 0, 8) == 'onBefore') || (substr($mName, 0, 7) == 'onAfter') || substr($mName, 0, 1) == '_')
			{
				continue;
			}

			// Add default display method if not explicitly declared.
			if (!in_array($mName, $xMethods) || in_array($mName, $iMethods))
			{
				$this->methods[] = strtolower($mName);

				// Auto register the methods as tasks.
				$this->taskMap[strtolower($mName)] = $mName;
			}
		}

		// Get the default values for the component and view names
		$classNameParts = FOFInflector::explode(get_class($this));

		if (count($classNameParts) == 3)
		{
			$defComponent = "com_" . $classNameParts[0];
			$defView = $classNameParts[2];
		}
		else
		{
			$defComponent = 'com_foobar';
			$defView = 'cpanel';
		}

		$this->component = $this->input->get('option', $defComponent, 'cmd');
		$this->view = $this->input->get('view', $defView, 'cmd');
		$this->layout = $this->input->get('layout', null, 'cmd');

		// Overrides from the config
		if (array_key_exists('option', $config))
		{
			$this->component = $config['option'];
		}

		if (array_key_exists('view', $config))
		{
			$this->view = $config['view'];
		}

		if (array_key_exists('layout', $config))
		{
			$this->layout = $config['layout'];
		}

		$this->layout = $this->configProvider->get($this->component . '.views.' . FOFInflector::singularize($this->view) . '.config.layout', $this->layout);

		$this->input->set('option', $this->component);

		// Set the bareComponent variable
		$this->bareComponent = str_replace('com_', '', strtolower($this->component));

		// Set the $name variable
		$this->name = $this->bareComponent;

		// Set the basePath variable
		$componentPaths = FOFPlatform::getInstance()->getComponentBaseDirs($this->component);
		$basePath = $componentPaths['main'];

		if (array_key_exists('base_path', $config))
		{
			$basePath = $config['base_path'];
		}

		$altBasePath = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.config.base_path', null
		);

		if (!is_null($altBasePath))
		{
            $platformDirs = FOFPlatform::getInstance()->getPlatformBaseDirs();
			$basePath     = $platformDirs['public'] . '/' . $altBasePath;
		}

		$this->basePath = $basePath;

		// If the default task is set, register it as such
		$defaultTask = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.config.default_task', 'display'
		);

		if (array_key_exists('default_task', $config))
		{
			$this->registerDefaultTask($config['default_task']);
		}
		else
		{
			$this->registerDefaultTask($defaultTask);
		}

		// Set the models prefix

		if (empty($this->model_prefix))
		{
			if (array_key_exists('model_prefix', $config))
			{
				// User-defined prefix
				$this->model_prefix = $config['model_prefix'];
			}
			else
			{
				$this->model_prefix = $this->name . 'Model';
				$this->model_prefix = $this->configProvider->get(
					$this->component . '.views.' .
					FOFInflector::singularize($this->view) . '.config.model_prefix', $this->model_prefix
				);
			}
		}

		// Set the default model search path

		if (array_key_exists('model_path', $config))
		{
			// User-defined dirs
			$this->addModelPath($config['model_path'], $this->model_prefix);
		}
		else
		{
			$modelPath = $this->basePath . '/models';
			$altModelPath = $this->configProvider->get(
				$this->component . '.views.' .
				FOFInflector::singularize($this->view) . '.config.model_path', null
			);

			if (!is_null($altModelPath))
			{
				$modelPath = $this->basePath . '/' . $altModelPath;
			}

			$this->addModelPath($modelPath, $this->model_prefix);
		}

		// Set the default view search path
		if (array_key_exists('view_path', $config))
		{
			// User-defined dirs
			$this->setPath('view', $config['view_path']);
		}
		else
		{
			$viewPath = $this->basePath . '/views';
			$altViewPath = $this->configProvider->get(
				$this->component . '.views.' .
				FOFInflector::singularize($this->view) . '.config.view_path', null
			);

			if (!is_null($altViewPath))
			{
				$viewPath = $this->basePath . '/' . $altViewPath;
			}

			$this->setPath('view', $viewPath);
		}

		// Set the default view.

		if (array_key_exists('default_view', $config))
		{
			$this->default_view = $config['default_view'];
		}
		else
		{
			if (empty($this->default_view))
			{
				$this->default_view = $this->getName();
			}

			$this->default_view = $this->configProvider->get(
				$this->component . '.views.' .
				FOFInflector::singularize($this->view) . '.config.default_view', $this->default_view
			);
		}

		// Set the CSRF protection
		if (array_key_exists('csrf_protection', $config))
		{
			$this->csrfProtection = $config['csrf_protection'];
		}

		$this->csrfProtection = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.config.csrf_protection', $this->csrfProtection
		);

		// Set any model/view name overrides
		if (array_key_exists('viewName', $config))
		{
			$this->setThisViewName($config['viewName']);
		}
		else
		{
			$overrideViewName = $this->configProvider->get(
				$this->component . '.views.' .
				FOFInflector::singularize($this->view) . '.config.viewName', null
			);

			if ($overrideViewName)
			{
				$this->setThisViewName($overrideViewName);
			}
		}

		if (array_key_exists('modelName', $config))
		{
			$this->setThisModelName($config['modelName']);
		}
		else
		{
			$overrideModelName = $this->configProvider->get(
				$this->component . '.views.' .
				FOFInflector::singularize($this->view) . '.config.modelName', null
			);

			if ($overrideModelName)
			{
				$this->setThisModelName($overrideModelName);
			}
		}

		// Caching
		if (array_key_exists('cacheableTasks', $config))
		{
			if (is_array($config['cacheableTasks']))
			{
				$this->cacheableTasks = $config['cacheableTasks'];
			}
		}
		else
		{
			$cacheableTasks = $this->configProvider->get(
				$this->component . '.views.' .
				FOFInflector::singularize($this->view) . '.config.cacheableTasks', null
			);

			if ($cacheableTasks)
			{
				$cacheableTasks = explode(',', $cacheableTasks);

				if (count($cacheableTasks))
				{
					$temp = array();

					foreach ($cacheableTasks as $t)
					{
						$temp[] = trim($t);
					}

					$temp = array_unique($temp);
					$this->cacheableTasks = $temp;
				}
			}
		}

		// Bit mask for auto routing on setRedirect
		$this->autoRouting = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.config.autoRouting', $this->autoRouting
		);

		if (array_key_exists('autoRouting', $config))
		{
			$this->autoRouting = $config['autoRouting'];
		}

		// Apply task map
		$taskmap = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.taskmap'
		);

		if (is_array($taskmap) && !empty($taskmap))
		{
			foreach ($taskmap as $aliasedtask => $realmethod)
			{
				$this->registerTask($aliasedtask, $realmethod);
			}
		}
	}

	/**
	 * Adds to the stack of model paths in LIFO order.
	 *
	 * @param   mixed   $path    The directory (string) , or list of directories (array) to add.
	 * @param   string  $prefix  A prefix for models
	 *
	 * @return  void
	 */
	public static function addModelPath($path, $prefix = '')
	{
		FOFModel::addIncludePath($path, $prefix);
	}

	/**
	 * Adds to the search path for templates and resources.
	 *
	 * @param   string  $type  The path type (e.g. 'model', 'view').
	 * @param   mixed   $path  The directory string  or stream array to search.
	 *
	 * @return  FOFController  A FOFController object to support chaining.
	 */
	protected function addPath($type, $path)
	{
		// Just force path to array
		settype($path, 'array');

        $filesystem = FOFPlatform::getInstance()->getIntegrationObject('filesystem');

		if (!isset($this->paths[$type]))
		{
			$this->paths[$type] = array();
		}

		// Loop through the path directories
		foreach ($path as $dir)
		{
			// No surrounding spaces allowed!
			$dir = rtrim($filesystem->pathCheck($dir, '/'), '/') . '/';

			// Add to the top of the search dirs
			array_unshift($this->paths[$type], $dir);
		}

		return $this;
	}

	/**
	 * Add one or more view paths to the controller's stack, in LIFO order.
	 *
	 * @param   mixed  $path  The directory (string) or list of directories (array) to add.
	 *
	 * @return  FOFController  This object to support chaining.
	 */
	public function addViewPath($path)
	{
		$this->addPath('view', $path);

		return $this;
	}

	/**
	 * Authorisation check
	 *
	 * @param   string  $task  The ACO Section Value to check access on.
	 *
	 * @return  boolean  True if authorised
	 *
	 * @deprecated  2.0  Use JAccess instead.
	 */
	public function authorise($task)
	{
		FOFPlatform::getInstance()->logDeprecated(__CLASS__ . '::' .__METHOD__ . ' is deprecated. Use checkACL() instead.');

		return true;
	}

	/**
	 * Create the filename for a resource.
	 *
	 * @param   string  $type   The resource type to create the filename for.
	 * @param   array   $parts  An associative array of filename information. Optional.
	 *
	 * @return  string  The filename.
	 */
	protected static function createFileName($type, $parts = array())
	{
		$filename = '';

		switch ($type)
		{
			case 'controller':
				if (!empty($parts['format']))
				{
					if ($parts['format'] == 'html')
					{
						$parts['format'] = '';
					}
					else
					{
						$parts['format'] = '.' . $parts['format'];
					}
				}
				else
				{
					$parts['format'] = '';
				}

				$filename = strtolower($parts['name'] . $parts['format'] . '.php');
				break;

			case 'view':
				if (!empty($parts['type']))
				{
					$parts['type'] = '.' . $parts['type'];
				}
				else
				{
					$parts['type'] = '';
				}

				$filename = strtolower($parts['name'] . '/view' . $parts['type'] . '.php');
				break;
		}

		return $filename;
	}

    /**
     * Executes a given controller task. The onBefore<task> and onAfter<task>
     * methods are called automatically if they exist.
     *
     * @param   string $task The task to execute, e.g. "browse"
     *
     * @throws  Exception   Exception thrown if the onBefore<task> returns false
     *
     * @return  null|bool  False on execution failure
     */
	public function execute($task)
	{
		$this->task = $task;

		$method_name = 'onBefore' . ucfirst($task);

		if (!method_exists($this, $method_name))
		{
			$result = $this->onBeforeGenericTask($task);
		}
		elseif (method_exists($this, $method_name))
		{
			$result = $this->$method_name();
		}
		else
		{
			$result = true;
		}

		if ($result)
		{
			$plugin_event  = FOFInflector::camelize('on before ' . $this->bareComponent . ' controller ' . $this->view . ' ' . $task);
			$plugin_result = FOFPlatform::getInstance()->runPlugins($plugin_event, array(&$this, &$this->input));

			if (in_array(false, $plugin_result, true))
			{
				$result = false;
			}
		}

		if (!$result)
		{
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}

		// Do not allow the display task to be directly called
		$task = strtolower($task);

		if (isset($this->taskMap[$task]))
		{
			$doTask = $this->taskMap[$task];
		}
		elseif (isset($this->taskMap['__default']))
		{
			$doTask = $this->taskMap['__default'];
		}
		else
		{
			$doTask = null;
		}

		if ($doTask == 'display')
		{
            FOFPlatform::getInstance()->setHeader('Status', '400 Bad Request', true);

			throw new Exception('Bad Request', 400);
		}

		$this->doTask = $doTask;

		$ret = $this->$doTask();

		$method_name = 'onAfter' . ucfirst($task);

		if (method_exists($this, $method_name))
		{
			$result = $this->$method_name();
		}
		else
		{
			$result = true;
		}

		if ($result)
		{
			$plugin_event = FOFInflector::camelize('on after ' . $this->bareComponent . ' controller ' . $this->view . ' ' . $task);
			$plugin_result = FOFPlatform::getInstance()->runPlugins($plugin_event, array(&$this, &$this->input, &$ret));

			if (in_array(false, $plugin_result, true))
			{
				$result = false;
			}
		}

		if (!$result)
		{
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}

		return $ret;
	}

	/**
	 * Default task. Assigns a model to the view and asks the view to render
	 * itself.
	 *
	 * YOU MUST NOT USETHIS TASK DIRECTLY IN A URL. It is supposed to be
	 * used ONLY inside your code. In the URL, use task=browse instead.
	 *
	 * @param   bool    $cachable   Is this view cacheable?
	 * @param   bool    $urlparams  Add your safe URL parameters (see further down in the code)
	 * @param   string  $tpl        The name of the template file to parse
	 *
	 * @return  bool
	 */
	public function display($cachable = false, $urlparams = false, $tpl = null)
	{
		$document = FOFPlatform::getInstance()->getDocument();

		if ($document instanceof JDocument)
		{
			$viewType = $document->getType();
		}
		else
		{
			$viewType = $this->input->getCmd('format', 'html');
		}

		$view = $this->getThisView();

		// Get/Create the model

		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		// Set the layout
		$view->setLayout(is_null($this->layout) ? 'default' : $this->layout);

		// Display the view
		$conf = FOFPlatform::getInstance()->getConfig();

		if (FOFPlatform::getInstance()->isFrontend() && $cachable && ($viewType != 'feed') && $conf->get('caching') >= 1)
		{
			// Get a JCache object
			$option = $this->input->get('option', 'com_foobar', 'cmd');
			$cache = JFactory::getCache($option, 'view');

			// Set up a cache ID based on component, view, task and user group assignment
			$user = FOFPlatform::getInstance()->getUser();

			if ($user->guest)
			{
				$groups = array();
			}
			else
			{
				$groups = $user->groups;
			}

			$importantParameters = array();

			// Set up safe URL parameters
			if (!is_array($urlparams))
			{
				$urlparams = array(
					'option'		=> 'CMD',
					'view'			=> 'CMD',
					'task'			=> 'CMD',
					'format'		=> 'CMD',
					'layout'		=> 'CMD',
					'id'			=> 'INT',
				);
			}

			if (is_array($urlparams))
			{
				$app = JFactory::getApplication();

				$registeredurlparams = null;

				if (version_compare(JVERSION, '3.0', 'ge'))
				{
					if (property_exists($app, 'registeredurlparams'))
					{
						$registeredurlparams = $app->registeredurlparams;
					}
				}
				else
				{
					$registeredurlparams = $app->get('registeredurlparams');
				}

				if (empty($registeredurlparams))
				{
					$registeredurlparams = new stdClass;
				}

				foreach ($urlparams AS $key => $value)
				{
					// Add your safe url parameters with variable type as value {@see JFilterInput::clean()}.
					$registeredurlparams->$key = $value;

					// Add the URL-important parameters into the array
					$importantParameters[$key] = $this->input->get($key, null, $value);
				}

				if (version_compare(JVERSION, '3.0', 'ge'))
				{
					$app->registeredurlparams = $registeredurlparams;
				}
				else
				{
					$app->set('registeredurlparams', $registeredurlparams);
				}
			}

			// Create the cache ID after setting the registered URL params, as they are used to generate the ID
			$cacheId = md5(serialize(array(JCache::makeId(), $view->getName(), $this->doTask, $groups, $importantParameters)));

			// Get the cached view or cache the current view
			$cache->get($view, 'display', $cacheId);
		}
		else
		{
			// Display without caching
			$view->display($tpl);
		}

		return true;
	}

	/**
	 * Implements a default browse task, i.e. read a bunch of records and send
	 * them to the browser.
	 *
	 * @return  boolean
	 */
	public function browse()
	{
		if ($this->input->get('savestate', -999, 'int') == -999)
		{
			$this->input->set('savestate', true);
		}

		// Do I have a form?
		$model = $this->getThisModel();

		if (empty($this->layout))
		{
			$formname = 'form.default';
		}
		else
		{
			$formname = 'form.' . $this->layout;
		}

		$model->setState('form_name', $formname);

		$form = $model->getForm();

		if ($form !== false)
		{
			$this->hasForm = true;
		}

		$this->display(in_array('browse', $this->cacheableTasks));

		return true;
	}

	/**
	 * Single record read. The id set in the request is passed to the model and
	 * then the item layout is used to render the result.
	 *
	 * @return  bool
	 */
	public function read()
	{
		// Load the model
		$model = $this->getThisModel();

		if (!$model->getId())
		{
			$model->setIDsFromRequest();
		}

		// Set the layout to item, if it's not set in the URL
		if (is_null($this->layout))
		{
			$this->layout = 'item';
		}

		// Do I have a form?
		$model->setState('form_name', 'form.' . $this->layout);

		$item = $model->getItem();

		if (!($item instanceof FOFTable))
		{
			return false;
		}

		$itemKey = $item->getKeyName();

		if ($item->$itemKey != $model->getId())
		{
			return false;
		}

		$formData = is_object($item) ? $item->getData() : array();
		$form = $model->getForm($formData);

		if ($form !== false)
		{
			$this->hasForm = true;
		}

		// Display
		$this->display(in_array('read', $this->cacheableTasks));

		return true;
	}

	/**
	 * Single record add. The form layout is used to present a blank page.
	 *
	 * @return  false|void
	 */
	public function add()
	{
		// Load and reset the model
		$model = $this->getThisModel();
		$model->reset();

		// Set the layout to form, if it's not set in the URL

		if (!$this->layout)
		{
			$this->layout = 'form';
		}

		// Do I have a form?
		$model->setState('form_name', 'form.' . $this->layout);

		$item = $model->getItem();

		if (!($item instanceof FOFTable))
		{
			return false;
		}

		$formData = is_object($item) ? $item->getData() : array();
		$form = $model->getForm($formData);

		if ($form !== false)
		{
			$this->hasForm = true;
		}

		// Display
		$this->display(in_array('add', $this->cacheableTasks));
	}

	/**
	 * Single record edit. The ID set in the request is passed to the model,
	 * then the form layout is used to edit the result.
	 *
	 * @return  bool
	 */
	public function edit()
	{
		// Load the model
		$model = $this->getThisModel();

		if (!$model->getId())
		{
			$model->setIDsFromRequest();
		}

		$status = $model->checkout();

		if (!$status)
		{
			// Redirect on error

			if ($customURL = $this->input->get('returnurl', '', 'string'))
			{
				$customURL = base64_decode($customURL);
			}

			$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->component . '&view=' . FOFInflector::pluralize($this->view) . $this->getItemidURLSuffix();
			$this->setRedirect($url, $model->getError(), 'error');

			return false;
		}

		// Set the layout to form, if it's not set in the URL

		if (is_null($this->layout))
		{
			$this->layout = 'form';
		}

		// Do I have a form?
		$model->setState('form_name', 'form.' . $this->layout);

		$item = $model->getItem();

		if (!($item instanceof FOFTable))
		{
			return false;
		}

		$itemKey = $item->getKeyName();

		if ($item->$itemKey != $model->getId())
		{
			return false;
		}

		$formData = is_object($item) ? $item->getData() : array();
		$form = $model->getForm($formData);

		if ($form !== false)
		{
			$this->hasForm = true;
		}

		// Display
		$this->display(in_array('edit', $this->cacheableTasks));

		return true;
	}

	/**
	 * Save the incoming data and then return to the Edit task
	 *
	 * @return  bool
	 */
	public function apply()
	{
		// CSRF prevention
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		$model = $this->getThisModel();
		$result = $this->applySave();

		// Redirect to the edit task
		if ($result)
		{
			$id = $this->input->get('id', 0, 'int');
			$textkey = strtoupper($this->component) . '_LBL_' . strtoupper($this->view) . '_SAVED';

			if ($customURL = $this->input->get('returnurl', '', 'string'))
			{
				$customURL = base64_decode($customURL);
			}

			$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->component . '&view=' . $this->view . '&task=edit&id=' . $id . $this->getItemidURLSuffix();
			$this->setRedirect($url, JText::_($textkey));
		}

		return $result;
	}

	/**
	 * Duplicates selected items
	 *
	 * @return  bool
	 */
	public function copy()
	{
		// CSRF prevention
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		$model = $this->getThisModel();

		if (!$model->getId())
		{
			$model->setIDsFromRequest();
		}

		$status = $model->copy();

		// Redirect
		if ($customURL = $this->input->get('returnurl', '', 'string'))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->component . '&view=' . FOFInflector::pluralize($this->view) . $this->getItemidURLSuffix();

		if (!$status)
		{
			$this->setRedirect($url, $model->getError(), 'error');

			return false;
		}
		else
		{
			if(!FOFPlatform::getInstance()->isCli())
			{
				FOFPlatform::getInstance()->setHeader('Status', '201 Created', true);
			}

			$this->setRedirect($url);

			return true;
		}
	}

	/**
	 * Save the incoming data and then return to the Browse task
	 *
	 * @return  bool
	 */
	public function save()
	{
		// CSRF prevention
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		$result = $this->applySave();

		// Redirect to the display task
		if ($result)
		{
			$textkey = strtoupper($this->component) . '_LBL_' . strtoupper($this->view) . '_SAVED';

			if ($customURL = $this->input->get('returnurl', '', 'string'))
			{
				$customURL = base64_decode($customURL);
			}

			$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->component . '&view=' . FOFInflector::pluralize($this->view) . $this->getItemidURLSuffix();
			$this->setRedirect($url, JText::_($textkey));
		}

		return $result;
	}

	/**
	 * Save the incoming data and then return to the Add task
	 *
	 * @return  bool
	 */
	public function savenew()
	{
		// CSRF prevention
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		$result = $this->applySave();

		// Redirect to the display task

		if ($result)
		{
			$textkey = strtoupper($this->component) . '_LBL_' . strtoupper($this->view) . '_SAVED';

			if ($customURL = $this->input->get('returnurl', '', 'string'))
			{
				$customURL = base64_decode($customURL);
			}

			$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->component . '&view=' . $this->view . '&task=add' . $this->getItemidURLSuffix();
			$this->setRedirect($url, JText::_($textkey));
		}

		return $result;
	}

	/**
	 * Cancel the edit, check in the record and return to the Browse task
	 *
	 * @return  bool
	 */
	public function cancel()
	{
		$model = $this->getThisModel();

		if (!$model->getId())
		{
			$model->setIDsFromRequest();
		}

		$model->checkin();

		// Remove any saved data
		JFactory::getSession()->set($model->getHash() . 'savedata', null);

		// Redirect to the display task

		if ($customURL = $this->input->get('returnurl', '', 'string'))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->component . '&view=' . FOFInflector::pluralize($this->view) . $this->getItemidURLSuffix();
		$this->setRedirect($url);

		return true;
	}

	/**
	 * Method to load a row from version history
	 *
	 * @return   boolean  True if the content history is reverted, false otherwise
	 *
	 * @since   2.2
	 */
	public function loadhistory()
	{
		$app = JFactory::getApplication();
		$lang  = JFactory::getLanguage();
		$model = $this->getThisModel();
		$table = $model->getTable();
		$historyId = $app->input->get('version_id', null, 'integer');
		$status = $model->checkout();
		$alias = $this->component . '.' . $this->view;

		if (!$model->loadhistory($historyId, $table, $alias))
		{
			$this->setMessage($model->getError(), 'error');

			$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->component . '&view=' . FOFInflector::pluralize($this->view) . $this->getItemidURLSuffix();
			$this->setRedirect($url);

			return false;
		}

		// Determine the name of the primary key for the data.
		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		$recordId = $table->$key;

		// To avoid data collisions the urlVar may be different from the primary key.
		$urlVar = empty($this->urlVar) ? $key : $this->urlVar;

		// Access check.
		$privilege = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.acl.edit', 'core.edit'
		);

		if (!$this->checkACL($privilege))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->component . '&view=' . FOFInflector::pluralize($this->view) . $this->getItemidURLSuffix();
			$this->setRedirect($url);
			$table->checkin();

			return false;
		}

		$table->store();
		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->component . '&view=' . FOFInflector::pluralize($this->view) . $this->getItemidURLSuffix();
		$this->setRedirect($url);

		$this->setMessage(JText::sprintf('JLIB_APPLICATION_SUCCESS_LOAD_HISTORY', $model->getState('save_date'), $model->getState('version_note')));

		return true;
	}

	/**
	 * Sets the access to public. Joomla! 1.5 compatibility.
	 *
	 * @return  bool
	 *
	 * @deprecated since 2.0
	 */
	public function accesspublic()
	{
		// CSRF prevention

		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		return $this->setaccess(0);
	}

	/**
	 * Sets the access to registered. Joomla! 1.5 compatibility.
	 *
	 * @return  bool
	 *
	 * @deprecated since 2.0
	 */
	public function accessregistered()
	{
		// CSRF prevention

		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		return $this->setaccess(1);
	}

	/**
	 * Sets the access to special. Joomla! 1.5 compatibility.
	 *
	 * @return  bool
	 *
	 * @deprecated since 2.0
	 */
	public function accessspecial()
	{
		// CSRF prevention

		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		return $this->setaccess(2);
	}

	/**
	 * Publish (set enabled = 1) an item.
	 *
	 * @return  bool
	 */
	public function publish()
	{
		// CSRF prevention
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		return $this->setstate(1);
	}

	/**
	 * Unpublish (set enabled = 0) an item.
	 *
	 * @return  bool
	 */
	public function unpublish()
	{
		// CSRF prevention
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		return $this->setstate(0);
	}

	/**
	 * Archive (set enabled = 2) an item.
	 *
	 * @return  bool
	 */
	public function archive()
	{
		// CSRF prevention
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		return $this->setstate(2);
	}

	/**
	 * Trash (set enabled = -2) an item.
	 *
	 * @return  bool
	 */
	public function trash()
	{
		// CSRF prevention
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		return $this->setstate(-2);
	}

	/**
	 * Saves the order of the items
	 *
	 * @return  bool
	 */
	public function saveorder()
	{
		// CSRF prevention
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		$model = $this->getThisModel();

		if (!$model->getId())
		{
			$model->setIDsFromRequest();
		}

        $ordering = $model->getTable()->getColumnAlias('ordering');
		$ids      = $model->getIds();
		$orders   = $this->input->get('order', array(), 'array');

		if ($n = count($ids))
		{
			for ($i = 0; $i < $n; $i++)
			{
				$model->setId($ids[$i]);
				$neworder = (int) $orders[$i];

				$item = $model->getItem();

				if (!($item instanceof FOFTable))
				{
					return false;
				}

				$key = $item->getKeyName();

				if ($item->$key == $ids[$i])
				{
					$item->$ordering = $neworder;
					$model->save($item);
				}
			}
		}

		$status = $model->reorder();

		// Redirect
		if ($customURL = $this->input->get('returnurl', '', 'string'))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->component . '&view=' . FOFInflector::pluralize($this->view) . $this->getItemidURLSuffix();
		$this->setRedirect($url);

		return $status;
	}

	/**
	 * Moves selected items one position down the ordering list
	 *
	 * @return  bool
	 */
	public function orderdown()
	{
		// CSRF prevention
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		$model = $this->getThisModel();

		if (!$model->getId())
		{
			$model->setIDsFromRequest();
		}

		$status = $model->move(1);

		// Redirect
		if ($customURL = $this->input->get('returnurl', '', 'string'))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->component . '&view=' . FOFInflector::pluralize($this->view) . $this->getItemidURLSuffix();

		if (!$status)
		{
			$this->setRedirect($url, $model->getError(), 'error');
		}
		else
		{
			$this->setRedirect($url);
		}

		return $status;
	}

	/**
	 * Moves selected items one position up the ordering list
	 *
	 * @return  bool
	 */
	public function orderup()
	{
		// CSRF prevention
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		$model = $this->getThisModel();

		if (!$model->getId())
		{
			$model->setIDsFromRequest();
		}

		$status = $model->move(-1);

		// Redirect
		if ($customURL = $this->input->get('returnurl', '', 'string'))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->component . '&view=' . FOFInflector::pluralize($this->view) . $this->getItemidURLSuffix();

		if (!$status)
		{
			$this->setRedirect($url, $model->getError(), 'error');
		}
		else
		{
			$this->setRedirect($url);
		}

		return $status;
	}

	/**
	 * Delete selected item(s)
	 *
	 * @return  bool
	 */
	public function remove()
	{
		// CSRF prevention
		if ($this->csrfProtection)
		{
			$this->_csrfProtection();
		}

		$model = $this->getThisModel();

		if (!$model->getId())
		{
			$model->setIDsFromRequest();
		}

		$status = $model->delete();

		// Redirect
		if ($customURL = $this->input->get('returnurl', '', 'string'))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->component . '&view=' . FOFInflector::pluralize($this->view) . $this->getItemidURLSuffix();

		if (!$status)
		{
			$this->setRedirect($url, $model->getError(), 'error');
		}
		else
		{
			$this->setRedirect($url);
		}

		return $status;
	}

	/**
	 * Redirects the browser or returns false if no redirect is set.
	 *
	 * @return  boolean  False if no redirect exists.
	 */
	public function redirect()
	{
		if ($this->redirect)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage($this->message, $this->messageType);
			$app->redirect($this->redirect);

			return true;
		}

		return false;
	}

	/**
	 * Returns true if there is a redirect set in the controller
	 *
	 * @return  boolean
	 */
	public function hasRedirect()
	{
		return !empty($this->redirect);
	}

	/**
	 * Register the default task to perform if a mapping is not found.
	 *
	 * @param   string  $method  The name of the method in the derived class to perform if a named task is not found.
	 *
	 * @return  FOFController  A FOFController object to support chaining.
	 */
	public function registerDefaultTask($method)
	{
		$this->registerTask('__default', $method);

		return $this;
	}

	/**
	 * Register (map) a task to a method in the class.
	 *
	 * @param   string  $task    The task.
	 * @param   string  $method  The name of the method in the derived class to perform for this task.
	 *
	 * @return  FOFController  A FOFController object to support chaining.
	 */
	public function registerTask($task, $method)
	{
		if (in_array(strtolower($method), $this->methods))
		{
			$this->taskMap[strtolower($task)] = $method;
		}

		return $this;
	}

	/**
	 * Unregister (unmap) a task in the class.
	 *
	 * @param   string  $task  The task.
	 *
	 * @return  FOFController  This object to support chaining.
	 */
	public function unregisterTask($task)
	{
		unset($this->taskMap[strtolower($task)]);

		return $this;
	}

	/**
	 * Sets the internal message that is passed with a redirect
	 *
	 * @param   string  $text  Message to display on redirect.
	 * @param   string  $type  Message type. Optional, defaults to 'message'.
	 *
	 * @return  string  Previous message
	 */
	public function setMessage($text, $type = 'message')
	{
		$previous = $this->message;
		$this->message = $text;
		$this->messageType = $type;

		return $previous;
	}

	/**
	 * Sets an entire array of search paths for resources.
	 *
	 * @param   string  $type  The type of path to set, typically 'view' or 'model'.
	 * @param   string  $path  The new set of search paths. If null or false, resets to the current directory only.
	 *
	 * @return  void
	 */
	protected function setPath($type, $path)
	{
		// Clear out the prior search dirs
		$this->paths[$type] = array();

		// Actually add the user-specified directories
		$this->addPath($type, $path);
	}

	/**
	 * Registers a redirection with an optional message. The redirection is
	 * carried out when you use the redirect method.
	 *
	 * @param   string  $url   The URL to redirect to
	 * @param   string  $msg   The message to be pushed to the application
	 * @param   string  $type  The message type to be pushed to the application, e.g. 'error'
	 *
	 * @return  FOFController  This object to support chaining
	 */
	public function setRedirect($url, $msg = null, $type = null)
	{
		// Do the logic only if we're parsing a raw url (index.php?foo=bar&etc=etc)
		if (strpos($url, 'index.php') === 0)
		{
			$isAdmin = FOFPlatform::getInstance()->isBackend();
			$auto = false;

			if (($this->autoRouting == 2 || $this->autoRouting == 3) && $isAdmin)
			{
				$auto = true;
			}
			elseif (($this->autoRouting == 1 || $this->autoRouting == 3) && !$isAdmin)
			{
				$auto = true;
			}

			if ($auto)
			{
				$url = JRoute::_($url, false);
			}
		}

		$this->redirect = $url;

		if ($msg !== null)
		{
			// Controller may have set this directly
			$this->message = $msg;
		}

		// Ensure the type is not overwritten by a previous call to setMessage.
		if (empty($type))
		{
			if (empty($this->messageType))
			{
				$this->messageType = 'message';
			}
		}
		// If the type is explicitly set, set it.
		else
		{
			$this->messageType = $type;
		}

		return $this;
	}

	/**
	 * Sets the published state (the enabled field) of the selected item(s)
	 *
	 * @param   integer  $state  The desired state. 0 is unpublished, 1 is published.
	 *
	 * @return  bool
	 */
	protected function setstate($state = 0)
	{
		$model = $this->getThisModel();

		if (!$model->getId())
		{
			$model->setIDsFromRequest();
		}

		$status = $model->publish($state);

		// Redirect
		if ($customURL = $this->input->get('returnurl', '', 'string'))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->component . '&view=' . FOFInflector::pluralize($this->view) . $this->getItemidURLSuffix();

		if (!$status)
		{
			$this->setRedirect($url, $model->getError(), 'error');
		}
		else
		{
			$this->setRedirect($url);
		}

		return $status;
	}

	/**
	 * Sets the access level of the selected item(s).
	 *
	 * @param   integer  $level  The desired viewing access level ID
	 *
	 * @return  bool
	 */
	protected function setaccess($level = 0)
	{
		$model = $this->getThisModel();

		if (!$model->getId())
		{
			$model->setIDsFromRequest();
		}

		$id   = $model->getId();
		$item = $model->getItem();

		if (!($item instanceof FOFTable))
		{
			return false;
		}

		$accessField = $item->getColumnAlias('access');
		$key         = $item->getKeyName();
		$loadedid    = $item->$key;

		if ($id == $loadedid)
		{
			$item->$accessField = $level;
			$status = $model->save($item);
		}
		else
		{
			$status = false;
		}

		// Redirect
		if ($customURL = $this->input->get('returnurl', '', 'string'))
		{
			$customURL = base64_decode($customURL);
		}

		$url = !empty($customURL) ? $customURL : 'index.php?option=' . $this->component . '&view=' . FOFInflector::pluralize($this->view) . $this->getItemidURLSuffix();

		if (!$status)
		{
			$this->setRedirect($url, $model->getError(), 'error');
		}
		else
		{
			$this->setRedirect($url);
		}

		return $status;
	}

	/**
	 * Common method to handle apply and save tasks
	 *
	 * @return  boolean  Returns true on success
	 */
	private function applySave()
	{
		// Load the model
		$model = $this->getThisModel();

		if (!$model->getId())
		{
			$model->setIDsFromRequest();
		}

		$id = $model->getId();

		$data = $this->input->getData();

		if (!$this->onBeforeApplySave($data))
		{
			return false;
		}

		// Set the layout to form, if it's not set in the URL

		if (is_null($this->layout))
		{
			$this->layout = 'form';
		}

		// Do I have a form?
		$model->setState('form_name', 'form.' . $this->layout);

		$status = $model->save($data);

		if ($status && ($id != 0))
		{
            FOFPlatform::getInstance()->setHeader('Status', '201 Created', true);

			// Try to check-in the record if it's not a new one
			$status = $model->checkin();
		}

		if ($status)
		{
			$status = $this->onAfterApplySave();
		}

		$this->input->set('id', $model->getId());

		if (!$status)
		{
			// Redirect on error
			$id = $model->getId();

			if ($customURL = $this->input->get('returnurl', '', 'string'))
			{
				$customURL = base64_decode($customURL);
			}

			if (!empty($customURL))
			{
				$url = $customURL;
			}
			elseif ($id != 0)
			{
				$url = 'index.php?option=' . $this->component . '&view=' . $this->view . '&task=edit&id=' . $id . $this->getItemidURLSuffix();
			}
			else
			{
				$url = 'index.php?option=' . $this->component . '&view=' . $this->view . '&task=add' . $this->getItemidURLSuffix();
			}

			$this->setRedirect($url, '<li>' . implode('</li><li>', $model->getErrors()) . '</li>', 'error');

			return false;
		}
		else
		{
			$session = JFactory::getSession();
			$session->set($model->getHash() . 'savedata', null);

			return true;
		}
	}

	/**
	 * Returns the default model associated with the current view
	 *
	 * @param   array  $config  Configuration variables for the model
	 *
	 * @return  FOFModel  The global instance of the model (singleton)
	 */
	final public function getThisModel($config = array())
	{
		if (!is_object($this->_modelObject))
		{
			// Make sure $config is an array
			if (is_object($config))
			{
				$config = (array) $config;
			}
			elseif (!is_array($config))
			{
				$config = array();
			}

			if (!empty($this->modelName))
			{
				$parts = FOFInflector::explode($this->modelName);
				$modelName = ucfirst(array_pop($parts));
				$prefix = FOFInflector::implode($parts);
			}
			else
			{
				$prefix = ucfirst($this->bareComponent) . 'Model';
				$modelName = ucfirst(FOFInflector::pluralize($this->view));
			}

			if (!array_key_exists('input', $config) || !($config['input'] instanceof FOFInput))
			{
				$config['input'] = $this->input;
			}

			$this->_modelObject = $this->getModel($modelName, $prefix, $config);
		}

		return $this->_modelObject;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 */
	public function getModel($name = '', $prefix = '', $config = array())
	{
		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array) $config;
		}
		elseif (!is_array($config) || empty($config))
		{
			// array_merge is required to create a copy instead of assigning by reference
			$config = array_merge($this->config);
		}

		if (empty($name))
		{
			$name = $this->getName();
		}

		if (empty($prefix))
		{
			$prefix = $this->model_prefix;
		}

		if ($model = $this->createModel($name, $prefix, $config))
		{
			// Task is a reserved state
			$model->setState('task', $this->task);

			// Let's get the application object and set menu information if it's available
			if (!FOFPlatform::getInstance()->isCli())
			{
				$app = JFactory::getApplication();
				$menu = $app->getMenu();

				if (is_object($menu))
				{
					if ($item = $menu->getActive())
					{
						$params = $menu->getParams($item->id);

						// Set default state data
						$model->setState('parameters.menu', $params);
					}
				}
			}
		}

		return $model;
	}

	/**
	 * Returns current view object
	 *
	 * @param   array  $config  Configuration variables for the model
	 *
	 * @return  FOFView  The global instance of the view object (singleton)
	 */
	final public function getThisView($config = array())
	{
		if (!is_object($this->_viewObject))
		{
			// Make sure $config is an array
			if (is_object($config))
			{
				$config = (array) $config;
			}
			elseif (!is_array($config) || empty($config))
			{
				// array_merge is required to create a copy instead of assigning by reference
				$config = array_merge($this->config);
			}

			$prefix = null;
			$viewName = null;
			$viewType = null;

			if (!empty($this->viewName))
			{
				$parts = FOFInflector::explode($this->viewName);
				$viewName = ucfirst(array_pop($parts));
				$prefix = FOFInflector::implode($parts);
			}
			else
			{
				$prefix = ucfirst($this->bareComponent) . 'View';
				$viewName = ucfirst($this->view);
			}

			$document = FOFPlatform::getInstance()->getDocument();

			if ($document instanceof JDocument)
			{
				$viewType = $document->getType();
			}
			else
			{
				$viewType = $this->input->getCmd('format', 'html');
			}

			if (($viewType == 'html') && $this->hasForm)
			{
				$viewType = 'form';
			}

			if (!array_key_exists('input', $config) || !($config['input'] instanceof FOFInput))
			{
				$config['input'] = $this->input;
			}

			$config['input']->set('base_path', $this->basePath);

			$this->_viewObject = $this->getView($viewName, $viewType, $prefix, $config);
		}

		return $this->_viewObject;
	}

    /**
     * Method to get the controller name
     *
     * The dispatcher name is set by default parsed using the classname, or it can be set
     * by passing a $config['name'] in the class constructor
     *
     * @throws Exception
     *
     * @return  string  The name of the dispatcher
     */
	public function getName()
	{
		if (empty($this->name))
		{
			if (empty($this->bareComponent))
			{
				$r = null;

				if (!preg_match('/(.*)Controller/i', get_class($this), $r))
				{
					throw new Exception(JText::_('JLIB_APPLICATION_ERROR_CONTROLLER_GET_NAME'), 500);
				}

				$this->name = strtolower($r[1]);
			}
			else
			{
				$this->name = $this->bareComponent;
			}
		}

		return $this->name;
	}

	/**
	 * Get the last task that is being performed or was most recently performed.
	 *
	 * @return  string  The task that is being performed or was most recently performed.
	 */
	public function getTask()
	{
		return $this->task;
	}

	/**
	 * Gets the available tasks in the controller.
	 *
	 * @return  array  Array[i] of task names.
	 */
	public function getTasks()
	{
		return $this->methods;
	}

    /**
     * Method to get a reference to the current view and load it if necessary.
     *
     * @param   string  $name   The view name. Optional, defaults to the controller name.
     * @param   string  $type   The view type. Optional.
     * @param   string  $prefix The class prefix. Optional.
     * @param   array   $config Configuration array for view. Optional.
     *
     * @throws Exception
     *
     * @return  FOFView  Reference to the view or an error.
     */
	public function getView($name = '', $type = '', $prefix = '', $config = array())
	{
		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array) $config;
		}
		elseif (!is_array($config))
		{
			$config = array();
		}

		if (empty($name))
		{
			$name = $this->getName();
		}

		if (empty($prefix))
		{
			$prefix = $this->getName() . 'View';
		}

		$signature = md5($name . $type . $prefix . serialize($config));

		if (empty($this->viewsCache[$signature]))
		{
			if ($view = $this->createView($name, $prefix, $type, $config))
			{
				$this->viewsCache[$signature] = & $view;
			}
			else
			{
				throw new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_VIEW_NOT_FOUND', $name, $type, $prefix), 500);
			}
		}

		return $this->viewsCache[$signature];
	}

	/**
	 * Creates a new model object
	 *
	 * @param   string  $name    The name of the model class, e.g. Items
	 * @param   string  $prefix  The prefix of the model class, e.g. FoobarModel
	 * @param   array   $config  The configuration parameters for the model class
	 *
	 * @return  FOFModel  The model object
	 */
	protected function createModel($name, $prefix = '', $config = array())
	{
		// Make sure $config is an array

		if (is_object($config))
		{
			$config = (array) $config;
		}
		elseif (!is_array($config))
		{
			$config = array();
		}

		$result = null;

		// Clean the model name
		$modelName = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$classPrefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

		$result = FOFModel::getAnInstance($modelName, $classPrefix, $config);

		return $result;
	}

	/**
	 * Method to load and return a model object.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  Optional model prefix.
	 * @param   array   $config  Configuration array for the model. Optional.
	 *
	 * @return  mixed   Model object on success; otherwise null
	 */
	protected function &_createModel($name, $prefix = '', $config = array())
	{
		FOFPlatform::getInstance()->logDeprecated(__CLASS__ . '::' .__METHOD__ . ' is deprecated. Use createModel() instead.');

		return $this->createModel($name, $prefix, $config);
	}

	/**
	 * Creates a View object instance and returns it
	 *
	 * @param   string  $name    The name of the view, e.g. Items
	 * @param   string  $prefix  The prefix of the view, e.g. FoobarView
	 * @param   string  $type    The type of the view, usually one of Html, Raw, Json or Csv
	 * @param   array   $config  The configuration variables to use for creating the view
	 *
	 * @return  FOFView
	 */
	protected function createView($name, $prefix = '', $type = '', $config = array())
	{
		// Make sure $config is an array

		if (is_object($config))
		{
			$config = (array) $config;
		}
		elseif (!is_array($config))
		{
			$config = array();
		}

		$result = null;

		// Clean the view name
		$viewName = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$classPrefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);
		$viewType = preg_replace('/[^A-Z0-9_]/i', '', $type);

		if (!isset($config['input']))
		{
			$config['input'] = $this->input;
		}

		if (($config['input'] instanceof FOFInput))
		{
			$tmpInput = $config['input'];
		}
		else
		{
			$tmpInput = new FOFInput($config['input']);
		}

		// Guess the component name and view

		if (!empty($prefix))
		{
			preg_match('/(.*)View$/', $prefix, $m);
			$component = 'com_' . strtolower($m[1]);
		}
		else
		{
			$component = '';
		}

		if (empty($component) && array_key_exists('input', $config))
		{
			$component = $tmpInput->get('option', $component, 'cmd');
		}

		if (array_key_exists('option', $config))
		{
			if ($config['option'])
			{
				$component = $config['option'];
			}
		}

		$config['option'] = $component;

		$view = strtolower($viewName);

		if (empty($view) && array_key_exists('input', $config))
		{
			$view = $tmpInput->get('view', $view, 'cmd');
		}

		if (array_key_exists('view', $config))
		{
			if ($config['view'])
			{
				$view = $config['view'];
			}
		}

		$config['view'] = $view;

		if (array_key_exists('input', $config))
		{
			$tmpInput->set('option', $config['option']);
			$tmpInput->set('view', $config['view']);
			$config['input'] = $tmpInput;
		}

		// Get the component directories
		$componentPaths = FOFPlatform::getInstance()->getComponentBaseDirs($config['option']);

		// Get the base paths where the view class files are expected to live
		$basePaths = array(
			$componentPaths['main'],
			$componentPaths['alt']
		);
		$basePaths = array_merge($this->paths['view']);

		// Get the alternate (singular/plural) view name
		$altViewName = FOFInflector::isPlural($viewName) ? FOFInflector::singularize($viewName) : FOFInflector::pluralize($viewName);

		$suffixes = array(
			$viewName,
			$altViewName,
			'default'
		);

        $filesystem = FOFPlatform::getInstance()->getIntegrationObject('filesystem');

		foreach ($suffixes as $suffix)
		{
			// Build the view class name
			$viewClass = $classPrefix . ucfirst($suffix);

			if (class_exists($viewClass))
			{
				// The class is already loaded
				break;
			}

			// The class is not loaded. Let's load it!
			$viewPath = $this->createFileName('view', array('name'	 => $suffix, 'type'	 => $viewType));
			$path = $filesystem->pathFind($basePaths, $viewPath);

			if ($path)
			{
				require_once $path;
			}

			if (class_exists($viewClass))
			{
				// The class was loaded successfully
				break;
			}
		}

		if (!class_exists($viewClass))
		{
			$viewClass = 'FOFView' . ucfirst($type);
		}

		$templateOverridePath = FOFPlatform::getInstance()->getTemplateOverridePath($config['option']);

		// Setup View configuration options

		if (!array_key_exists('template_path', $config))
		{
			$config['template_path'][] = $componentPaths['main'] . '/views/' . FOFInflector::pluralize($config['view']) . '/tmpl';

			if ($templateOverridePath)
			{
				$config['template_path'][] = $templateOverridePath . '/' . FOFInflector::pluralize($config['view']);
			}

			$config['template_path'][] = $componentPaths['main'] . '/views/' . FOFInflector::singularize($config['view']) . '/tmpl';

			if ($templateOverridePath)
			{
				$config['template_path'][] = $templateOverridePath . '/' . FOFInflector::singularize($config['view']);
			}

			$config['template_path'][] = $componentPaths['main'] . '/views/' . $config['view'] . '/tmpl';

			if ($templateOverridePath)
			{
				$config['template_path'][] = $templateOverridePath . '/' . $config['view'];
			}
		}

		$extraTemplatePath = $this->configProvider->get($config['option'] . '.views.' . $config['view'] . '.config.template_path', null);

		if ($extraTemplatePath)
		{
			array_unshift($config['template_path'], $componentPaths['main'] . '/' . $extraTemplatePath);
		}

		if (!array_key_exists('helper_path', $config))
		{
			$config['helper_path'] = array(
				$componentPaths['main'] . '/helpers',
				$componentPaths['admin'] . '/helpers'
			);
		}

		$extraHelperPath = $this->configProvider->get($config['option'] . '.views.' . $config['view'] . '.config.helper_path', null);

		if ($extraHelperPath)
		{
			$config['helper_path'][] = $componentPaths['main'] . '/' . $extraHelperPath;
		}

		// Set up the page title
		$setFrontendPageTitle = $this->configProvider->get($config['option'] . '.views.' . $config['view'] . '.config.setFrontendPageTitle', null);

		if ($setFrontendPageTitle)
		{
			$setFrontendPageTitle = strtolower($setFrontendPageTitle);
			$config['setFrontendPageTitle'][] = in_array($setFrontendPageTitle, array('1', 'yes', 'true', 'on'));
		}

		$defaultPageTitle = $this->configProvider->get($config['option'] . '.views.' . $config['view'] . '.config.defaultPageTitle', null);

		if ($defaultPageTitle)
		{
			$config['defaultPageTitle'][] = in_array($defaultPageTitle, array('1', 'yes', 'true', 'on'));
		}

		// Set the use_hypermedia flag in $config if it's not already set
		if (!isset($config['use_hypermedia']))
		{
			$config['use_hypermedia'] = $this->configProvider->get($config['option'] . '.views.' . $config['view'] . '.config.use_hypermedia', false);
		}

		// Set also the linkbar_style
		if (!isset($config['linkbar_style']))
		{
			$style = $this->configProvider->get($config['option'] . '.views.' . $config['view'] . '.config.linkbar_style', false);

			if ($style) {
				$config['linkbar_style'] = $style;
			}
		}

		/**
		 * Some administrative templates force format=utf (yeah, I know, what the heck, right?) when a format
		 * URL parameter does not exist in the URL. Of course there is no such thing as FOFViewUtf (why the heck would
		 * it be, there is no such thing as a format=utf in Joomla! for crying out loud) which causes a Fatal Error. So
		 * we have to detect that and force $type='html'...
		 */
		if (!class_exists($viewClass) && ($type != 'html'))
		{
			$type = 'html';
			$result = $this->createView($name, $prefix, $type, $config);
		}
		else
		{
			$result = new $viewClass($config);
		}

		return $result;
	}

	/**
	 * Deprecated function to create a View object instance
	 *
	 * @param   string  $name    The name of the view, e.g. 'Items'
	 * @param   string  $prefix  The prefix of the view, e.g. 'FoobarView'
	 * @param   string  $type    The view type, e.g. 'html'
	 * @param   array   $config  The configuration array for the view
	 *
	 * @return  FOFView
	 *
	 * @see FOFController::createView
	 *
	 * @deprecated since version 2.0
	 */
	protected function &_createView($name, $prefix = '', $type = '', $config = array())
	{
		FOFPlatform::getInstance()->logDeprecated(__CLASS__ . '::' . __METHOD__ . ' is deprecated. Use createView() instead.');

		return $this->createView($name, $prefix, $type, $config);
	}

	/**
	 * Set the name of the view to be used by this Controller
	 *
	 * @param   string  $viewName  The name of the view
	 *
	 * @return  void
	 */
	public function setThisViewName($viewName)
	{
		$this->viewName = $viewName;
	}

	/**
	 * Set the name of the model to be used by this Controller
	 *
	 * @param   string  $modelName  The name of the model
	 *
	 * @return  void
	 */
	public function setThisModelName($modelName)
	{
		$this->modelName = $modelName;
	}

	/**
	 * Checks if the current user has enough privileges for the requested ACL
	 * area.
	 *
	 * @param   string  $area  The ACL area, e.g. core.manage.
	 *
	 * @return  boolean  True if the user has the ACL privilege specified
	 */
	protected function checkACL($area)
	{
		if (in_array(strtolower($area), array('false','0','no','403')))
		{
			return false;
		}

		if (in_array(strtolower($area), array('true','1','yes')))
		{
			return true;
		}
		elseif (empty($area))
		{
			return true;
		}
		else
		{
			// Check if we're dealing with ids
			$ids = null;

			// First, check if there is an asset for this record
			$table = $this->getThisModel()->getTable();

			if ($table && $table->isAssetsTracked())
			{
				$ids = $this->getThisModel()->getId() ? $this->getThisModel()->getId() : null;
			}

			// Generic or Asset tracking

			if (empty($ids))
			{
				return FOFPlatform::getInstance()->authorise($area, $this->component);
			}
			else
			{
				if (!is_array($ids))
				{
					$ids = array($ids);
				}

				$resource = FOFInflector::singularize($this->view);
				$isEditState = ($area == 'core.edit.state');

				foreach ($ids as $id)
				{
					$asset = $this->component . '.' . $resource . '.' . $id;

					// Dedicated permission found, check it!

					if (FOFPlatform::getInstance()->authorise($area, $asset) )
					{
						return true;
					}

					// Fallback on edit.own, if not edit.state. First test if the permission is available.

					if ((!$isEditState) && (FOFPlatform::getInstance()->authorise('core.edit.own', $asset)))
					{
						$table = $this->getThisModel()->getTable();
                        $table->load($id);

                        $created_by = $table->getColumnAlias('created_by');

						if ($table && isset($table->$created_by))
						{
							// Now test the owner is the user.
							$owner_id = (int) $table->$created_by;

							// If the owner matches 'me' then do the test.
							if ($owner_id == FOFPlatform::getInstance()->getUser()->id)
							{
								return true;
							}
							else
							{
								return false;
							}
						}
						else
						{
							return false;
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * A catch-all method for all tasks without a corresponding onBefore
	 * method. Applies the ACL preferences defined in fof.xml.
	 *
	 * @param   string  $task  The task being executed
	 *
	 * @return  boolean  True to allow execution of the task
	 */
	protected function onBeforeGenericTask($task)
	{
		$privilege = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.acl.' . $task, ''
		);

		return $this->checkACL($privilege);
	}

	/**
	 * Execute something before applySave is called. Return false to prevent
	 * applySave from executing.
	 *
	 * @param   array  &$data  The data upon which applySave will act
	 *
	 * @return  boolean  True to allow applySave to run
	 */
	protected function onBeforeApplySave(&$data)
	{
		return true;
	}

	/**
	 * Execute something after applySave has run.
	 *
	 * @return  boolean  True to allow normal return, false to cause a 403 error
	 */
	protected function onAfterApplySave()
	{
		return true;
	}

	/**
	 * ACL check before changing the access level; override to customise
	 *
	 * @return  boolean  True to allow accesspublic() to run
	 */
	protected function onBeforeAccesspublic()
	{
		$privilege = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.acl.accesspublic', 'core.edit.state');

		return $this->checkACL($privilege);
	}

	/**
	 * ACL check before changing the access level; override to customise
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeAccessregistered()
	{
		$privilege = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.acl.accessregistered', 'core.edit.state'
		);

		return $this->checkACL($privilege);
	}

	/**
	 * ACL check before changing the access level; override to customise
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeAccessspecial()
	{
		$privilege = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.acl.accessspecial', 'core.edit.state'
		);

		return $this->checkACL($privilege);
	}

	/**
	 * ACL check before adding a new record; override to customise
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeAdd()
	{
		$privilege = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.acl.add', 'core.create'
		);

		return $this->checkACL($privilege);
	}

	/**
	 * ACL check before saving a new/modified record; override to customise
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeApply()
	{
        $model = $this->getThisModel();

        if (!$model->getId())
        {
            $model->setIDsFromRequest();
        }

        $id = $model->getId();

        if(!$id)
        {
            $defaultPrivilege = 'core.create';
        }
        else
        {
            $defaultPrivilege = 'core.edit';
        }

		$privilege = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.acl.apply', $defaultPrivilege
		);

		return $this->checkACL($privilege);
	}

	/**
	 * ACL check before allowing someone to browse
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeBrowse()
	{
		$defaultPrivilege = '';

		$privilege = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.acl.browse', $defaultPrivilege
		);

		return $this->checkACL($privilege);
	}

	/**
	 * ACL check before cancelling an edit
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeCancel()
	{
        $model = $this->getThisModel();

        if (!$model->getId())
        {
            $model->setIDsFromRequest();
        }

        $id = $model->getId();

        if(!$id)
        {
            $defaultPrivilege = 'core.create';
        }
        else
        {
            $defaultPrivilege = 'core.edit';
        }

		$privilege = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.acl.cancel', $defaultPrivilege
		);

		return $this->checkACL($privilege);
	}

	/**
	 * ACL check before editing a record; override to customise
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeEdit()
	{
		$privilege = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.acl.edit', 'core.edit'
		);

		return $this->checkACL($privilege);
	}

	/**
	 * ACL check before changing the ordering of a record; override to customise
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeOrderdown()
	{
		$privilege = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.acl.orderdown', 'core.edit.state'
		);

		return $this->checkACL($privilege);
	}

	/**
	 * ACL check before changing the ordering of a record; override to customise
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeOrderup()
	{
		$privilege = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.acl.orderup', 'core.edit.state'
		);

		return $this->checkACL($privilege);
	}

	/**
	 * ACL check before changing the publish status of a record; override to customise
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforePublish()
	{
		$privilege = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.acl.publish', 'core.edit.state'
		);

		return $this->checkACL($privilege);
	}

	/**
	 * ACL check before removing a record; override to customise
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeRemove()
	{
		$privilege = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.acl.remove', 'core.delete'
		);

		return $this->checkACL($privilege);
	}

	/**
	 * ACL check before saving a new/modified record; override to customise
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeSave()
	{
		$model = $this->getThisModel();

		if (!$model->getId())
		{
			$model->setIDsFromRequest();
		}

		$id = $model->getId();

		if(!$id)
		{
			$defaultPrivilege = 'core.create';
		}
		else
		{
			$defaultPrivilege = 'core.edit';
		}

		$privilege = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.acl.save', $defaultPrivilege
		);

		return $this->checkACL($privilege);
	}

	/**
	 * ACL check before saving a new/modified record; override to customise
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeSavenew()
	{
		$privilege = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.acl.savenew', 'core.create'
		);

		return $this->checkACL($privilege);
	}

	/**
	 * ACL check before changing the ordering of a record; override to customise
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeSaveorder()
	{
		$privilege = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.acl.saveorder', 'core.edit.state'
		);

		return $this->checkACL($privilege);
	}

	/**
	 * ACL check before changing the publish status of a record; override to customise
	 *
	 * @return  boolean  True to allow the method to run
	 */
	protected function onBeforeUnpublish()
	{
		$privilege = $this->configProvider->get(
			$this->component . '.views.' .
			FOFInflector::singularize($this->view) . '.acl.unpublish', 'core.edit.state'
		);

		return $this->checkACL($privilege);
	}

	/**
	 * Gets a URL suffix with the Itemid parameter. If it's not the front-end of the site, or if
	 * there is no Itemid set it returns an empty string.
	 *
	 * @return  string  The &Itemid=123 URL suffix, or an empty string if Itemid is not applicable
	 */
	public function getItemidURLSuffix()
	{
		if (FOFPlatform::getInstance()->isFrontend() && ($this->input->getCmd('Itemid', 0) != 0))
		{
			return '&Itemid=' . $this->input->getInt('Itemid', 0);
		}
		else
		{
			return '';
		}
	}

	/**
	 * Applies CSRF protection by means of a standard Joomla! token (nonce) check.
	 * Raises a 403 Access Forbidden error through the platform if the check fails.
	 *
     * TODO Move this check inside the platform
     *
	 * @return  boolean  True if the CSRF check is successful
	 *
	 * @throws Exception
	 */
	protected function _csrfProtection()
	{
		static $isCli = null, $isAdmin = null;

		if (is_null($isCli))
		{
			$isCli   = FOFPlatform::getInstance()->isCli();
			$isAdmin = FOFPlatform::getInstance()->isBackend();
		}

		switch ($this->csrfProtection)
		{
			// Never
			case 0:
				return true;
				break;

			// Always
			case 1:
				break;

			// Only back-end and HTML format
			case 2:
				if ($isCli)
				{
					return true;
				}
				elseif (!$isAdmin && ($this->input->get('format', 'html', 'cmd') != 'html'))
				{
					return true;
				}
				break;

			// Only back-end
			case 3:
				if (!$isAdmin)
				{
					return true;
				}
				break;
		}

		$hasToken = false;
		$session  = JFactory::getSession();

		// Joomla! 1.5/1.6/1.7/2.5 (classic Joomla! API) method
		if (method_exists('JUtility', 'getToken'))
		{
			$token    = JUtility::getToken();
			$hasToken = $this->input->get($token, false, 'none') == 1;

			if (!$hasToken)
			{
				$hasToken = $this->input->get('_token', null, 'none') == $token;
			}
		}

		// Joomla! 2.5+ (Platform 12.1+) method
		if (!$hasToken)
		{
			if (method_exists($session, 'getToken'))
			{
				$token    = $session->getToken();
				$hasToken = $this->input->get($token, false, 'none') == 1;

				if (!$hasToken)
				{
					$hasToken = $this->input->get('_token', null, 'none') == $token;
				}
			}
		}

		// Joomla! 2.5+ formToken method
		if (!$hasToken)
		{
			if (method_exists($session, 'getFormToken'))
			{
				$token    = $session->getFormToken();
				$hasToken = $this->input->get($token, false, 'none') == 1;

				if (!$hasToken)
				{
					$hasToken = $this->input->get('_token', null, 'none') == $token;
				}
			}
		}

		if (!$hasToken)
		{
            FOFPlatform::getInstance()->raiseError(403, JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));

			return false;
		}
	}
}
