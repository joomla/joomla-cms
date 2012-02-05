<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.log.logger');

/**
 * Joomla MessageQueue logger class.
 *
 * This class is designed to output logs to a specific MySQL database table. Fields in this
 * table are based on the SysLog style of log output. This is designed to allow quick and
 * easy searching.
 *
 * @package     Joomla.Platform
 * @subpackage  Log
 * @since       11.1
 */
class JLoggerMessageQueue extends JLogger
{
	/**
	 * Method to add an entry to the log.
	 *
	 * @param   JLogEntry  $entry  The log entry object to add to the log.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function addEntry(JLogEntry $entry)
	{
		switch ($entry->priority)
		{
			case JLog::EMERGENCY:
			case JLog::ALERT:
			case JLog::CRITICAL:
			case JLog::ERROR:
				JFactory::getApplication()->enqueueMessage($entry->message, 'error');
				break;
			case JLog::WARNING:
				JFactory::getApplication()->enqueueMessage($entry->message, 'warning');
				break;
			case JLog::NOTICE:
				JFactory::getApplication()->enqueueMessage($entry->message, 'notice');
				break;
			case JLog::INFO:
				JFactory::getApplication()->enqueueMessage($entry->message, 'message');
				break;
			default:
				// Ignore other priorities.
				break;
		}
	}
}
