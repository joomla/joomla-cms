<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\Notallowed;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Controller\Controller;
use Joomla\CMS\Mvc\Factory\MvcFactory;

/**
 * Base class for a Joomla Dispatcher
 *
 * Dispatchers are responsible for checking ACL of a component if appropriate and
 * choosing an appropriate controller (and if necessary, a task) and executing it.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class Dispatcher implements DispatcherInterface
{
	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $option;

	/**
	 * The extension namespace
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $namespace;

	/**
	 * The CmsApplication instance
	 *
	 * @var    CMSApplication
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * The JApplication instance
	 *
	 * @var    \JInput
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $input;

	/**
	 * Constructor for Dispatcher
	 *
	 * @param   CMSApplication  $app    The JApplication for the dispatcher
	 * @param   \JInput         $input  JInput
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(CMSApplication $app, \JInput $input = null)
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
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
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
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	protected function checkAccess()
	{
		// Check the user has permission to access this component if in the backend
		if ($this->app->isClient('administrator') && !$this->app->getIdentity()->authorise('core.manage', $this->option))
		{
			throw new Notallowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
		}
	}

	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getApplication()
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
	 * @return  Controller
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getController($name, $client = null, $config = array())
	{
		// Set up the namespace
		$namespace = rtrim($this->namespace, '\\') . '\\';

		// Set up the client
		$client = $client ? $client : ucfirst($this->app->getName());

		$controllerClass = $namespace . $client . '\\Controller\\' . ucfirst($name) . 'Controller';

		// @todo Remove me when core extensions are converted
		if (!class_exists($controllerClass))
		{
			$controllerClass = $namespace . $client . '\\Controller\\' . ucfirst($name);
		}

		// @todo Remove me when core extensions are converted
		if (!class_exists($controllerClass) && $name == 'display')
		{
			$controllerClass = $namespace . $client . '\\Controller\\Controller';
		}

		if (!class_exists($controllerClass))
		{
			throw new \InvalidArgumentException(\JText::sprintf('JLIB_APPLICATION_ERROR_INVALID_CONTROLLER_CLASS', $controllerClass));
		}

		$controller = new $controllerClass($config, new MvcFactory($namespace, $this->app), $this->app, $this->input);

		return $controller;
	}
}
