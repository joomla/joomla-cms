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
	 * The array store dispatcher/component config data
	 *
	 * @var array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $config;

	/**
	 * The input object which will be passed to controller
	 *
	 * @var Input
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $input;

	/**
	 * Method to get a dispatcher instance for a component.
	 *
	 * @param   string  $option  The component
	 * @param   array   $config  An array of optional constructor options.
	 *
	 * @return  static
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getInstance($option, array $config = array())
	{
		if (!isset(self::$instances[$option]))
		{
			// Normalize component config data, populate default config data if needed
			$config = self::normalizeConfig($option, $config);

			// Register component auto-loader
			$autoLoader = include JPATH_LIBRARIES . '/vendor/autoload.php';
			$autoLoader->setPsr4($config['frontend_namespace'] . '\\', JPATH_ROOT . '/components/' . $option);
			$autoLoader->setPsr4($config['backend_namespace'] . '\\', JPATH_ADMINISTRATOR . '/components/' . $option);

			// If component has dispatcher class, use it. Otherwise, use default dispatcher
			$class = $config['namespace'] . '\\Dispatcher\\Dispatcher';

			if (!class_exists($class))
			{
				$class = __CLASS__;
			}

			self::$instances[$option] = new $class($config);
		}

		return self::$instances[$option];
	}

	/**
	 * Constructor for Dispatcher
	 *
	 * @param   array  $config  An array of configuration issue
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(array $config = array())
	{
		$this->config = $config;

		if (isset($config['input']))
		{
			$this->setInput($config['input']);
		}
		else
		{
			$this->setInput(\JFactory::getApplication()->input);
		}

		// If component is not passed in the input, set it from config array
		$this->input->def('option', $config['option']);

		// Load common and local component language files.
		if ($this->config['load_language'])
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
		$app    = \JFactory::getApplication();
		$option = $this->input->getCmd('option');

		// Check the user has permission to access this component if in the backend
		if ($app->isClient('administrator') && !$app->getIdentity()->authorise('core.manage', $option))
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
				return new $class($this->input, $config);
			}
		}

		throw new \InvalidArgumentException(\JText::sprintf('JLIB_APPLICATION_ERROR_INVALID_CONTROLLER_CLASS', $class));
	}

	/**
	 * Set controller input
	 *
	 * @param   array|JInput  $input  An array or input object
	 *
	 * @return Input The original input, might be used for backup purpose
	 *
	 * @throws \InvalidArgumentException
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

	/**
	 * Normalize provided component config data
	 *
	 * @param   string  $option  The component name
	 * @param   array   $config  An optional array of configuration information
	 *
	 * @return array
	 */
	protected static function normalizeConfig($option, array $config = array())
	{
		$config['option'] = $option;

		// Component namespace
		if (!isset($config['component_namespace']))
		{
			$config['component_namespace'] = 'Joomla\\' . substr($option, 4);
		}

		if (!isset($config['frontend_namespace']))
		{
			$config['frontend_namespace'] = $config['component_namespace'] . '\\Site';
		}

		if (!isset($config['backend_namespace']))
		{
			$config['backend_namespace'] = $config['component_namespace'] . '\\Admin';
		}

		if (\JFactory::getApplication()->isClient('site'))
		{
			$config['namespace'] = $config['frontend_namespace'];
		}
		else
		{
			$config['namespace'] = $config['backend_namespace'];
		}

		// Redirect after executing a task?
		if (!isset($config['redirect']))
		{
			$config['redirect'] = true;
		}

		// Load component language?
		if (!isset($config['load_language']))
		{
			$config['load_language'] = true;
		}

		return $config;
	}
}
