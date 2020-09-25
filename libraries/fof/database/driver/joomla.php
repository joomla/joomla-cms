<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  database
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @note        This file has been modified by the Joomla! Project and no longer reflects the original work of its author.
 *
 * This file is adapted from the Joomla! Platform. It is used to iterate a database cursor returning FOFTable objects
 * instead of plain stdClass objects
 */

// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * This crazy three line bit is required to convince Joomla! to load JDatabaseInterface which is on the same file as the
 * abstract JDatabaseDriver class for reasons that beat me. It makes no sense. Furthermore, jimport on Joomla! 3.4
 * doesn't seem to actually load the file, merely registering the association in the autoloader. Hence the class_exists
 * in here.
 */
jimport('joomla.database.driver');
jimport('joomla.database.driver.mysqli');
class_exists('JDatabaseDriver', true);

/**
 * Joomla! pass-through database driver.
 */
class FOFDatabaseDriverJoomla extends FOFDatabase implements FOFDatabaseInterface
{
	/** @var FOFDatabase The real database connection object */
	private $dbo;

	/**
	 * @var    string  The character(s) used to quote SQL statement names such as table names or field names,
	 *                 etc.  The child classes should define this as necessary.  If a single character string the
	 *                 same character is used for both sides of the quoted name, else the first character will be
	 *                 used for the opening quote and the second for the closing quote.
	 * @since  11.1
	 */
	protected $nameQuote = '';

	/**
	 * Is this driver supported
	 *
	 * @since  11.2
	 */
	public static function isSupported()
	{
		return true;
	}

	/**
	 * Database object constructor
	 *
	 * @param    array $options List of options used to configure the connection
	 */
	public function __construct($options = array())
	{
		// Get best matching Akeeba Backup driver instance
		$this->dbo = JFactory::getDbo();

		$reflection = new ReflectionClass($this->dbo);

		try
		{
			$refProp = $reflection->getProperty('nameQuote');
			$refProp->setAccessible(true);
			$this->nameQuote = $refProp->getValue($this->dbo);
		}
		catch (Exception $e)
		{
			$this->nameQuote = '`';
		}
	}

	public function close()
	{
		if (method_exists($this->dbo, 'close'))
		{
			$this->dbo->close();
		}
		elseif (method_exists($this->dbo, 'disconnect'))
		{
			$this->dbo->disconnect();
		}
	}

	public function disconnect()
	{
		$this->close();
	}

	public function open()
	{
		if (method_exists($this->dbo, 'open'))
		{
			$this->dbo->open();
		}
		elseif (method_exists($this->dbo, 'connect'))
		{
			$this->dbo->connect();
		}
	}

	public function connect()
	{
		$this->open();
	}

	public function connected()
	{
		if (method_exists($this->dbo, 'connected'))
		{
			return $this->dbo->connected();
		}

		return true;
	}

	public function escape($text, $extra = false)
	{
		return $this->dbo->escape($text, $extra);
	}

	public function execute()
	{
		if (method_exists($this->dbo, 'execute'))
		{
			return $this->dbo->execute();
		}

		return $this->dbo->query();
	}

	public function getAffectedRows()
	{
		if (method_exists($this->dbo, 'getAffectedRows'))
		{
			return $this->dbo->getAffectedRows();
		}

		return 0;
	}

	public function getCollation()
	{
		if (method_exists($this->dbo, 'getCollation'))
		{
			return $this->dbo->getCollation();
		}

		return 'utf8_general_ci';
	}

	public function getConnection()
	{
		if (method_exists($this->dbo, 'getConnection'))
		{
			return $this->dbo->getConnection();
		}

		return null;
	}

	public function getCount()
	{
		if (method_exists($this->dbo, 'getCount'))
		{
			return $this->dbo->getCount();
		}

		return 0;
	}

	public function getDateFormat()
	{
		if (method_exists($this->dbo, 'getDateFormat'))
		{
			return $this->dbo->getDateFormat();
		}

		return 'Y-m-d H:i:s';;
	}

	public function getMinimum()
	{
		if (method_exists($this->dbo, 'getMinimum'))
		{
			return $this->dbo->getMinimum();
		}

		return '5.0.40';
	}

	public function getNullDate()
	{
		if (method_exists($this->dbo, 'getNullDate'))
		{
			return $this->dbo->getNullDate();
		}

		return '0000-00-00 00:00:00';
	}

	public function getNumRows($cursor = null)
	{
		if (method_exists($this->dbo, 'getNumRows'))
		{
			return $this->dbo->getNumRows($cursor);
		}

		return 0;
	}

	public function getQuery($new = false)
	{
		if (method_exists($this->dbo, 'getQuery'))
		{
			return $this->dbo->getQuery($new);
		}

		return null;
	}

	public function getTableColumns($table, $typeOnly = true)
	{
		if (method_exists($this->dbo, 'getTableColumns'))
		{
			return $this->dbo->getTableColumns($table, $typeOnly);
		}

		$result = $this->dbo->getTableFields(array($table), $typeOnly);

		return $result[$table];
	}

	public function getTableKeys($tables)
	{
		if (method_exists($this->dbo, 'getTableKeys'))
		{
			return $this->dbo->getTableKeys($tables);
		}

		return array();
	}

	public function getTableList()
	{
		if (method_exists($this->dbo, 'getTableList'))
		{
			return $this->dbo->getTableList();
		}

		return array();
	}

	public function getVersion()
	{
		if (method_exists($this->dbo, 'getVersion'))
		{
			return $this->dbo->getVersion();
		}

		return '5.0.40';
	}

	public function insertid()
	{
		if (method_exists($this->dbo, 'insertid'))
		{
			return $this->dbo->insertid();
		}

		return null;
	}

	public function insertObject($table, &$object, $key = null)
	{
		if (method_exists($this->dbo, 'insertObject'))
		{
			return $this->dbo->insertObject($table, $object, $key);
		}

		return null;
	}

	public function loadAssoc()
	{
		if (method_exists($this->dbo, 'loadAssoc'))
		{
			return $this->dbo->loadAssoc();
		}

		return null;
	}

	public function loadAssocList($key = null, $column = null)
	{
		if (method_exists($this->dbo, 'loadAssocList'))
		{
			return $this->dbo->loadAssocList($key, $column);
		}

		return null;
	}

	public function loadObject($class = 'stdClass')
	{
		if (method_exists($this->dbo, 'loadObject'))
		{
			return $this->dbo->loadObject($class);
		}

		return null;
	}

	public function loadObjectList($key = '', $class = 'stdClass')
	{
		if (method_exists($this->dbo, 'loadObjectList'))
		{
			return $this->dbo->loadObjectList($key, $class);
		}

		return null;
	}

	public function loadResult()
	{
		if (method_exists($this->dbo, 'loadResult'))
		{
			return $this->dbo->loadResult();
		}

		return null;
	}

	public function loadRow()
	{
		if (method_exists($this->dbo, 'loadRow'))
		{
			return $this->dbo->loadRow();
		}

		return null;
	}

	public function loadRowList($key = null)
	{
		if (method_exists($this->dbo, 'loadRowList'))
		{
			return $this->dbo->loadRowList($key);
		}

		return null;
	}

	public function lockTable($tableName)
	{
		if (method_exists($this->dbo, 'lockTable'))
		{
			return $this->dbo->lockTable($this);
		}

		return $this;
	}

	public function quote($text, $escape = true)
	{
		if (method_exists($this->dbo, 'quote'))
		{
			return $this->dbo->quote($text, $escape);
		}

		return $text;
	}

	public function select($database)
	{
		if (method_exists($this->dbo, 'select'))
		{
			return $this->dbo->select($database);
		}

		return false;
	}

	public function setQuery($query, $offset = 0, $limit = 0)
	{
		if (method_exists($this->dbo, 'setQuery'))
		{
			return $this->dbo->setQuery($query, $offset, $limit);
		}

		return false;
	}

	public function transactionCommit($toSavepoint = false)
	{
		if (method_exists($this->dbo, 'transactionCommit'))
		{
			$this->dbo->transactionCommit($toSavepoint);
		}
	}

	public function transactionRollback($toSavepoint = false)
	{
		if (method_exists($this->dbo, 'transactionRollback'))
		{
			$this->dbo->transactionRollback($toSavepoint);
		}
	}

	public function transactionStart($asSavepoint = false)
	{
		if (method_exists($this->dbo, 'transactionStart'))
		{
			$this->dbo->transactionStart($asSavepoint);
		}
	}

	public function unlockTables()
	{
		if (method_exists($this->dbo, 'unlockTables'))
		{
			return $this->dbo->unlockTables();
		}

		return $this;
	}

	public function updateObject($table, &$object, $key, $nulls = false)
	{
		if (method_exists($this->dbo, 'updateObject'))
		{
			return $this->dbo->updateObject($table, $object, $key, $nulls);
		}

		return false;
	}

	public function getLog()
	{
		if (method_exists($this->dbo, 'getLog'))
		{
			return $this->dbo->getLog();
		}

		return array();
	}

	public function dropTable($table, $ifExists = true)
	{
		if (method_exists($this->dbo, 'dropTable'))
		{
			return $this->dbo->dropTable($table, $ifExists);
		}

		return $this;
	}

	public function getTableCreate($tables)
	{
		if (method_exists($this->dbo, 'getTableCreate'))
		{
			return $this->dbo->getTableCreate($tables);
		}

		return array();
	}

	public function renameTable($oldTable, $newTable, $backup = null, $prefix = null)
	{
		if (method_exists($this->dbo, 'renameTable'))
		{
			return $this->dbo->renameTable($oldTable, $newTable, $backup, $prefix);
		}

		return $this;
	}

	public function setUtf()
	{
		if (method_exists($this->dbo, 'setUtf'))
		{
			return $this->dbo->setUtf();
		}

		return false;
	}


	protected function freeResult($cursor = null)
	{
		return false;
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
		if (method_exists($this->dbo, 'loadColumn'))
		{
			return $this->dbo->loadColumn($offset);
		}

		return $this->dbo->loadResultArray($offset);
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
				$parts[] = $q[0] . $part . $q[1];
			}
		}

		return implode('.', $parts);
	}

	/**
	 * Gets the error message from the database connection.
	 *
	 * @param   boolean  $escaped  True to escape the message string for use in JavaScript.
	 *
	 * @return  string  The error message for the most recent query.
	 *
	 * @since   11.1
	 */
	public function getErrorMsg($escaped = false)
	{
		if (method_exists($this->dbo, 'getErrorMsg'))
		{
			$errorMessage = $this->dbo->getErrorMsg();
		}
		else
		{
			$errorMessage = $this->errorMsg;
		}

		if ($escaped)
		{
			return addslashes($errorMessage);
		}

		return $errorMessage;
	}

	/**
	 * Gets the error number from the database connection.
	 *
	 * @return      integer  The error number for the most recent query.
	 *
	 * @since       11.1
	 * @deprecated  13.3 (Platform) & 4.0 (CMS)
	 */
	public function getErrorNum()
	{
		if (method_exists($this->dbo, 'getErrorNum'))
		{
			$errorNum = $this->dbo->getErrorNum();
		}
		else
		{
			$errorNum = $this->getErrorNum;
		}

		return $errorNum;
	}

	/**
	 * Return the most recent error message for the database connector.
	 *
	 * @param   boolean  $showSQL  True to display the SQL statement sent to the database as well as the error.
	 *
	 * @return  string  The error message for the most recent query.
	 */
	public function stderr($showSQL = false)
	{
		if (method_exists($this->dbo, 'stderr'))
		{
			return $this->dbo->stderr($showSQL);
		}

		return parent::stderr($showSQL);
	}

	/**
	 * Magic method to proxy all calls to the loaded database driver object
	 */
	public function __call($name, array $arguments)
	{
		if (is_null($this->dbo))
		{
			throw new Exception('FOF database driver is not loaded');
		}

		if (method_exists($this->dbo, $name) || in_array($name, array('q', 'nq', 'qn', 'query')))
		{
			switch ($name)
			{
				case 'execute':
					$name = 'query';
					break;

				case 'q':
					$name = 'quote';
					break;

				case 'qn':
				case 'nq':
					switch (count($arguments))
					{
						case 0 :
							$result = $this->quoteName();
							break;
						case 1 :
							$result = $this->quoteName($arguments[0]);
							break;
						case 2:
						default:
							$result = $this->quoteName($arguments[0], $arguments[1]);
							break;
					}
					return $result;

					break;
			}

			switch (count($arguments))
			{
				case 0 :
					$result = $this->dbo->$name();
					break;
				case 1 :
					$result = $this->dbo->$name($arguments[0]);
					break;
				case 2:
					$result = $this->dbo->$name($arguments[0], $arguments[1]);
					break;
				case 3:
					$result = $this->dbo->$name($arguments[0], $arguments[1], $arguments[2]);
					break;
				case 4:
					$result = $this->dbo->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
					break;
				case 5:
					$result = $this->dbo->$name($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
					break;
				default:
					// Resort to using call_user_func_array for many segments
					$result = call_user_func_array(array($this->dbo, $name), $arguments);
			}

			if (class_exists('JDatabase') && is_object($result) && ($result instanceof JDatabase))
			{
				return $this;
			}

			return $result;
		}
		else
		{
			throw new \Exception('Method ' . $name . ' not found in FOFDatabase');
		}
	}

	public function __get($name)
	{
		if (isset($this->dbo->$name) || property_exists($this->dbo, $name))
		{
			return $this->dbo->$name;
		}
		else
		{
			$this->dbo->$name = null;
			user_error('Database driver does not support property ' . $name);
		}
	}

	public function __set($name, $value)
	{
		if (isset($this->dbo->name) || property_exists($this->dbo, $name))
		{
			$this->dbo->$name = $value;
		}
		else
		{
			$this->dbo->$name = null;
			user_error('Database driver not support property ' . $name);
		}
	}
}
