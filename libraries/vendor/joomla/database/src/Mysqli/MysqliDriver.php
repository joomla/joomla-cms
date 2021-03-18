<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Mysqli;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseEvents;
use Joomla\Database\Event\ConnectionEvent;
use Joomla\Database\Exception\ConnectionFailureException;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\Exception\PrepareStatementFailureException;
use Joomla\Database\Exception\UnsupportedAdapterException;
use Joomla\Database\StatementInterface;
use Joomla\Database\UTF8MB4SupportInterface;

/**
 * MySQLi Database Driver
 *
 * @link   https://www.php.net/manual/en/book.mysqli.php
 * @since  1.0
 */
class MysqliDriver extends DatabaseDriver implements UTF8MB4SupportInterface
{
	/**
	 * The database connection resource.
	 *
	 * @var    \mysqli
	 * @since  1.0
	 */
	protected $connection;

	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $name = 'mysqli';

	/**
	 * The character(s) used to quote SQL statement names such as table names or field names, etc.
	 *
	 * If a single character string the same character is used for both sides of the quoted name, else the first character will be used for the
	 * opening quote and the second for the closing quote.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $nameQuote = '`';

	/**
	 * The null or zero representation of a timestamp for the database driver.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $nullDate = '0000-00-00 00:00:00';

	/**
	 * True if the database engine supports UTF-8 Multibyte (utf8mb4) character encoding.
	 *
	 * @var    boolean
	 * @since  1.4.0
	 */
	protected $utf8mb4 = false;

	/**
	 * True if the database engine is MariaDB.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $mariadb = false;

	/**
	 * The minimum supported MySQL database version.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected static $dbMinimum = '5.6';

	/**
	 * The minimum supported MariaDB database version.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $dbMinMariadb = '10.0';

	/**
	 * Constructor.
	 *
	 * @param   array  $options  List of options used to configure the connection
	 *
	 * @since   1.0
	 */
	public function __construct(array $options)
	{
		/**
		 * sql_mode to MySql 5.7.8+ default strict mode minus ONLY_FULL_GROUP_BY
		 *
		 * @link https://dev.mysql.com/doc/relnotes/mysql/5.7/en/news-5-7-8.html#mysqld-5-7-8-sql-mode
		 */
		$sqlModes = [
			'STRICT_TRANS_TABLES',
			'ERROR_FOR_DIVISION_BY_ZERO',
			'NO_ENGINE_SUBSTITUTION',
		];

		// Get some basic values from the options.
		$options['host']     = $options['host'] ?? 'localhost';
		$options['user']     = $options['user'] ?? 'root';
		$options['password'] = $options['password'] ?? '';
		$options['database'] = $options['database'] ?? '';
		$options['select']   = isset($options['select']) ? (bool) $options['select'] : true;
		$options['port']     = isset($options['port']) ? (int) $options['port'] : null;
		$options['socket']   = $options['socket'] ?? null;
		$options['utf8mb4']  = isset($options['utf8mb4']) ? (bool) $options['utf8mb4'] : false;
		$options['sqlModes'] = isset($options['sqlModes']) ? (array) $options['sqlModes'] : $sqlModes;
		$options['ssl']      = isset($options['ssl']) ? $options['ssl'] : [];

		if ($options['ssl'] !== [])
		{
			$options['ssl']['enable']             = isset($options['ssl']['enable']) ? $options['ssl']['enable'] : false;
			$options['ssl']['cipher']             = isset($options['ssl']['cipher']) ? $options['ssl']['cipher'] : null;
			$options['ssl']['ca']                 = isset($options['ssl']['ca']) ? $options['ssl']['ca'] : null;
			$options['ssl']['capath']             = isset($options['ssl']['capath']) ? $options['ssl']['capath'] : null;
			$options['ssl']['key']                = isset($options['ssl']['key']) ? $options['ssl']['key'] : null;
			$options['ssl']['cert']               = isset($options['ssl']['cert']) ? $options['ssl']['cert'] : null;
			$options['ssl']['verify_server_cert'] = isset($options['ssl']['verify_server_cert']) ? $options['ssl']['verify_server_cert'] : null;
		}

		// Finalize initialisation.
		parent::__construct($options);
	}

	/**
	 * Connects to the database if needed.
	 *
	 * @return  void  Returns void if the database connected successfully.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function connect()
	{
		if ($this->connection)
		{
			return;
		}

		// Make sure the MySQLi extension for PHP is installed and enabled.
		if (!static::isSupported())
		{
			throw new UnsupportedAdapterException('The MySQLi extension is not available');
		}

		/*
		 * Unlike mysql_connect(), mysqli_connect() takes the port and socket as separate arguments. Therefore, we
		 * have to extract them from the host string.
		 */
		$port = isset($this->options['port']) ? $this->options['port'] : 3306;

		if (preg_match('/^unix:(?P<socket>[^:]+)$/', $this->options['host'], $matches))
		{
			// UNIX socket URI, e.g. 'unix:/path/to/unix/socket.sock'
			$this->options['host']   = null;
			$this->options['socket'] = $matches['socket'];
			$this->options['port']   = null;
		}
		elseif (preg_match(
			'/^(?P<host>((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))(:(?P<port>.+))?$/',
			$this->options['host'],
			$matches
		))
		{
			// It's an IPv4 address with or without port
			$this->options['host'] = $matches['host'];

			if (!empty($matches['port']))
			{
				$port = $matches['port'];
			}
		}
		elseif (preg_match('/^(?P<host>\[.*\])(:(?P<port>.+))?$/', $this->options['host'], $matches))
		{
			// We assume square-bracketed IPv6 address with or without port, e.g. [fe80:102::2%eth1]:3306
			$this->options['host'] = $matches['host'];

			if (!empty($matches['port']))
			{
				$port = $matches['port'];
			}
		}
		elseif (preg_match('/^(?P<host>(\w+:\/{2,3})?[a-z0-9\.\-]+)(:(?P<port>[^:]+))?$/i', $this->options['host'], $matches))
		{
			// Named host (e.g example.com or localhost) with or without port
			$this->options['host'] = $matches['host'];

			if (!empty($matches['port']))
			{
				$port = $matches['port'];
			}
		}
		elseif (preg_match('/^:(?P<port>[^:]+)$/', $this->options['host'], $matches))
		{
			// Empty host, just port, e.g. ':3306'
			$this->options['host'] = 'localhost';
			$port                  = $matches['port'];
		}

		// ... else we assume normal (naked) IPv6 address, so host and port stay as they are or default

		// Get the port number or socket name
		if (is_numeric($port))
		{
			$this->options['port'] = (int) $port;
		}
		else
		{
			$this->options['socket'] = $port;
		}

		$this->connection = mysqli_init();

		$connectionFlags = 0;

		// For SSL/TLS connection encryption.
		if ($this->options['ssl'] !== [] && $this->options['ssl']['enable'] === true)
		{
			$connectionFlags += MYSQLI_CLIENT_SSL;

			// Verify server certificate is only available in PHP 5.6.16+. See https://www.php.net/ChangeLog-5.php#5.6.16
			if (isset($this->options['ssl']['verify_server_cert']))
			{
				// New constants in PHP 5.6.16+. See https://www.php.net/ChangeLog-5.php#5.6.16
				if ($this->options['ssl']['verify_server_cert'] === true && defined('MYSQLI_CLIENT_SSL_VERIFY_SERVER_CERT'))
				{
					$connectionFlags += MYSQLI_CLIENT_SSL_VERIFY_SERVER_CERT;
				}
				elseif ($this->options['ssl']['verify_server_cert'] === false && defined('MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT'))
				{
					$connectionFlags += MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
				}
				elseif (defined('MYSQLI_OPT_SSL_VERIFY_SERVER_CERT'))
				{
					$this->connection->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, $this->options['ssl']['verify_server_cert']);
				}
			}

			// Add SSL/TLS options only if changed.
			$this->connection->ssl_set(
				$this->options['ssl']['key'],
				$this->options['ssl']['cert'],
				$this->options['ssl']['ca'],
				$this->options['ssl']['capath'],
				$this->options['ssl']['cipher']
			);
		}

		// Attempt to connect to the server, use error suppression to silence warnings and allow us to throw an Exception separately.
		$connected = @$this->connection->real_connect(
			$this->options['host'],
			$this->options['user'],
			$this->options['password'],
			null,
			$this->options['port'],
			$this->options['socket'],
			$connectionFlags
		);

		if (!$connected)
		{
			throw new ConnectionFailureException(
				'Could not connect to database: ' . $this->connection->connect_error,
				$this->connection->connect_errno
			);
		}

		// If needed, set the sql modes.
		if ($this->options['sqlModes'] !== [])
		{
			$this->connection->query('SET @@SESSION.sql_mode = \'' . implode(',', $this->options['sqlModes']) . '\';');
		}

		// And read the real sql mode to mitigate changes in mysql > 5.7.+
		$this->options['sqlModes'] = explode(',', $this->setQuery('SELECT @@SESSION.sql_mode;')->loadResult());

		// If auto-select is enabled select the given database.
		if ($this->options['select'] && !empty($this->options['database']))
		{
			$this->select($this->options['database']);
		}

		$this->mariadb = stripos($this->connection->server_info, 'mariadb') !== false;

		$this->utf8mb4 = $this->serverClaimsUtf8mb4Support();

		// Set charactersets (needed for MySQL 4.1.2+ and MariaDB).
		$this->utf = $this->setUtf();

		$this->dispatchEvent(new ConnectionEvent(DatabaseEvents::POST_CONNECT, $this));
	}

	/**
	 * Automatically downgrade a CREATE TABLE or ALTER TABLE query from utf8mb4 (UTF-8 Multibyte) to plain utf8.
	 *
	 * Used when the server doesn't support UTF-8 Multibyte.
	 *
	 * @param   string  $query  The query to convert
	 *
	 * @return  string  The converted query
	 *
	 * @since   1.4.0
	 */
	public function convertUtf8mb4QueryToUtf8($query)
	{
		if ($this->utf8mb4)
		{
			return $query;
		}

		// If it's not an ALTER TABLE or CREATE TABLE command there's nothing to convert
		if (!preg_match('/^(ALTER|CREATE)\s+TABLE\s+/i', $query))
		{
			return $query;
		}

		// Don't do preg replacement if string does not exist
		if (stripos($query, 'utf8mb4') === false)
		{
			return $query;
		}

		// Replace utf8mb4 with utf8 if not within single or double quotes or name quotes
		return preg_replace('/[`"\'][^`"\']*[`"\'](*SKIP)(*FAIL)|utf8mb4/i', 'utf8', $query);
	}

	/**
	 * Disconnects the database.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function disconnect()
	{
		// Close the connection.
		if (\is_callable($this->connection, 'close'))
		{
			$this->connection->close();
		}

		parent::disconnect();
	}

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 *
	 * @since   1.0
	 */
	public function escape($text, $extra = false)
	{
		if (\is_int($text))
		{
			return $text;
		}

		if (\is_float($text))
		{
			// Force the dot as a decimal point.
			return str_replace(',', '.', $text);
		}

		$this->connect();

		$result = $this->connection->real_escape_string($text);

		if ($extra)
		{
			$result = addcslashes($result, '%_');
		}

		return $result;
	}

	/**
	 * Test to see if the MySQLi connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 */
	public static function isSupported()
	{
		return \extension_loaded('mysqli');
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return  boolean  True if connected to the database engine.
	 *
	 * @since   1.0
	 */
	public function connected()
	{
		if (\is_object($this->connection))
		{
			return $this->connection->ping();
		}

		return false;
	}

	/**
	 * Return the query string to alter the database character set.
	 *
	 * @param   string  $dbName  The database name
	 *
	 * @return  string  The query that alter the database query string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getAlterDbCharacterSet($dbName)
	{
		$charset = $this->utf8mb4 ? 'utf8mb4' : 'utf8';

		return 'ALTER DATABASE ' . $this->quoteName($dbName) . ' CHARACTER SET `' . $charset . '`';
	}

	/**
	 * Method to get the database collation in use by sampling a text field of a table in the database.
	 *
	 * @return  string|boolean  The collation in use by the database (string) or boolean false if not supported.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getCollation()
	{
		$this->connect();

		return $this->setQuery('SELECT @@collation_database;')->loadResult();
	}

	/**
	 * Method to get the database connection collation in use by sampling a text field of a table in the database.
	 *
	 * @return  string|boolean  The collation in use by the database connection (string) or boolean false if not supported.
	 *
	 * @since   1.6.0
	 * @throws  \RuntimeException
	 */
	public function getConnectionCollation()
	{
		$this->connect();

		return $this->setQuery('SELECT @@collation_connection;')->loadResult();
	}

	/**
	 * Method to get the database encryption details (cipher and protocol) in use.
	 *
	 * @return  string  The database encryption details.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 */
	public function getConnectionEncryption(): string
	{
		$this->connect();

		$variables = $this->setQuery('SHOW SESSION STATUS WHERE `Variable_name` IN (\'Ssl_version\', \'Ssl_cipher\')')
			->loadObjectList('Variable_name');

		if (!empty($variables['Ssl_cipher']->Value))
		{
			return $variables['Ssl_version']->Value . ' (' . $variables['Ssl_cipher']->Value . ')';
		}

		return '';
	}

	/**
	 * Method to test if the database TLS connections encryption are supported.
	 *
	 * @return  boolean  Whether the databse supports TLS connections encryption.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isConnectionEncryptionSupported(): bool
	{
		$this->connect();

		$variables = $this->setQuery('SHOW SESSION VARIABLES WHERE `Variable_name` IN (\'have_ssl\')')->loadObjectList('Variable_name');

		return !empty($variables['have_ssl']->Value) && $variables['have_ssl']->Value === 'YES';
	}

	/**
	 * Return the query string to create new Database.
	 *
	 * @param   stdClass  $options  Object used to pass user and database name to database driver. This object must have "db_name" and "db_user" set.
	 * @param   boolean   $utf      True if the database supports the UTF-8 character set.
	 *
	 * @return  string  The query that creates database
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getCreateDatabaseQuery($options, $utf)
	{
		if ($utf)
		{
			$charset   = $this->utf8mb4 ? 'utf8mb4' : 'utf8';
			$collation = $charset . '_unicode_ci';

			return 'CREATE DATABASE ' . $this->quoteName($options->db_name) . ' CHARACTER SET `' . $charset . '` COLLATE `' . $collation . '`';
		}

		return 'CREATE DATABASE ' . $this->quoteName($options->db_name);
	}

	/**
	 * Shows the table CREATE statement that creates the given tables.
	 *
	 * @param   mixed  $tables  A table name or a list of table names.
	 *
	 * @return  array  A list of the create SQL for the tables.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getTableCreate($tables)
	{
		$this->connect();

		$result = [];

		// Sanitize input to an array and iterate over the list.
		$tables = (array) $tables;

		foreach ($tables as $table)
		{
			// Set the query to get the table CREATE statement.
			$row = $this->setQuery('SHOW CREATE TABLE ' . $this->quoteName($this->escape($table)))->loadRow();

			// Populate the result array based on the create statements.
			$result[$table] = $row[1];
		}

		return $result;
	}

	/**
	 * Retrieves field information about a given table.
	 *
	 * @param   string   $table     The name of the database table.
	 * @param   boolean  $typeOnly  True to only return field types.
	 *
	 * @return  array  An array of fields for the database table.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getTableColumns($table, $typeOnly = true)
	{
		$this->connect();

		$result = [];

		// Set the query to get the table fields statement.
		$fields = $this->setQuery('SHOW FULL COLUMNS FROM ' . $this->quoteName($this->escape($table)))->loadObjectList();

		// If we only want the type as the value add just that to the list.
		if ($typeOnly)
		{
			foreach ($fields as $field)
			{
				$result[$field->Field] = preg_replace('/[(0-9)]/', '', $field->Type);
			}
		}
		else
		{
			// If we want the whole field data object add that to the list.
			foreach ($fields as $field)
			{
				$result[$field->Field] = $field;
			}
		}

		return $result;
	}

	/**
	 * Get the details list of keys for a table.
	 *
	 * @param   string  $table  The name of the table.
	 *
	 * @return  array  An array of the column specification for the table.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getTableKeys($table)
	{
		$this->connect();

		// Get the details columns information.
		return $this->setQuery('SHOW KEYS FROM ' . $this->quoteName($table))->loadObjectList();
	}

	/**
	 * Method to get an array of all tables in the database.
	 *
	 * @return  array  An array of all the tables in the database.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getTableList()
	{
		$this->connect();

		// Set the query to get the tables statement.
		return $this->setQuery('SHOW TABLES')->loadColumn();
	}

	/**
	 * Get the version of the database connector.
	 *
	 * @return  string  The database connector version.
	 *
	 * @since   1.0
	 */
	public function getVersion()
	{
		$this->connect();

		if ($this->mariadb)
		{
			// MariaDB: Strip off any leading '5.5.5-', if present
			return preg_replace('/^5\.5\.5-/', '', $this->connection->server_info);
		}

		return $this->connection->server_info;
	}

	/**
	 * Get the minimum supported database version.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getMinimum()
	{
		return $this->mariadb ? static::$dbMinMariadb : static::$dbMinimum;
	}

	/**
	 * Determine whether the database engine support the UTF-8 Multibyte (utf8mb4) character encoding.
	 *
	 * @return  boolean  True if the database engine supports UTF-8 Multibyte.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function hasUTF8mb4Support()
	{
		return $this->utf8mb4;
	}

	/**
	 * Determine if the database engine is MariaDB.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isMariaDb(): bool
	{
		$this->connect();

		return $this->mariadb;
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  mixed  The value of the auto-increment field from the last inserted row.
	 *                 If the value is greater than maximal int value, it will return a string.
	 *
	 * @since   1.0
	 */
	public function insertid()
	{
		$this->connect();

		return $this->connection->insert_id;
	}

	/**
	 * Inserts a row into a table based on an object's properties.
	 *
	 * @param   string  $table   The name of the database table to insert into.
	 * @param   object  $object  A reference to an object whose public properties match the table fields.
	 * @param   string  $key     The name of the primary key. If provided the object property is updated.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 */
	public function insertObject($table, &$object, $key = null)
	{
		$fields       = [];
		$values       = [];
		$tableColumns = $this->getTableColumns($table);

		// Iterate over the object variables to build the query fields and values.
		foreach (get_object_vars($object) as $k => $v)
		{
			// Skip columns that don't exist in the table.
			if (!array_key_exists($k, $tableColumns))
			{
				continue;
			}

			// Only process non-null scalars.
			if (\is_array($v) || \is_object($v) || $v === null)
			{
				continue;
			}

			// Ignore any internal fields.
			if ($k[0] === '_')
			{
				continue;
			}

			// Ignore null datetime fields.
			if ($tableColumns[$k] === 'datetime' && empty($v))
			{
				continue;
			}

			// Ignore null integer fields.
			if (stristr($tableColumns[$k], 'int') !== false && $v === '')
			{
				continue;
			}

			// Prepare and sanitize the fields and values for the database query.
			$fields[] = $this->quoteName($k);
			$values[] = $this->quote($v);
		}

		// Create the base insert statement.
		$query = $this->getQuery(true)
			->insert($this->quoteName($table))
			->columns($fields)
			->values(implode(',', $values));

		// Set the query and execute the insert.
		$this->setQuery($query)->execute();

		// Update the primary key if it exists.
		$id = $this->insertid();

		if ($key && $id && \is_string($key))
		{
			$object->$key = $id;
		}

		return true;
	}

	/**
	 * Locks a table in the database.
	 *
	 * @param   string  $table  The name of the table to unlock.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function lockTable($table)
	{
		$this->executeUnpreparedQuery($this->replacePrefix('LOCK TABLES ' . $this->quoteName($table) . ' WRITE'));

		return $this;
	}

	/**
	 * Renames a table in the database.
	 *
	 * @param   string  $oldTable  The name of the table to be renamed
	 * @param   string  $newTable  The new name for the table.
	 * @param   string  $backup    Not used by MySQL.
	 * @param   string  $prefix    Not used by MySQL.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function renameTable($oldTable, $newTable, $backup = null, $prefix = null)
	{
		$this->setQuery('RENAME TABLE ' . $oldTable . ' TO ' . $newTable)->execute();

		return $this;
	}

	/**
	 * Select a database for use.
	 *
	 * @param   string  $database  The name of the database to select for use.
	 *
	 * @return  boolean  True if the database was successfully selected.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function select($database)
	{
		$this->connect();

		if (!$database)
		{
			return false;
		}

		if (!$this->connection->select_db($database))
		{
			throw new ConnectionFailureException('Could not connect to database.');
		}

		return true;
	}

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0
	 */
	public function setUtf()
	{
		// If UTF is not supported return false immediately
		if (!$this->utf)
		{
			return false;
		}

		// Make sure we're connected to the server
		$this->connect();

		// Which charset should I use, plain utf8 or multibyte utf8mb4?
		$charset = $this->utf8mb4 && $this->options['utf8mb4'] ? 'utf8mb4' : 'utf8';

		$result = @$this->connection->set_charset($charset);

		/*
		 * If I could not set the utf8mb4 charset then the server doesn't support utf8mb4 despite claiming otherwise. This happens on old MySQL
		 * server versions (less than 5.5.3) using the mysqlnd PHP driver. Since mysqlnd masks the server version and reports only its own we
		 * can not be sure if the server actually does support UTF-8 Multibyte (i.e. it's MySQL 5.5.3 or later). Since the utf8mb4 charset is
		 * undefined in this case we catch the error and determine that utf8mb4 is not supported!
		 */
		if (!$result && $this->utf8mb4 && $this->options['utf8mb4'])
		{
			$this->utf8mb4 = false;
			$result        = @$this->connection->set_charset('utf8');
		}

		return $result;
	}

	/**
	 * Method to commit a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, commit to the last savepoint.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function transactionCommit($toSavepoint = false)
	{
		if (!$toSavepoint || $this->transactionDepth <= 1)
		{
			$this->connect();

			if ($this->connection->commit())
			{
				$this->transactionDepth = 0;
			}

			return;
		}

		$this->transactionDepth--;
	}

	/**
	 * Method to roll back a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, rollback to the last savepoint.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function transactionRollback($toSavepoint = false)
	{
		if (!$toSavepoint || $this->transactionDepth <= 1)
		{
			$this->connect();

			if ($this->connection->rollback())
			{
				$this->transactionDepth = 0;
			}

			return;
		}

		$savepoint = 'SP_' . ($this->transactionDepth - 1);

		if ($this->executeUnpreparedQuery('ROLLBACK TO SAVEPOINT ' . $this->quoteName($savepoint)))
		{
			$this->transactionDepth--;
		}
	}

	/**
	 * Method to initialize a transaction.
	 *
	 * @param   boolean  $asSavepoint  If true and a transaction is already active, a savepoint will be created.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function transactionStart($asSavepoint = false)
	{
		$this->connect();

		if (!$asSavepoint || !$this->transactionDepth)
		{
			if ($this->connection->begin_transaction())
			{
				$this->transactionDepth = 1;
			}

			return;
		}

		$savepoint = 'SP_' . $this->transactionDepth;

		if ($this->connection->savepoint($savepoint))
		{
			$this->transactionDepth++;
		}
	}

	/**
	 * Internal method to execute queries which cannot be run as prepared statements.
	 *
	 * @param   string  $sql  SQL statement to execute.
	 *
	 * @return  boolean
	 *
	 * @since   1.5.0
	 */
	protected function executeUnpreparedQuery($sql)
	{
		$this->connect();

		$cursor = $this->connection->query($sql);

		// If an error occurred handle it.
		if (!$cursor)
		{
			$errorNum = (int) $this->connection->errno;
			$errorMsg = (string) $this->connection->error;

			// Check if the server was disconnected.
			if (!$this->connected())
			{
				try
				{
					// Attempt to reconnect.
					$this->connection = null;
					$this->connect();
				}
				catch (ConnectionFailureException $e)
				{
					// If connect fails, ignore that exception and throw the normal exception.
					throw new ExecutionFailureException($sql, $errorMsg, $errorNum);
				}

				// Since we were able to reconnect, run the query again.
				return $this->executeUnpreparedQuery($sql);
			}

			// The server was not disconnected.
			throw new ExecutionFailureException($sql, $errorMsg, $errorNum);
		}

		$this->freeResult();

		if ($cursor instanceof \mysqli_result)
		{
			$cursor->free_result();
		}

		return true;
	}

	/**
	 * Prepares a SQL statement for execution
	 *
	 * @param   string  $query  The SQL query to be prepared.
	 *
	 * @return  StatementInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  PrepareStatementFailureException
	 */
	protected function prepareStatement(string $query): StatementInterface
	{
		return new MysqliStatement($this->connection, $query);
	}

	/**
	 * Unlocks tables in the database.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function unlockTables()
	{
		$this->executeUnpreparedQuery('UNLOCK TABLES');

		return $this;
	}

	/**
	 * Does the database server claim to have support for UTF-8 Multibyte (utf8mb4) collation?
	 *
	 * libmysql supports utf8mb4 since 5.5.3 (same version as the MySQL server). mysqlnd supports utf8mb4 since 5.0.9.
	 *
	 * @return  boolean
	 *
	 * @since   1.4.0
	 */
	private function serverClaimsUtf8mb4Support()
	{
		$client_version = mysqli_get_client_info();
		$server_version = $this->getVersion();

		if (version_compare($server_version, '5.5.3', '<'))
		{
			return false;
		}

		if ($this->mariadb && version_compare($server_version, '10.0.0', '<'))
		{
			return false;
		}

		if (strpos($client_version, 'mysqlnd') !== false)
		{
			$client_version = preg_replace('/^\D+([\d.]+).*/', '$1', $client_version);

			return version_compare($client_version, '5.0.9', '>=');
		}

		return version_compare($client_version, '5.5.3', '>=');
	}

	/**
	 * Get the null or zero representation of a timestamp for the database driver.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getNullDate()
	{
		// Check the session sql mode;
		if (\in_array('NO_ZERO_DATE', $this->options['sqlModes']) !== false)
		{
			$this->nullDate = '1000-01-01 00:00:00';
		}

		return $this->nullDate;
	}
}
