<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Dispatcher;

use Joomla\Cms\Controller\Controller;
use Joomla\Input\Input;

defined('_JEXEC') or die;

/**
 * Base class for a Joomla Component Dispatcher
 *
 * Dispatchers are responsible for checking ACL of a component if appropriate and
 * choosing an appropriate controller (and if necessary, a task) and executing it.
 *
 * @since  __DEPLOY_VERSION__
 */
class Dispatcher implements DispatcherInterface
{
	/**
	 * An array contains all created dispatchers
	 *
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * The application object
	 *
	 * @var \JApplicationCms
	 */
	protected $app;

	/**
	 * The input object which will be passed to controller
	 *
	 * @var Input
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $input;

	/**
	 * The array store dispatcher/component config data
	 *
	 * @var array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $config;

	/**
	 * Method to get a dispatcher instance for a component.
	 *
	 * @param   string            $option  The component
	 * @param   array             $config  An array of optional constructor options.
	 * @param   Input             $input   The controller input
	 * @param   \JApplicationCms  $app     The JApplication for the dispatcher
	 *
	 * @return static
	 */
	public static function getInstance($option, array $config = array(), Input $input = null, \JApplicationCms $app = null)
	{
		if (!isset(self::$instances[$option]))
		{
			$app   = $app ? $app : \JFactory::getApplication();
			$input = $input ? $input : $app->input;

			if (isset($config['component_namespace']))
			{
				$cNamespace = $config['component_namespace'];
			}
			else
			{
				$cNamespace = 'Joomla\\Component\\' . ucfirst(substr($option, 4));
			}

			if ($app->isClient('site'))
			{
				$namespace = $cNamespace . '\\Site\\';
			}
			else
			{
				$namespace = $cNamespace . '\\Admin\\';
			}

			$config['option']    = $option;
			$config['namespace'] = $namespace;

			// Register component auto-loader
			$autoLoader = include JPATH_LIBRARIES . '/vendor/autoload.php';
			$autoLoader->setPsr4($cNamespace . '\\Site\\', JPATH_ROOT . '/components/' . $option);
			$autoLoader->setPsr4($cNamespace . '\\Admin\\', JPATH_ADMINISTRATOR . '/components/' . $option);

			// If component has dispatcher class, use it. Otherwise, use default dispatcher
			$class = $namespace . '\\Dispatcher';

			if (!class_exists($class))
			{
				$class = __CLASS__;
			}

			self::$instances[$option] = new $class($app, $input, $config);
		}

		return self::$instances[$option];
	}

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
		// If option is not provided in the config, try to get it from input
		if (!isset($config['option']))
		{
			$config['option'] = $input->getCmd('option');
		}

		// To config keys option and namespace is important, we need to valid and make sure it is available in config array
		if (empty($config['option']) || empty($config['namespace']))
		{
			throw new \InvalidArgumentException('option and namespace must be available for dispatcher constructor');
		}

		$this->app   = $app;
		$this->input = $input;

		// Populate default data if not propvided
		$this->input->def('option', $config['option']);

		if (!isset($config['load_language']))
		{
			$config['load_language'] = true;
		}

		if (!isset($config['redirect']))
		{
			$config['redirect'] = true;
		}

		$this->config = $config;

		// Load common and local component language files.
		if (!empty($this->config['load_language']))
		{
			$option   = $this->input->getCmd('option');
			$language = \JFactory::getApplication()->getLanguage();
			$language->load($option, JPATH_BASE, null, false, true) ||
			$language->load($option, JPATH_BASE . '/components/' . $option, null, false, true);
		}
	}

	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @throws \Exception
	 */
	public function dispatch()
	{
		$option = $this->input->getCmd('option');

		// Check the user has permission to access this component if in the backend
		if ($this->app->isClient('administrator') && !$this->app->getIdentity()->authorise('core.manage', $option))
		{
			throw new \Exception(\JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		// Get the controller base on input data, execute the task for this component
		$controller = $this->getController();
		$controller->execute($this->input->get('task', 'display'));

		// Redirect
		if ($this->config['redirect'])
		{
			$controller->redirect();
		}
	}

	/**
	 * The application the dispatcher is working with.
	 *
	 * @return Controller
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getController()
	{
		$input   = $this->input;
		$format  = $input->getWord('format');
		$command = $input->getCmd('task', 'display');

		if (strpos($command, '.') !== false)
		{
			// Explode the controller.task command.
			list ($name, $task) = explode('.', $command);

			$this->input->set('task', $task);
		}
		else
		{
			$name = 'Controller';
		}

		// Build list of possible controller classes, should we support override, too?
		$classes = array();

		if ($format)
		{
			$classes[] = $this->config['namespace'] . '\\Controller\\' . ucfirst($format) . '\\' . ucfirst($name);
		}

		$classes[] = $this->config['namespace'] . '\\Controller\\' . ucfirst($name);

		// Controller config array, base on component config
		$config         = $this->config;
		$config['name'] = $name;

		// Loop over possible create class and create the controller if class is found
		foreach ($classes as $class)
		{
			// Check for a possible service from the container otherwise manually instantiate the class
			if (\JFactory::getContainer()->exists($class))
			{
				return \JFactory::getContainer()->get($class);
			}

			if (class_exists($class))
			{
				return new $class($this->app, $this->input, $config);
			}
		}

		throw new \InvalidArgumentException(\JText::sprintf('JLIB_APPLICATION_ERROR_INVALID_CONTROLLER_CLASS', $class));
	}

	/**
	 * Set controller input
	 *
	 * @param   mixed  $input  The input data for the request
	 *
	 * @return  Input The original input, might be used for backup purpose
	 *
	 * @throws  \InvalidArgumentException
	 */
	public function setInput($input)
	{
		$oldInput = $this->input;

		if (is_array($input))
		{
			$this->input = new Input($input);
		}
		elseif ($input instanceof Input)
		{
			$this->input = $input;
		}
		else
		{
			throw new \InvalidArgumentException('Input needs to be an array or an object Input');
		}

		return $oldInput;
	}
}
