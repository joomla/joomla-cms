<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Registry\Registry;

/**
 * Base class for a Joomla Module Dispatcher.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class ModuleDispatcher extends Dispatcher implements ModuleDispatcherInterface
{
	/**
	 * The module instance
	 *
	 * @var    \stdClass
	 * @since  __DEPLOY_VERSION__
	 */
	protected $module;

	/**
	 * Dispatches the dispatcher.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function dispatch()
	{
		$this->loadLanguage();

		$displayData = $this->getLayoutData();

		// Abort when display data is false
		if ($displayData === false)
		{
			return;
		}

		// Execute the layout without the module context
		$loader = static function(array $displayData)
		{
			extract($displayData);
			require ModuleHelper::getLayoutPath($displayData['module']->module, $displayData['params']->get('layout', 'default'));
		};
		$loader($displayData);
	}

	/**
	 * Returns the layout data. This function can be overridden by subclasses to add more
	 * attributes for the layout.
	 *
	 * If false is returned, then it means that the dispatch process should be aborted.
	 *
	 * @return  array|false
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getLayoutData()
	{
		return ['module' => $this->module, 'params' => new Registry($this->module->params), 'app' => $this->app];
	}

	/**
	 * Load the language.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function loadLanguage()
	{
		$lang = $this->app->getLanguage();

		$coreLanguageDirectory      = JPATH_BASE;
		$extensionLanguageDirectory = dirname(JPATH_BASE . '/modules/' . $this->module->module);

		$langPaths = $lang->getPaths();

		// Only load the module's language file if it hasn't been already
		if (!$langPaths || (!isset($langPaths[$coreLanguageDirectory]) && !isset($langPaths[$extensionLanguageDirectory])))
		{
			// 1.5 or Core then 1.6 3PD
			$lang->load($this->module->module, $coreLanguageDirectory, null, false, true) ||
			$lang->load($this->module->module, $extensionLanguageDirectory, null, false, true);
		}
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
