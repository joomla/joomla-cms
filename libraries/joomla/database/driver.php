<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla Platform Database Driver Class
 *
 * @since  12.1
 *
 * @method      string  q()   q($text, $escape = true)  Alias for quote method
 * @method      string  qn()  qn($name, $as = null)     Alias for quoteName method
 */
abstract class JDatabaseDriver extends JDatabase implements JDatabaseInterface
{
	/**
	 * The name of the database.
	 *
	 * @var    string
	 * @since  11.4
	 */
	private $_database;

	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $name;

	/**
	 * The type of the database server family supported by this driver. Examples: mysql, oracle, postgresql, mssql,
	 * sqlite.
	 *
	 * @var    string
	 * @since  CMS 3.5.0
	 */
	public $serverType;

	/**
	 * @var    resource  The database connection resource.
	 * @since  11.1
	 */
	protected $connection;

	/**
	 * @var    integer  The number of SQL statements executed by the database driver.
	 * @since  11.1
	 */
	protected $count = 0;

	/**
	 * @var    resource  The database connection cursor from the last query.
	 * @since  11.1
	 */
	protected $cursor;

	/**
	 * @var    boolean  The database driver debugging state.
	 * @since  11.1
	 */
	protected $debug = false;

	/**
	 * @var    integer  The affected row limit for the current SQL statement.
	 * @since  11.1
	 */
	protected $limit = 0;

	/**
	 * @var    array  The log of executed SQL statements by the database driver.
	 * @since  11.1
	 */
	protected $log = array();

	/**
	 * @var    array  The log of executed SQL statements timings (start and stop microtimes) by the database driver.
	 * @since  CMS 3.1.2
	 */
	protected $timings = array();

	/**
	 * @var    array  The log of executed SQL statements timings (start and stop microtimes) by the database driver.
	 * @since  CMS 3.1.2
	 */
	protected $callStacks = array();

	/**
	 * @var    string  The character(s) used to quote SQL statement names such as table names or field names,
	 *                 etc.  The child classes should define this as necessary.  If a single character string the
	 *                 same character is used for both sides of the quoted name, else the first character will be
	 *                 used for the opening quote and the second for the closing quote.
	 * @since  11.1
	 */
	protected $nameQuote;

	/**
	 * @var    string  The null or zero representation of a timestamp for the database driver.  This should be
	 *                 defined in child classes to hold the appropriate value for the engine.
	 * @since  11.1
	 */
	protected $nullDate;

	/**
	 * @var    integer  The affected row offset to apply for the current SQL statement.
	 * @since  11.1
	 */
	protected $offset = 0;

	/**
	 * @var    array  Passed in upon instantiation and saved.
	 * @since  11.1
	 */
	protected $options;

	/**
	 * @var    mixed  The current SQL statement to execute.
	 * @since  11.1
	 */
	protected $sql;

	/**
	 * @var    string  The common database table prefix.
	 * @since  11.1
	 */
	protected $tablePrefix;

	/**
	 * @var    boolean  True if the database engine supports UTF-8 character encoding.
	 * @since  11.1
	 */
	protected $utf = true;

	/**
	 * @var    boolean  True if the database engine supports UTF-8 Multibyte (utf8mb4) character encoding.
	 * @since  CMS 3.5.0
	 */
	protected $utf8mb4 = false;

	/**
	 * @var         integer  The database error number
	 * @since       11.1
	 * @deprecated  12.1
	 */
	protected $errorNum = 0;

	/**
	 * @var         string  The database error message
	 * @since       11.1
	 * @deprecated  12.1
	 */
	protected $errorMsg;

	/**
	 * @var    array  JDatabaseDriver instances container.
	 * @since  11.1
	 */
	protected static $instances = array();

	/**
	 * @var    string  The minimum supported database version.
	 * @since  12.1
	 */
	protected static $dbMinimum;

	/**
	 * @var    integer  The depth of the current transaction.
	 * @since  12.3
	 */
	protected $transactionDepth = 0;

	/**
	 * @var    callable[]  List of callables to call just before disconnecting database
	 * @since  CMS 3.1.2
	 */
	protected $disconnectHandlers = array();

	/**
	 * Get a list of available database connectors.  The list will only be populated with connectors that both
	 * the class exists and the static test method returns true.  This gives us the ability to have a multitude
	 * of connector classes that are self-aware as to whether or not they are able to be used on a given system.
	 *
	 * @return  array  An array of available database connectors.
	 *
	 * @since   11.1
	 */
	public static function getConnectors()
	{
		$connectors = array();

		// Get an iterator and loop trough the driver classes.
		$iterator = new DirectoryIterator(__DIR__ . '/driver');

		/* @type  $file  DirectoryIterator */
		foreach ($iterator as $file)
		{
			$fileName = $file->getFilename();

			// Only load for php files.
			if (!$file->isFile() || $file->getExtension() != 'php')
			{
				continue;
			}

			// Derive the class name from the type.
			$class = str_ireplace('.php', '', 'JDatabaseDriver' . ucfirst(trim($fileName)));

			// If the class doesn't exist we have nothing left to do but look at the next type. We did our best.
			if (!class_exists($class))
			{
				continue;
			}

			// Sweet!  Our class exists, so now we just need to know if it passes its test method.
			if ($class::isSupported())
			{
				// Connector names should not have file extensions.
				$connectors[] = str_ireplace('.php', '', $fileName);
			}
		}

		return $connectors;
	}

	/**
	 * Method to return a JDatabaseDriver instance based on the given options.  There are three global options and then
	 * the rest are specific to the database driver.  The 'driver' option defines which JDatabaseDriver class is
	 * used for the connection -- the default is 'mysqli'.  The 'database' option determines which database is to
	 * be used for the connection.  The 'select' option determines whether the connector should automatically select
	 * the chosen database.
	 *
	 * Instances are unique to the given options and new objects are only created when a unique options array is
	 * passed into the method.  This ensures that we don't end up with unnecessary database connection resources.
	 *
	 * @param   array  $options  Parameters to be passed to the database driver.
	 *
	 * @return  JDatabaseDriver  A database object.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public static function getInstance($options = array())
	{
		// Sanitize the database connector options.
		$options['driver']   = (isset($options['driver'])) ? preg_replace('/[^A-Z0-9_\.-]/i', '', $options['driver']) : 'mysqli';
		$options['database'] = (isset($options['database'])) ? $options['database'] : null;
		$options['select']   = (isset($options['select'])) ? $options['select'] : true;

		// If the selected driver is `mysql` and we are on PHP 7 or greater, switch to the `mysqli` driver.
		if ($options['driver'] === 'mysql' && PHP_MAJOR_VERSION >= 7)
		{
			// Check if we have support for the other MySQL drivers
			$mysqliSupported   = JDatabaseDriverMysqli::isSupported();
			$pdoMysqlSupported = JDatabaseDriverPdomysql::isSupported();

			// If neither is supported, then the user cannot use MySQL; throw an exception
			if (!$mysqliSupported && !$pdoMysqlSupported)
			{
				throw new JDatabaseExceptionUnsupported(
					'The PHP `ext/mysql` extension is removed in PHP 7, cannot use the `mysql` driver.'
					. ' Also, this system does not support MySQLi or PDO MySQL.  Cannot instantiate database driver.'
				);
			}

			// Prefer MySQLi as it is a closer replacement for the removed MySQL driver, otherwise use the PDO driver
			if ($mysqliSupported)
			{
				JLog::add(
					'The PHP `ext/mysql` extension is removed in PHP 7, cannot use the `mysql` driver.  Trying `mysqli` instead.',
					JLog::WARNING,
					'deprecated'
				);

				$options['driver'] = 'mysqli';
			}
			else
			{
				JLog::add(
					'The PHP `ext/mysql` extension is removed in PHP 7, cannot use the `mysql` driver.  Trying `pdomysql` instead.',
					JLog::WARNING,
					'deprecated'
				);

				$options['driver'] = 'pdomysql';
			}
		}

		// Get the options signature for the database connector.
		$signature = md5(serialize($options));

		// If we already have a database connector instance for these options then just use that.
		if (empty(self::$instances[$signature]))
		{
			// Derive the class name from the driver.
			$class = 'JDatabaseDriver' . ucfirst(strtolower($options['driver']));

			// If the class still doesn't exist we have nothing left to do but throw an exception.  We did our best.
			if (!class_exists($class))
			{
				throw new JDatabaseExceptionUnsupported(sprintf('Unable to load Database Driver: %s', $options['driver']));
			}

			// Create our new JDatabaseDriver connector based on the options given.
			try
			{
				$instance = new $class($options);
			}
			catch (RuntimeException $e)
			{
				throw new JDatabaseExceptionConnecting(sprintf('Unable to connect to the Database: %s', $e->getMessage()), $e->getCode(), $e);
			}

			// Set the new connector to the global instances based on signature.
			self::$instances[$signature] = $instance;
		}

		return self::$instances[$signature];
	}

	/**
	 * Splits a string of multiple queries into an array of individual queries.
	 * Single line or line end comments and multi line comments are stripped off.
	 *
	 * @param   string  $sql  Input SQL string with which to split into individual queries.
	 *
	 * @return  array  The queries from the input string separated into an array.
	 *
	 * @since   11.1
	 */
	public static function splitSql($sql)
	{
		$start = 0;
		$open = false;
		$comment = false;
		$endString = '';
		$end = strlen($sql);
		$queries = array();
		$query = '';

		for ($i = 0; $i < $end; $i++)
		{
			$current = substr($sql, $i, 1);
			$current2 = substr($sql, $i, 2);
			$current3 = substr($sql, $i, 3);
			$lenEndString = strlen($endString);
			$testEnd = substr($sql, $i, $lenEndString);

			if ($current == '"' || $current == "'" || $current2 == '--'
				|| ($current2 == '/*' && $current3 != '/*!' && $current3 != '/*+')
				|| ($current == '#' && $current3 != '#__')
				|| ($comment && $testEnd == $endString))
			{
				// Check if quoted with previous backslash
				$n = 2;

				while (substr($sql, $i - $n + 1, 1) == '\\' && $n < $i)
				{
					$n++;
				}

				// Not quoted
				if ($n % 2 == 0)
				{
					if ($open)
					{
						if ($testEnd == $endString)
						{
							if ($comment)
							{
								$comment = false;
								if ($lenEndString > 1)
								{
									$i += ($lenEndString - 1);
									$current = substr($sql, $i, 1);
								}
								$start = $i + 1;
							}
							$open = false;
							$endString = '';
						}
					}
					else
					{
						$open = true;
						if ($current2 == '--')
						{
							$endString = "\n";
							$comment = true;
						}
						elseif ($current2 == '/*')
						{
							$endString = '*/';
							$comment = true;
						}
						elseif ($current == '#')
						{
							$endString = "\n";
							$comment = true;
						}
						else
						{
							$endString = $current;
						}
						if ($comment && $start < $i)
						{
							$query = $query . substr($sql, $start, ($i - $start));
						}
					}
				}
			}

			if ($comment)
			{
				$start = $i + 1;
			}

			if (($current == ';' && !$open) || $i == $end - 1)
			{
				if ($start <= $i)
				{
					$query = $query . substr($sql, $start, ($i - $start + 1));
				}
				$query = trim($query);

				if ($query)
				{
					if (($i == $end - 1) && ($current != ';'))
					{
						$query = $query . ';';
					}
					$queries[] = $query;
				}

				$query = '';
				$start = $i + 1;
			}
		}

		return $queries;
	}

	/**
	 * Magic method to provide method alias support for quote() and quoteName().
	 *
	 * @param   string  $method  The called method.
	 * @param   array   $args    The array of arguments passed to the method.
	 *
	 * @return  mixed  The aliased method's return value or null.
	 *
	 * @since   11.1
	 */
	public function __call($method, $args)
	{
		if (empty($args))
		{
			return;
		}

		switch ($method)
		{
			case 'q':
				return $this->quote($args[0], isset($args[1]) ? $args[1] : true);
				break;
			case 'qn':
				return $this->quoteName($args[0], isset($args[1]) ? $args[1] : null);
				break;
		}
	}

	/**
	 * Constructor.
	 *
	 * @param   array  $options  List of options used to configure the connection
	 *
	 * @since   11.1
	 */
	public function __construct($options)
	{
		// Initialise object variables.
		$this->_database = (isset($options['database'])) ? $options['database'] : '';

		$this->tablePrefix = (isset($options['prefix'])) ? $options['prefix'] : 'jos_';
		$this->count = 0;
		$this->errorNum = 0;
		$this->log = array();

		// Set class options.
		$this->options = $options;
	}

	/**
	 * Alter database's character set, obtaining query string from protected member.
	 *
	 * @param   string  $dbName  The database name that will be altered
	 *
	 * @return  string  The query that alter the database query string
	 *
	 * @since   12.2
	 * @throws  RuntimeException
	 */
	public function alterDbCharacterSet($dbName)
	{
		if (is_null($dbName))
		{
			throw new RuntimeException('Database name must not be null.');
		}

		$this->setQuery($this->getAlterDbCharacterSet($dbName));

		return $this->execute();
	}

	/**
	 * Alter a table's character set, obtaining an array of queries to do so from a protected method. The conversion is
	 * wrapped in a transaction, if supported by the database driver. Otherwise the table will be locked before the
	 * conversion. This prevents data corruption.
	 *
	 * @param   string   $tableName  The name of the table to alter
	 * @param   boolean  $rethrow    True to rethrow database exceptions. Default: false (exceptions are suppressed)
	 *
	 * @return  boolean  True if successful
	 *
	 * @since   CMS 3.5.0
	 * @throws  RuntimeException  If the table name is empty
	 * @throws  Exception  Relayed from the database layer if a database error occurs and $rethrow == true
	 */
	public function alterTableCharacterSet($tableName, $rethrow = false)
	{
		if (is_null($tableName))
		{
			throw new RuntimeException('Table name must not be null.');
		}

		$queries = $this->getAlterTableCharacterSet($tableName);

		if (empty($queries))
		{
			return false;
		}

		$hasTransaction = true;

		try
		{
			$this->transactionStart();
		}
		catch (Exception $e)
		{
			$hasTransaction = false;
			$this->lockTable($tableName);
		}

		foreach ($queries as $query)
		{
			try
			{
				$this->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
				if ($hasTransaction)
				{
					$this->transactionRollback();
				}
				else
				{
					$this->unlockTables();
				}

				if ($rethrow)
				{
					throw $e;
				}

				return false;
			}
		}

		if ($hasTransaction)
		{
			try
			{
				$this->transactionCommit();
			}
			catch (Exception $e)
			{
				$this->transactionRollback();

				if ($rethrow)
				{
					throw $e;
				}

				return false;
			}
		}
		else
		{
			$this->unlockTables();
		}

		return true;
	}

	/**
	 * Connects to the database if needed.
	 *
	 * @return  void  Returns void if the database connected successfully.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	abstract public function connect();

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return  boolean  True if connected to the database engine.
	 *
	 * @since   11.1
	 */
	abstract public function connected();

	/**
	 * Create a new database using information from $options object, obtaining query string
	 * from protected member.
	 *
	 * @param   stdClass  $options  Object used to pass user and database name to database driver.
	 * 									This object must have "db_name" and "db_user" set.
	 * @param   boolean   $utf      True if the database supports the UTF-8 character set.
	 *
	 * @return  string  The query that creates database
	 *
	 * @since   12.2
	 * @throws  RuntimeException
	 */
	public function createDatabase($options, $utf = true)
	{
		if (is_null($options))
		{
			throw new RuntimeException('$options object must not be null.');
		}
		elseif (empty($options->db_name))
		{
			throw new RuntimeException('$options object must have db_name set.');
		}
		elseif (empty($options->db_user))
		{
			throw new RuntimeException('$options object must have db_user set.');
		}

		$this->setQuery($this->getCreateDatabaseQuery($options, $utf));

		return $this->execute();
	}

	/**
	 * Disconnects the database.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	abstract public function disconnect();

	/**
	 * Adds a function callable just before disconnecting the database. Parameter of the callable is $this JDatabaseDriver
	 *
	 * @param   callable  $callable  Function to call in disconnect() method just before disconnecting from database
	 *
	 * @return  void
	 *
	 * @since   CMS 3.1.2
	 */
	public function addDisconnectHandler(callable $callable)
	{
		$this->disconnectHandlers[] = $callable;
	}

	/**
	 * Drops a table from the database.
	 *
	 * @param   string   $table     The name of the database table to drop.
	 * @param   boolean  $ifExists  Optionally specify that the table must exist before it is dropped.
	 *
	 * @return  JDatabaseDriver     Returns this object to support chaining.
	 *
	 * @since   11.4
	 * @throws  RuntimeException
	 */
	abstract public function dropTable($table, $ifExists = true);

	/**
	 * Escapes a string for usage in an SQL statement.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Optional parameter to provide extra escaping.
	 *
	 * @return  string   The escaped string.
	 *
	 * @since   11.1
	 */
	abstract public function escape($text, $extra = false);

	/**
	 * Method to fetch a row from the result set cursor as an array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   11.1
	 */
	abstract protected function fetchArray($cursor = null);

	/**
	 * Method to fetch a row from the result set cursor as an associative array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   11.1
	 */
	abstract protected function fetchAssoc($cursor = null);

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @param   mixed   $cursor  The optional result set cursor from which to fetch the row.
	 * @param   string  $class   The class name to use for the returned row object.
	 *
	 * @return  mixed   Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   11.1
	 */
	abstract protected function fetchObject($cursor = null, $class = 'stdClass');

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	abstract protected function freeResult($cursor = null);

	/**
	 * Get the number of affected rows for the previous executed SQL statement.
	 *
	 * @return  integer  The number of affected rows.
	 *
	 * @since   11.1
	 */
	abstract public function getAffectedRows();

	/**
	 * Return the query string to alter the database character set.
	 *
	 * @param   string  $dbName  The database name
	 *
	 * @return  string  The query that alter the database query string
	 *
	 * @since   12.2
	 */
	public function getAlterDbCharacterSet($dbName)
	{
		$charset = $this->utf8mb4 ? 'utf8mb4' : 'utf8';

		return 'ALTER DATABASE ' . $this->quoteName($dbName) . ' CHARACTER SET `' . $charset . '`';
	}

	/**
	 * Get the query strings to alter the character set and collation of a table.
	 *
	 * @param   string  $tableName  The name of the table
	 *
	 * @return  string[]  The queries required to alter the table's character set and collation
	 *
	 * @since   CMS 3.5.0
	 */
	public function getAlterTableCharacterSet($tableName)
	{
		$charset = $this->utf8mb4 ? 'utf8mb4' : 'utf8';
		$collation = $charset . '_unicode_ci';

		$quotedTableName = $this->quoteName($tableName);

		$queries = array();
		$queries[] = "ALTER TABLE $quotedTableName CONVERT TO CHARACTER SET $charset COLLATE $collation";

		/**
		 * We also need to convert each text column, modifying their character set and collation. This allows us to
		 * change, for example, a utf8_bin collated column to a utf8mb4_bin collated column.
		 */
		$sql = "SHOW FULL COLUMNS FROM $quotedTableName";
		$this->setQuery($sql);
		$columns = $this->loadAssocList();
		$columnMods = array();

		if (is_array($columns))
		{
			foreach ($columns as $column)
			{
				// Make sure we are redefining only columns which do support a collation
				$col = (object) $column;

				if (empty($col->Collation))
				{
					continue;
				}

				// Default new collation: utf8_unicode_ci or utf8mb4_unicode_ci
				$newCollation = $charset . '_unicode_ci';
				$collationParts = explode('_', $col->Collation);

				/**
				 * If the collation is in the form charset_collationType_ci or charset_collationType we have to change
				 * the charset but leave the collationType intact (e.g. utf8_bin must become utf8mb4_bin, NOT
				 * utf8mb4_general_ci).
				 */
				if (count($collationParts) >= 2)
				{
					$ci = array_pop($collationParts);
					$collationType = array_pop($collationParts);
					$newCollation = $charset . '_' . $collationType . '_' . $ci;

					/**
					 * When the last part of the old collation is not _ci we have a charset_collationType format,
					 * something like utf8_bin. Therefore the new collation only has *two* parts.
					 */
					if ($ci != 'ci')
					{
						$newCollation = $charset . '_' . $ci;
					}
				}

				// If the old and new collation is the same we don't have to change the collation type
				if (strtolower($newCollation) == strtolower($col->Collation))
				{
					continue;
				}

				$null = $col->Null == 'YES' ? 'NULL' : 'NOT NULL';
				$default = is_null($col->Default) ? '' : "DEFAULT '" . $this->q($col->Default) . "'";
				$columnMods[] = "MODIFY COLUMN `{$col->Field}` {$col->Type} CHARACTER SET $charset COLLATE $newCollation $null $default";
			}
		}

		if (count($columnMods))
		{
			$queries[] = "ALTER TABLE $quotedTableName " .
				implode(',', $columnMods) .
				" CHARACTER SET $charset COLLATE $collation";
		}

		return $queries;
	}

	/**
	 * Automatically downgrade a CREATE TABLE or ALTER TABLE query from utf8mb4 (UTF-8 Multibyte) to plain utf8. Used
	 * when the server doesn't support UTF-8 Multibyte.
	 *
	 * @param   string  $query  The query to convert
	 *
	 * @return  string  The converted query
	 */
	public function convertUtf8mb4QueryToUtf8($query)
	{
		if ($this->hasUTF8mb4Support())
		{
			return $query;
		}

		// If it's not an ALTER TABLE or CREATE TABLE command there's nothing to convert
		if (!preg_match('/^(ALTER|CREATE)\s+TABLE\s+/i', $query))
		{
			return $query;
		}

		// Replace utf8mb4 with utf8 if not within single or double quotes or name quotes
		return preg_replace('/[`"\'][^`"\']*[`"\'](*SKIP)(*FAIL)|utf8mb4/i', 'utf8', $query);
	}

	/**
	 * Return the query string to create new Database.
	 * Each database driver, other than MySQL, need to override this member to return correct string.
	 *
	 * @param   stdClass  $options  Object used to pass user and database name to database driver.
	 *                   This object must have "db_name" and "db_user" set.
	 * @param   boolean   $utf      True if the database supports the UTF-8 character set.
	 *
	 * @return  string  The query that creates database
	 *
	 * @since   12.2
	 */
	protected function getCreateDatabaseQuery($options, $utf)
	{
		if ($utf)
		{
			$charset = $this->utf8mb4 ? 'utf8mb4' : 'utf8';
			$collation = $charset . '_unicode_ci';

			return 'CREATE DATABASE ' . $this->quoteName($options->db_name) . ' CHARACTER SET `' . $charset . '` COLLATE `' . $collation . '`';
		}

		return 'CREATE DATABASE ' . $this->quoteName($options->db_name);
	}

	/**
	 * Method to get the database collation in use by sampling a text field of a table in the database.
	 *
	 * @return  mixed  The collation in use by the database or boolean false if not supported.
	 *
	 * @since   11.1
	 */
	abstract public function getCollation();

	/**
	 * Method to get the database connection collation, as reported by the driver. If the connector doesn't support
	 * reporting this value please return an empty string.
	 *
	 * @return  string
	 */
	public function getConnectionCollation()
	{
		return '';
	}

	/**
	 * Method that provides access to the underlying database connection. Useful for when you need to call a
	 * proprietary method such as postgresql's lo_* methods.
	 *
	 * @return  resource  The underlying database connection resource.
	 *
	 * @since   11.1
	 */
	public function getConnection()
	{
		return $this->connection;
	}

	/**
	 * Get the total number of SQL statements executed by the database driver.
	 *
	 * @return  integer
	 *
	 * @since   11.1
	 */
	public function getCount()
	{
		return $this->count;
	}

	/**
	 * Gets the name of the database used by this conneciton.
	 *
	 * @return  string
	 *
	 * @since   11.4
	 */
	protected function getDatabase()
	{
		return $this->_database;
	}

	/**
	 * Returns a PHP date() function compliant date format for the database driver.
	 *
	 * @return  string  The format string.
	 *
	 * @since   11.1
	 */
	public function getDateFormat()
	{
		return 'Y-m-d H:i:s';
	}

	/**
	 * Get the database driver SQL statement log.
	 *
	 * @return  array  SQL statements executed by the database driver.
	 *
	 * @since   11.1
	 */
	public function getLog()
	{
		return $this->log;
	}

	/**
	 * Get the database driver SQL statement log.
	 *
	 * @return  array  SQL statements executed by the database driver.
	 *
	 * @since   CMS 3.1.2
	 */
	public function getTimings()
	{
		return $this->timings;
	}

	/**
	 * Get the database driver SQL statement log.
	 *
	 * @return  array  SQL statements executed by the database driver.
	 *
	 * @since   CMS 3.1.2
	 */
	public function getCallStacks()
	{
		return $this->callStacks;
	}

	/**
	 * Get the minimum supported database version.
	 *
	 * @return  string  The minimum version number for the database driver.
	 *
	 * @since   12.1
	 */
	public function getMinimum()
	{
		return static::$dbMinimum;
	}

	/**
	 * Get the null or zero representation of a timestamp for the database driver.
	 *
	 * @return  string  Null or zero representation of a timestamp.
	 *
	 * @since   11.1
	 */
	public function getNullDate()
	{
		return $this->nullDate;
	}

	/**
	 * Get the number of returned rows for the previous executed SQL statement.
	 *
	 * @param   resource  $cursor  An optional database cursor resource to extract the row count from.
	 *
	 * @return  integer   The number of returned rows.
	 *
	 * @since   11.1
	 */
	abstract public function getNumRows($cursor = null);

	/**
	 * Get the common table prefix for the database driver.
	 *
	 * @return  string  The common database table prefix.
	 *
	 * @since   11.1
	 */
	public function getPrefix()
	{
		return $this->tablePrefix;
	}

	/**
	 * Gets an exporter class object.
	 *
	 * @return  JDatabaseExporter  An exporter object.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getExporter()
	{
		// Derive the class name from the driver.
		$class = 'JDatabaseExporter' . ucfirst($this->name);

		// Make sure we have an exporter class for this driver.
		if (!class_exists($class))
		{
			// If it doesn't exist we are at an impasse so throw an exception.
			throw new JDatabaseExceptionUnsupported('Database Exporter not found.');
		}

		$o = new $class;
		$o->setDbo($this);

		return $o;
	}

	/**
	 * Gets an importer class object.
	 *
	 * @return  JDatabaseImporter  An importer object.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getImporter()
	{
		// Derive the class name from the driver.
		$class = 'JDatabaseImporter' . ucfirst($this->name);

		// Make sure we have an importer class for this driver.
		if (!class_exists($class))
		{
			// If it doesn't exist we are at an impasse so throw an exception.
			throw new JDatabaseExceptionUnsupported('Database Importer not found');
		}

		$o = new $class;
		$o->setDbo($this);

		return $o;
	}

	/**
	 * Get the name of the database driver. If $this->name is not set it will try guessing the driver name from the
	 * class name.
	 *
	 * @return  string
	 *
	 * @since   CMS 3.5.0
	 */
	public function getName()
	{
		if (empty($this->name))
		{
			$className = get_class($this);
			$className = str_replace('JDatabaseDriver', '', $className);
			$this->name = strtolower($className);
		}

		return $this->name;
	}

	/**
	 * Get the server family type, e.g. mysql, postgresql, oracle, sqlite, mssql. If $this->serverType is not set it
	 * will attempt guessing the server family type from the driver name. If this is not possible the driver name will
	 * be returned instead.
	 *
	 * @return  string
	 *
	 * @since   CMS 3.5.0
	 */
	public function getServerType()
	{
		if (empty($this->serverType))
		{
			$name = $this->getName();

			if (stristr($name, 'mysql') !== false)
			{
				$this->serverType = 'mysql';
			}
			elseif (stristr($name, 'postgre') !== false)
			{
				$this->serverType = 'postgresql';
			}
			elseif (stristr($name, 'oracle') !== false)
			{
				$this->serverType = 'oracle';
			}
			elseif (stristr($name, 'sqlite') !== false)
			{
				$this->serverType = 'sqlite';
			}
			elseif (stristr($name, 'sqlsrv') !== false)
			{
				$this->serverType = 'mssql';
			}
			elseif (stristr($name, 'mssql') !== false)
			{
				$this->serverType = 'mssql';
			}
			else
			{
				$this->serverType = $name;
			}
		}

		return $this->serverType;
	}

	/**
	 * Get the current query object or a new JDatabaseQuery object.
	 *
	 * @param   boolean  $new  False to return the current query object, True to return a new JDatabaseQuery object.
	 *
	 * @return  JDatabaseQuery  The current query object or a new object extending the JDatabaseQuery class.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function getQuery($new = false)
	{
		if ($new)
		{
			// Derive the class name from the driver.
			$class = 'JDatabaseQuery' . ucfirst($this->name);

			// Make sure we have a query class for this driver.
			if (!class_exists($class))
			{
				// If it doesn't exist we are at an impasse so throw an exception.
				throw new JDatabaseExceptionUnsupported('Database Query Class not found.');
			}

			return new $class($this);
		}
		else
		{
			return $this->sql;
		}
	}

	/**
	 * Get a new iterator on the current query.
	 *
	 * @param   string  $column  An option column to use as the iterator key.
	 * @param   string  $class   The class of object that is returned.
	 *
	 * @return  JDatabaseIterator  A new database iterator.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function getIterator($column = null, $class = 'stdClass')
	{
		// Derive the class name from the driver.
		$iteratorClass = 'JDatabaseIterator' . ucfirst($this->name);

		// Make sure we have an iterator class for this driver.
		if (!class_exists($iteratorClass))
		{
			// If it doesn't exist we are at an impasse so throw an exception.
			throw new JDatabaseExceptionUnsupported(sprintf('class *%s* is not defined', $iteratorClass));
		}

		// Return a new iterator
		return new $iteratorClass($this->execute(), $column, $class);
	}

	/**
	 * Retrieves field information about the given tables.
	 *
	 * @param   string   $table     The name of the database table.
	 * @param   boolean  $typeOnly  True (default) to only return field types.
	 *
	 * @return  array  An array of fields by table.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	abstract public function getTableColumns($table, $typeOnly = true);

	/**
	 * Shows the table CREATE statement that creates the given tables.
	 *
	 * @param   mixed  $tables  A table name or a list of table names.
	 *
	 * @return  array  A list of the create SQL for the tables.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	abstract public function getTableCreate($tables);

	/**
	 * Retrieves field information about the given tables.
	 *
	 * @param   mixed  $tables  A table name or a list of table names.
	 *
	 * @return  array  An array of keys for the table(s).
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	abstract public function getTableKeys($tables);

	/**
	 * Method to get an array of all tables in the database.
	 *
	 * @return  array  An array of all the tables in the database.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	abstract public function getTableList();

	/**
	 * Determine whether or not the database engine supports UTF-8 character encoding.
	 *
	 * @return  boolean  True if the database engine supports UTF-8 character encoding.
	 *
	 * @since   11.1
	 * @deprecated 12.3 (Platform) & 4.0 (CMS) - Use hasUTFSupport() instead
	 */
	public function getUTFSupport()
	{
		JLog::add('JDatabaseDriver::getUTFSupport() is deprecated. Use JDatabaseDriver::hasUTFSupport() instead.', JLog::WARNING, 'deprecated');

		return $this->hasUTFSupport();
	}

	/**
	 * Determine whether or not the database engine supports UTF-8 character encoding.
	 *
	 * @return  boolean  True if the database engine supports UTF-8 character encoding.
	 *
	 * @since   12.1
	 */
	public function hasUTFSupport()
	{
		return $this->utf;
	}

	/**
	 * Determine whether the database engine support the UTF-8 Multibyte (utf8mb4) character encoding. This applies to
	 * MySQL databases.
	 *
	 * @return  boolean  True if the database engine supports UTF-8 Multibyte.
	 *
	 * @since   CMS 3.5.0
	 */
	public function hasUTF8mb4Support()
	{
		return $this->utf8mb4;
	}

	/**
	 * Get the version of the database connector
	 *
	 * @return  string  The database connector version.
	 *
	 * @since   11.1
	 */
	abstract public function getVersion();

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  mixed  The value of the auto-increment field from the last inserted row.
	 *
	 * @since   11.1
	 */
	abstract public function insertid();

	/**
	 * Inserts a row into a table based on an object's properties.
	 *
	 * @param   string  $table    The name of the database table to insert into.
	 * @param   object  &$object  A reference to an object whose public properties match the table fields.
	 * @param   string  $key      The name of the primary key. If provided the object property is updated.
	 *
	 * @return  boolean    True on success.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function insertObject($table, &$object, $key = null)
	{
		$fields = array();
		$values = array();

		// Iterate over the object variables to build the query fields and values.
		foreach (get_object_vars($object) as $k => $v)
		{
			// Only process non-null scalars.
			if (is_array($v) or is_object($v) or $v === null)
			{
				continue;
			}

			// Ignore any internal fields.
			if ($k[0] == '_')
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
		$this->setQuery($query);

		if (!$this->execute())
		{
			return false;
		}

		// Update the primary key if it exists.
		$id = $this->insertid();

		if ($key && $id && is_string($key))
		{
			$object->$key = $id;
		}

		return true;
	}

	/**
	 * Method to check whether the installed database version is supported by the database driver
	 *
	 * @return  boolean  True if the database version is supported
	 *
	 * @since   12.1
	 */
	public function isMinimumVersion()
	{
		return version_compare($this->getVersion(), static::$dbMinimum) >= 0;
	}

	/**
	 * Method to get the first row of the result set from the database query as an associative array
	 * of ['field_name' => 'row_value'].
	 *
	 * @return  mixed  The return value or null if the query failed.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function loadAssoc()
	{
		$this->connect();

		$ret = null;

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return;
		}

		// Get the first row from the result set as an associative array.
		if ($array = $this->fetchAssoc($cursor))
		{
			$ret = $array;
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $ret;
	}

	/**
	 * Method to get an array of the result set rows from the database query where each row is an associative array
	 * of ['field_name' => 'row_value'].  The array of rows can optionally be keyed by a field name, but defaults to
	 * a sequential numeric array.
	 *
	 * NOTE: Chosing to key the result array by a non-unique field name can result in unwanted
	 * behavior and should be avoided.
	 *
	 * @param   string  $key     The name of a field on which to key the result array.
	 * @param   string  $column  An optional column name. Instead of the whole row, only this column value will be in
	 * the result array.
	 *
	 * @return  mixed   The return value or null if the query failed.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function loadAssocList($key = null, $column = null)
	{
		$this->connect();

		$array = array();

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return;
		}

		// Get all of the rows from the result set.
		while ($row = $this->fetchAssoc($cursor))
		{
			$value = ($column) ? (isset($row[$column]) ? $row[$column] : $row) : $row;

			if ($key)
			{
				$array[$row[$key]] = $value;
			}
			else
			{
				$array[] = $value;
			}
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $array;
	}

	/**
	 * Method to get an array of values from the <var>$offset</var> field in each row of the result set from
	 * the database query.
	 *
	 * @param   integer  $offset  The row offset to use to build the result array.
	 *
	 * @return  mixed    The return value or null if the query failed.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function loadColumn($offset = 0)
	{
		$this->connect();

		$array = array();

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return;
		}

		// Get all of the rows from the result set as arrays.
		while ($row = $this->fetchArray($cursor))
		{
			$array[] = $row[$offset];
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $array;
	}

	/**
	 * Method to get the next row in the result set from the database query as an object.
	 *
	 * @param   string  $class  The class name to use for the returned row object.
	 *
	 * @return  mixed   The result of the query as an array, false if there are no more rows.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 * @deprecated  12.3 (Platform) & 4.0 (CMS) - Use getIterator() instead
	 */
	public function loadNextObject($class = 'stdClass')
	{
		JLog::add(__METHOD__ . '() is deprecated. Use JDatabaseDriver::getIterator() instead.', JLog::WARNING, 'deprecated');
		$this->connect();

		static $cursor = null;

		// Execute the query and get the result set cursor.
		if (is_null($cursor))
		{
			if (!($cursor = $this->execute()))
			{
				return $this->errorNum ? null : false;
			}
		}

		// Get the next row from the result set as an object of type $class.
		if ($row = $this->fetchObject($cursor, $class))
		{
			return $row;
		}

		// Free up system resources and return.
		$this->freeResult($cursor);
		$cursor = null;

		return false;
	}

	/**
	 * Method to get the next row in the result set from the database query as an array.
	 *
	 * @return  mixed  The result of the query as an array, false if there are no more rows.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 * @deprecated  4.0 (CMS)  Use JDatabaseDriver::getIterator() instead
	 */
	public function loadNextRow()
	{
		JLog::add(__METHOD__ . '() is deprecated. Use JDatabaseDriver::getIterator() instead.', JLog::WARNING, 'deprecated');
		$this->connect();

		static $cursor = null;

		// Execute the query and get the result set cursor.
		if (is_null($cursor))
		{
			if (!($cursor = $this->execute()))
			{
				return $this->errorNum ? null : false;
			}
		}

		// Get the next row from the result set as an object of type $class.
		if ($row = $this->fetchArray($cursor))
		{
			return $row;
		}

		// Free up system resources and return.
		$this->freeResult($cursor);
		$cursor = null;

		return false;
	}

	/**
	 * Method to get the first row of the result set from the database query as an object.
	 *
	 * @param   string  $class  The class name to use for the returned row object.
	 *
	 * @return  mixed   The return value or null if the query failed.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function loadObject($class = 'stdClass')
	{
		$this->connect();

		$ret = null;

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return;
		}

		// Get the first row from the result set as an object of type $class.
		if ($object = $this->fetchObject($cursor, $class))
		{
			$ret = $object;
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $ret;
	}

	/**
	 * Method to get an array of the result set rows from the database query where each row is an object.  The array
	 * of objects can optionally be keyed by a field name, but defaults to a sequential numeric array.
	 *
	 * NOTE: Choosing to key the result array by a non-unique field name can result in unwanted
	 * behavior and should be avoided.
	 *
	 * @param   string  $key    The name of a field on which to key the result array.
	 * @param   string  $class  The class name to use for the returned row objects.
	 *
	 * @return  mixed   The return value or null if the query failed.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function loadObjectList($key = '', $class = 'stdClass')
	{
		$this->connect();

		$array = array();

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return;
		}

		// Get all of the rows from the result set as objects of type $class.
		while ($row = $this->fetchObject($cursor, $class))
		{
			if ($key)
			{
				$array[$row->$key] = $row;
			}
			else
			{
				$array[] = $row;
			}
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $array;
	}

	/**
	 * Method to get the first field of the first row of the result set from the database query.
	 *
	 * @return  mixed  The return value or null if the query failed.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function loadResult()
	{
		$this->connect();

		$ret = null;

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return;
		}

		// Get the first row from the result set as an array.
		if ($row = $this->fetchArray($cursor))
		{
			$ret = $row[0];
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $ret;
	}

	/**
	 * Method to get the first row of the result set from the database query as an array.  Columns are indexed
	 * numerically so the first column in the result set would be accessible via <var>$row[0]</var>, etc.
	 *
	 * @return  mixed  The return value or null if the query failed.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function loadRow()
	{
		$this->connect();

		$ret = null;

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return;
		}

		// Get the first row from the result set as an array.
		if ($row = $this->fetchArray($cursor))
		{
			$ret = $row;
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $ret;
	}

	/**
	 * Method to get an array of the result set rows from the database query where each row is an array.  The array
	 * of objects can optionally be keyed by a field offset, but defaults to a sequential numeric array.
	 *
	 * NOTE: Choosing to key the result array by a non-unique field can result in unwanted
	 * behavior and should be avoided.
	 *
	 * @param   string  $key  The name of a field on which to key the result array.
	 *
	 * @return  mixed   The return value or null if the query failed.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function loadRowList($key = null)
	{
		$this->connect();

		$array = array();

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return;
		}

		// Get all of the rows from the result set as arrays.
		while ($row = $this->fetchArray($cursor))
		{
			if ($key !== null)
			{
				$array[$row[$key]] = $row;
			}
			else
			{
				$array[] = $row;
			}
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $array;
	}

	/**
	 * Locks a table in the database.
	 *
	 * @param   string  $tableName  The name of the table to unlock.
	 *
	 * @return  JDatabaseDriver     Returns this object to support chaining.
	 *
	 * @since   11.4
	 * @throws  RuntimeException
	 */
	abstract public function lockTable($tableName);

	/**
	 * Quotes and optionally escapes a string to database requirements for use in database queries.
	 *
	 * @param   mixed    $text    A string or an array of strings to quote.
	 * @param   boolean  $escape  True (default) to escape the string, false to leave it unchanged.
	 *
	 * @return  string  The quoted input string.
	 *
	 * @note    Accepting an array of strings was added in 12.3.
	 * @since   11.1
	 */
	public function quote($text, $escape = true)
	{
		if (is_array($text))
		{
			foreach ($text as $k => $v)
			{
				$text[$k] = $this->quote($v, $escape);
			}

			return $text;
		}
		else
		{
			return '\'' . ($escape ? $this->escape($text) : $text) . '\'';
		}
	}

	/**
	 * Wrap an SQL statement identifier name such as column, table or database names in quotes to prevent injection
	 * risks and reserved word conflicts.
	 *
	 * @param   mixed  $name  The identifier name to wrap in quotes, or an array of identifier names to wrap in quotes.
	 *                        Each type supports dot-notation name.
	 * @param   mixed  $as    The AS query part associated to $name. It can be string or array, in latter case it has to be
	 *                        same length of $name; if is null there will not be any AS part for string or array element.
	 *
	 * @return  mixed  The quote wrapped name, same type of $name.
	 *
	 * @since   11.1
	 */
	public function quoteName($name, $as = null)
	{
		if (is_string($name))
		{
			$quotedName = $this->quoteNameStr(explode('.', $name));

			$quotedAs = '';

			if (!is_null($as))
			{
				settype($as, 'array');
				$quotedAs .= ' AS ' . $this->quoteNameStr($as);
			}

			return $quotedName . $quotedAs;
		}
		else
		{
			$fin = array();

			if (is_null($as))
			{
				foreach ($name as $str)
				{
					$fin[] = $this->quoteName($str);
				}
			}
			elseif (is_array($name) && (count($name) == count($as)))
			{
				$count = count($name);

				for ($i = 0; $i < $count; $i++)
				{
					$fin[] = $this->quoteName($name[$i], $as[$i]);
				}
			}

			return $fin;
		}
	}

	/**
	 * Quote strings coming from quoteName call.
	 *
	 * @param   array  $strArr  Array of strings coming from quoteName dot-explosion.
	 *
	 * @return  string  Dot-imploded string of quoted parts.
	 *
	 * @since 11.3
	 */
	protected function quoteNameStr($strArr)
	{
		$parts = array();
		$q = $this->nameQuote;

		foreach ($strArr as $part)
		{
			if (is_null($part))
			{
				continue;
			}

			if (strlen($q) == 1)
			{
				$parts[] = $q . $part . $q;
			}
			else
			{
				$parts[] = $q{0} . $part . $q{1};
			}
		}

		return implode('.', $parts);
	}

	/**
	 * This function replaces a string identifier <var>$prefix</var> with the string held is the
	 * <var>tablePrefix</var> class variable.
	 *
	 * @param   string  $sql     The SQL statement to prepare.
	 * @param   string  $prefix  The common table prefix.
	 *
	 * @return  string  The processed SQL statement.
	 *
	 * @since   11.1
	 */
	public function replacePrefix($sql, $prefix = '#__')
	{
		$startPos = 0;
		$literal = '';

		$sql = trim($sql);
		$n = strlen($sql);

		while ($startPos < $n)
		{
			$ip = strpos($sql, $prefix, $startPos);

			if ($ip === false)
			{
				break;
			}

			$j = strpos($sql, "'", $startPos);
			$k = strpos($sql, '"', $startPos);

			if (($k !== false) && (($k < $j) || ($j === false)))
			{
				$quoteChar = '"';
				$j = $k;
			}
			else
			{
				$quoteChar = "'";
			}

			if ($j === false)
			{
				$j = $n;
			}

			$literal .= str_replace($prefix, $this->tablePrefix, substr($sql, $startPos, $j - $startPos));
			$startPos = $j;

			$j = $startPos + 1;

			if ($j >= $n)
			{
				break;
			}

			// Quote comes first, find end of quote
			while (true)
			{
				$k = strpos($sql, $quoteChar, $j);
				$escaped = false;

				if ($k === false)
				{
					break;
				}

				$l = $k - 1;

				while ($l >= 0 && $sql{$l} == '\\')
				{
					$l--;
					$escaped = !$escaped;
				}

				if ($escaped)
				{
					$j = $k + 1;
					continue;
				}

				break;
			}

			if ($k === false)
			{
				// Error in the query - no end quote; ignore it
				break;
			}

			$literal .= substr($sql, $startPos, $k - $startPos + 1);
			$startPos = $k + 1;
		}

		if ($startPos < $n)
		{
			$literal .= substr($sql, $startPos, $n - $startPos);
		}

		return $literal;
	}

	/**
	 * Renames a table in the database.
	 *
	 * @param   string  $oldTable  The name of the table to be renamed
	 * @param   string  $newTable  The new name for the table.
	 * @param   string  $backup    Table prefix
	 * @param   string  $prefix    For the table - used to rename constraints in non-mysql databases
	 *
	 * @return  JDatabaseDriver    Returns this object to support chaining.
	 *
	 * @since   11.4
	 * @throws  RuntimeException
	 */
	abstract public function renameTable($oldTable, $newTable, $backup = null, $prefix = null);

	/**
	 * Select a database for use.
	 *
	 * @param   string  $database  The name of the database to select for use.
	 *
	 * @return  boolean  True if the database was successfully selected.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	abstract public function select($database);

	/**
	 * Sets the database debugging state for the driver.
	 *
	 * @param   boolean  $level  True to enable debugging.
	 *
	 * @return  boolean  The old debugging level.
	 *
	 * @since   11.1
	 */
	public function setDebug($level)
	{
		$previous = $this->debug;
		$this->debug = (bool) $level;

		return $previous;
	}

	/**
	 * Sets the SQL statement string for later execution.
	 *
	 * @param   mixed    $query   The SQL statement to set either as a JDatabaseQuery object or a string.
	 * @param   integer  $offset  The affected row offset to set.
	 * @param   integer  $limit   The maximum affected rows to set.
	 *
	 * @return  JDatabaseDriver  This object to support method chaining.
	 *
	 * @since   11.1
	 */
	public function setQuery($query, $offset = 0, $limit = 0)
	{
		$this->sql = $query;

		if ($query instanceof JDatabaseQueryLimitable)
		{
			if (!$limit && $query->limit)
			{
				$limit = $query->limit;
			}

			if (!$offset && $query->offset)
			{
				$offset = $query->offset;
			}

			$query->setLimit($limit, $offset);
		}
		else
		{
			$this->limit = (int) max(0, $limit);
			$this->offset = (int) max(0, $offset);
		}

		return $this;
	}

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	abstract public function setUtf();

	/**
	 * Method to commit a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, commit to the last savepoint.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	abstract public function transactionCommit($toSavepoint = false);

	/**
	 * Method to roll back a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, rollback to the last savepoint.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	abstract public function transactionRollback($toSavepoint = false);

	/**
	 * Method to initialize a transaction.
	 *
	 * @param   boolean  $asSavepoint  If true and a transaction is already active, a savepoint will be created.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	abstract public function transactionStart($asSavepoint = false);

	/**
	 * Method to truncate a table.
	 *
	 * @param   string  $table  The table to truncate
	 *
	 * @return  void
	 *
	 * @since   11.3
	 * @throws  RuntimeException
	 */
	public function truncateTable($table)
	{
		$this->setQuery('TRUNCATE TABLE ' . $this->quoteName($table));
		$this->execute();
	}

	/**
	 * Updates a row in a table based on an object's properties.
	 *
	 * @param   string   $table    The name of the database table to update.
	 * @param   object   &$object  A reference to an object whose public properties match the table fields.
	 * @param   array    $key      The name of the primary key.
	 * @param   boolean  $nulls    True to update null fields or false to ignore them.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function updateObject($table, &$object, $key, $nulls = false)
	{
		$fields = array();
		$where = array();

		if (is_string($key))
		{
			$key = array($key);
		}

		if (is_object($key))
		{
			$key = (array) $key;
		}

		// Create the base update statement.
		$statement = 'UPDATE ' . $this->quoteName($table) . ' SET %s WHERE %s';

		// Iterate over the object variables to build the query fields/value pairs.
		foreach (get_object_vars($object) as $k => $v)
		{
			// Only process scalars that are not internal fields.
			if (is_array($v) or is_object($v) or $k[0] == '_')
			{
				continue;
			}

			// Set the primary key to the WHERE clause instead of a field to update.
			if (in_array($k, $key))
			{
				$where[] = $this->quoteName($k) . '=' . $this->quote($v);
				continue;
			}

			// Prepare and sanitize the fields and values for the database query.
			if ($v === null)
			{
				// If the value is null and we want to update nulls then set it.
				if ($nulls)
				{
					$val = 'NULL';
				}
				// If the value is null and we do not want to update nulls then ignore this field.
				else
				{
					continue;
				}
			}
			// The field is not null so we prep it for update.
			else
			{
				$val = $this->quote($v);
			}

			// Add the field to be updated.
			$fields[] = $this->quoteName($k) . '=' . $val;
		}

		// We don't have any fields to update.
		if (empty($fields))
		{
			return true;
		}

		// Set the query and execute the update.
		$this->setQuery(sprintf($statement, implode(',', $fields), implode(' AND ', $where)));

		return $this->execute();
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	abstract public function execute();

	/**
	 * Unlocks tables in the database.
	 *
	 * @return  JDatabaseDriver  Returns this object to support chaining.
	 *
	 * @since   11.4
	 * @throws  RuntimeException
	 */
	abstract public function unlockTables();
}
