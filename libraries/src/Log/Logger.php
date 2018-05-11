<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Log;

defined('JPATH_PLATFORM') or die;

/**
 * Joomla! Logger Base Class
 *
 * This class is used to be the basis of logger classes to allow for defined functions
 * to exist regardless of the child class.
 *
 * @since  12.2
 */
abstract class Logger
{
	/**
	 * Options array for the JLog instance.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $options = array();

	/**
	 * Translation array for LogEntry priorities to text strings.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $priorities = array(
		Log::EMERGENCY => 'EMERGENCY',
		Log::ALERT     => 'ALERT',
		Log::CRITICAL  => 'CRITICAL',
		Log::ERROR     => 'ERROR',
		Log::WARNING   => 'WARNING',
		Log::NOTICE    => 'NOTICE',
		Log::INFO      => 'INFO',
		Log::DEBUG     => 'DEBUG',
	);

	/**
	 * Constructor.
	 *
	 * @param   array  &$options  Log object options.
	 *
	 * @since   12.2
	 */
	public function __construct(array &$options)
	{
		// Set the options for the class.
		$this->options = & $options;
	}

	/**
	 * Method to add an entry to the log.
	 *
	 * @param   LogEntry  $entry  The log entry object to add to the log.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 * @throws  \RuntimeException
	 */
	abstract public function addEntry(LogEntry $entry);
}
