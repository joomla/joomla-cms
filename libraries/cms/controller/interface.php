<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla Platform CMS Interface
 *
 * @since  4.0
 */
interface JControllerInterface
{
	/**
	 * Execute a controller task.
	 *
	 * @param   string  $task  The task to perform.
	 *
	 * @return  mixed   The value returned by the called method.
	 *
	 * @since   4.0
	 * @throws  InvalidArgumentException
	 * @throws  RuntimeException
	 */
	public function execute($task);
}
