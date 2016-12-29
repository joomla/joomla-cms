<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Log
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla! MySQL Database Log class
 *
 * This class is designed to output logs to a specific MySQL database table. Fields in this
 * table are based on the Syslog style of log output. This is designed to allow quick and
 * easy searching.
 *
 * @since  11.1
 */
class JLogLoggerDatabase extends JLogLogger
{
	/**
	 * The name of the database driver to use for connecting to the database.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $driver = 'mysqli';

	/**
	 * The host name (or IP) of the server with which to connect for the logger.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $host = '127.0.0.1';

	/**
	 * The database server user to connect as for the logger.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $user = 'root';

	/**
	 * The password to use for connecting to the database server.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $password = '';

	/**
	 * The name of the database table to use for the logger.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $database = 'logging';

	/**
	 * The database table to use for logging entries.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $table = 'jos_';

	/**
	 * The database driver object for the logger.
	 *
	 * @var    JDatabaseDriver
	 * @since  11.1
	 */
	protected $db;

	/**
	 * Constructor.
	 *
	 * @param   array  &$options  Log object options.
	 *
	 * @since   11.1
	 */
	public function __construct(array &$options)
	{
		// Call the parent constructor.
		parent::__construct($options);

		// If both the database object and driver options are empty we want to use the system database connection.
		if (empty($this->options['db_driver']))
		{
			$this->db = JFactory::getDbo();
			$this->driver = null;
			$this->host = null;
			$this->user = null;
			$this->password = null;
			$this->database = null;
			$this->prefix = null;
		}
		else
		{
			$this->db = null;
			$this->driver = (empty($this->options['db_driver'])) ? 'mysqli' : $this->options['db_driver'];
			$this->host = (empty($this->options['db_host'])) ? '127.0.0.1' : $this->options['db_host'];
			$this->user = (empty($this->options['db_user'])) ? 'root' : $this->options['db_user'];
			$this->password = (empty($this->options['db_pass'])) ? '' : $this->options['db_pass'];
			$this->database = (empty($this->options['db_database'])) ? 'logging' : $this->options['db_database'];
			$this->prefix = (empty($this->options['db_prefix'])) ? 'jos_' : $this->options['db_prefix'];
		}

		// The table name is independent of how we arrived at the connection object.
		$this->table = (empty($this->options['db_table'])) ? '#__log_entries' : $this->options['db_table'];
	}

	/**
	 * Method to add an entry to the log.
	 *
	 * @param   JLogEntry  $entry  The log entry object to add to the log.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function addEntry(JLogEntry $entry)
	{
		// Connect to the database if not connected.
		if (empty($this->db))
		{
			$this->connect();
		}

		// Convert the date.
		$entry->date = $entry->date->toSql(false, $this->db);

		$this->db->insertObject($this->table, $entry);
	}

	/**
	 * Method to connect to the database server based on object properties.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	protected function connect()
	{
		// Build the configuration object to use for JDatabaseDriver.
		$options = array(
			'driver' => $this->driver,
			'host' => $this->host,
			'user' => $this->user,
			'password' => $this->password,
			'database' => $this->database,
			'prefix' => $this->prefix,
		);

		$this->db = JDatabaseDriver::getInstance($options);
	}
}
