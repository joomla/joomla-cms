<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;

/**
 * API Implementation for our dispatcher. It loads a component's administrator language files, and calls the API
 * Controller so that components that haven't implemented web services can add their own handling.
 *
 * @since  4.0.0
 */
final class ApiDispatcher extends ComponentDispatcher
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
		$task = $this->input->getCmd('task', 'display');

		// Build controller config data
		$config['option'] = $this->option;

		// Set name of controller if it is passed in the request
		if ($this->input->exists('controller'))
		{
			$config['name'] = strtolower($this->input->get('controller'));
		}

		$controller = $this->input->get('controller');
		$controller = $this->getController($controller, ucfirst($this->app->getName()), $config);

		$controller->execute($task);
	}
}
