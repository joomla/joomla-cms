<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Driver;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Driver\Query\Pdomysql as QueryPdomysql;
use Exception;
use PDO;
use PDOException;
use PDOStatement;
use ReflectionClass;
use RuntimeException;

/**
 * PDO MySQL database driver for Akeeba Engine
 *
 * Based on Joomla! Platform 12.1
 */
class Pdomysql extends Mysql
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 */
	public $name = 'pdomysql';
	/** @var PDO The db connection resource */
	protected $connection = null;
	/** @var PDOStatement The database connection cursor from the last query. */
	protected $cursor;
	/** @var string Connection character set */
	protected $charset = 'utf8mb4';
	/** @var array Driver options for PDO */
	protected $driverOptions = [];

	/**
	 * Database object constructor
	 *
	 * @param   array  $options  List of options used to configure the connection
	 */
	public function __construct($options)
	{
		$this->driverType = 'mysql';

		// Init
		$this->nameQuote = '`';

		$host          = array_key_exists('host', $options) ? $options['host'] : 'localhost';
		$port          = array_key_exists('port', $options) ? $options['port'] : '';
		$user          = array_key_exists('user', $options) ? $options['user'] : '';
		$password      = array_key_exists('password', $options) ? $options['password'] : '';
		$database      = array_key_exists('database', $options) ? $options['database'] : '';
		$prefix        = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		$select        = array_key_exists('select', $options) ? $options['select'] : true;
		$charset       = array_key_exists('charset', $options) ? $options['charset'] : 'utf8mb4';
		$driverOptions = array_key_exists('driverOptions', $options) ? $options['driverOptions'] : [];
		$connection    = array_key_exists('connection', $options) ? $options['connection'] : null;
		$socket        = null;

		// Figure out if a port is included in the host name
		if (empty($port))
		{
			// Unlike mysql_connect(), mysqli_connect() takes the port and socket
			// as separate arguments. Therefore, we have to extract them from the
			// host string.
			$port       = null;
			$socket     = null;
			$targetSlot = substr(strstr($host, ":"), 1);
			if (!empty($targetSlot))
			{
				// Get the port number or socket name
				if (is_numeric($targetSlot))
				{
					$port = $targetSlot;
				}
				else
				{
					$socket = $targetSlot;
				}

				// Extract the host name only
				$host = substr($host, 0, strlen($host) - (strlen($targetSlot) + 1));
				// This will take care of the following notation: ":3306"
				if ($host == '')
				{
					$host = 'localhost';
				}
			}
		}

		// Open the connection
		$this->host           = $host;
		$this->user           = $user;
		$this->password       = $password;
		$this->port           = $port;
		$this->socket         = $socket;
		$this->charset        = $charset;
		$this->_database      = $database;
		$this->selectDatabase = $select;
		$this->driverOptions  = $driverOptions;
		$this->tablePrefix    = $prefix;
		$this->connection     = $connection;
		$this->errorNum       = 0;
		$this->count          = 0;
		$this->log            = [];
		$this->options        = $options;

		if (!is_object($this->connection))
		{
			$this->open();
		}
	}

	/**
	 * Test to see if the MySQL connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 */
	public static function isSupported()
	{
		if (!defined('\PDO::ATTR_DRIVER_NAME'))
		{
			return false;
		}

		return in_array('mysql', PDO::getAvailableDrivers());
	}

	public function open()
	{
		if ($this->connected())
		{
			return;
		}
		else
		{
			$this->close();
		}

		if (!isset($this->charset))
		{
			$this->charset = 'utf8mb4';
		}

		$this->port = $this->port ?: 3306;

		$format = 'mysql:host=#HOST#;port=#PORT#;dbname=#DBNAME#;charset=#CHARSET#';

		if ($this->socket)
		{
			$format = 'mysql:socket=#SOCKET#;dbname=#DBNAME#;charset=#CHARSET#';
		}

		$replace = ['#HOST#', '#PORT#', '#SOCKET#', '#DBNAME#', '#CHARSET#'];
		$with    = [$this->host, $this->port, $this->socket, $this->_database, $this->charset];

		// Create the connection string:
		$connectionString = str_replace($replace, $with, $format);

		// connect to the server
		try
		{
			$this->connection = new PDO(
				$connectionString,
				$this->user,
				$this->password,
				$this->driverOptions
			);
		}
		catch (PDOException $e)
		{
			// If we tried connecting through utf8mb4 and we failed let's retry with regular utf8
			if ($this->charset == 'utf8mb4')
			{
				$this->charset = 'UTF8';
				$this->open();

				return;
			}

			$this->errorNum = 2;
			$this->errorMsg = 'Could not connect to MySQL via PDO: ' . $e->getMessage();

			return;
		}

		// Reset the SQL mode of the connection
		try
		{
			$this->connection->exec("SET @@SESSION.sql_mode = '';");
		}
			// Ignore any exceptions (incompatible MySQL versions)
		catch (Exception $e)
		{
		}

		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

		if ($this->selectDatabase && !empty($this->_database))
		{
			$this->select($this->_database);
		}

		$this->freeResult();
	}

	public function close()
	{
		$return = false;

		if (is_object($this->cursor))
		{
			$this->cursor->closeCursor();
		}

		$this->connection = null;

		return $return;
	}

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * @param   string   $text   The string to be escaped.
	 * @param   boolean  $extra  Optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 */
	public function escape($text, $extra = false)
	{
		if (is_int($text) || is_float($text))
		{
			return $text;
		}

		$result = substr($this->connection->quote($text), 1, -1);

		if ($extra)
		{
			$result = addcslashes($result, '%_');
		}

		return $result;
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 */
	public function query()
	{
		static $isReconnecting = false;

		if (!is_object($this->connection))
		{
			$this->open();
		}

		$this->freeResult();

		// Take a local copy so that we don't modify the original query and cause issues later
		$query = $this->replacePrefix((string) $this->sql);

		if ($this->limit > 0 || $this->offset > 0)
		{
			$query .= ' LIMIT ' . $this->offset . ', ' . $this->limit;
		}

		// Increment the query counter.
		$this->count++;

		// If debugging is enabled then let's log the query.
		if ($this->debug)
		{
			// Add the query to the object queue.
			$this->log[] = $query;
		}

		// Reset the error values.
		$this->errorNum = 0;
		$this->errorMsg = '';

		// Execute the query. Error suppression is used here to prevent warnings/notices that the connection has been lost.
		try
		{
			$this->cursor = $this->connection->query($query);
		}
		catch (Exception $e)
		{
		}

		// If an error occurred handle it.
		if (!$this->cursor)
		{
			$errorInfo      = $this->connection->errorInfo();
			$this->errorNum = $errorInfo[1];
			$this->errorMsg = $errorInfo[2] . ' SQL=' . $query;

			// Check if the server was disconnected.
			if (!$this->connected() && !$isReconnecting)
			{
				$isReconnecting = true;

				try
				{
					// Attempt to reconnect.
					$this->connection = null;
					$this->open();
				}
					// If connect fails, ignore that exception and throw the normal exception.
				catch (RuntimeException $e)
				{
					throw new RuntimeException($this->errorMsg, $this->errorNum);
				}

				// Since we were able to reconnect, run the query again.
				$result         = $this->query();
				$isReconnecting = false;

				return $result;
			}
			// The server was not disconnected.
			else
			{
				throw new RuntimeException($this->errorMsg, $this->errorNum);
			}
		}

		return $this->cursor;
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return  boolean  True if connected to the database engine.
	 */
	public function connected()
	{
		if (!is_object($this->connection))
		{
			return false;
		}

		try
		{
			/** @var PDOStatement $statement */
			$statement = $this->connection->prepare('SELECT 1');
			$executed  = $statement->execute();
			$ret       = 0;

			if ($executed)
			{
				$row = [0];

				if (!empty($statement) && $statement instanceof PDOStatement)
				{
					$row = $statement->fetch(PDO::FETCH_NUM);
				}

				$ret = $row[0];
			}

			$status = $ret == 1;

			$statement->closeCursor();
			$statement = null;
		}
			// If we catch an exception here, we must not be connected.
		catch (Exception $e)
		{
			$status = false;
		}

		return $status;
	}

	/**
	 * Get the number of affected rows for the previous executed SQL statement.
	 *
	 * @return  integer  The number of affected rows.
	 */
	public function getAffectedRows()
	{
		if ($this->cursor instanceof PDOStatement)
		{
			return $this->cursor->rowCount();
		}

		return 0;
	}

	/**
	 * Get the number of returned rows for the previous executed SQL statement.
	 *
	 * @param   resource  $cursor  An optional database cursor resource to extract the row count from.
	 *
	 * @return  integer   The number of returned rows.
	 */
	public function getNumRows($cursor = null)
	{
		if ($cursor instanceof PDOStatement)
		{
			return $cursor->rowCount();
		}

		if ($this->cursor instanceof PDOStatement)
		{
			return $this->cursor->rowCount();
		}

		return 0;
	}

	/**
	 * Get the current or query, or new JDatabaseQuery object.
	 *
	 * @param   boolean  $new  False to return the last query set, True to return a new JDatabaseQuery object.
	 *
	 * @return  mixed  The current value of the internal SQL variable or a new JDatabaseQuery object.
	 */
	public function getQuery($new = false)
	{
		if ($new)
		{
			return new QueryPdomysql($this);
		}
		else
		{
			return $this->sql;
		}
	}

	/**
	 * Get the version of the database connector.
	 *
	 * @return  string  The database connector version.
	 */
	public function getVersion()
	{
		return $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION);
	}

	/**
	 * Determines if the database engine supports UTF-8 character encoding.
	 *
	 * @return  boolean  True if supported.
	 */
	public function hasUTF()
	{
		$verParts = explode('.', $this->getVersion());

		return ($verParts[0] == 5 || ($verParts[0] == 4 && $verParts[1] == 1 && (int) $verParts[2] >= 2));
	}

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  integer  The value of the auto-increment field from the last inserted row.
	 */
	public function insertid()
	{
		// Error suppress this to prevent PDO warning us that the driver doesn't support this operation.
		return @$this->connection->lastInsertId();
	}

	/**
	 * Select a database for use.
	 *
	 * @param   string  $database  The name of the database to select for use.
	 *
	 * @return  boolean  True if the database was successfully selected.
	 */
	public function select($database)
	{
		try
		{
			$this->connection->exec('USE ' . $this->quoteName($database));
		}
		catch (Exception $e)
		{
			$errorInfo      = $this->connection->errorInfo();
			$this->errorNum = $errorInfo[1];
			$this->errorMsg = $errorInfo[2];

			return false;
		}

		return true;
	}

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * @return  boolean  True on success.
	 */
	public function setUTF()
	{
		return true;
	}

	/**
	 * Method to commit a transaction.
	 *
	 * @return  void
	 */
	public function transactionCommit()
	{
		$this->connection->commit();
	}

	/**
	 * Method to roll back a transaction.
	 *
	 * @return  void
	 */
	public function transactionRollback()
	{
		$this->connection->rollBack();
	}

	/**
	 * Method to initialize a transaction.
	 *
	 * @return  void
	 */
	public function transactionStart()
	{
		$this->connection->beginTransaction();
	}

	/**
	 * Method to fetch a row from the result set cursor as an associative array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 */
	public function fetchAssoc($cursor = null)
	{
		$ret = null;

		if (!empty($cursor) && $cursor instanceof PDOStatement)
		{
			$ret = $cursor->fetch(PDO::FETCH_ASSOC);
		}
		elseif ($this->cursor instanceof PDOStatement)
		{
			$ret = $this->cursor->fetch(PDO::FETCH_ASSOC);
		}

		return $ret;
	}

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  void
	 */
	public function freeResult($cursor = null)
	{
		if ($cursor instanceof PDOStatement)
		{
			$cursor->closeCursor();
			$cursor = null;
		}

		if ($this->cursor instanceof PDOStatement)
		{
			$this->cursor->closeCursor();
			$this->cursor = null;
		}
	}

	/**
	 * Method to get the next row in the result set from the database query as an object.
	 *
	 * @param   string  $class  The class name to use for the returned row object.
	 *
	 * @return  mixed   The result of the query as an array, false if there are no more rows.
	 */
	public function loadNextObject($class = 'stdClass')
	{
		// Execute the query and get the result set cursor.
		if (!$this->cursor)
		{
			if (!($this->execute()))
			{
				return $this->errorNum ? null : false;
			}
		}

		// Get the next row from the result set as an object of type $class.
		if ($row = $this->fetchObject(null, $class))
		{
			return $row;
		}

		// Free up system resources and return.
		$this->freeResult();

		return false;
	}

	/**
	 * Method to get the next row in the result set from the database query as an array.
	 *
	 * @return  mixed  The result of the query as an array, false if there are no more rows.
	 */
	public function loadNextRow()
	{
		// Execute the query and get the result set cursor.
		if (!$this->cursor)
		{
			if (!($this->execute()))
			{
				return $this->errorNum ? null : false;
			}
		}

		// Get the next row from the result set as an object of type $class.
		if ($row = $this->fetchArray())
		{
			return $row;
		}

		// Free up system resources and return.
		$this->freeResult();

		return false;
	}

	/**
	 * PDO does not support serialize
	 *
	 * @return  array
	 */
	public function __sleep()
	{
		$serializedProperties = [];

		$reflect = new ReflectionClass($this);

		// Get properties of the current class
		$properties = $reflect->getProperties();

		foreach ($properties as $property)
		{
			// Do not serialize properties that are \PDO
			if ($property->isStatic() == false && !($this->{$property->name} instanceof PDO))
			{
				array_push($serializedProperties, $property->name);
			}
		}

		return $serializedProperties;
	}

	/**
	 * Wake up after serialization
	 *
	 * @return  array
	 */
	public function __wakeup()
	{
		// Get connection back
		$this->__construct($this->options);
	}

	/**
	 * Method to fetch a row from the result set cursor as an array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 */
	protected function fetchArray($cursor = null)
	{
		$ret = null;

		if (!empty($cursor) && $cursor instanceof PDOStatement)
		{
			$ret = $cursor->fetch(PDO::FETCH_NUM);
		}
		elseif ($this->cursor instanceof PDOStatement)
		{
			$ret = $this->cursor->fetch(PDO::FETCH_NUM);
		}

		return $ret;
	}

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @param   mixed   $cursor  The optional result set cursor from which to fetch the row.
	 * @param   string  $class   The class name to use for the returned row object.
	 *
	 * @return  mixed   Either the next row from the result set or false if there are no more rows.
	 */
	protected function fetchObject($cursor = null, $class = 'stdClass')
	{
		$ret = null;

		if (!empty($cursor) && $cursor instanceof PDOStatement)
		{
			$ret = $cursor->fetchObject($class);
		}
		elseif ($this->cursor instanceof PDOStatement)
		{
			$ret = $this->cursor->fetchObject($class);
		}

		return $ret;
	}
}
