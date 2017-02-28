<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Dispatcher;

use Joomla\Cms\Controller\Controller;

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
	 * @var     \JApplicationCms
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private $app;

	/**
	 * Constructor for Dispatcher
	 *
	 * @param   \JApplicationCms  $app  The JApplication for the dispatcher
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(\JApplicationCms $app)
	{
		$this->app  = $app;
	}

	/**
	 * Returns the namespace of the extension this dispatcher belongs to. If
	 * the returned string is empty, then a none namespaced extension is assumed.
	 *
	 * @return  string|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getNamespace()
	{
		return null;
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
			throw new \JAccessExceptionNotallowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
		}

		// Load common and local language files.
		$this->app->getLanguage()->load($this->app->scope, JPATH_BASE, null, false, true) ||
			$this->app->getLanguage()->load($this->app->scope, JPATH_COMPONENT, null, false, true);

		$namespace = $this->getNamespace();

		if (!$namespace)
		{
			// Execute the task for this component
			$controller = Controller::getInstance(ucwords(substr($this->app->scope, 4)));
		}
		else
		{
			// Register the namespace
			\JLoader::registerNamespace($namespace, JPATH_COMPONENT, false, false, 'psr4');

			$command = $this->app->input->get('task', 'display');
			$format  = $this->app->input->getWord('format', '');

			// The config for the controller
			$config = array();

			// Check for a controller.task command
			if (strpos($command, '.') !== false)
			{
				// Explode the controller.task command
				list ($type, $task) = explode('.', $command);

				// Define the controller class name
				$class = '\\Controller\\' . ucfirst($type) . ucfirst($format);

				// Reset the task without the controller context
				$this->app->input->set('task', $task);
			}
			else
			{
				// Define the View as we run the display command class name
				$class = '\\Controller\\View';
			}

			// Compile the full class name
			$class = \JPath::clean($namespace . '\\' . $class, '\\');

			$controller = null;

			// Check for a possible service from the container otherwise manually instantiate the class
			if (\JFactory::getContainer()->exists($class))
			{
				$controller = \JFactory::getContainer()->get($class);
			}
			else
			{
				$controller = new $class($config);
			}
		}

		// Execute the task
		$controller->execute($this->app->input->get('task'));

		// Redirect if needed
		$controller->redirect();
	}

	/**
	 * The application the dispatcher is working with.
	 *
	 * @return \JApplicationCms
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getApplication()
	{
		return $this->app;
	}
}
