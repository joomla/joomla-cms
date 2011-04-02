<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.log.log');
jimport('joomla.log.logger');

/**
 * Joomla! MySQL Database Log class
 *
 * This class is designed to output logs to a specific MySQL database table. Fields in this
 * table are based on the SysLog style of log output. This is designed to allow quick and
 * easy searching.
 *
 * @package     Joomla.Platform
 * @subpackage  Log
 * @since       11.1
 */
class JLoggerDatabase extends JLogger
{
	/**
	 * Constructor.
	 *
	 * @param   array  $options  Log object options.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function __construct(array & $options)
	{
		// Call the parent constructor.
		parent::__construct($options);

		// Check if we are supposed to use the system database connection.
		if (empty($this->options['db_type'])) {
			$this->db = JFactory::getDBO();
		}
		else {
			// TODO: Build a database connection.
		}

		// Ensure we have a database table in which to add the log entries.
		if (empty($this->options['db_table'])) {
			$this->options['db_table'] = '#__log_entries';
		}
	}

	/**
	 * Method to add an entry to the log.
	 *
	 * @param   JLogEntry  The log entry object to add to the log.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function addEntry(JLogEntry $entry)
	{
		$this->db->insertObject($this->options['db_table'], $entry);
	}
}
