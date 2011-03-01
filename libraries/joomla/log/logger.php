<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Log
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla! Logging Format Base Class
 *
 * This class is used to be the basis of logging format classes
 * to allow for defined functions to exist regardless of the
 * child class
 *
 * @package     Joomla.Platform
 * @subpackage  Log
 * @since       11.1
 */
abstract class JLogFormat
{
	/**
	 * Options array for the JLog instance.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $options = array();

	/**
	 * Constructor.
	 *
	 * @param   array  $options  Log object options.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	protected function __construct(array & $options)
	{
		// Set the options for the class.
		$this->options = & $options;
	}

	/**
	 * Method to add an entry to the log.
	 *
	 * @param   JLogEntry  The log entry object to add to the log.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	abstract public function addEntry(JLogEntry $entry);
}
