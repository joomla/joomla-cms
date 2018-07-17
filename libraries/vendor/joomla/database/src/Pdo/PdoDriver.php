<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Pdo;

use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseEvents;
use Joomla\Database\Event\ConnectionEvent;
use Joomla\Database\Exception\ConnectionFailureException;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\Exception\PrepareStatementFailureException;
use Joomla\Database\Exception\UnsupportedAdapterException;
use Joomla\Database\StatementInterface;

/**
 * Joomla Framework PDO Database Driver Class
 *
 * @link   https://secure.php.net/pdo
 * @since  1.0
 */
abstract class PdoDriver extends DatabaseDriver
{
	/**
	 * The database connection resource.
	 *
	 * @var    \PDO
	 * @since  1.0
	 */
	protected $connection;

	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $name = 'pdo';

	/**
	 * The character(s) used to quote SQL statement names such as table names or field names, etc.
	 *
	 * If a single character string the same character is used for both sides of the quoted name, else the first character will be used for the
	 * opening quote and the second for the closing quote.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $nameQuote = "'";

	/**
	 * The null or zero representation of a timestamp for the database driver.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $nullDate = '0000-00-00 00:00:00';

	/**
	 * Constructor.
	 *
	 * @param   array  $options  List of options used to configure the connection
	 *
	 * @since   1.0
	 */
	public function __construct(array $options)
	{
		// Get some basic values from the options.
		$options['driver']        = isset($options['driver']) ? $options['driver'] : 'odbc';
		$options['dsn']           = isset($options['dsn']) ? $options['dsn'] : '';
		$options['host']          = isset($options['host']) ? $options['host'] : 'localhost';
		$options['database']      = isset($options['database']) ? $options['database'] : '';
		$options['user']          = isset($options['user']) ? $options['user'] : '';
		$options['port']          = isset($options['port']) ? (int) $options['port'] : null;
		$options['password']      = isset($options['password']) ? $options['password'] : '';
		$options['driverOptions'] = isset($options['driverOptions']) ? $options['driverOptions'] : [];

		// Finalize initialisation
		parent::__construct($options);
	}

	/**
	 * Destructor.
	 *
	 * @since   1.0
	 */
	public function __destruct()
	{
		$this->disconnect();
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

		// Make sure the PDO extension for PHP is installed and enabled.
		if (!static::isSupported())
		{
			throw new UnsupportedAdapterException('PDO Extension is not available.', 1);
		}

		// Find the correct PDO DSN Format to use:
		switch ($this->options['driver'])
		{
			case 'cubrid':
				$this->options['port'] = isset($this->options['port']) ? $this->options['port'] : 33000;

				$format = 'cubrid:host=#HOST#;port=#PORT#;dbname=#DBNAME#';

				$replace = ['#HOST#', '#PORT#', '#DBNAME#'];
				$with    = [$this->options['host'], $this->options['port'], $this->options['database']];

				break;

			case 'dblib':
				$this->options['port'] = isset($this->options['port']) ? $this->options['port'] : 1433;

				$format = 'dblib:host=#HOST#;port=#PORT#;dbname=#DBNAME#';

				$replace = ['#HOST#', '#PORT#', '#DBNAME#'];
				$with    = [$this->options['host'], $this->options['port'], $this->options['database']];

				break;

			case 'firebird':
				$this->options['port'] = isset($this->options['port']) ? $this->options['port'] : 3050;

				$format = 'firebird:dbname=#DBNAME#';

				$replace = ['#DBNAME#'];
				$with    = [$this->options['database']];

				break;

			case 'ibm':
				$this->options['port'] = isset($this->options['port']) ? $this->options['port'] : 56789;

				if (!empty($this->options['dsn']))
				{
					$format = 'ibm:DSN=#DSN#';

					$replace = ['#DSN#'];
					$with    = [$this->options['dsn']];
				}
				else
				{
					$format = 'ibm:hostname=#HOST#;port=#PORT#;database=#DBNAME#';

					$replace = ['#HOST#', '#PORT#', '#DBNAME#'];
					$with    = [$this->options['host'], $this->options['port'], $this->options['database']];
				}

				break;

			case 'informix':
				$this->options['port']     = isset($this->options['port']) ? $this->options['port'] : 1526;
				$this->options['protocol'] = isset($this->options['protocol']) ? $this->options['protocol'] : 'onsoctcp';

				if (!empty($this->options['dsn']))
				{
					$format = 'informix:DSN=#DSN#';

					$replace = ['#DSN#'];
					$with    = [$this->options['dsn']];
				}
				else
				{
					$format = 'informix:host=#HOST#;service=#PORT#;database=#DBNAME#;server=#SERVER#;protocol=#PROTOCOL#';

					$replace = ['#HOST#', '#PORT#', '#DBNAME#', '#SERVER#', '#PROTOCOL#'];
					$with    = [
						$this->options['host'],
						$this->options['port'],
						$this->options['database'],
						$this->options['server'],
						$this->options['protocol']
					];
				}

				break;

			case 'mssql':
				$this->options['port'] = isset($this->options['port']) ? $this->options['port'] : 1433;

				$format = 'mssql:host=#HOST#;port=#PORT#;dbname=#DBNAME#';

				$replace = ['#HOST#', '#PORT#', '#DBNAME#'];
				$with    = [$this->options['host'], $this->options['port'], $this->options['database']];

				break;

			case 'mysql':
				$this->options['port'] = isset($this->options['port']) ? $this->options['port'] : 3306;

				$format = 'mysql:host=#HOST#;port=#PORT#;dbname=#DBNAME#;charset=#CHARSET#';

				$replace = ['#HOST#', '#PORT#', '#DBNAME#', '#CHARSET#'];
				$with    = [$this->options['host'], $this->options['port'], $this->options['database'], $this->options['charset']];

				break;

			case 'oci':
				$this->options['port']    = isset($this->options['port']) ? $this->options['port'] : 1521;
				$this->options['charset'] = isset($this->options['charset']) ? $this->options['charset'] : 'AL32UTF8';

				if (!empty($this->options['dsn']))
				{
					$format = 'oci:dbname=#DSN#';

					$replace = ['#DSN#'];
					$with    = [$this->options['dsn']];
				}
				else
				{
					$format = 'oci:dbname=//#HOST#:#PORT#/#DBNAME#';

					$replace = ['#HOST#', '#PORT#', '#DBNAME#'];
					$with    = [$this->options['host'], $this->options['port'], $this->options['database']];
				}

				$format .= ';charset=' . $this->options['charset'];

				break;

			case 'odbc':
				$format = 'odbc:DSN=#DSN#;UID:#USER#;PWD=#PASSWORD#';

				$replace = ['#DSN#', '#USER#', '#PASSWORD#'];
				$with    = [$this->options['dsn'], $this->options['user'], $this->options['password']];

				break;

			case 'pgsql':
				$this->options['port'] = isset($this->options['port']) ? $this->options['port'] : 5432;

				$format = 'pgsql:host=#HOST#;port=#PORT#;dbname=#DBNAME#';

				$replace = ['#HOST#', '#PORT#', '#DBNAME#'];
				$with    = [$this->options['host'], $this->options['port'], $this->options['database']];

				break;

			case 'sqlite':
				if (isset($this->options['version']) && $this->options['version'] == 2)
				{
					$format = 'sqlite2:#DBNAME#';
				}
				else
				{
					$format = 'sqlite:#DBNAME#';
				}

				$replace = ['#DBNAME#'];
				$with    = [$this->options['database']];

				break;

			case 'sybase':
				$this->options['port'] = isset($this->options['port']) ? $this->options['port'] : 1433;

				$format = 'mssql:host=#HOST#;port=#PORT#;dbname=#DBNAME#';

				$replace = ['#HOST#', '#PORT#', '#DBNAME#'];
				$with    = [$this->options['host'], $this->options['port'], $this->options['database']];

				break;

			default:
				throw new UnsupportedAdapterException('The ' . $this->options['driver'] . ' driver is not supported.');
		}

		// Create the connection string:
		$connectionString = str_replace($replace, $with, $format);

		try
		{
			$this->connection = new \PDO(
				$connectionString,
				$this->options['user'],
				$this->options['password'],
				$this->options['driverOptions']
			);
		}
		catch (\PDOException $e)
		{
			throw new ConnectionFailureException('Could not connect to PDO: ' . $e->getMessage(), $e->getCode(), $e);
		}

		$this->setOption(\PDO::ATTR_STATEMENT_CLASS, [PdoStatement::class, []]);
		$this->setOption(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

		$this->dispatchEvent(new ConnectionEvent(DatabaseEvents::POST_CONNECT, $this));
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
		if (is_int($text))
		{
			return $text;
		}

		if (is_float($text))
		{
			// Force the dot as a decimal point.
			return str_replace(',', '.', $text);
		}

		$text = str_replace("'", "''", $text);

		return addcslashes($text, "\000\n\r\\\032");
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 * @throws  \Exception
	 * @throws  \RuntimeException
	 */
	public function execute()
	{
		$this->connect();

		// Take a local copy so that we don't modify the original query and cause issues later
		$sql = $this->replacePrefix((string) $this->sql);

		// Increment the query counter.
		$this->count++;

		// If there is a monitor registered, let it know we are starting this query
		if ($this->monitor)
		{
			$this->monitor->startQuery($sql);
		}

		// Execute the query.
		$this->executed = false;

		// Bind the variables
		$bounded =& $this->sql->getBounded();

		foreach ($bounded as $key => $obj)
		{
			$this->statement->bindParam($key, $obj->value, $obj->dataType, $obj->length, $obj->driverOptions);
		}

		try
		{
			$this->executed = $this->statement->execute();

			// If there is a monitor registered, let it know we have finished this query
			if ($this->monitor)
			{
				$this->monitor->stopQuery();
			}

			return true;
		}
		catch (\PDOException $exception)
		{
			// If there is a monitor registered, let it know we have finished this query
			if ($this->monitor)
			{
				$this->monitor->stopQuery();
			}

			// Get the error number and message before we execute any more queries.
			$errorNum = (int) $this->statement->errorCode();
			$errorMsg = (string) implode(', ', $this->statement->errorInfo());

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
				return $this->execute();
			}

			// Throw the normal query exception.
			throw new ExecutionFailureException($sql, $errorMsg, $errorNum);
		}
	}

	/**
	 * Retrieve a PDO database connection attribute
	 * http://www.php.net/manual/en/pdo.getattribute.php
	 *
	 * Usage: $db->getOption(PDO::ATTR_CASE);
	 *
	 * @param   mixed  $key  One of the PDO::ATTR_* Constants
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public function getOption($key)
	{
		$this->connect();

		return $this->connection->getAttribute($key);
	}

	/**
	 * Get the version of the database connector.
	 *
	 * @return  string  The database connector version.
	 *
	 * @since   1.5.0
	 */
	public function getVersion()
	{
		$this->connect();

		return $this->getOption(\PDO::ATTR_SERVER_VERSION);
	}

	/**
	 * Get a query to run and verify the database is operational.
	 *
	 * @return  string  The query to check the health of the DB.
	 *
	 * @since   1.0
	 */
	public function getConnectedQuery()
	{
		return 'SELECT 1';
	}

	/**
	 * Sets an attribute on the PDO database handle.
	 * http://www.php.net/manual/en/pdo.setattribute.php
	 *
	 * Usage: $db->setOption(PDO::ATTR_CASE, PDO::CASE_UPPER);
	 *
	 * @param   integer  $key    One of the PDO::ATTR_* Constants
	 * @param   mixed    $value  One of the associated PDO Constants
	 *                           related to the particular attribute
	 *                           key.
	 *
	 * @return boolean
	 *
	 * @since  1.0
	 */
	public function setOption($key, $value)
	{
		$this->connect();

		return $this->connection->setAttribute($key, $value);
	}

	/**
	 * Test to see if the PDO extension is available.
	 * Override as needed to check for specific PDO Drivers.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 */
	public static function isSupported()
	{
		return defined('\\PDO::ATTR_DRIVER_NAME');
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
		// Flag to prevent recursion into this function.
		static $checkingConnected = false;

		if ($checkingConnected)
		{
			// Reset this flag and throw an exception.
			$checkingConnected = true;
			die('Recursion trying to check if connected.');
		}

		// Backup the query state.
		$sql       = $this->sql;
		$limit     = $this->limit;
		$offset    = $this->offset;
		$statement = $this->statement;

		try
		{
			// Set the checking connection flag.
			$checkingConnected = true;

			// Run a simple query to check the connection.
			$this->setQuery($this->getConnectedQuery());
			$status = (bool) $this->loadResult();
		}
		catch (\Exception $e)
			// If we catch an exception here, we must not be connected.
		{
			$status = false;
		}

		// Restore the query state.
		$this->sql         = $sql;
		$this->limit       = $limit;
		$this->offset      = $offset;
		$this->statement   = $statement;
		$checkingConnected = false;

		return $status;
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  string  The value of the auto-increment field from the last inserted row.
	 *
	 * @since   1.0
	 */
	public function insertid()
	{
		$this->connect();

		// Error suppress this to prevent PDO warning us that the driver doesn't support this operation.
		return @$this->connection->lastInsertId();
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
		return false;
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

		if (!$toSavepoint || $this->transactionDepth === 1)
		{
			$this->connection->commit();
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
		$this->connect();

		if (!$toSavepoint || $this->transactionDepth === 1)
		{
			$this->connection->rollBack();
		}

		$this->transactionDepth--;
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
			$this->connection->beginTransaction();
		}

		$this->transactionDepth++;
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
		try
		{
			return $this->connection->prepare($query, $this->options['driverOptions']);
		}
		catch (\PDOException $exception)
		{
			throw new PrepareStatementFailureException($exception->getMessage(), $exception->getCode(), $exception);
		}
	}

	/**
	 * PDO does not support serialize
	 *
	 * @return  array
	 *
	 * @since   1.0
	 */
	public function __sleep()
	{
		$serializedProperties = [];

		$reflect = new \ReflectionClass($this);

		// Get properties of the current class
		$properties = $reflect->getProperties();

		foreach ($properties as $property)
		{
			// Do not serialize properties that are PDO
			if ($property->isStatic() === false && !($this->{$property->name} instanceof \PDO))
			{
				$serializedProperties[] = $property->name;
			}
		}

		return $serializedProperties;
	}

	/**
	 * Wake up after serialization
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function __wakeup()
	{
		// Get connection back
		$this->__construct($this->options);
	}
}
