<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\Notallowed;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Dispatcher\Dispatcher;

/**
 * Dispatcher class for com_contenthistory
 *
 * @since  4.0.0
 */
class ContenthistoryDispatcher extends Dispatcher
{
	/**
	 * The extension namespace
	 *
	 * @var    string
	 *
	 * @since  4.0.0
	 */
	protected $namespace = 'Joomla\\Component\\Contenthistory';

	/**
	 * Load the language
	 *
	 * @since   4.0.0
	 *
	 * @return  void
	 */
	protected function loadLanguage()
	{
		// Load common and local language files.
		$this->app->getLanguage()->load($this->option, JPATH_ADMINISTRATOR, null, false, true) ||
		$this->app->getLanguage()->load($this->option, JPATH_SITE, null, false, true);
	}

	/**
	 * Method to check component access permission
	 *
	 * @since   4.0.0
	 *
	 * @return  void
	 */
	protected function checkAccess()
	{
		if ($this->app->getIdentity()->guest)
		{
			throw new Notallowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
		}
	}

	/**
	 * Get a controller from the component. We want to proxy everything to the backend.
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
		$app = \Joomla\CMS\Application\CMSApplication::getInstance('Administrator');

		$controllerClass = $namespace . 'Administrator\\Controller\\' . ucfirst($name) . 'Controller';
		$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;

		if (!class_exists($controllerClass))
		{
			throw new \InvalidArgumentException(\JText::sprintf('JLIB_APPLICATION_ERROR_INVALID_CONTROLLER_CLASS', $controllerClass));
		}

		return new $controllerClass($config, new \Joomla\CMS\MVC\Factory\MVCFactory($namespace, $app), $app, $this->input);
	}
}
