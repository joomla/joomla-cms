<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  database
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file is adapted from the Joomla! Platform. It is used to iterate a database cursor returning FOFTable objects
 * instead of plain stdClass objects
 */

// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * Database iterator
 */
abstract class FOFDatabaseIterator implements Iterator
{
	/**
	 * The database cursor.
	 *
	 * @var    mixed
	 */
	protected $cursor;

	/**
	 * The class of object to create.
	 *
	 * @var    string
	 */
	protected $class;

	/**
	 * The name of the column to use for the key of the database record.
	 *
	 * @var    mixed
	 */
	private $_column;

	/**
	 * The current database record.
	 *
	 * @var    mixed
	 */
	private $_current;

	/**
	 * The current database record as a FOFTable object.
	 *
	 * @var    FOFTable
	 */
	private $_currentTable;

	/**
	 * A numeric or string key for the current database record.
	 *
	 * @var    scalar
	 */
	private $_key;

	/**
	 * The number of fetched records.
	 *
	 * @var    integer
	 */
	private $_fetched = 0;

	/**
	 * A FOFTable object created using the class type $class, used by getTable
	 *
	 * @var   FOFTable
	 */
	private $_tableObject = null;

	/**
	 * Returns an iterator object for a specific database type
	 *
	 * @param   string  $dbName  The database type, e.g. mysql, mysqli, sqlazure etc.
	 * @param   mixed   $cursor  The database cursor
	 * @param   string  $column  An option column to use as the iterator key
	 * @param   string  $class   The table class of the returned objects
	 * @param   array   $config  Configuration parameters to push to the table class
	 *
	 * @return  FOFDatabaseIterator
	 *
	 * @throws  InvalidArgumentException
	 */
	public static function &getIterator($dbName, $cursor, $column = null, $class, $config = array())
	{
		$className = 'FOFDatabaseIterator' . ucfirst($dbName);

		$object = new $className($cursor, $column, $class, $config);

		return $object;
	}

	/**
	 * Database iterator constructor.
	 *
	 * @param   mixed   $cursor  The database cursor.
	 * @param   string  $column  An option column to use as the iterator key.
	 * @param   string  $class   The table class of the returned objects.
	 * @param   array   $config  Configuration parameters to push to the table class
	 *
	 * @throws  InvalidArgumentException
	 */
	public function __construct($cursor, $column = null, $class, $config = array())
	{
		// Figure out the type and prefix of the class by the class name
		$parts = FOFInflector::explode($class);

        if(count($parts) != 3)
        {
            throw new InvalidArgumentException('Invalid table name, expected a pattern like ComponentTableFoobar got '.$class);
        }

		$this->_tableObject = FOFTable::getInstance($parts[2], ucfirst($parts[0]) . ucfirst($parts[1]))->getClone();

		$this->cursor   = $cursor;
		$this->class    = 'stdClass';
		$this->_column  = $column;
		$this->_fetched = 0;

		$this->next();
	}

	/**
	 * Database iterator destructor.
	 */
	public function __destruct()
	{
		if ($this->cursor)
		{
			$this->freeResult($this->cursor);
		}
	}

	/**
	 * The current element in the iterator.
	 *
	 * @return  object
	 *
	 * @see     Iterator::current()
	 */
	public function current()
	{
		return $this->_currentTable;
	}

	/**
	 * The key of the current element in the iterator.
	 *
	 * @return  scalar
	 *
	 * @see     Iterator::key()
	 */
	public function key()
	{
		return $this->_key;
	}

	/**
	 * Moves forward to the next result from the SQL query.
	 *
	 * @return  void
	 *
	 * @see     Iterator::next()
	 */
	public function next()
	{
		// Set the default key as being the number of fetched object
		$this->_key = $this->_fetched;

		// Try to get an object
		$this->_current = $this->fetchObject();

		// If an object has been found
		if ($this->_current)
		{
			$this->_currentTable = $this->getTable();

			// Set the key as being the indexed column (if it exists)
			if (isset($this->_current->{$this->_column}))
			{
				$this->_key = $this->_current->{$this->_column};
			}

			// Update the number of fetched object
			$this->_fetched++;
		}
	}

	/**
	 * Rewinds the iterator.
	 *
	 * This iterator cannot be rewound.
	 *
	 * @return  void
	 *
	 * @see     Iterator::rewind()
	 */
	public function rewind()
	{
	}

	/**
	 * Checks if the current position of the iterator is valid.
	 *
	 * @return  boolean
	 *
	 * @see     Iterator::valid()
	 */
	public function valid()
	{
		return (boolean) $this->_current;
	}

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 */
	abstract protected function fetchObject();

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @return  void
	 */
	abstract protected function freeResult();

	/**
	 * Returns the data in $this->_current as a FOFTable instance
	 *
	 * @return  FOFTable
	 *
	 * @throws  OutOfBoundsException
	 */
	protected function getTable()
	{
		if (!$this->valid())
		{
			throw new OutOfBoundsException('Cannot get item past iterator\'s bounds', 500);
		}

		$this->_tableObject->bind($this->_current);

		return $this->_tableObject;
	}
}
