<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\MVC\Factory\MVCFactoryFactory;
use Joomla\Input\Input;

/**
 * Base class for a legacy Joomla Dispatcher
 *
 * Executes the single entry file of a legacy component.
 *
 * @since  __DEPLOY_VERSION__
 */
class LegacyDispatcher implements DispatcherInterface
{
	/**
	 * The application instance
	 *
	 * @var    CMSApplication
	 * @since  __DEPLOY_VERSION__
	 */
	private $app;

	/**
	 * Constructor for Dispatcher
	 *
	 * @param   CMSApplication  $app  The application instance
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(CMSApplication $app)
	{
		$this->app   = $app;
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
		$name = substr($this->app->scope, 4);

		// Will be removed once transition of all components is done
		if (file_exists(JPATH_COMPONENT . '/dispatcher.php'))
		{
			require_once JPATH_COMPONENT . '/dispatcher.php';

			$class = ucwords($name) . 'Dispatcher';

			// Check the class exists and implements the dispatcher interface
			if (!class_exists($class) || !in_array(DispatcherInterface::class, class_implements($class)))
			{
				throw new \LogicException(\JText::sprintf('JLIB_APPLICATION_ERROR_APPLICATION_LOAD', $this->app->scope), 500);
			}

			// Dispatch the component.
			$dispatcher = new $class($this->app, $this->app->input, new MVCFactoryFactory('Joomla\\Component\\' . ucwords($name)));
			$dispatcher->dispatch();

			return;
		}

		$path = JPATH_COMPONENT . '/' . $name . '.php';

		// If component file doesn't exist throw error
		if (!file_exists($path))
		{
			throw new \Exception(\JText::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
		}

		$lang = $this->app->getLanguage();

		// Load common and local language files.
		$lang->load($this->app->scope, JPATH_BASE, null, false, true) || $lang->load($this->app->scope, JPATH_COMPONENT, null, false, true);

		// Execute the component
		$loader = static function($path){
			require_once $path;
		};
		$loader($path);
	}
}
