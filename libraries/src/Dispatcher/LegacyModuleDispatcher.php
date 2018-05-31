<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\Registry\Registry;

/**
 * Base class for a legacy Joomla Dispatcher
 *
 * Executes the single entry file of a legacy component.
 *
 * @since  __DEPLOY_VERSION__
 */
class LegacyModuleDispatcher implements ModuleDispatcherInterface
{
	/**
	 * The module instance
	 *
	 * @var    \stdClass
	 * @since  __DEPLOY_VERSION__
	 */
	private $module;

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
		$path = JPATH_BASE . '/modules/' . $this->module->module . '/' . $this->module->module . '.php';

		if (!file_exists($path))
		{
			return;
		}

		$lang = $this->app->getLanguage();

		$coreLanguageDirectory      = JPATH_BASE;
		$extensionLanguageDirectory = dirname($path);

		$langPaths = $lang->getPaths();

		// Only load the module's language file if it hasn't been already
		if (!$langPaths || (!isset($langPaths[$coreLanguageDirectory]) && !isset($langPaths[$extensionLanguageDirectory])))
		{
			// 1.5 or Core then 1.6 3PD
			$lang->load($this->module->module, $coreLanguageDirectory, null, false, true) ||
			$lang->load($this->module->module, $extensionLanguageDirectory, null, false, true);
		}

		// Execute the module
		$loader = static function($path, $module, $app, $template, $params) {
			include $path;
		};
		$loader($path, $this->module, $this->app, $this->app->getTemplate(), new Registry($this->module->params));
	}

	/**
	 * Returns the module.
	 *
	 * @return  \stdClass
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getModule()
	{
		return $this->module;
	}

	/**
	 * Sets the module.
	 *
	 * @param   \stdClass  $module  The module
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setModule(\stdClass $module)
	{
		$this->module = $module;
	}
}
