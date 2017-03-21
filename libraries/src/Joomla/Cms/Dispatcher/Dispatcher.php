<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Dispatcher
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Dispatcher;

defined('_JEXEC') or die;

use Joomla\Cms\Controller\Controller;

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
	 * @var     JApplicationCms
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private $app;

	/**
	 * Constructor for JDispatcher
	 *
	 * @param   \JApplicationCms  $app  The \JApplication for the dispatcher
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
		if ($this->app->isClient('admin') && !\JFactory::getUser()->authorise('core.manage', $this->app->scope))
		{
			throw new \JAccessExceptionNotallowed(\JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		// Load common and local language files.
		$lang = \JFactory::getLanguage();
		$lang->load($this->app->scope, JPATH_BASE, null, false, true) || $lang->load($this->app->scope, JPATH_COMPONENT, null, false, true);

		// Execute the task for this component
		$controller = Controller::getInstance(ucwords(substr($this->app->scope, 4)));
		$controller->execute($this->app->input->get('task'));
		$controller->redirect();
	}
}
