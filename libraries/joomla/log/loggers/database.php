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
jimport('joomla.database.database');

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
	 * @var    string  The name of the database driver to use for connecting to the database.
	 * @since  11.1
	 */
	protected $driver = 'mysql';

	/**
	 * @var    string  The host name (or IP) of the server with which to connect for the logger.
	 * @since  11.1
	 */
	protected $host = '127.0.0.1';

	/**
	 * @var    string  The database server user to connect as for the logger.
	 * @since  11.1
	 */
	protected $user = 'root';

	/**
	 * @var    string  The password to use for connecting to the database server.
	 * @since  11.1
	 */
	protected $password = '';

	/**
	 * @var    string  The name of the database table to use for the logger.
	 * @since  11.1
	 */
	protected $database = 'logging';

	/**
	 * @var    string  The database table to use for logging entries.
	 * @since  11.1
	 */
	protected $table = 'jos_';

	/**
	 * @var    JDatabase  The database connection object for the logger.
	 * @since  11.1
	 */
	protected $dbo;

	/**
	 * Constructor.
	 *
	 * @param   array  $options  Log object options.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @throws  LogException
	 */
	public function __construct(array & $options)
	{
		// Call the parent constructor.
		parent::__construct($options);

		// If both the database object and driver options are empty we want to use the system database connection.
		if (empty($this->options['db_object']) && empty($this->options['db_driver'])) {
			$this->dbo      = JFactory::getDBO();
			$this->driver   = JFactory::getConfig()->get('dbtype');
			$this->host     = JFactory::getConfig()->get('host');
			$this->user     = JFactory::getConfig()->get('user');
			$this->password = JFactory::getConfig()->get('password');
			$this->database = JFactory::getConfig()->get('db');
			$this->prefix   = JFactory::getConfig()->get('dbprefix');
		}
		// We need to get the database connection settings from the configuration options.
		else {
			$this->driver   = (empty($this->options['db_driver']))   ? 'mysql' : $this->options['db_driver'];
			$this->host     = (empty($this->options['db_host']))     ? '127.0.0.1' : $this->options['db_host'];
			$this->user     = (empty($this->options['db_user']))     ? 'root' : $this->options['db_user'];
			$this->password = (empty($this->options['db_pass']))     ? '' : $this->options['db_pass'];
			$this->database = (empty($this->options['db_database'])) ? 'logging' : $this->options['db_database'];
			$this->prefix   = (empty($this->options['db_prefix']))   ? 'jos_' : $this->options['db_prefix'];
		}

		// The table name is independent of how we arrived at the connection object.
		$this->table = (empty($this->options['db_table'])) ? '#__log_entries' : $this->options['db_table'];
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
		// Connect to the database if not connected.
		if (empty($this->dbo)) {
			$this->connect();
		}

		// Convert the date.
		$entry->date = $entry->date->toMySQL();

		$this->dbo->insertObject($this->table, $entry);
	}

	/**
	 * Method to connect to the database server based on object properties.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @throws  LogException
	 */
	protected function connect()
	{
		// Build the configuration object to use for JDatabase.
		$options = array(
			'driver'   => $this->driver,
			'host'     => $this->host,
			'user'     => $this->user,
			'password' => $this->password,
			'database' => $this->database,
			'prefix'   => $this->prefix
		);

		try {
			$db = JDatabase::getInstance($options);

			if (JError::isError($db)) {
				throw new LogException('Database Error: ' . (string) $db);
			}

			if ($db->getErrorNum() > 0) {
				throw new LogException(JText::sprintf('JLIB_UTIL_ERROR_CONNECT_DATABASE', $db->getErrorNum(), $db->getErrorMsg()));
			}

			// Assign the database connector to the class.
			$this->dbo = $db;
		}
		catch (DatabaseException $e) {
			throw new LogException($e->getMessage());
		}
	}
}
