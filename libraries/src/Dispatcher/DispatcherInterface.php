<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Dispatcher
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Dispatcher;

defined('_JEXEC') or die;

/**
 * Joomla Platform CMS Dispatcher Interface
 *
 * @since  4.0.0
 */
interface DispatcherInterface
{
	/**
	 * Dispatch a controller task.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function dispatch();
}
