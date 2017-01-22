<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Dispatcher
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Base class for a Joomla Dispatcher
 *
 * Dispatchers are responsible for checking ACL of a component if appropriate and
 * choosing an appropriate controller (and if necessary, a task) and executing it.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class JDispatcher implements JDispatcherInterface
{
	/**
	 * Router this rule belongs to
	 *
	 * @var    JApplicationCms
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public $app;

	/**
	 * Constructor.
	 *
	 * @param  JApplicationCms  $app
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct(JApplicationCms $app)
	{
		$this->app = $app;

		// Load common and local language files.
		$lang = JFactory::getLanguage();
		$lang->load($app->scope, JPATH_BASE, null, false, true) || $lang->load($app->scope, JPATH_COMPONENT, null, false, true);
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
		if ($this->app->isClient('admin') && !JFactory::getUser()->authorise('core.manage', $this->app->scope))
		{
			throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$controller = JControllerLegacy::getInstance(ucwords(substr($this->app->scope, 4)));
		$controller->execute($this->app->input->get('task'));
		$controller->redirect();
	}
}
