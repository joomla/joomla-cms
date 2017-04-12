<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

use Joomla\CMS\Access\Exception\Notallowed;
use Joomla\CMS\Application\CmsApplication;
use Joomla\CMS\Controller\Controller;
use Joomla\CMS\Mvc\Factory\MvcFactoryInterface;
use Joomla\CMS\Mvc\Factory\MvcFactory;

defined('_JEXEC') or die;

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
	 * The JApplication instance
	 *
	 * @var    CmsApplication
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
	 * The extension namespace
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $namespace;

	/**
	 * The extension namespace
	 *
	 * @var    MvcFactoryInterface
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $factory;

	/**
	 * Constructor for Dispatcher
	 *
	 * @param   string               $namespace  Namespace of the Extension
	 * @param   CmsApplication       $app        The JApplication for the dispatcher
	 * @param   \JInput              $input      JInput
	 * @param   MvcFactoryInterface  $factory    The factory object for the component
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($namespace, CmsApplication $app, \JInput $input = null, MvcFactoryInterface $factory = null)
	{
		$this->namespace = rtrim($namespace, '\\') . '\\';
		$this->app       = $app;
		$this->input     = $input ? $input : $app->input;
		$this->factory   = $factory ? $factory : new MvcFactory($namespace, $this->app);

		$this->loadLanguage();
		$this->autoLoad();
	}

	/**
	 * Load the laguage
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	protected function loadLanguage()
	{
		// Load common and local language files.
		$this->app->getLanguage()->load($this->app->scope, JPATH_BASE, null, false, true) ||
		$this->app->getLanguage()->load($this->app->scope, JPATH_COMPONENT, null, false, true);
	}


	/**
	 * Autoload the extension files
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function autoLoad()
	{
		$autoLoader = include JPATH_LIBRARIES . '/vendor/autoload.php';

		// Autoload the component
		$autoLoader->setPsr4($this->namespace . 'Administrator\\', JPATH_ADMINISTRATOR . '/components/' . $this->app->scope);
		$autoLoader->setPsr4($this->namespace . 'Site\\', JPATH_BASE . '/components/' . $this->app->scope);
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
		// Check the user has permission to access this component if in the backend
		if ($this->app->isClient('administrator') && !$this->app->getIdentity()->authorise('core.manage', $this->app->scope))
		{
			throw new Notallowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
		}

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
			$controller = $this->input->get('controller', ucwords(substr($this->app->scope, 4)));
			$task       = $command;
		}

		// Execute the task for this component
		$controller = $this->getController($controller);
		$controller->execute($task);
		$controller->redirect();
	}

	/**
	 * The application the dispatcher is working with.
	 *
	 * @return  CmsApplication
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
	 * @param   string  $client  Optional client (like Admin, Site etc.)
	 * @param   array   $config  Optional controller config
	 *
	 * @return  Controller|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getController($name, $client = null, $config = array())
	{
		$client = $client ? $client : ucfirst($this->app->getName()) . '\\';

		$controllerName = $this->namespace . $client . 'Controller\\' . ucfirst($name);

		$controller = new $controllerName($config, $this->factory, $this->app, $this->input);

		return $controller;
	}

	/**
	 * Method to get factory object
	 *
	 * @return  MvcFactoryInterface
	 */
	public function getFactory()
	{
		return $this->factory;
	}
}
