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

use Akeeba\Engine\Driver\Query\Mysqli as QueryMysqli;
use mysqli_result;
use RuntimeException;

/**
 * MySQL Improved (mysqli) database driver for Akeeba Engine
 *
 * Based on Joomla! Platform 11.2
 */
class Mysqli extends Mysql
{
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $name = 'mysqli';

	protected $port;
	protected $socket;

	/** @var \mysqli|null The db connection resource */
	protected $connection = '';

	/** @var mysqli_result|null The database connection cursor from the last query. */
	protected $cursor;

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

		$host     = array_key_exists('host', $options) ? $options['host'] : 'localhost';
		$port     = array_key_exists('port', $options) ? $options['port'] : '';
		$user     = array_key_exists('user', $options) ? $options['user'] : '';
		$password = array_key_exists('password', $options) ? $options['password'] : '';
		$database = array_key_exists('database', $options) ? $options['database'] : '';
		$prefix   = array_key_exists('prefix', $options) ? $options['prefix'] : '';
		$select   = array_key_exists('select', $options) ? $options['select'] : true;
		$socket   = null;

		// Figure out if a port is included in the host name
		$this->fixHostnamePortSocket($host, $port, $socket);

		// Set the information
		$this->host           = $host;
		$this->user           = $user;
		$this->password       = $password;
		$this->port           = $port;
		$this->socket         = $socket;
		$this->_database      = $database;
		$this->selectDatabase = $select;

		// finalize initialization
		parent::__construct($options);

		// Open the connection
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
		return (function_exists('mysqli_connect'));
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

		// perform a number of fatality checks, then return gracefully
		if (!function_exists('mysqli_connect'))
		{
			$this->errorNum = 1;
			$this->errorMsg = 'The MySQL adapter "mysqli" is not available.';

			return;
		}

		// connect to the server
		if (!($this->connection = @mysqli_connect($this->host, $this->user, $this->password, null, $this->port, $this->socket)))
		{
			$this->errorNum = 2;
			$this->errorMsg = 'Could not connect to MySQL';

			return;
		}

		// Set sql_mode to non_strict mode
		mysqli_query($this->connection, "SET @@SESSION.sql_mode = '';");

		if ($this->selectDatabase && !empty($this->_database))
		{
			if (!$this->select($this->_database))
			{
				$this->errorNum = 3;
				$this->errorMsg = "Cannot select database {$this->_database}";

				return;
			}
		}

		$this->setUTF();
	}

	public function close()
	{
		$return = false;

		if (is_object($this->cursor) && ($this->cursor instanceof mysqli_result))
		{
			try
			{
				@$this->cursor->free();
			}
			catch (\Throwable $e)
			{
			}

			$this->cursor = null;
		}

		if (is_object($this->connection) && ($this->connection instanceof \mysqli))
		{
			try
			{
				$return = @$this->connection->close();
			}
			catch (\Throwable $e)
			{
				$return = false;
			}
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
		$result = @mysqli_real_escape_string($this->getConnection(), $text);

		if ($result === false)
		{
			// Attempt to reconnect.
			try
			{
				$this->connection = null;
				$this->open();

				$result = @mysqli_real_escape_string($this->getConnection(), $text);;
			}
			catch (RuntimeException $e)
			{
				$result = $this->unsafe_escape($text);
			}
		}

		if ($extra)
		{
			$result = addcslashes($result, '%_');
		}

		return $result;
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return  boolean  True if connected to the database engine.
	 */
	public function connected()
	{
		if (is_object($this->connection))
		{
			return @mysqli_ping($this->connection);
		}

		return false;
	}

	/**
	 * Get the number of affected rows for the previous executed SQL statement.
	 *
	 * @return  integer  The number of affected rows.
	 */
	public function getAffectedRows()
	{
		return mysqli_affected_rows($this->connection);
	}

	/**
	 * Get the number of returned rows for the previous executed SQL statement.
	 *
	 * @param   mysqli_result  $cursor  An optional database cursor resource to extract the row count from.
	 *
	 * @return  integer   The number of returned rows.
	 */
	public function getNumRows($cursor = null)
	{
		return mysqli_num_rows($cursor ?: $this->cursor);
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
			return new QueryMysqli($this);
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
		return mysqli_get_server_info($this->connection);
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
		return mysqli_insert_id($this->connection);
	}

	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 */
	public function query()
	{
		static $isReconnecting = false;

		$this->open();

		if (!is_object($this->connection))
		{
			throw new RuntimeException($this->errorMsg, $this->errorNum);
		}

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
		$this->cursor = @mysqli_query($this->connection, $query);

		// If an error occurred handle it.
		if (!$this->cursor)
		{
			$this->errorNum = (int) mysqli_errno($this->connection);
			$this->errorMsg = (string) mysqli_error($this->connection) . ' SQL=' . $query;

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
			elseif ($this->errorNum != 0)
			{
				throw new RuntimeException($this->errorMsg, $this->errorNum);
			}
		}

		return $this->cursor;
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
		if (!$database)
		{
			return false;
		}

		if (!mysqli_select_db($this->connection, $database))
		{
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
		$result = false;

		if ($this->supportsUtf8mb4())
		{
			$result = @mysqli_set_charset($this->connection, 'utf8mb4');
		}

		if (!$result)
		{
			$result = @mysqli_set_charset($this->connection, 'utf8');
		}

		return $result;

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
		return mysqli_fetch_assoc($cursor ?: $this->cursor);
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
		mysqli_free_result($cursor ?: $this->cursor);
	}

	/**
	 * Does this database server support UTF-8 four byte (utf8mb4) collation?
	 *
	 * libmysql supports utf8mb4 since 5.5.3 (same version as the MySQL server). mysqlnd supports utf8mb4 since 5.0.9.
	 *
	 * This method's code is based on WordPress' wpdb::has_cap() method
	 *
	 * @return  bool
	 */
	public function supportsUtf8mb4()
	{
		$client_version = mysqli_get_client_info();

		if (strpos($client_version, 'mysqlnd') !== false)
		{
			$client_version = preg_replace('/^\D+([\d.]+).*/', '$1', $client_version);

			return version_compare($client_version, '5.0.9', '>=');
		}
		else
		{
			return version_compare($client_version, '5.5.3', '>=');
		}
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
		return mysqli_fetch_row($cursor ?: $this->cursor);
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
		return mysqli_fetch_object($cursor ?: $this->cursor, $class);
	}

	/**
	 * Tries to parse all the weird hostname definitions and normalize them into something that the MySQLi connector
	 * will understand. Please note that there are some differences to the old MySQL driver:
	 *
	 * * Port and socket MUST be provided separately from the hostname. Hostnames in the form of 127.0.0.1:8336 are no
	 *   longer acceptable.
	 *
	 * * The hostname "localhost" has special meaning. It means "use named pipes / sockets". Anything else uses TCP/IP.
	 *   This is the ONLY way to specify a. TCP/IP or b. named pipes / sockets connection.
	 *
	 * * You SHOULD NOT use a numeric TCP/IP port with hostname localhost. For some strange reason it's still allowed
	 *   but the manual is self-contradicting over what this really does...
	 *
	 * * Likewise you CANNOT use a socket / named pipe path with hostname other than localhost. Named pipes and sockets
	 *   can only be used with the local machine, therefore the hostname MUST be localhost.
	 *
	 * * You cannot give a TCP/IP port number in the socket parameter or a named pipe / socket path to the port
	 *   parameter. This leads to an error.
	 *
	 * * You cannot use an empty string, 0 or any other non-null value when you want to omit either of the port or
	 *   socket parameters.
	 *
	 * * Persistent connections must be prefixed with the string literal 'p:'. Therefore you cannot have a hostname
	 *   called 'p' (not to mention that'd be daft). You can also not specify something like 'p:1234' to make a
	 *   persistent connection to a port. This wasn't even supported by the old MySQL driver. As a result we don't even
	 *   try to catch that degenerate case.
	 *
	 * This method will try to apply all of the aforementioned rules with one additional disambiguation rule:
	 *
	 * A port / socket set in the hostname overrides a port specified separately. A port specified separately overrides
	 * a socket specified separately.
	 *
	 * @param   string  $host    The hostname. Can contain legacy hostname:port or hostname:sc=ocket definitions.
	 * @param   int     $port    The port. Alternatively it can contain the path to the socket.
	 * @param   string  $socket  The path to the socket. You could abuse it to enter the port number. DON'T!
	 *
	 * @return  void  All parameters are passed by reference.
	 */
	protected function fixHostnamePortSocket(&$host, &$port, &$socket)
	{
		// Is this a persistent connection? Persistent connections are indicated by the literal "p:" in front of the hostname
		$isPersistent = (substr($host, 0, 2) == 'p:');
		$host         = $isPersistent ? substr($host, 2) : $host;

		/*
		 * Unlike mysql_connect(), mysqli_connect() takes the port and socket as separate arguments. Therefore, we
		 * have to extract them from the host string.
		 */
		$port = !empty($port) ? $port : 3306;

		$regex = '/^(?P<host>((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))(:(?P<port>.+))?$/';

		// It's an IPv4 address with or without port
		if (preg_match($regex, $host, $matches))
		{
			$host = $matches['host'];

			if (!empty($matches['port']))
			{
				$port = $matches['port'];
			}
		}
		// Square-bracketed IPv6 address with or without port, e.g. [fe80:102::2%eth1]:3306
		elseif (preg_match('/^(?P<host>\[.*\])(:(?P<port>.+))?$/', $host, $matches))
		{
			$host = $matches['host'];

			if (!empty($matches['port']))
			{
				$port = $matches['port'];
			}
		}
		// Named host (e.g example.com or localhost) with or without port
		elseif (preg_match('/^(?P<host>(\w+:\/{2,3})?[a-z0-9\.\-]+)(:(?P<port>[^:]+))?$/i', $host, $matches))
		{
			$host = $matches['host'];

			if (!empty($matches['port']))
			{
				$port = $matches['port'];
			}
		}
		// Empty host, just port, e.g. ':3306'
		elseif (preg_match('/^:(?P<port>[^:]+)$/', $host, $matches))
		{
			$host = 'localhost';
			$port = $matches['port'];
		}
		// ... else we assume normal (naked) IPv6 address, so host and port stay as they are or default

		// Get the port number or socket name
		if (is_numeric($port))
		{
			$port   = (int) $port;
			$socket = '';
		}
		else
		{
			$socket = $port;

			// If we're going to use sockets, port MUST BE null, otherwise mysqli_connect will try to use it ignoring
			// the socket, causing a connection error
			$port = null;
		}

		// Finally, if it's a persistent connection we have to prefix the hostname with 'p:'
		$host = $isPersistent ? "p:$host" : $host;
	}
}
