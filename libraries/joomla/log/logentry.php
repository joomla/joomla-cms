<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Log
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.log.log');

/**
 * Joomla! Log Entry class
 *
 * This class is designed to hold log entries for either writing to an engine, or for
 * supported engines, retrieving lists and building in memory (PHP based) search operations.
 *
 * @package     Joomla.Platform
 * @subpackage  Log
 * @since       11.1
 */
class JLogEntry
{
	/**
	 * Application responsible for log entry.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $category;

	/**
	 * The date the message was logged in ISO 8601 format.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $date = '0000-00-00T00:00Z';

	/**
	 * Message to be logged.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $message;

	/**
	 * The priority of the message to be logged.
	 *
	 * @see $_priorities
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $priority = JLog::INFO;

	/**
	 * List of available log priority levels [Based on the SysLog default levels].
	 *
	 * EMERGENCY  - The system is unusable.
	 * ALERT      - Action must be taken immediately.
	 * CRITICAL   - Critical conditions.
	 * ERROR      - Error conditions.
	 * WARNING    - Warning conditions.
	 * NOTICE     - Normal, but significant condition.
	 * INFO       - Informational message.
	 * DEBUG      - Debugging message.
	 *
	 * @var    array
	 * @since  11.1
	 */
	private $_priorities = array(
		JLog::EMERGENCY,
		JLog::ALERT,
		JLog::CRITICAL,
		JLog::ERROR,
		JLog::WARNING,
		JLog::NOTICE,
		JLog::INFO,
		JLog::DEBUG
	);

	/**
	 * Constructor
	 *
	 * @param   string  $message   The message to log.
	 * @param   string  $priority  Message priority based on {$this->_priorities}.
	 * @param   string  $category  Type of entry
	 * @param   string  $date      Date of entry (defaults to now if not specified or blank)
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function __construct($message, $priority = JLog::INFO, $category = '', $date = null)
	{
		$this->message = (string) $message;

		// Sanitize the priority.
		if (!in_array($priority, $this->_priorities, true)) {
			$priority = JLog::INFO;
		}
		$this->priority = $priority;

		// Sanitize category if it exists.
		if (!empty($category)) {
			$this->category = (string) strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $category));
		}

		// Get the date string in ISO 8601 format.
		$this->date = JFactory::getDate(($date ? $date : 'now'))->toISO8601();
	}
}
