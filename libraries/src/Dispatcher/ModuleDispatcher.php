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
use Joomla\Input\Input;

/**
 * Base class for a Joomla Module Dispatcher.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class ModuleDispatcher implements ModuleDispatcherInterface
{
	/**
	 * The application instance
	 *
	 * @var    CMSApplication
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * The input instance
	 *
	 * @var    Input
	 * @since  __DEPLOY_VERSION__
	 */
	protected $input;

	/**
	 * The module instance
	 *
	 * @var    \stdClass
	 * @since  __DEPLOY_VERSION__
	 */
	protected $module;

	/**
	 * Constructor for Dispatcher
	 *
	 * @param   CMSApplication  $app    The application instance
	 * @param   Input           $input  The input instance
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(CMSApplication $app, Input $input = null)
	{
		$this->app   = $app;
		$this->input = $input ?: $app->input;

		$this->loadLanguage();
	}

	/**
	 * Load the language
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function loadLanguage()
	{
		$lang = $this->app->getLanguage();

		$coreLanguageDirectory      = JPATH_BASE;
		$extensionLanguageDirectory = dirname(JPATH_BASE . '/modules/' . $this->module);

		$langPaths = $lang->getPaths();

		// Only load the module's language file if it hasn't been already
		if (!$langPaths || (!isset($langPaths[$coreLanguageDirectory]) && !isset($langPaths[$extensionLanguageDirectory])))
		{
			// 1.5 or Core then 1.6 3PD
			$lang->load($this->module, $coreLanguageDirectory, null, false, true) ||
			$lang->load($this->module, $extensionLanguageDirectory, null, false, true);
		}
	}

	/**
	 * The application the dispatcher is working with.
	 *
	 * @return  CMSApplication
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getApplication(): CMSApplication
	{
		return $this->app;
	}

	/**
	 * Sets the module.
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
