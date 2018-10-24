<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

defined('_JEXEC') or die;

/**
 * Base class for a Joomla Module Dispatcher
 *
 * Executes the single entry file of a module.
 *
 * @since  __DEPLOY_VERSION__
 */
class ModuleDispatcher extends AbstractModuleDispatcher
{
	/**
	 * Dispatches the dispatcher.
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

		$this->loadLanguage();

		// Execute the layout without the module context
		$loader = static function($path, array $displayData)
		{
			extract($displayData);
			include $path;
		};

		$loader($path, $this->getLayoutData());
	}
}
