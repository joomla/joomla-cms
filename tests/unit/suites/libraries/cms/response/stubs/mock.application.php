<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Response
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test mock class JResponseJson.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Response
 * @since       12.2
 */
class JApplicationResponseJsonMock
{
	/**
	 * @var    array  The message queue.
	 * @since  12.2
	 */
	public $queue = array();

	/**
	 * Enqueue a system message.
	 *
	 * @param   string  $msg   The message to enqueue.
	 * @param   string  $type  The message type. Default is message.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	public function enqueueMessage($msg, $type = 'message')
	{
		$this->queue[] = array('message' => $msg, 'type' => strtolower($type));
	}

	/**
	 * Get the system message queue.
	 *
	 * @return  array  The system message queue.
	 *
	 * @since   12.2
	 */
	public function getMessageQueue()
	{
		$queue = $this->queue;

		$this->queue = array();

		return $queue;
	}
}
