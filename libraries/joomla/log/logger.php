<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla! Logger Base Class
 *
 * This class is used to be the basis of logger classes to allow for defined functions
 * to exist regardless of the child class.
 *
 * @since  12.2
 */
abstract class JLogLogger
{
	/**
	 * Options array for the JLog instance.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $options = array();

	/**
	 * Translation array for JLogEntry priorities to text strings.
	 *
	 * @var    array
	 * @since  12.2
	 */
	protected $priorities = array(
		JLog::EMERGENCY => 'EMERGENCY',
		JLog::ALERT => 'ALERT',
		JLog::CRITICAL => 'CRITICAL',
		JLog::ERROR => 'ERROR',
		JLog::WARNING => 'WARNING',
		JLog::NOTICE => 'NOTICE',
		JLog::INFO => 'INFO',
		JLog::DEBUG => 'DEBUG',
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
	 * @param   JLogEntry  $entry  The log entry object to add to the log.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 * @throws  RuntimeException
	 */
	abstract public function addEntry(JLogEntry $entry);
}

/**
 * Deprecated class placeholder.  You should use JLogLogger instead.
 *
 * @since       11.1
 * @deprecated  13.3 (Platform) & 4.0 (CMS)
 * @codeCoverageIgnore
 */
abstract class JLogger extends JLogLogger
{
	/**
	 * Constructor.
	 *
	 * @param   array  &$options  Log object options.
	 *
	 * @since   11.1
	 * @deprecated  13.3
	 */
	public function __construct(array &$options)
	{
		JLog::add('JLogger is deprecated. Use JLogLogger instead.', JLog::WARNING, 'deprecated');
		parent::__construct($options);
	}
}
