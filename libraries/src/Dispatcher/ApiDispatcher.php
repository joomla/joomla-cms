<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\MVC\Factory\ApiMVCFactory;

/**
 * API Implementation for our dispatcher. It loads a component's administrator language files, and calls the API
 * Controller so that components that haven't implemented web services can add their own handling.
 *
 * @since  4.0.0
 */
final class ApiDispatcher implements DispatcherInterface
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
	 * @since  4.0.0
	 */
	protected $namespace;

	/**
	 * The CmsApplication instance
	 *
	 * @var    CMSApplication
	 *
	 * @since  4.0.0
	 */
	protected $app;

	/**
	 * The JApplication instance
	 *
	 * @var    \JInput
	 *
	 * @since  4.0.0
	 */
	protected $input;

	/**
	 * Constructor for Dispatcher
	 *
	 * @param   CMSApplication  $app    The JApplication for the dispatcher
	 * @param   \JInput         $input  JInput
	 *
	 * @since   4.0.0
	 */
	public function __construct(CMSApplication $app, \JInput $input = null)
	{
		$this->app    = $app;
		$this->input  = $input ?: $app->input;
		$this->option = $this->input->get('option');

		$db = Factory::getDbo();

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('namespace')))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('namespace') . ' IS NOT NULL AND ' . $db->quoteName('namespace') . ' != ""')
			->where($db->quoteName('element') . ' = ' . $db->quote($this->option));

		$db->setQuery($query);

		$this->namespace = $db->loadResult();

		if ($this->namespace === null)
		{
			throw new \RuntimeException('Namespace can not be empty!');
		}

		$this->loadLanguage();
	}

	/**
	 * Load the component's administrator language
	 *
	 * @since   4.0.0
	 *
	 * @return  void
	 */
	protected function loadLanguage()
	{
		// Load common and local language files.
		$this->app->getLanguage()->load($this->option, JPATH_BASE, null, false, true) ||
		$this->app->getLanguage()->load($this->option, JPATH_COMPONENT_ADMINISTRATOR, null, false, true);
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
		$command = $this->input->getCmd('task', 'display');
		$task    = $command;

		// Check for a controller.task command.
		if (strpos($command, '.') !== false)
		{
			// Explode the controller.task command.
			list ($controllerName, $task) = explode('.', $command);

			$this->input->set('controller', $controllerName);
			$this->input->set('task', $task);
		}

		// Build controller config data
		$config['option'] = $this->option;

		// Set name of controller if it is passed in the request
		if ($this->input->exists('controller'))
		{
			$config['name'] = strtolower($this->input->get('controller'));
		}

		// Execute the task for this component
		$namespace = rtrim($this->namespace, '\\') . '\\';
		$controller = new ApiController($config, new ApiMVCFactory($namespace), $this->app, $this->input);
		$controller->execute($task);
		$controller->redirect();
	}
}
