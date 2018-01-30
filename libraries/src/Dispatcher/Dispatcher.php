<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Input\Input;
use Joomla\CMS\MVC\Factory\MVCFactory;

/**
 * Base class for a Joomla Dispatcher
 *
 * Dispatchers are responsible for checking ACL of a component if appropriate and
 * choosing an appropriate controller (and if necessary, a task) and executing it.
 *
 * @since  4.0.0
 */
abstract class Dispatcher implements DispatcherInterface
{
	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $option;

	/**
	 * The extension namespace
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $namespace;

	/**
	 * The application instance
	 *
	 * @var    CMSApplication
	 * @since  4.0.0
	 */
	protected $app;

	/**
	 * The input instance
	 *
	 * @var    Input
	 * @since  4.0.0
	 */
	protected $input;

	/**
	 * Constructor for Dispatcher
	 *
	 * @param   CMSApplication  $app    The application instance
	 * @param   Input           $input  The input instance
	 *
	 * @since   4.0.0
	 */
	public function __construct(CMSApplication $app, Input $input = null)
	{
		if (empty($this->namespace))
		{
			throw new \RuntimeException('Namespace can not be empty!');
		}

		$this->app   = $app;
		$this->input = $input ?: $app->input;

		// If option is not provided, detect it from dispatcher class name, ie ContentDispatcher
		if (empty($this->option))
		{
			$className = get_class($this);
			$pos       = strpos($className, 'Dispatcher');

			if ($pos !== false)
			{
				$this->option = 'com_' . strtolower(substr($className, 0, $pos));
			}
		}

		$this->loadLanguage();
	}

	/**
	 * Load the language
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function loadLanguage()
	{
		// Load common and local language files.
		$this->app->getLanguage()->load($this->option, JPATH_BASE, null, false, true) ||
		$this->app->getLanguage()->load($this->option, JPATH_COMPONENT, null, false, true);
	}

	/**
	 * Method to check component access permission
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function checkAccess()
	{
		// Check the user has permission to access this component if in the backend
		if ($this->app->isClient('administrator') && !$this->app->getIdentity()->authorise('core.manage', $this->option))
		{
			throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
		}
	}

	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function dispatch()
	{
		// Check component access permission
		$this->checkAccess();

		$command = $this->input->getCmd('task', 'display');

		// Check for a controller.task command.
		if (strpos($command, '.') !== false)
		{
			// Explode the controller.task command.
			list ($controller, $task) = explode('.', $command);

			$this->input->set('controller', $controller);
			$this->input->set('task', $task);
		}
		else
		{
			// Do we have a controller?
			$controller = $this->input->get('controller', 'display');
			$task       = $command;
		}

		// Build controller config data
		$config['option'] = $this->option;

		// Set name of controller if it is passed in the request
		if ($this->input->exists('controller'))
		{
			$config['name'] = strtolower($this->input->get('controller'));
		}

		// Execute the task for this component
		$controller = $this->getController($controller, ucfirst($this->app->getName()), $config);
		$controller->execute($task);
		$controller->redirect();
	}

	/**
	 * The application the dispatcher is working with.
	 *
	 * @return  CMSApplication
	 *
	 * @since   4.0.0
	 */
	protected function getApplication(): CMSApplication
	{
		return $this->app;
	}

	/**
	 * Get a controller from the component
	 *
	 * @param   string  $name    Controller name
	 * @param   string  $client  Optional client (like Administrator, Site etc.)
	 * @param   array   $config  Optional controller config
	 *
	 * @return  BaseController
	 *
	 * @since   4.0.0
	 */
	public function getController(string $name, string $client = '', array $config = array()): BaseController
	{
		// Set up the namespace
		$namespace = rtrim($this->namespace, '\\') . '\\';

		// Set up the client
		$client = $client ?: ucfirst($this->app->getName());

		$controllerClass = $namespace . $client . '\\Controller\\' . ucfirst($name) . 'Controller';

		if (!class_exists($controllerClass))
		{
			throw new \InvalidArgumentException(\JText::sprintf('JLIB_APPLICATION_ERROR_INVALID_CONTROLLER_CLASS', $controllerClass));
		}

		return new $controllerClass($config, new MVCFactory($namespace, $this->app), $this->app, $this->input);
	}
}
