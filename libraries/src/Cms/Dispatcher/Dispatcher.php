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
	 * @param   \JApplicationCms  $app  The JApplicationCms for the dispatcher
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(\JApplicationCms $app)
	{
		$this->app = $app;
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
		if ($this->app->isClient('admin') && !$this->app->getIdentity()->authorise('core.manage', $this->app->scope))
		{
			throw new \JAccessExceptionNotallowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
		}

		// Load common and local language files.
		$this->app->getLanguage()->load($this->app->scope, JPATH_BASE, null, false, true) ||
			$this->app->getLanguage()->load($this->app->scope, JPATH_COMPONENT, null, false, true);

		// Execute the task for this component
		$controller = Controller::getInstance(ucwords(substr($this->app->scope, 4)));
		$controller->execute($this->app->input->get('task'));
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
