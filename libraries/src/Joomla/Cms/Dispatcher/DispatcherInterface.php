<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Dispatcher;

defined('_JEXEC') or die;

/**
 * Joomla Platform CMS Dispatcher Interface
 *
 * @since  __DEPLOY_VERSION__
 */
interface DispatcherInterface
{
	/**
	 * Dispatch a controller task.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function dispatch();
}
