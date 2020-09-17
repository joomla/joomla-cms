<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Controller;

defined('_JEXEC') || die;

use FOF30\Container\Container;
use FOF30\Controller\Exception\CannotGetName;
use FOF30\Controller\Exception\TaskNotFound;
use FOF30\Model\Model;
use FOF30\View\View;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Cache\Controller\ViewController;
use Joomla\CMS\Cache\Exception\CacheExceptionInterface;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * Class Controller
 *
 * A generic MVC controller implementation
 *
 * @property-read  \FOF30\Input\Input $input  The input object (magic __get returns the Input from the Container)
 */
class Controller
{
	/**
	 * Instance container.
	 *
	 * @var    Controller
	 */
	protected static $instance;
	/**
	 * The name of the controller
	 *
	 * @var    array
	 */
	protected $name = null;
	/**
	 * The mapped task that was performed.
	 *
	 * @var    string
	 */
	protected $doTask;
	/**
	 * Bit mask to enable routing through JRoute on redirects. The value can be:
	 *
	 * 0 = never
	 * 1 = frontend only
	 * 2 = backend  only
	 * 3 = always
	 *
	 * @var    int
	 */
	protected $autoRouting = 0;
	/**
	 * Should I protect against state bleedover? When this is enabled the default model's state hash will be
	 * automatically set to include the controller name i.e. `com_example.controllerName.modelName.` instead of
	 * `com_example.modelName.`. This will happen ONLY if the preventStateBleedover flag is set, the controller and
	 * model names are different and the model doesn't set its own hash (or override getHash altogether).
	 *
	 * You should only need to enable this feature when you have multiple controllers using the _same_ Model as their
	 * default. For example, if you have a blog component with Latest and Posts Controllers, both using the Posts Model
	 * as their default Model the state variables set in the latest posts page would bleed over to the posts page. This
	 * can include filtering and pagination preferences, resulting in a confusing experience for the user.
	 *
	 * Caveat: if you are using a different Controller class for singular / plural view names you will need to override
	 *         getModel() yourself. Otherwise the state of the singular view would be disjointed from the state of the
	 *         plural view (since the Controller names are different). That's the reason why this feature is turned off
	 *         by default.
	 *
	 * False = same behavior as FOF 3.0.0 to 3.1.1 inclusive.
	 *
	 * @var   bool
	 */
	protected $preventStateBleedover = false;
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
	 * Array of class methods
	 *
	 * @var    array
	 */
	protected $methods;
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
	 * The current view name; you can override it in the configuration
	 *
	 * @var string
	 */
	protected $view = '';

	/**
	 * The current layout; you can override it in the configuration
	 *
	 * @var string
	 */
	protected $layout = null;

	/**
	 * A cached copy of the class configuration parameter passed during initialisation
	 *
	 * @var array
	 */
	protected $config = [];

	/**
	 * Overrides the name of the view's default model
	 *
	 * @var string
	 */
	protected $modelName = null;

	/**
	 * Overrides the name of the view's default view
	 *
	 * @var string
	 */
	protected $viewName = null;

	/**
	 * An array of Model instances known to this Controller
	 *
	 * @var   array[Model]
	 */
	protected $modelInstances = [];

	/**
	 * An array of View instances known to this Controller
	 *
	 * @var   array[View]
	 */
	protected $viewInstances = [];

	/**
	 * The container attached to this Controller
	 *
	 * @var Container
	 */
	protected $container = null;

	/**
	 * The tasks for which caching should be enabled by default
	 *
	 * @var array
	 */
	protected $cacheableTasks = [];

	/**
	 * How user group membership affects caching. The values are:
	 * - 0 : Not taken into account, everyone sees the same page, always
	 * - 1 : Only user groups are taken into account (default behaviour of FOF 3.0 to 3.4.2)
	 * - 2 : The user ID itself is taken into account
	 *
	 * @var   bool
	 * @since 3.4.3
	 */
	protected $userCaching = 1;

	/**
	 * An associative array for required ACL privileges per task. For example:
	 * array(
	 *   'edit' => 'core.edit',
	 *   'jump' => 'foobar.jump',
	 *   'alwaysallow' => 'true',
	 *   'neverallow' => 'false'
	 * );
	 *
	 * You can use the notation '@task' which means 'apply the same privileges as "task"'. If you create a reference
	 * back to yourself (e.g. 'mytask' => array('@mytask')) it will return TRUE.
	 *
	 * @var array
	 */
	protected $taskPrivileges = [];

	/**
	 * Enable CSRF protection on selected tasks. The possible values are:
	 *
	 * 0    Disabled; no token checks are performed
	 * 1    Enabled; token checks are always performed
	 * 2    Only on HTML requests and backend; token checks are always performed in the back-end and in the front-end
	 * only when format is 'html'
	 * 3    Only on back-end; token checks are performed only in the back-end
	 *
	 * @var    integer
	 */
	protected $csrfProtection = 2;

	/**
	 * Public constructor of the Controller class. You can pass the following variables in the $config array:
	 * name            string  The name of the Controller. Default: auto detect from the class name
	 * default_task    string  The task to use when none is specified. Default: main
	 * autoRouting     int     See the autoRouting property
	 * csrfProtection  int     See the csrfProtection property
	 * viewName        string  The view name. Default: the same as the controller name
	 * modelName       string  The model name. Default: the same as the controller name
	 * viewConfig      array   The configuration overrides for the View.
	 * modelConfig     array   The configuration overrides for the Model.
	 *
	 * @param   Container  $container  The application container
	 * @param   array      $config     The configuration array
	 *
	 * @return  Controller
	 */
	public function __construct(Container $container, array $config = [])
	{
		// Initialise
		$this->methods     = [];
		$this->message     = null;
		$this->messageType = 'message';
		$this->paths       = [];
		$this->redirect    = null;
		$this->taskMap     = [];

		// Get a local copy of the container
		$this->container = $container;

		// Determine the methods to exclude from the base class.
		$xMethods = get_class_methods('\\FOF30\\Controller\\Controller');

		// Get the public methods in this class using reflection.
		$r        = new \ReflectionClass($this);
		$rMethods = $r->getMethods(\ReflectionMethod::IS_PUBLIC);

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
			if (!in_array($mName, $xMethods) || $mName == 'display' || $mName == 'main')
			{
				$this->methods[] = $mName;

				// Auto register the methods as tasks.
				$this->taskMap[$mName] = $mName;
			}
		}

		if (isset($config['name']))
		{
			$this->name = $config['name'];
		}

		// Get the default values for the component and view names
		$this->view   = $this->getName();
		$this->layout = $this->input->getCmd('layout', null);

		// If the default task is set, register it as such
		if (array_key_exists('default_task', $config) && !empty($config['default_task']))
		{
			$this->registerDefaultTask($config['default_task']);
		}
		else
		{
			$this->registerDefaultTask('main');
		}

		// Cache the config
		$this->config = $config;

		// Set any model/view name overrides
		if (array_key_exists('viewName', $config) && !empty($config['viewName']))
		{
			$this->setViewName($config['viewName']);
		}

		if (array_key_exists('modelName', $config) && !empty($config['modelName']))
		{
			$this->setModelName($config['modelName']);
		}

		// Apply the autoRouting preference
		if (array_key_exists('autoRouting', $config))
		{
			$this->autoRouting = (int) $config['autoRouting'];
		}

		// Apply the csrfProtection preference
		if (array_key_exists('csrfProtection', $config))
		{
			$this->csrfProtection = (int) $config['csrfProtection'];
		}

		// Apply the preventStateBleedover preference
		if (array_key_exists('preventStateBleedover', $config))
		{
			$this->preventStateBleedover = (bool) ((int) $config['preventStateBleedover']);
		}
	}

	/**
	 * Magic get method. Handles magic properties:
	 * $this->input  mapped to $this->container->input
	 *
	 * @param   string  $name  The property to fetch
	 *
	 * @return  mixed|null
	 */
	public function __get($name)
	{
		// Handle $this->input
		if ($name == 'input')
		{
			return $this->container->input;
		}

		// Property not found; raise error
		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE);

		return null;
	}

	/**
	 * Executes a given controller task. The onBefore<task> and onAfter<task>
	 * methods are called automatically if they exist.
	 *
	 * @param   string  $task  The task to execute, e.g. "browse"
	 *
	 * @return  null|bool  False on execution failure
	 *
	 * @throws  TaskNotFound  When the task is not found
	 */
	public function execute($task)
	{
		$this->task = $task;

		if (!isset($this->taskMap[$task]) && !isset($this->taskMap['__default']))
		{
			throw new TaskNotFound(Text::sprintf('JLIB_APPLICATION_ERROR_TASK_NOT_FOUND', $task), 404);
		}

		$result = $this->triggerEvent('onBeforeExecute', [&$task]);

		if ($result === false)
		{
			return false;
		}

		$eventName = 'onBefore' . ucfirst($task);
		$result    = $this->triggerEvent($eventName);

		if ($result === false)
		{
			return false;
		}

		// Do not allow the display task to be directly called
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

		// Record the actual task being fired
		$this->doTask = $doTask;

		$ret = $this->$doTask();

		$eventName = 'onAfter' . ucfirst($task);
		$result    = $this->triggerEvent($eventName);

		if ($result === false)
		{
			return false;
		}

		$result = $this->triggerEvent('onAfterExecute', [$task]);

		if ($result === false)
		{
			return false;
		}

		return $ret;
	}

	/**
	 * Default task. Assigns a model to the view and asks the view to render
	 * itself.
	 *
	 * YOU MUST NOT USE THIS TASK DIRECTLY IN A URL. It is supposed to be
	 * used ONLY inside your code. In the URL, use task=browse instead.
	 *
	 * @param   bool    $cachable   Is this view cacheable?
	 * @param   bool    $urlparams  Add your safe URL parameters (see further down in the code)
	 * @param   string  $tpl        The name of the template file to parse
	 *
	 * @return  void
	 */
	public function display($cachable = false, $urlparams = false, $tpl = null)
	{
		$document = $this->container->platform->getDocument();

		if ($document instanceof Document)
		{
			$viewType = $document->getType();
		}
		else
		{
			$viewType = $this->input->getCmd('format', 'html');
		}

		$view = $this->getView();
		$view->setTask($this->task);
		$view->setDoTask($this->doTask);

		// Get/Create the model
		if ($model = $this->getModel())
		{
			// Push the model into the view (as default)
			$view->setDefaultModel($model);
		}

		// Set the layout
		if (!is_null($this->layout))
		{
			$view->setLayout($this->layout);
		}

		$conf = $this->container->platform->getConfig();

		if ($cachable && ($viewType != 'feed') && ($conf->get('caching') >= 1))
		{
			// Get a JCache object
			$option = $this->input->get('option', 'com_foobar', 'cmd');

			// Set up a cache ID based on component, view, task and user group assignment
			$user = $this->container->platform->getUser();

			if ($user->guest)
			{
				$groups = [];
			}
			else
			{
				$groups = $user->groups;
			}

			$userId = $user->guest ? 0 : $user->id;

			switch ($this->userCaching)
			{
				case 0:
					// Developer chose to apply the same caching to everyone
					$groups = [];
					$userId = 0;
					break;

				case 1:
					// Developer chose to apply caching per user group membership only
					$userId = 0;
					break;
			}

			$importantParameters = [];

			// Set up safe URL parameters
			if (!is_array($urlparams))
			{
				$urlparams = [
					'option' => 'CMD',
					'view'   => 'CMD',
					'task'   => 'CMD',
					'format' => 'CMD',
					'layout' => 'CMD',
					'id'     => 'INT',
				];
			}

			if (is_array($urlparams))
			{
				/** @var CMSApplication $app */
				$app = Factory::getApplication();

				$registeredurlparams = null;

				if (!empty($app->registeredurlparams))
				{
					$registeredurlparams = $app->registeredurlparams;
				}
				else
				{
					$registeredurlparams = new \stdClass;
				}

				foreach ($urlparams as $key => $value)
				{
					// Add your safe url parameters with variable type as value {@see JFilterInput::clean()}.
					$registeredurlparams->$key = $value;

					// Add the URL-important parameters into the array
					$importantParameters[$key] = $this->input->get($key, null, $value);
				}

				$app->registeredurlparams = $registeredurlparams;
			}

			// Create the cache ID after setting the registered URL params, as they are used to generate the ID
			$cacheId = md5(serialize([
				Cache::makeId(), $view->getName(), $this->doTask, $groups, $userId, $importantParameters,
			]));

			// Get the cached view or cache the current view
			try
			{
				/** @var ViewController $cache */
				$cache = Factory::getCache($option, 'view');
				$cache->get($view, 'display', $cacheId);
			}
			catch (CacheExceptionInterface $e)
			{
				// Display without caching
				$view->display($tpl);
			}
		}
		else
		{
			// Display without caching
			$view->display($tpl);
		}
	}

	/**
	 * Alias to the display() task
	 *
	 * @codeCoverageIgnore
	 */
	public function main()
	{
		$this->display();
	}

	/**
	 * Returns a named Model object
	 *
	 * @param   string  $name    The Model name. If null we'll use the modelName
	 *                           variable or, if it's empty, the same name as
	 *                           the Controller
	 * @param   array   $config  Configuration parameters to the Model. If skipped
	 *                           we will use $this->config
	 *
	 * @return  Model  The instance of the Model known to this Controller
	 */
	public function getModel($name = null, $config = [])
	{
		if (!empty($name))
		{
			$modelName = $name;
		}
		elseif (!empty($this->modelName))
		{
			$modelName = $this->modelName;
		}
		else
		{
			$modelName = $this->view;
		}

		if (!array_key_exists($modelName, $this->modelInstances))
		{
			if (empty($config) && isset($this->config['modelConfig']))
			{
				$config = $this->config['modelConfig'];
			}

			if (empty($name))
			{
				$config['modelTemporaryInstance'] = true;
				$controllerName                   = $this->getName();

				if ($controllerName != $modelName)
				{
					$config['hash_view'] = $controllerName;
				}

			}
			else
			{
				// Other classes are loaded with persistent state disabled and their state/input blanked out
				$config['modelTemporaryInstance'] = false;
				$config['modelClearState']        = true;
				$config['modelClearInput']        = true;
			}

			$this->modelInstances[$modelName] = $this->container->factory->model(ucfirst($modelName), $config);
		}

		return $this->modelInstances[$modelName];
	}

	/**
	 * Returns a named View object
	 *
	 * @param   string  $name    The Model name. If null we'll use the modelName
	 *                           variable or, if it's empty, the same name as
	 *                           the Controller
	 * @param   array   $config  Configuration parameters to the Model. If skipped
	 *                           we will use $this->config
	 *
	 * @return  View  The instance of the Model known to this Controller
	 */
	public function getView($name = null, $config = [])
	{
		if (!empty($name))
		{
			$viewName = $name;
		}
		elseif (!empty($this->viewName))
		{
			$viewName = $this->viewName;
		}
		else
		{
			$viewName = $this->view;
		}

		if (!array_key_exists($viewName, $this->viewInstances))
		{
			if (empty($config) && isset($this->config['viewConfig']))
			{
				$config = $this->config['viewConfig'];
			}

			$viewType = $this->input->getCmd('format', 'html');

			// Get the model's class name
			$this->viewInstances[$viewName] = $this->container->factory->view($viewName, $viewType, $config);
		}

		return $this->viewInstances[$viewName];
	}

	/**
	 * Pushes a named view to the Controller
	 *
	 * @param   string  $viewName  The name of the View
	 * @param   View    $view      The actual View object to push
	 *
	 * @return  void
	 */
	public function setView($viewName, View &$view)
	{
		$this->viewInstances[$viewName] = $view;
	}

	/**
	 * Set the name of the view to be used by this Controller
	 *
	 * @param   string  $viewName  The name of the view
	 *
	 * @return  void
	 */
	public function setViewName($viewName)
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
	public function setModelName($modelName)
	{
		$this->modelName = $modelName;
	}

	/**
	 * Pushes a named model to the Controller
	 *
	 * @param   string  $modelName  The name of the Model
	 * @param   Model   $model      The actual Model object to push
	 *
	 * @return  void
	 */
	public function setModel($modelName, Model &$model)
	{
		$this->modelInstances[$modelName] = $model;
	}

	/**
	 * Method to get the controller name
	 *
	 * The controller name is set by default parsed using the classname, or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @return  string  The name of the controller
	 *
	 * @throws  CannotGetName  If it's impossible to determine the name and it's not set
	 */
	public function getName()
	{
		if (empty($this->name))
		{
			$r = null;

			if (!preg_match('/(.*)\\\\Controller\\\\(.*)/i', get_class($this), $r))
			{
				throw new CannotGetName(Text::_('LIB_FOF_CONTROLLER_ERR_GET_NAME'), 500);
			}

			$this->name = $r[2];
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
	 * Redirects the browser or returns false if no redirect is set.
	 *
	 * @return  boolean  False if no redirect exists.
	 */
	public function redirect()
	{
		if ($this->redirect)
		{
			$this->container->platform->redirect($this->redirect, 301, $this->message, $this->messageType);

			return true;
		}

		return false;
	}

	/**
	 * Register the default task to perform if a mapping is not found.
	 *
	 * @param   string  $method  The name of the method in the derived class to perform if a named task is not found.
	 *
	 * @return  Controller  This object to support chaining.
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
	 * @return  Controller  This object to support chaining.
	 */
	public function registerTask($task, $method)
	{
		if (in_array($method, $this->methods))
		{
			$this->taskMap[$task] = $method;
		}

		return $this;
	}

	/**
	 * Unregister (unmap) a task in the class.
	 *
	 * @param   string  $task  The task.
	 *
	 * @return  Controller  This object to support chaining.
	 */
	public function unregisterTask($task)
	{
		unset($this->taskMap[$task]);

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
		$previous          = $this->message;
		$this->message     = $text;
		$this->messageType = $type;

		return $previous;
	}

	/**
	 * Set a URL for browser redirection.
	 *
	 * @param   string  $url   URL to redirect to.
	 * @param   string  $msg   Message to display on redirect. Optional, defaults to value set internally by
	 *                         controller, if any.
	 * @param   string  $type  Message type. Optional, defaults to 'message' or the type set by a previous call to
	 *                         setMessage.
	 *
	 * @return  Controller   This object to support chaining.
	 */
	public function setRedirect($url, $msg = null, $type = null)
	{
		// If we're parsing a non-SEF URL decide whether to use JRoute or not
		if (strpos($url, 'index.php') === 0)
		{
			$isAdmin = $this->container->platform->isBackend();
			$auto    = false;

			if (($this->autoRouting == 2 || $this->autoRouting == 3) && $isAdmin)
			{
				$auto = true;
			}

			if (($this->autoRouting == 1 || $this->autoRouting == 3) && !$isAdmin)
			{
				$auto = true;
			}

			if ($auto)
			{
				$url = Route::_($url, false);
			}

			/**
			 * Joomla 4 does not add the base URI to redirections.
			 *
			 * This means that all bare redirects, e.g. to 'index.php?option=com_example', no longer work correctly.
			 *
			 * In the frontend, if your site is located in a subdirectory e.g. /foobar you get redirected to
			 * /index.php?option=com_example instead of /foobar/index.php?option=com_example
			 *
			 * In the backend, you're redirected to /index.php?option=com_example instead of the expected
			 * /administrator/index.php?option=com_example which breaks your application since the backend redirects to
			 * the frontend.
			 *
			 * This is an undocumented b/c break in Joomla 4. It even breaks some of the core components...
			 *
			 * The following code detects bare redirect URLs and adds the base URI path if auto-routing has been
			 * disabled, automatically fixing the observed issue. It only does that on Joomla 4 since adding the base
			 * URI on Joomla 3 can cause redirection problems.
			 */
			if (!$auto && version_compare(JVERSION, '3.999.999', 'gt'))
			{
				$url = Uri::base() . $url;
			}
		}

		// Set the redirection
		$this->redirect = $url;

		if ($msg !== null)
		{
			// Controller may have set this directly
			$this->message = $msg;
		}

		// Ensure the type is not overwritten by a previous call to setMessage.
		if (empty($this->messageType))
		{
			$this->messageType = 'message';
		}

		// If the type is explicitly set, set it.
		if (!empty($type))
		{
			$this->messageType = $type;
		}

		return $this;
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
	 * Provides CSRF protection through the forced use of a secure token. If the token doesn't match the one in the
	 * session we return false.
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 */
	protected function csrfProtection()
	{
		static $isCli = null, $isAdmin = null;

		$platform = $this->container->platform;

		if (is_null($isCli))
		{
			$isCli   = $platform->isCli();
			$isAdmin = $platform->isBackend();
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

		// Check for a session token
		$token    = $this->container->platform->getToken(false);
		$hasToken = $this->input->get($token, false, 'none') == 1;

		if (!$hasToken)
		{
			$hasToken = $this->input->get('_token', null, 'none') == $token;
		}

		if ($hasToken)
		{
			$view = $this->input->getCmd('view');
			$task = $this->input->getCmd('task');
			Log::add(
				"FOF: You are using a legacy session token in (view, task)=($view, $task). Support for legacy tokens will go away. Use form tokens instead.",
				Log::WARNING,
				'deprecated'
			);
		}

		// Check for a form token
		if (!$hasToken)
		{
			$token    = $this->container->platform->getToken(true);
			$hasToken = $this->input->get($token, false, 'none') == 1;

			if (!$hasToken)
			{
				$view = $this->input->getCmd('view');
				$task = $this->input->getCmd('task');
				Log::add(
					"FOF: You are using the insecure _token form variable in (view, task)=($view, $task). Support for it will go away. Submit a variable with the token as the name and a value of 1 instead.",
					Log::WARNING,
					'deprecated'
				);


				$hasToken = $this->input->get('_token', null, 'none') == $token;
			}
		}

		if (!$hasToken)
		{
			$platform->raiseError(403, Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));

			return false;
		}

		return true;
	}

	/**
	 * Triggers an object-specific event. The event runs both locally –if a suitable method exists– and through the
	 * Joomla! plugin system. A true/false return value is expected. The first false return cancels the event.
	 *
	 * EXAMPLE
	 * Component: com_foobar, Object name: item, Event: onBeforeSomething, Arguments: array(123, 456)
	 * The event calls:
	 * 1. $this->onBeforeSomething(123, 456)
	 * 2. $this->checkACL('@something') if there is no onBeforeSomething and the event starts with onBefore
	 * 3. Joomla! plugin event onComFoobarControllerItemBeforeSomething($this, 123, 456)
	 *
	 * @param   string  $event      The name of the event, typically named onPredicateVerb e.g. onBeforeKick
	 * @param   array   $arguments  The arguments to pass to the event handlers
	 *
	 * @return  bool
	 */
	protected function triggerEvent($event, array $arguments = [])
	{
		$result = true;

		// If there is an object method for this event, call it
		if (method_exists($this, $event))
		{
			switch (count($arguments))
			{
				case 0:
					$result = $this->{$event}();
					break;
				case 1:
					$result = $this->{$event}($arguments[0]);
					break;
				case 2:
					$result = $this->{$event}($arguments[0], $arguments[1]);
					break;
				case 3:
					$result = $this->{$event}($arguments[0], $arguments[1], $arguments[2]);
					break;
				case 4:
					$result = $this->{$event}($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
					break;
				case 5:
					$result = $this->{$event}($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
					break;
				default:
					$result = call_user_func_array([$this, $event], $arguments);
					break;
			}
		}
		// If there is no handler method perform a simple ACL check
		elseif (substr($event, 0, 8) == 'onBefore')
		{
			$task   = substr($event, 8);
			$result = $this->checkACL('@' . $task);
		}

		if ($result === false)
		{
			return false;
		}

		// All other event handlers live outside this object, therefore they need to be passed a reference to this
		// objects as the first argument.
		array_unshift($arguments, $this);

		// If we have an "on" prefix for the event (e.g. onFooBar) remove it and stash it for later.
		$prefix = '';

		if (substr($event, 0, 2) == 'on')
		{
			$prefix = 'on';
			$event  = substr($event, 2);
		}

		// Get the component/model prefix for the event
		$prefix .= 'Com' . ucfirst($this->container->bareComponentName) . 'Controller';
		$prefix .= ucfirst($this->getName());

		// The event name will be something like onComFoobarItemsBeforeSomething
		$event = $prefix . $event;

		// Call the Joomla! plugins
		$results = $this->container->platform->runPlugins($event, $arguments);

		if (!empty($results))
		{
			foreach ($results as $result)
			{
				if ($result === false)
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Checks if the current user has enough privileges for the requested ACL area.
	 *
	 * @param   string  $area  The ACL area, e.g. core.manage.
	 *
	 * @return  boolean  True if the user has the ACL privilege specified
	 */
	protected function checkACL($area)
	{
		$area = $this->getACLRuleFor($area);

		if (is_bool($area))
		{
			return $area;
		}

		if (in_array(strtolower($area), ['false', '0', 'no', '403']))
		{
			return false;
		}

		if (in_array(strtolower($area), ['true', '1', 'yes']))
		{
			return true;
		}

		if (in_array(strtolower($area), ['guest']))
		{
			return $this->container->platform->getUser()->guest;
		}

		if (in_array(strtolower($area), ['user']))
		{
			return !$this->container->platform->getUser()->guest;
		}

		if (empty($area))
		{
			return true;
		}

		return $this->container->platform->authorise($area, $this->container->componentName);
	}

	/**
	 * Resolves @task and &callback notations for ACL privileges
	 *
	 * @param   string  $area      The task notation to resolve
	 * @param   array   $oldAreas  Areas we've already been redirected from, used to detect circular references
	 *
	 * @return  mixed  The resolved ACL privilege
	 */
	protected function getACLRuleFor($area, $oldAreas = [])
	{
		// If it's a &notation return the callback result
		if (substr($area, 0, 1) == '&')
		{
			$oldAreas[] = $area;
			$method     = substr($area, 1);

			// Method not found? Assume true.
			if (!method_exists($this, $method))
			{
				return true;
			}

			$area = $this->$method();

			return $this->getACLRuleFor($area, $oldAreas);
		}

		// If it's not an @notation return the raw string
		if (substr($area, 0, 1) != '@')
		{
			return $area;
		}

		// Get the array index (other task)
		$index = substr($area, 1);

		// If the referenced task has no ACL map, return true
		if (!isset($this->taskPrivileges[$index]))
		{
			$index = strtolower($index);

			if (!isset($this->taskPrivileges[$index]))
			{
				return true;
			}
		}

		// Get the new ACL area
		$newArea = $this->taskPrivileges[$index];

		$oldAreas[] = $area;

		// Circular reference found
		if (in_array($newArea, $oldAreas))
		{
			return true;
		}

		// We've found an ACL privilege. Return it.
		if (substr($area, 0, 1) != '@')
		{
			return $newArea;
		}

		// We have another reference. Resolve it.
		return $this->getACLRuleFor($newArea, $oldAreas);
	}


}
