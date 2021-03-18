<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Mysql;

use Joomla\Database\Exception\ConnectionFailureException;
use Joomla\Database\Pdo\PdoDriver;
use Joomla\Database\UTF8MB4SupportInterface;

/**
 * MySQL database driver supporting PDO based connections
 *
 * @link   https://www.php.net/manual/en/ref.pdo-mysql.php
 * @since  1.0
 */
class MysqlDriver extends PdoDriver implements UTF8MB4SupportInterface
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $name = 'mysql';

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
	 * The minimum supported database version.
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
	 * The default cipher suite for TLS connections.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultCipherSuite = [
		'AES128-GCM-SHA256',
		'AES256-GCM-SHA384',
		'AES128-CBC-SHA256',
		'AES256-CBC-SHA384',
		'DES-CBC3-SHA',
	];

	/**
	 * Constructor.
	 *
	 * @param   array  $options  Array of database options with keys: host, user, password, database, select.
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
		$options['driver']   = 'mysql';
		$options['charset']  = $options['charset'] ?? 'utf8';
		$options['sqlModes'] = isset($options['sqlModes']) ? (array) $options['sqlModes'] : $sqlModes;

		$this->charset = $options['charset'];

		/*
		 * Pre-populate the UTF-8 Multibyte compatibility flag. Unfortunately PDO won't report the server version unless we're connected to it,
		 * and we cannot connect to it unless we know if it supports utf8mb4, which requires us knowing the server version. Because of this
		 * chicken and egg issue, we _assume_ it's supported and we'll just catch any problems at connection time.
		 */
		$this->utf8mb4 = $options['charset'] === 'utf8mb4';

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
		if ($this->getConnection())
		{
			return;
		}

		// For SSL/TLS connection encryption.
		if ($this->options['ssl'] !== [] && $this->options['ssl']['enable'] === true)
		{
			$sslContextIsNull = true;

			// If customised, add cipher suite, ca file path, ca path, private key file path and certificate file path to PDO driver options.
			foreach (['cipher', 'ca', 'capath', 'key', 'cert'] as $key => $value)
			{
				if ($this->options['ssl'][$value] !== null)
				{
					$this->options['driverOptions'][constant('\PDO::MYSQL_ATTR_SSL_' . strtoupper($value))] = $this->options['ssl'][$value];
					$sslContextIsNull                                                                       = false;
				}
			}

			// PDO, if no cipher, ca, capath, cert and key are set, can't start TLS one-way connection, set a common ciphers suite to force it.
			if ($sslContextIsNull === true)
			{
				$this->options['driverOptions'][\PDO::MYSQL_ATTR_SSL_CIPHER] = implode(':', static::$defaultCipherSuite);
			}

			// If customised, for capable systems (PHP 7.0.14+ and 7.1.4+) verify certificate chain and Common Name to driver options.
			if ($this->options['ssl']['verify_server_cert'] !== null && defined('\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT'))
			{
				$this->options['driverOptions'][\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = $this->options['ssl']['verify_server_cert'];
			}
		}

		try
		{
			// Try to connect to MySQL
			parent::connect();
		}
		catch (ConnectionFailureException $e)
		{
			// If the connection failed, but not because of the wrong character set, then bubble up the exception.
			if (!$this->utf8mb4)
			{
				throw $e;
			}

			/*
			 * Otherwise, try connecting again without using utf8mb4 and see if maybe that was the problem. If the connection succeeds, then we
			 * will have learned that the client end of the connection does not support utf8mb4.
  			 */
			$this->utf8mb4            = false;
			$this->options['charset'] = 'utf8';

			parent::connect();
		}

		$serverVersion = $this->getVersion();

		$this->mariadb = stripos($serverVersion, 'mariadb') !== false;

		if ($this->utf8mb4)
		{
			// At this point we know the client supports utf8mb4.  Now we must check if the server supports utf8mb4 as well.
			$this->utf8mb4 = version_compare($serverVersion, '5.5.3', '>=');

			if ($this->mariadb && version_compare($serverVersion, '10.0.0', '<'))
			{
				$this->utf8mb4 = false;
			}

			if (!$this->utf8mb4)
			{
				// Reconnect with the utf8 character set.
				parent::disconnect();
				$this->options['charset'] = 'utf8';
				parent::connect();
			}
		}

		// If needed, set the sql modes.
		if ($this->options['sqlModes'] !== [])
		{
			$this->connection->query('SET @@SESSION.sql_mode = \'' . implode(',', $this->options['sqlModes']) . '\';');
		}

		$this->setOption(\PDO::ATTR_EMULATE_PREPARES, true);
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
		if ($this->hasUTF8mb4Support())
		{
			return $query;
		}

		// If it's not an ALTER TABLE or CREATE TABLE command there's nothing to convert
		$beginningOfQuery = substr($query, 0, 12);
		$beginningOfQuery = strtoupper($beginningOfQuery);

		if (!\in_array($beginningOfQuery, ['ALTER TABLE ', 'CREATE TABLE'], true))
		{
			return $query;
		}

		// Replace utf8mb4 with utf8
		return str_replace('utf8mb4', 'utf8', $query);
	}

	/**
	 * Test to see if the MySQL connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 */
	public static function isSupported()
	{
		return class_exists('\\PDO') && \in_array('mysql', \PDO::getAvailableDrivers(), true);
	}

	/**
	 * Select a database for use.
	 *
	 * @param   string  $database  The name of the database to select for use.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function select($database)
	{
		$this->connect();

		$this->setQuery('USE ' . $this->quoteName($database))
			->execute();

		return true;
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
	 * @param   array|string  $tables  A table name or a list of table names.
	 *
	 * @return  array  A list of the create SQL for the tables.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 */
	public function getTableCreate($tables)
	{
		$this->connect();

		// Initialise variables.
		$result = [];

		// Sanitize input to an array and iterate over the list.
		$tables = (array) $tables;

		foreach ($tables as $table)
		{
			$row = $this->setQuery('SHOW CREATE TABLE ' . $this->quoteName($table))->loadRow();

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
		$fields = $this->setQuery('SHOW FULL COLUMNS FROM ' . $this->quoteName($table))->loadObjectList();

		// If we only want the type as the value add just that to the list.
		if ($typeOnly)
		{
			foreach ($fields as $field)
			{
				$result[$field->Field] = preg_replace('/[(0-9)]/', '', $field->Type);
			}
		}
		// If we want the whole field data object add that to the list.
		else
		{
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function getVersion()
	{
		$this->connect();

		$version = $this->getOption(\PDO::ATTR_SERVER_VERSION);

		if (stripos($version, 'mariadb') !== false)
		{
			// MariaDB: Strip off any leading '5.5.5-', if present
			return preg_replace('/^5\.5\.5-/', '', $version);
		}

		return $version;
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
		$this->setQuery('LOCK TABLES ' . $this->quoteName($table) . ' WRITE')
			->execute();

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
		$this->setQuery('RENAME TABLE ' . $this->quoteName($oldTable) . ' TO ' . $this->quoteName($newTable))
			->execute();

		return $this;
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
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * Oracle escaping reference:
	 * http://www.orafaq.com/wiki/SQL_FAQ#How_does_one_escape_special_characters_when_writing_SQL_queries.3F
	 *
	 * SQLite escaping notes:
	 * http://www.sqlite.org/faq.html#q14
	 *
	 * Method body is as implemented by the Zend Framework
	 *
	 * Note: Using query objects with bound variables is preferable to the below.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Unused optional parameter to provide extra escaping.
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

		$result = substr($this->connection->quote($text), 1, -1);

		if ($extra)
		{
			$result = addcslashes($result, '%_');
		}

		return $result;
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
		$this->setQuery('UNLOCK TABLES')
			->execute();

		return $this;
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
		$this->connect();

		if (!$toSavepoint || $this->transactionDepth <= 1)
		{
			parent::transactionCommit($toSavepoint);
		}
		else
		{
			$this->transactionDepth--;
		}
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
		$this->connect();

		if (!$toSavepoint || $this->transactionDepth <= 1)
		{
			parent::transactionRollback($toSavepoint);
		}
		else
		{
			$savepoint = 'SP_' . ($this->transactionDepth - 1);
			$this->setQuery('ROLLBACK TO SAVEPOINT ' . $this->quoteName($savepoint));

			if ($this->execute())
			{
				$this->transactionDepth--;
			}
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
			parent::transactionStart($asSavepoint);
		}
		else
		{
			$savepoint = 'SP_' . $this->transactionDepth;
			$this->setQuery('SAVEPOINT ' . $this->quoteName($savepoint));

			if ($this->execute())
			{
				$this->transactionDepth++;
			}
		}
	}
}
