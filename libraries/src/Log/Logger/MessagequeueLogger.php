<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Log\Logger;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Log\Log;
use Joomla\CMS\Log\LogEntry;
use Joomla\CMS\Log\Logger;

/**
 * Joomla MessageQueue logger class.
 *
 * This class is designed to output logs to a specific MySQL database table. Fields in this
 * table are based on the Syslog style of log output. This is designed to allow quick and
 * easy searching.
 *
 * @since  11.1
 */
class MessagequeueLogger extends Logger
{
	/**
	 * Method to add an entry to the log.
	 *
	 * @param   LogEntry  $entry  The log entry object to add to the log.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function addEntry(LogEntry $entry)
	{
		switch ($entry->priority)
		{
			case Log::EMERGENCY:
			case Log::ALERT:
			case Log::CRITICAL:
			case Log::ERROR:
				\JFactory::getApplication()->enqueueMessage($entry->message, 'error');
				break;
			case Log::WARNING:
				\JFactory::getApplication()->enqueueMessage($entry->message, 'warning');
				break;
			case Log::NOTICE:
				\JFactory::getApplication()->enqueueMessage($entry->message, 'notice');
				break;
			case Log::INFO:
				\JFactory::getApplication()->enqueueMessage($entry->message, 'message');
				break;
			default:
				// Ignore other priorities.
				break;
		}
	}
}
