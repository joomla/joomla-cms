<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector classes for the JLog package.
 */

/**
 * JApplicationMock class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       11.1
 */
class JApplicationMock
{
	/**
	 * @var    array  The message queue.
	 * @since  11.1
	 */
	public $queue = array();

	/**
	 * Enqueue a system message.
	 *
	 * @param   string  $msg   The message to enqueue.
	 * @param   string  $type  The message type.
	 *
	 * @return  void
	 *
	 * @since    11.1
	 */
	public function enqueueMessage($msg, $type = 'message')
	{
		$this->queue[] = array('message' => $msg, 'type' => strtolower($type));
	}
}
