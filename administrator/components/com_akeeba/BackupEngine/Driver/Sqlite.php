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

use Akeeba\Engine\Driver\Query\Base as QueryBase;
use Akeeba\Engine\Driver\Query\Limitable;
use Akeeba\Engine\Driver\Query\Preparable;
use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;
use SQLite3;

/**
 * SQLite database driver supporting PDO based connections
 *
 * @see    http://php.net/manual/en/ref.pdo-sqlite.php
 * @since  1.0
 */
class Sqlite extends Base
{

	public static $dbtech = 'sqlite';
	/**
	 * The name of the database driver.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $name = 'sqlite';
	/**
	 * The character(s) used to quote SQL statement names such as table names or field names,
	 * etc. The child classes should define this as necessary.  If a single character string the
	 * same character is used for both sides of the quoted name, else the first character will be
	 * used for the opening quote and the second for the closing quote.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $nameQuote = '`';

	/** @var PDOStatement The database connection cursor from the last query. */
	protected $cursor;

	/** @var resource   The prepared statement. */
	protected $prepared;

	/** @var array   Contains the current query execution status */
	protected $executed = false;

	public function __construct(array $options)
	{
		$this->driverType = 'sqlite';

		parent::__construct($options);

		if (!is_object($this->connection))
		{
			$this->open();
		}
	}

	/**
	 * Test to see if the PDO ODBC connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.0
	 */
	public static function isSupported()
	{
		return class_exists('\\PDO') && in_array('sqlite', PDO::getAvailableDrivers());
	}

	/**
	 * Destructor.
	 *
	 * @since   1.0
	 */
	public function __destruct()
	{
		$this->freeResult();
		unset($this->connection);
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
		$this->freeResult();
		unset($this->connection);
	}

	/**
	 * Drops a table from the database.
	 *
	 * @param   string   $tableName  The name of the database table to drop.
	 * @param   boolean  $ifExists   Optionally specify that the table must exist before it is dropped.
	 *
	 * @return  Sqlite  Returns this object to support chaining.
	 *
	 * @since   1.0
	 */
	public function dropTable($tableName, $ifExists = true)
	{
		$this->open();

		$query = $this->getQuery(true);

		$this->setQuery('DROP TABLE ' . ($ifExists ? 'IF EXISTS ' : '') . $query->quoteName($tableName));

		$this->execute();

		return $this;
	}

	/**
	 * Method to escape a string for usage in an SQLite statement.
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
		if (is_int($text) || is_float($text))
		{
			return $text;
		}

		return SQLite3::escapeString($text);
	}

	/**
	 * Method to get the database collation in use by sampling a text field of a table in the database.
	 *
	 * @return  mixed  The collation in use by the database or boolean false if not supported.
	 *
	 * @since   1.0
	 */
	public function getCollation()
	{
		return $this->charset;
	}

	/**
	 * Shows the table CREATE statement that creates the given tables.
	 *
	 * Note: Doesn't appear to have support in SQLite
	 *
	 * @param   mixed  $tables  A table name or a list of table names.
	 *
	 * @return  array  A list of the create SQL for the tables.
	 *
	 * @throws  RuntimeException
	 * @since   1.0
	 */
	public function getTableCreate($tables)
	{
		$this->open();

		// Sanitize input to an array and iterate over the list.
		$tables = (array) $tables;

		return $tables;
	}

	/**
	 * Retrieves field information about a given table.
	 *
	 * @param   string   $table     The name of the database table.
	 * @param   boolean  $typeOnly  True to only return field types.
	 *
	 * @return  array  An array of fields for the database table.
	 *
	 * @throws  RuntimeException
	 * @since   1.0
	 */
	public function getTableColumns($table, $typeOnly = true)
	{
		$this->open();

		$columns = [];
		$query   = $this->getQuery(true);

		$fieldCasing = $this->getOption(PDO::ATTR_CASE);

		$this->setOption(PDO::ATTR_CASE, PDO::CASE_UPPER);

		$table = strtoupper($table);

		$query->setQuery('pragma table_info(' . $table . ')');

		$this->setQuery($query);
		$fields = $this->loadObjectList();

		if ($typeOnly)
		{
			foreach ($fields as $field)
			{
				$columns[$field->NAME] = $field->TYPE;
			}
		}
		else
		{
			foreach ($fields as $field)
			{
				// Do some dirty translation to MySQL output.
				$columns[$field->NAME] = (object) [
					'Field'   => $field->NAME,
					'Type'    => $field->TYPE,
					'Null'    => ($field->NOTNULL == '1' ? 'NO' : 'YES'),
					'Default' => $field->DFLT_VALUE,
					'Key'     => ($field->PK == '1' ? 'PRI' : ''),
				];
			}
		}

		$this->setOption(PDO::ATTR_CASE, $fieldCasing);

		return $columns;
	}

	/**
	 * Get the details list of keys for a table.
	 *
	 * @param   string  $table  The name of the table.
	 *
	 * @return  array  An array of the column specification for the table.
	 *
	 * @throws  RuntimeException
	 * @since   1.0
	 */
	public function getTableKeys($table)
	{
		$this->open();

		$keys  = [];
		$query = $this->getQuery(true);

		$fieldCasing = $this->getOption(PDO::ATTR_CASE);

		$this->setOption(PDO::ATTR_CASE, PDO::CASE_UPPER);

		$table = strtoupper($table);
		$query->setQuery('pragma table_info( ' . $table . ')');

		// $query->bind(':tableName', $table);

		$this->setQuery($query);
		$rows = $this->loadObjectList();

		foreach ($rows as $column)
		{
			if ($column->PK == 1)
			{
				$keys[$column->NAME] = $column;
			}
		}

		$this->setOption(PDO::ATTR_CASE, $fieldCasing);

		return $keys;
	}

	/**
	 * Method to get an array of all tables in the database (schema).
	 *
	 * @return  array   An array of all the tables in the database.
	 *
	 * @throws  RuntimeException
	 * @since   1.0
	 */
	public function getTableList()
	{
		$this->open();

		/* @type  Query\Sqlite $query */
		$query = $this->getQuery(true);

		$type = 'table';

		$query->select('name');
		$query->from('sqlite_master');
		$query->where('type = :type');
		$query->bind(':type', $type);
		$query->order('name');

		$this->setQuery($query);

		$tables = $this->loadColumn();

		return $tables;
	}

	/**
	 * There's no point on return "a list of tables" inside a SQLite database: we are simple going to
	 * copy the whole database file in the new location
	 *
	 * @param   bool  $abstract
	 *
	 * @return array
	 */
	public function getTables($abstract = true)
	{
		return [];
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
		$this->open();

		$this->setQuery("SELECT sqlite_version()");

		return $this->loadResult();
	}

	/**
	 * Select a database for use.
	 *
	 * @param   string  $database  The name of the database to select for use.
	 *
	 * @return  boolean  True if the database was successfully selected.
	 *
	 * @throws  RuntimeException
	 * @since   1.0
	 */
	public function select($database)
	{
		$this->open();

		$this->_database = $database;

		return true;
	}

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * Returns false automatically for the Oracle driver since
	 * you can only set the character set when the connection
	 * is created.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0
	 */
	public function setUTF()
	{
		$this->open();

		return false;
	}

	/**
	 * Locks a table in the database.
	 *
	 * @param   string  $table  The name of the table to unlock.
	 *
	 * @return  Sqlite Returns this object to support chaining.
	 *
	 * @throws  RuntimeException
	 * @since   1.0
	 */
	public function lockTable($table)
	{
		return $this;
	}

	/**
	 * Renames a table in the database.
	 *
	 * @param   string  $oldTable  The name of the table to be renamed
	 * @param   string  $newTable  The new name for the table.
	 * @param   string  $backup    Not used by Sqlite.
	 * @param   string  $prefix    Not used by Sqlite.
	 *
	 * @return  Sqlite Returns this object to support chaining.
	 *
	 * @throws  RuntimeException
	 * @since   1.0
	 */
	public function renameTable($oldTable, $newTable, $backup = null, $prefix = null)
	{
		$this->setQuery('ALTER TABLE ' . $oldTable . ' RENAME TO ' . $newTable)->execute();

		return $this;
	}

	/**
	 * Unlocks tables in the database.
	 *
	 * @return  Sqlite Returns this object to support chaining.
	 *
	 * @throws  RuntimeException
	 * @since   1.0
	 */
	public function unlockTables()
	{
		return $this;
	}

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return  boolean  True if connected to the database engine.
	 */
	public function connected()
	{
		return !empty($this->connection);
	}

	/**
	 * Method to commit a transaction.
	 *
	 * @param   boolean  $toSavepoint  If true, commit to the last savepoint.
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 * @since   1.0
	 */
	public function transactionCommit($toSavepoint = false)
	{
		$this->open();

		if (!$toSavepoint || $this->transactionDepth <= 1)
		{
			$this->open();

			if (!$toSavepoint || $this->transactionDepth == 1)
			{
				$this->connection->commit();
			}

			$this->transactionDepth--;
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
	 * @throws  RuntimeException
	 * @since   1.0
	 */
	public function transactionRollback($toSavepoint = false)
	{
		$this->connected();

		if (!$toSavepoint || $this->transactionDepth <= 1)
		{
			$this->open();

			if (!$toSavepoint || $this->transactionDepth == 1)
			{
				$this->connection->rollBack();
			}

			$this->transactionDepth--;
		}
		else
		{
			$savepoint = 'SP_' . ($this->transactionDepth - 1);
			$this->setQuery('ROLLBACK TO ' . $this->quoteName($savepoint));

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
	 * @throws  RuntimeException
	 * @since   1.0
	 */
	public function transactionStart($asSavepoint = false)
	{
		$this->connected();

		if (!$asSavepoint || !$this->transactionDepth)
		{
			$this->open();

			if (!$asSavepoint || !$this->transactionDepth)
			{
				$this->connection->beginTransaction();
			}

			$this->transactionDepth++;
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

	/**
	 * Get the current query object or a new Query object.
	 * We have to override the parent method since it will always return a PDO query, while we have a
	 * specialized class for SQLite
	 *
	 * @param   boolean  $new  False to return the current query object, True to return a new Query object.
	 *
	 * @return  QueryBase  The current query object or a new object extending the Query class.
	 *
	 * @throws  RuntimeException
	 */
	public function getQuery($new = false)
	{
		if ($new)
		{
			return new Query\Sqlite($this);
		}

		return $this->sql;
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

		// Create the connection string:
		$connectionString = str_replace($replace, $with, $format);

		try
		{
			$this->connection = new PDO(
				$connectionString,
				$this->options['user'],
				$this->options['password']
			);
		}
		catch (PDOException $e)
		{
			throw new RuntimeException('Could not connect to PDO' . ': ' . $e->getMessage(), 2, $e);
		}
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

	public function fetchAssoc($cursor = null)
	{
		if (!empty($cursor) && $cursor instanceof PDOStatement)
		{
			return $cursor->fetch(PDO::FETCH_ASSOC);
		}

		if ($this->prepared instanceof PDOStatement)
		{
			return $this->prepared->fetch(PDO::FETCH_ASSOC);
		}
	}

	public function freeResult($cursor = null)
	{
		$this->executed = false;

		if ($cursor instanceof PDOStatement)
		{
			$cursor->closeCursor();
			$cursor = null;
		}

		if ($this->prepared instanceof PDOStatement)
		{
			$this->prepared->closeCursor();
			$this->prepared = null;
		}
	}

	public function getAffectedRows()
	{
		$this->open();

		if ($this->prepared instanceof PDOStatement)
		{
			return $this->prepared->rowCount();
		}
		else
		{
			return 0;
		}
	}

	public function getNumRows($cursor = null)
	{
		$this->open();

		if ($cursor instanceof PDOStatement)
		{
			return $cursor->rowCount();
		}
		elseif ($this->prepared instanceof PDOStatement)
		{
			return $this->prepared->rowCount();
		}
		else
		{
			return 0;
		}
	}

	public function insertid()
	{
		$this->open();

		// Error suppress this to prevent PDO warning us that the driver doesn't support this operation.
		return @$this->connection->lastInsertId();
	}

	public function query()
	{
		static $isReconnecting = false;

		$this->open();

		if (!is_object($this->connection))
		{
			throw new RuntimeException($this->errorMsg, $this->errorNum);
		}

		// Take a local copy so that we don't modify the original query and cause issues later
		$sql = $this->replacePrefix((string) $this->sql);

		if ($this->limit > 0 || $this->offset > 0)
		{
			$sql .= ' LIMIT ' . $this->limit;

			if ($this->offset > 0)
			{
				$sql .= ' OFFSET ' . $this->offset;
			}
		}

		// Increment the query counter.
		$this->count++;

		// If debugging is enabled then let's log the query.
		if ($this->debug)
		{
			// Add the query to the object queue.
			$this->log[] = $sql;
		}

		// Reset the error values.
		$this->errorNum = 0;
		$this->errorMsg = '';

		// Execute the query.
		$this->executed = false;

		if ($this->prepared instanceof PDOStatement)
		{
			// Bind the variables:
			if ($this->sql instanceof Preparable)
			{
				$bounded =& $this->sql->getBounded();

				foreach ($bounded as $key => $obj)
				{
					$this->prepared->bindParam($key, $obj->value, $obj->dataType, $obj->length, $obj->driverOptions);
				}
			}

			$this->executed = $this->prepared->execute();
		}

		// If an error occurred handle it.
		if (!$this->executed)
		{
			// Get the error number and message before we execute any more queries.
			$errorNum = (int) $this->connection->errorCode();
			$errorMsg = (string) 'SQL: ' . implode(", ", $this->connection->errorInfo());

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
				catch (RuntimeException $e)
					// If connect fails, ignore that exception and throw the normal exception.
				{
					// Get the error number and message.
					$this->errorNum = (int) $this->connection->errorCode();
					$this->errorMsg = (string) 'SQL: ' . implode(", ", $this->connection->errorInfo());

					// Throw the normal query exception.
					throw new RuntimeException($this->errorMsg, $this->errorNum);
				}

				// Since we were able to reconnect, run the query again.
				$result         = $this->query();
				$isReconnecting = false;

				return $result;
			}
			else
				// The server was not disconnected.
			{
				// Get the error number and message from before we tried to reconnect.
				$this->errorNum = $errorNum;
				$this->errorMsg = $errorMsg;

				// Throw the normal query exception.
				throw new RuntimeException($this->errorMsg, $this->errorNum);
			}
		}

		return $this->prepared;
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
		$this->open();

		return $this->connection->getAttribute($key);
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
		$this->open();

		return $this->connection->setAttribute($key, $value);
	}

	/**
	 * Sets the SQL statement string for later execution.
	 *
	 * @param   mixed    $query          The SQL statement to set either as a JDatabaseQuery object or a string.
	 * @param   integer  $offset         The affected row offset to set.
	 * @param   integer  $limit          The maximum affected rows to set.
	 * @param   array    $driverOptions  The optional PDO driver options
	 *
	 * @return  Base  This object to support method chaining.
	 *
	 * @since   1.0
	 */
	public function setQuery($query, $offset = null, $limit = null, $driverOptions = [])
	{
		$this->open();

		$this->freeResult();

		if (is_string($query))
		{
			// Allows taking advantage of bound variables in a direct query:
			$query = $this->getQuery(true)->setQuery($query);
		}

		if ($query instanceof Limitable && !is_null($offset) && !is_null($limit))
		{
			$query->setLimit($limit, $offset);
		}

		$sql = $this->replacePrefix((string) $query);

		$this->prepared = $this->connection->prepare($sql, $driverOptions);

		// Store reference to the DatabaseQuery instance:
		parent::setQuery($query, $offset, $limit);

		return $this;
	}

	protected function fetchArray($cursor = null)
	{
		if (!empty($cursor) && $cursor instanceof PDOStatement)
		{
			return $cursor->fetch(PDO::FETCH_NUM);
		}

		if ($this->prepared instanceof PDOStatement)
		{
			return $this->prepared->fetch(PDO::FETCH_NUM);
		}
	}

	protected function fetchObject($cursor = null, $class = 'stdClass')
	{
		if (!empty($cursor) && $cursor instanceof PDOStatement)
		{
			return $cursor->fetchObject($class);
		}

		if ($this->prepared instanceof PDOStatement)
		{
			return $this->prepared->fetchObject($class);
		}
	}
}
