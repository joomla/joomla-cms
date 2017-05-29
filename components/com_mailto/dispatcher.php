<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\Dispatcher;
use Joomla\CMS\Controller\Controller;

/**
 * Dispatcher class for com_mailto
 *
 * @since  __DEPLOY_VERSION__
 */
class MailtoDispatcher extends Dispatcher
{
	/**
	 * The extension namespace
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $namespace = 'Joomla\\Component\\Mailto';

	/**
	 * Get a controller from the component
	 *
	 * @param   string $name   Controller name
	 * @param   string $client Optional client (like Administrator, Site etc.)
	 * @param   array  $config Optional controller config
	 *
	 * @return  Controller
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getController($name, $client = null, $config = array())
	{
		$controller = parent::getController($name, $client, $config);
		$controller->registerDefaultTask('mailto');

		return $controller;
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
		$command = $this->input->getCmd('task', 'mailto');
		$this->input->set('task', $command);

		parent::dispatch();
	}
}
