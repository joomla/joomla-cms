<?php
/**
 * @package     Joomla.CMS
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_PLATFORM') or die();

/**
 * Base class for a Joomla Controller
 *
 * Controller (Controllers are where you put all the actual code.) Provides basic
 * functionality, such as rendering views (aka displaying templates).
 *
 * @package Joomla.CMS
 * @subpackage Controller
 * @since 3.5
 */
class JCmsController
{

	/**
	 * Array which hold all the controller objects has been created
	 *
	 * @var Array
	 */
	protected static $instances = array();

	/**
	 * The application object.
	 *
	 * @var JApplicationBase
	 */
	protected $app;

	/**
	 * The input object.
	 *
	 * @var JInput
	 */
	protected $input;

	/**
	 * Full name of the component being dispatchaed com_foobar
	 *
	 * @var string
	 */
	protected $option;

	/**
	 * Name of the component, use as prefix for controller, model and view classes
	 *
	 * @var string
	 */
	protected $component;

	/**
	 * Name of the controller
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Class prefix used as prefix for controllers, models, views, tables class name
	 *
	 * @var string
	 */
	protected $classPrefix = null;

	/**
	 * Language prefix, used for language strings
	 *
	 * @var string
	 */
	protected $languagePrefix;

	/**
	 * The default view which will be rendered in case there is no view specified
	 *
	 * @var string
	 */
	protected $defaultView;

	/**
	 * Array of class methods
	 *
	 * @var array
	 */
	protected $methods;

	/**
	 * Array of class methods to call for a given task.
	 *
	 * @var array
	 */
	protected $taskMap = array();

	/**
	 * Current or most recently performed task.
	 *
	 * @var string
	 */
	protected $task;

	/**
	 * Redirect message.
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * Redirect message type.
	 *
	 * @var string
	 */
	protected $messageType;

	/**
	 * URL for redirection.
	 *
	 * @var string
	 */
	protected $redirect;

	/**
	 * Method to get instance of a controller
	 *
	 * @param JInput $input        	
	 * @param array $config        	
	 *
	 * @return JCmsController
	 */
	public static function getInstance(JInput $input = null, array $config = array())
	{
		$input = $input ? $input : JFactory::getApplication()->input;
		$option = $input->getCmd('option');
		$view = $input->getCmd('view');
		$task = $input->get('task', '');
		$pos = strpos($task, '.');
		if ($pos !== false)
		{
			//In case task has dot in it, task need to have the format controllername.task
			$view = substr($task, 0, $pos);
			$task = substr($task, $pos + 1);
			$input->set('view', $view);
			$input->set('task', $task);
		}
		$component = substr($option, 4);
		if (!isset(self::$instances[$component . $view]))
		{
			if (isset($config['class_prefix']))
			{
				$prefix = ucfirst($config['class_prefix']);
			}
			else
			{
				$prefix = ucfirst($component);
			}
			$config['class_prefix'] = $prefix;
			if ($view)
			{
				$class = $prefix . 'Controller' . ucfirst(JCmsInflector::singularize($view));
			}
			else
			{
				$class = $prefix . 'Controller';
			}
			if (!class_exists($class))
			{
				if (isset($config['default_controller_class']))
				{
					$class = $config['default_controller_class'];
				}
				else
				{
					$class = 'JCmsController';
				}
			}
			self::$instances[$option . $view] = new $class($input, $config);
		}
		return self::$instances[$option . $view];
	}

	/**
	 * Constructor.
	 *
	 * @param array $config An optional associative array of configuration settings.
	 *        	
	 */
	public function __construct(JInput $input = null, array $config = array())
	{
		$this->app = JFactory::getApplication();
		$this->input = $input;
		$this->classPrefix = $config['class_prefix'];
		$this->option = $input->get('option');
		$this->component = substr($this->option, 4);
		if (isset($config['name']))
		{
			$this->name = $config['name'];
		}
		else
		{
			$this->name = JCmsInflector::singularize($input->get('view'));
			if (!$this->name)
			{
				$this->name = 'controller';
			}
		}
		if (isset($config['language_prefix']))
		{
			$this->languagePrefix = $config['language_prefix'];
		}
		else
		{
			$this->languagePrefix = strtoupper($this->component);
		}
		if (isset($config['default_view']))
		{
			$this->defaultView = $config['default_view'];
		}
		else
		{
			$this->defaultView = $this->component;
		}
		// Build the default taskMap based on the class methods
		$xMethods = get_class_methods('JCmsController');
		$r = new ReflectionClass($this);
		$rMethods = $r->getMethods(ReflectionMethod::IS_PUBLIC);
		foreach ($rMethods as $rMethod)
		{
			$mName = $rMethod->getName();
			if (!in_array($mName, $xMethods) || $mName == 'display')
			{
				$this->taskMap[strtolower($mName)] = $mName;
				$this->methods[] = strtolower($mName);
			}
		}
		
		$this->task = $input->get('task', 'display');
		if (isset($config['default_task']))
		{
			$this->registerTask('__default', $config['default_task']);
		}
		else
		{
			$this->registerTask('__default', 'display');
		}
	}

	/**
	 * Excute the given task
	 *
	 * @return JCmsController return itself to support changing
	 */
	public function execute()
	{
		$task = strtolower($this->task);
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
			throw new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_TASK_NOT_FOUND', $task), 404);
		}
		$this->$doTask();
		
		return $this;
	}

	/**
	 * Method to display a view
	 *
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @param boolean $cachable If true, the view output will be cached
	 *        	
	 * @param array $urlparams An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *        		        	
	 * @return JCmsController A JCmsController object to support chaining.
	 */
	public function display($cachable = false, array $urlparams = array())
	{
		// Create the view object
		$viewType = $this->input->get('type', 'html');
		$viewName = $this->input->get('view', $this->defaultView);
		$viewLayout = $this->input->get('layout', 'default');
		$view = $this->getView($viewName, $viewType, $viewLayout);
		
		// If view has model, create the model, and assign it to the view
		if ($view->hasModel)
		{
			$model = $this->getModel($viewName);
			$view->setModel($model);
		}
		
		// Display the view		
		if ($cachable && $viewType != 'feed' && JFactory::getConfig()->get('caching') >= 1)
		{
			$cache = JFactory::getCache($this->option, 'view');
			if (is_array($urlparams))
			{
				if (!empty($this->app->registeredurlparams))
				{
					$registeredurlparams = $this->app->registeredurlparams;
				}
				else
				{
					$registeredurlparams = new stdClass();
				}
				
				foreach ($urlparams as $key => $value)
				{
					// Add your safe url parameters with variable type as value {@see JFilterInput::clean()}.
					$registeredurlparams->$key = $value;
				}
				
				$this->app->registeredurlparams = $registeredurlparams;
			}
			$cache->get($view, 'display');
		}
		else
		{
			$view->display();
		}
		return $this;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param string $name The model name. Optional. Default will be the controller name
	 *        		
	 * @param array $config Configuration array for model. Optional.
	 *        	        	
	 * @return JCmsModelAdmin The model.
	 *        
	 */
	public function getModel($name = '', array $config = array())
	{
		// If name is not given, the model will has same name with controller
		if (empty($name))
		{
			$name = $this->name;
		}
		
		// Merge config array with default config values
		$config += array(
			'name' => $name, 
			'option' => $this->option, 
			'class_prefix' => $this->classPrefix, 
			'language_prefix' => $this->languagePrefix);
		
		// Set the table prefix of the database table name
		if (!isset($config['table_prefix']))
		{
			$config['table_prefix'] = '#__' . strtolower($this->component) . '_';
		}
		
		// Set default model class in case it is not existed
		if (!isset($config['default_model_class']))
		{
			if (JCmsInflector::isPlural($name))
			{
				$config['default_model_class'] = 'JCmsModelList';
			}
			else
			{
				if ($this->app->isAdmin())
				{
					$config['default_model_class'] = 'JCmsModelAdmin';
					$defaultConfig['is_admin_model'] = true;
				}
				else
				{
					$config['default_model_class'] = 'JCmsModelItem';
				}
			}
		}
		
		//Create model and auto populate model states
		$model = JCmsModel::getInstance($name, $this->classPrefix . 'Model', $config);
		if (!$model->ignoreRequest)
		{
			$model->set($this->input->getArray());
		}
		
		return $model;
	}

	/**
	 * Method to get instance of a view
	 *
	 * @param string $name The view name
	 *        	
	 * @param array $config Configuration array for view. Optional.
	 *        	
	 * @return JCmsView Reference to the view
	 *        
	 */
	public function getView($name, $type = 'html', $layout = 'default', array $config = array())
	{
		// Merge config array with default config parameters
		$config += array(
			'name' => $name, 
			'layout' => $layout, 
			'option' => $this->option, 
			'class_prefix' => $this->classPrefix, 
			'language_prefix' => $this->languagePrefix);
		
		// Set the default paths for finding the layout if it is not specified in the $config array		
		if (!isset($config['paths']))
		{
			$paths = new SplPriorityQueue();
			$paths->insert($this->basePath . '/view/' . $name . '/tmpl', 0);
			$paths->insert(JPATH_THEMES . '/' . $this->app->getTemplate() . '/html/' . $this->option . '/' . $name, 1);
			$config['paths'] = $paths;
		}
		
		//Set default view class if class is not existed
		if (!isset($config['default_view_class']))
		{
			if (JCmsInflector::isPlural($name))
			{
				$config['default_view_class'] = 'JCmsViewList';
			}
			else
			{
				$config['default_view_class'] = 'JCmsViewItem';
			}
		}
		if ($this->app->isAdmin())
		{
			$config['is_admin_view'] = true;
		}
		$config['Itemid'] = $this->input->getInt('Itemid');
		
		return JCmsView::getInstance($name, $type, $this->classPrefix . 'View', $config);
	}

	/**
	 * Sets the internal message that is passed with a redirect
	 *
	 * @param string $text Message to display on redirect.
	 *        	
	 * @param string $type Message type. Optional, defaults to 'message'.
	 *        	
	 * @return string Previous message
	 *        
	 */
	public function setMessage($text, $type = 'message')
	{
		$previous = $this->message;
		$this->message = $text;
		$this->messageType = $type;
		
		return $previous;
	}

	/**
	 * Set a URL for browser redirection.
	 *
	 * @param string $url URL to redirect to.
	 *        	
	 * @param string $msg Message to display on redirect. Optional, defaults to value set internally by controller, if any.
	 *        	
	 * @param string $type Message type. Optional, defaults to 'message' or the type set by a previous call to setMessage.
	 *        	
	 * @return JCmsController This object to support chaining.
	 *        
	 */
	public function setRedirect($url, $msg = null, $type = null)
	{
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
	 * Redirects the browser or returns false if no redirect is set.
	 *
	 * @return boolean False if no redirect exists.
	 *        
	 */
	public function redirect()
	{
		if ($this->redirect)
		{
			$this->app->enqueueMessage($this->message, $this->messageType);
			$this->app->redirect($this->redirect);
		}
		
		return false;
	}

	/**
	 * Get the last task that is being performed or was most recently performed.
	 *
	 * @return string The task that is being performed or was most recently performed.
	 *        
	 */
	public function getTask()
	{
		return $this->task;
	}

	/**
	 * Register (map) a task to a method in the class.
	 *
	 * @param string $task The task name
	 *        	
	 * @param string $method The name of the method in the derived class to perform for this task.
	 *        	
	 * @return JCmsController A JCmsController object to support chaining.
	 *        
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
	 * Get the application object.
	 *
	 * @return JApplicationBase The application object.
	 *        
	 */
	public function getApplication()
	{
		return $this->app;
	}

	/**
	 * Get the input object.
	 *
	 * @return JInput The input object.
	 *        
	 */
	public function getInput()
	{
		return $this->input;
	}
}
