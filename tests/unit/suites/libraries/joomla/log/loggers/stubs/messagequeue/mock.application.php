<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Inspector classes for the JLog package.
 */

/**
 * JApplicationMock class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Log
 * @since       1.7.0
 */
class JApplicationMock
{
	/**
	 * @var    array  The message queue.
	 * @since  1.7.0
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
	 * @since    1.7.0
	 */
	public function enqueueMessage($msg, $type = 'message')
	{
		$this->queue[] = array('message' => $msg, 'type' => strtolower($type));
	}
}
