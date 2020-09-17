<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Cli\Traits;

defined('_JEXEC') || die;

/**
 * Sometimes other extensions will try to enqueue messages to the application. Methods for those tasks only exists in
 * web applications, so we have to replicate their behavior in CLI environment or fatal errors will occur
 *
 * @package FOF30\Cli\Traits
 */
trait MessageAware
{
	/** @var array Queue holding all messages */
	protected $messageQueue = [];

	/**
	 * @param $msg
	 * @param $type
	 *
	 * @return null
	 */
	public function enqueueMessage($msg, $type)
	{
		// Don't add empty messages.
		if (trim($msg) === '')
		{
			return;
		}

		$message = ['message' => $msg, 'type' => strtolower($type)];

		if (!in_array($message, $this->messageQueue))
		{
			// Enqueue the message.
			$this->messageQueue[] = $message;
		}
	}

	/**
	 * Loosely based on Joomla getMessageQueue
	 *
	 * @param bool $clear
	 *
	 * @return array
	 */
	public function getMessageQueue($clear = false)
	{
		$messageQueue = $this->messageQueue;

		if ($clear)
		{
			$this->messageQueue = [];
		}

		return $messageQueue;
	}
}
