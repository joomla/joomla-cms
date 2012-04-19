<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

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
	 * @var    string
	 * @since  11.1
	 */
	public $category;

	/**
	 * The date the message was logged.
	 * @var    JDate
	 * @since  11.1
	 */
	public $date;

	/**
	 * Message to be logged.
	 * @var    string
	 * @since  11.1
	 */
	public $message;

	/**
	 * The priority of the message to be logged.
	 * @var    string
	 * @since  11.1
	 * @see    $priorities
	 */
	public $priority = JLog::INFO;

	/**
	 * List of available log priority levels [Based on the Syslog default levels].
	 * @var    array
	 * @since  11.1
	 */
	protected $priorities = array(
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
	 * @param   string  $priority  Message priority based on {$this->priorities}.
	 * @param   string  $category  Type of entry
	 * @param   string  $date      Date of entry (defaults to now if not specified or blank)
	 *
	 * @since   11.1
	 */
	public function __construct($message, $priority = JLog::INFO, $category = '', $date = null)
	{
		$this->message = (string) $message;

		// Sanitize the priority.
		if (!in_array($priority, $this->priorities, true))
		{
			$priority = JLog::INFO;
		}
		$this->priority = $priority;

		// Sanitize category if it exists.
		if (!empty($category))
		{
			$this->category = (string) strtolower(preg_replace('/[^A-Z0-9_\.-]/i', '', $category));
		}

		// Get the date as a JDate object.
		$this->date = new JDate($date ? $date : 'now');
	}
}
