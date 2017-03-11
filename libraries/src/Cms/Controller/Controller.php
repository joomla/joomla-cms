<?php
/**
 * @package     Joomla.Cms
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Controller;

defined('JPATH_PLATFORM') or die;

use Joomla\Cms\Model\Model;
use Joomla\Cms\View\View;
use Joomla\Input\Input;

/**
 * Base class for a Joomla Controller
 *
 * Controller (Controllers are where you put all the actual code.) Provides basic
 * functionality, such as rendering views (aka displaying templates).
 *
 * @since  2.5.5
 */
class Controller implements ControllerInterface
{
	/**
	 * The name of component controller belong to
	 *
	 * @var string
	 */
	protected $option;

	/**
	 * The name of the controller
	 *
	 * @var    array
	 * @since  3.0
	 */
	protected $name;

	/**
	 * The application object
	 *
	 * @var \JApplicationCms
	 */
	protected $app;

	/**
	 * Hold an Input object for easier access to the input variables.
	 *
	 * @var    Input
	 * @since  3.0
	 */
	protected $input;

	/**
	 * The controller config data, usually passed from component dispatcher
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * Array of class methods to call for a given task.
	 *
	 * @var    array
	 * @since  3.0
	 */
	protected $taskMap = array();

	/**
	 * Array of class methods
	 *
	 * @var    array
	 * @since  3.0
	 */
	protected $methods = array();

	/**
	 * Current or most recently performed task.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $task;

	/**
	 * Redirect message.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $message;

	/**
	 * Redirect message type.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $messageType = 'message';

	/**
	 * URL for redirection.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $redirect;
	
	/**
	 * Constructor for Dispatcher
	 *
	 * @param   \JApplicationCms  $app     The JApplication for the dispatcher
	 * @param   Input             $input   The controller input
	 * @param   array             $config  An array of optional constructor options
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct(\JApplicationCms $app, Input $input, array $config)
	{
		if (!isset($config['namespace']))
		{
			throw new \InvalidArgumentException('namespace is required config key to create controller');
		}

		$this->app    = $app;
		$this->input  = $input;
		$this->option = $input->getCmd('option');

		// Check and make sure the component is enabled
		if (!\JComponentHelper::isEnabled($this->option))
		{
			throw new \RuntimeException(sprintf('Component %s not found', $this->option), 404);
		}

		// Determine the methods to exclude from the base class.
		$xMethods = get_class_methods(__CLASS__);

		// Get the public methods in this class using reflection.
		$r        = new \ReflectionClass($this);
		$rMethods = $r->getMethods(\ReflectionMethod::IS_PUBLIC);

		foreach ($rMethods as $rMethod)
		{
			$mName = $rMethod->getName();

			// Add default display method if not explicitly declared.
			if (!in_array($mName, $xMethods) || $mName == 'display')
			{
				$this->methods[] = strtolower($mName);

				// Auto register the methods as tasks.
				$this->taskMap[strtolower($mName)] = $mName;
			}
		}

		// Set the controller name
		if (empty($this->name))
		{
			if (isset($config['name']))
			{
				$this->name = $config['name'];
			}
			else
			{
				$this->name = $this->getName();
			}
		}

		// Default view
		if (!isset($config['default_view']))
		{
			$config['default_view'] = ucfirst(substr($this->option, 4));
		}

		// If the default task is set, register it as such
		if (array_key_exists('default_task', $config))
		{
			$this->registerDefaultTask($config['default_task']);
		}
		else
		{
			$this->registerDefaultTask('display');
		}

		$this->config = $config;
	}

	/**
	 * Method to check whether an ID is in the edit list.
	 *
	 * @param   string   $context  The context for the session storage.
	 * @param   integer  $id       The ID of the record to add to the edit list.
	 *
	 * @return  boolean  True if the ID is in the edit list.
	 *
	 * @since   3.0
	 */
	protected function checkEditId($context, $id)
	{
		if ($id)
		{
			$values = (array) $this->app->getUserState($context . '.id');

			$result = in_array((int) $id, $values);

			if (defined('JDEBUG') && JDEBUG)
			{
				$this->app->getLogger()->info(
					sprintf(
						'Checking edit ID %s.%s: %d %s',
						$context,
						$id,
						(int) $result,
						str_replace("\n", ' ', print_r($values, 1))
					),
					array('category' => 'controller')
				);
			}

			return $result;
		}

		// No id for a new item.
		return true;
	}

	/**
	 * Typical view method for MVC based architecture
	 *
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlParams  An array of safe url parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  static  A Controller object to support chaining.
	 *
	 * @since   3.0
	 */
	public function display($cachable = false, $urlParams = array())
	{
		$document = \JFactory::getDocument();

		if ($document instanceof \JDocument)
		{
			$viewType = $document->getType();
		}
		else
		{
			$viewType = $this->input->getCmd('format', 'html');
		}

		$viewName   = $this->input->getString('view', $this->config['default_view']);
		$viewLayout = $this->input->getString('layout', 'default');

		$view = $this->getView($viewName, $viewType, '', array('layout' => $viewLayout));

		// Get/Create the model
		if ($model = $this->getModel($viewName))
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		$view->document = $document;

		// Display the view
		if ($cachable && $viewType != 'feed' && \JFactory::getConfig()->get('caching') >= 1)
		{
			$option = $this->input->get('option');

			if (is_array($urlParams))
			{
				if (!empty($this->app->registeredurlparams))
				{
					$registeredUrlParams = $this->app->registeredurlparams;
				}
				else
				{
					$registeredUrlParams = new \stdClass;
				}

				foreach ($urlParams as $key => $value)
				{
					// Add your safe url parameters with variable type as value {@see \JFilterInput::clean()}.
					$registeredUrlParams->$key = $value;
				}

				$this->app->registeredurlparams = $registeredUrlParams;
			}

			try
			{
				/** @var \JCacheControllerView $cache */
				$cache = \JFactory::getCache($option, 'view');
				$cache->get($view, 'display');
			}
			catch (\JCacheException $exception)
			{
				$view->display();
			}
		}
		else
		{
			$view->display();
		}

		return $this;
	}

	/**
	 * Execute a task by triggering a method in the derived class.
	 *
	 * @param   string  $task  The task to perform. If no matching task is found, the '__default' task is executed, if defined.
	 *
	 * @return  mixed   The value returned by the called method.
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function execute($task)
	{
		$this->task = $task;

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
			throw new \Exception(\JText::sprintf('JLIB_APPLICATION_ERROR_TASK_NOT_FOUND', $task), 404);
		}

		return $this->$doTask();
	}

	/**
	 * Method to get a model object
	 *
	 * @param   string  $name       The model name. Optional.
	 * @param   string  $namespace  The base namespace to get model class. Optional.
	 * @param   array   $config     Configuration array for model. Optional.
	 *
	 * @return  Model|boolean  Model object on success; otherwise false on failure.
	 *
	 * @since   3.0
	 */
	public function getModel($name = '', $namespace = '', array $config = array())
	{
		if (empty($name))
		{
			$name = $this->getName();
		}

		if (empty($namespace))
		{
			$namespace = $this->config['namespace'];
		}

		$name       = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$modelClass = $namespace . '\\Model\\' . ucfirst($name);

		// If model class exists, create the model and build state
		if (class_exists($modelClass))
		{
			$config['option'] = $this->option;
			$config['name']   = $name;
			$config += $this->config;

			if (empty($config['ignore_request']))
			{
				$config['state'] = $this->buildModelState();
			}

			return new $modelClass($config);
		}

		// Model class not found, return false

		return false;
	}

	/**
	 * Method to get the controller name
	 *
	 * The dispatcher name is set by default parsed using the classname, or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @return  string  The name of the controller
	 *
	 * @since   3.0
	 */
	public function getName()
	{
		if (!$this->name)
		{
			$this->name = (new \ReflectionClass($this))->getShortName();
		}

		return $this->name;
	}

	/**
	 * Get the last task that is being performed or was most recently performed.
	 *
	 * @return  string  The task that is being performed or was most recently performed.
	 *
	 * @since   3.0
	 */
	public function getTask()
	{
		return $this->task;
	}

	/**
	 * Gets the available tasks in the controller.
	 *
	 * @return  array  Array[i] of task names.
	 *
	 * @since   3.0
	 */
	public function getTasks()
	{
		return $this->methods;
	}

	/**
	 * Method to get a reference to the current view and load it if necessary.
	 *
	 * @param   string  $name       The view name. Optional, defaults to the controller name.
	 * @param   string  $type       The view type. Optional.
	 * @param   string  $namespace  The base namespace to get the view class. Optional.
	 * @param   array   $config     Configuration array for view. Optional.
	 *
	 * @return  View  Reference to the view or an error.
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function getView($name = '', $type = 'Html', $namespace = '', array $config = array())
	{
		if (empty($name))
		{
			$name = $this->getName();
		}

		if (empty($type))
		{
			$type = 'Html';
		}

		if (empty($namespace))
		{
			$namespace = $this->config['namespace'];
		}

		$viewName  = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$viewType  = preg_replace('/[^A-Z0-9_]/i', '', $type);
		$viewClass = $namespace . '\\View\\' . ucfirst($viewName) . '\\' . ucfirst($viewType);

		if (class_exists($viewClass))
		{
			$config['option'] = $this->option;
			$config['name']   = $viewName;
			$config += $this->config;

			if (empty($config['template_path']))
			{
				$paths    = array();
				$template = $this->app->getTemplate();

				if ($this->app->isClient('site'))
				{
					$paths[] = JPATH_ROOT . '/templates/' . $template . '/html/' . $this->option . '/' . ucfirst($viewName);
					$paths[] = JPATH_ROOT . '/components/' . $this->option . '/View/' . ucfirst($viewName) . '/tmpl';
				}
				else
				{
					$paths[] = JPATH_ADMINISTRATOR . '/templates/' . $template . '/html/' . $this->option . '/' . ucfirst($viewName);
					$paths[] = JPATH_ADMINISTRATOR . '/components/' . $this->option . '/View/' . ucfirst($viewName) . '/tmpl';
				}

				$config['template_path'] = $paths;
			}

			return new $viewClass($config);
		}

		// No view class found, throws error
		$response = 500;

		/*
		 * With URL rewriting enabled on the server, all client requests for non-existent files are being
		 * forwarded to Joomla.  Return a 404 response here and assume the client was requesting a non-existent
		 * file for which there is no view type that matches the file's extension (the most likely scenario).
		 */
		if ($this->app->get('sef_rewrite'))
		{
			$response = 404;
		}

		throw new \Exception(\JText::sprintf('JLIB_APPLICATION_ERROR_VIEW_CLASS_NOT_FOUND', $viewClass), $response);
	}

	/**
	 * Method to add a record ID to the edit list.
	 *
	 * @param   string   $context  The context for the session storage.
	 * @param   integer  $id       The ID of the record to add to the edit list.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function holdEditId($context, $id)
	{
		$values = (array) $this->app->getUserState($context . '.id');

		// Add the id to the list if non-zero.
		if (!empty($id))
		{
			$values[] = (int) $id;
			$values   = array_unique($values);
			$this->app->setUserState($context . '.id', $values);

			if (defined('JDEBUG') && JDEBUG)
			{
				$this->app->getLogger()->info(
					sprintf(
						'Holding edit ID %s.%s %s',
						$context,
						$id,
						str_replace("\n", ' ', print_r($values, 1))
					),
					array('category' => 'controller')
				);
			}
		}
	}

	/**
	 * Redirects the browser or returns false if no redirect is set.
	 *
	 * @return  boolean  False if no redirect exists.
	 *
	 * @since   3.0
	 */
	public function redirect()
	{
		if ($this->redirect)
		{
			// Enqueue the redirect message
			$this->app->enqueueMessage($this->message, $this->messageType);

			// Execute the redirect
			$this->app->redirect($this->redirect);
		}

		return false;
	}

	/**
	 * Register the default task to perform if a mapping is not found.
	 *
	 * @param   string  $method  The name of the method in the derived class to perform if a named task is not found.
	 *
	 * @return  static  A Controller object to support chaining.
	 *
	 * @since   3.0
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
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   3.0
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
	 * @return  static  This object to support chaining.
	 *
	 * @since   3.0
	 */
	public function unregisterTask($task)
	{
		unset($this->taskMap[strtolower($task)]);

		return $this;
	}

	/**
	 * Method to check whether an ID is in the edit list.
	 *
	 * @param   string   $context  The context for the session storage.
	 * @param   integer  $id       The ID of the record to add to the edit list.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function releaseEditId($context, $id)
	{
		$values = (array) $this->app->getUserState($context . '.id');

		// Do a strict search of the edit list values.
		$index = array_search((int) $id, $values, true);

		if (is_int($index))
		{
			unset($values[$index]);
			$this->app->setUserState($context . '.id', $values);

			if (defined('JDEBUG') && JDEBUG)
			{
				$this->app->getLogger()->info(
					sprintf(
						'Releasing edit ID %s.%s %s',
						$context,
						$id,
						str_replace("\n", ' ', print_r($values, 1))
					),
					array('category' => 'controller')
				);
			}
		}
	}

	/**
	 * Sets the internal message that is passed with a redirect
	 *
	 * @param   string  $text  Message to display on redirect.
	 * @param   string  $type  Message type. Optional, defaults to 'message'.
	 *
	 * @return  string  Previous message
	 *
	 * @since   3.0
	 */
	public function setMessage($text, $type = 'message')
	{
		$previous          = $this->message;
		$this->message     = $text;
		$this->messageType = $type;

		return $previous;
	}

	/**
	 * Checks for a form token in the request.
	 *
	 * Use in conjunction with \JHtml::_('form.token') or \JSession::getFormToken.
	 *
	 * @param   string   $method    The request method in which to look for the token key.
	 * @param   boolean  $redirect  Whether to implicitly redirect user to the referrer page on failure or simply return false.
	 *
	 * @return  boolean  True if found and valid, otherwise return false or redirect to referrer page.
	 *
	 * @since   3.7.0
	 * @see     \JSession::checkToken()
	 */
	public function checkToken($method = 'post', $redirect = true)
	{
		$valid = \JSession::checkToken($method);

		if (!$valid && $redirect)
		{
			$referrer = $this->input->server->getString('HTTP_REFERER');

			if (!\JUri::isInternal($referrer))
			{
				$referrer = 'index.php';
			}

			$this->app->enqueueMessage(\JText::_('JINVALID_TOKEN_NOTICE'), 'warning');
			$this->app->redirect($referrer);
		}

		return $valid;
	}

	/**
	 * Set a URL for browser redirection.
	 *
	 * @param   string  $url   URL to redirect to.
	 * @param   string  $msg   Message to display on redirect. Optional, defaults to value set internally by controller, if any.
	 * @param   string  $type  Message type. Optional, defaults to 'message' or the type set by a previous call to setMessage.
	 *
	 * @return  static  This object to support chaining.
	 *
	 * @since   3.0
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
	 * Method to build model state
	 *
	 * @return \JObject
	 */
	protected function buildModelState()
	{
		$state = new \JObject;

		// Task is a reserved state
		$state->set('task', $this->task);

		// Let's get the application object and set menu information if it's available
		$menu = $this->app->getMenu();

		if (is_object($menu))
		{
			if ($item = $menu->getActive())
			{
				$params = $menu->getParams($item->id);

				// Set default state data
				$state->set('parameters.menu', $params);
			}
		}

		return $state;
	}
}
