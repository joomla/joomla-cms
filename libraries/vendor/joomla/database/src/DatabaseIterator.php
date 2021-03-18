<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database;

/**
 * Joomla Framework Database Driver Class
 *
 * @since  1.0
 */
class DatabaseIterator implements \Countable, \Iterator
{
	/**
	 * The class of object to create.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $class;

	/**
	 * The name of the column to use for the key of the database record.
	 *
	 * @var    mixed
	 * @since  1.0
	 */
	private $column;

	/**
	 * The current database record.
	 *
	 * @var    mixed
	 * @since  1.0
	 */
	private $current;

	/**
	 * A numeric or string key for the current database record.
	 *
	 * @var    scalar
	 * @since  1.0
	 */
	private $key;

	/**
	 * The number of fetched records.
	 *
	 * @var    integer
	 * @since  1.0
	 */
	private $fetched = 0;

	/**
	 * The statement holding the result set to iterate.
	 *
	 * @var    StatementInterface
	 * @since  1.0
	 */
	protected $statement;

	/**
	 * Database iterator constructor.
	 *
	 * @param   StatementInterface  $statement  The statement holding the result set to iterate.
	 * @param   string              $column     An option column to use as the iterator key.
	 * @param   string              $class      The class of object that is returned.
	 *
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public function __construct(StatementInterface $statement, $column = null, $class = \stdClass::class)
	{
		if (!class_exists($class))
		{
			throw new \InvalidArgumentException(sprintf('new %s(*%s*, cursor)', \get_class($this), \gettype($class)));
		}

		if ($statement)
		{
			$fetchMode = $class === \stdClass::class ? FetchMode::STANDARD_OBJECT : FetchMode::CUSTOM_OBJECT;

			// PDO doesn't allow extra arguments for \PDO::FETCH_CLASS, so only forward the class for the custom object mode
			if ($fetchMode === FetchMode::STANDARD_OBJECT)
			{
				$statement->setFetchMode($fetchMode);
			}
			else
			{
				$statement->setFetchMode($fetchMode, $class);
			}
		}

		$this->statement = $statement;
		$this->class     = $class;
		$this->column    = $column;
		$this->fetched   = 0;
		$this->next();
	}

	/**
	 * Database iterator destructor.
	 *
	 * @since   1.0
	 */
	public function __destruct()
	{
		if ($this->statement)
		{
			$this->freeResult();
		}
	}

	/**
	 * Get the number of rows in the result set for the executed SQL given by the cursor.
	 *
	 * @return  integer  The number of rows in the result set.
	 *
	 * @see     Countable::count()
	 * @since   1.0
	 */
	public function count()
	{
		if ($this->statement)
		{
			return $this->statement->rowCount();
		}

		return 0;
	}

	/**
	 * The current element in the iterator.
	 *
	 * @return  object
	 *
	 * @see     Iterator::current()
	 * @since   1.0
	 */
	public function current()
	{
		return $this->current;
	}

	/**
	 * The key of the current element in the iterator.
	 *
	 * @return  scalar
	 *
	 * @see     Iterator::key()
	 * @since   1.0
	 */
	public function key()
	{
		return $this->key;
	}

	/**
	 * Moves forward to the next result from the SQL query.
	 *
	 * @return  void
	 *
	 * @see     Iterator::next()
	 * @since   1.0
	 */
	public function next()
	{
		// Set the default key as being the number of fetched object
		$this->key = $this->fetched;

		// Try to get an object
		$this->current = $this->fetchObject();

		// If an object has been found
		if ($this->current)
		{
			// Set the key as being the indexed column (if it exists)
			if ($this->column && isset($this->current->{$this->column}))
			{
				$this->key = $this->current->{$this->column};
			}

			// Update the number of fetched object
			$this->fetched++;
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
	 * @since   1.0
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
	 * @since   1.0
	 */
	public function valid()
	{
		return (boolean) $this->current;
	}

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @return  mixed  Either the next row from the result set or false if there are no more rows.
	 *
	 * @since   1.0
	 */
	protected function fetchObject()
	{
		if ($this->statement)
		{
			return $this->statement->fetch();
		}

		return false;
	}

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function freeResult()
	{
		if ($this->statement)
		{
			$this->statement->closeCursor();
		}
	}
}
