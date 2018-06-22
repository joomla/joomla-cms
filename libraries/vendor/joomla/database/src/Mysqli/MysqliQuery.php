<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Mysqli;

use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use Joomla\Database\Query\LimitableInterface;
use Joomla\Database\Query\MysqlQueryBuilder;

/**
 * MySQLi Query Building Class.
 *
 * @since  1.0
 */
class MysqliQuery extends DatabaseQuery implements LimitableInterface
{
	use MysqlQueryBuilder;

	/**
	 * The offset for the result set.
	 *
	 * @var    integer
	 * @since  1.0
	 */
	protected $offset;

	/**
	 * The limit for the result set.
	 *
	 * @var    integer
	 * @since  1.0
	 */
	protected $limit;

	/**
	 * Holds key / value pair of bound objects.
	 *
	 * @var    mixed
	 * @since  1.5.0
	 */
	protected $bounded = array();

	/**
	 * Mapping array for parameter types.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $parameterMapping = [
		ParameterType::BOOLEAN      => 'i',
		ParameterType::INTEGER      => 'i',
		ParameterType::LARGE_OBJECT => 's',
		ParameterType::NULL         => 's',
		ParameterType::STRING       => 's',
	];

	/**
	 * The list of zero or null representation of a datetime.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $nullDatetimeList = ['0000-00-00 00:00:00', '1000-01-01 00:00:00'];

	/**
	 * Magic function to convert the query to a string.
	 *
	 * @return  string  The completed query.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __toString()
	{
		switch ($this->type)
		{
			case 'select':
				if ($this->selectRowNumber)
				{
					$orderBy      = $this->selectRowNumber['orderBy'];
					$tmpOffset    = $this->offset;
					$tmpLimit     = $this->limit;
					$this->offset = 0;
					$this->limit  = 0;
					$tmpOrder     = $this->order;
					$this->order  = null;
					$query        = parent::__toString();
					$this->order  = $tmpOrder;
					$this->offset = $tmpOffset;
					$this->limit  = $tmpLimit;

					// Add support for second order by, offset and limit
					$query = PHP_EOL . 'SELECT * FROM (' . $query . PHP_EOL . "ORDER BY $orderBy" . PHP_EOL . ') w';

					if ($this->order)
					{
						$query .= (string) $this->order;
					}

					return $this->processLimit($query, $this->limit, $this->offset);
				}
		}

		return parent::__toString();
	}

	/**
	 * Method to add a variable to an internal array that will be bound to a prepared SQL statement before query execution. Also
	 * removes a variable that has been bounded from the internal bounded array when the passed in value is null.
	 *
	 * @param   string|integer  $key            The key that will be used in your SQL query to reference the value. Usually of
	 *                                          the form ':key', but can also be an integer.
	 * @param   mixed           $value          The value that will be bound. The value is passed by reference to support output
	 *                                          parameters such as those possible with stored procedures.
	 * @param   string          $dataType       The corresponding bind type.
	 * @param   integer         $length         The length of the variable. Usually required for OUTPUT parameters. (Unused)
	 * @param   array           $driverOptions  Optional driver options to be used. (Unused)
	 *
	 * @return  MysqliQuery
	 *
	 * @since   1.5.0
	 */
	public function bind($key = null, &$value = null, $dataType = ParameterType::STRING, $length = 0, $driverOptions = array())
	{
		// Case 1: Empty Key (reset $bounded array)
		if (empty($key))
		{
			$this->bounded = array();

			return $this;
		}

		// Case 2: Key Provided, null value (unset key from $bounded array)
		if (is_null($value))
		{
			if (isset($this->bounded[$key]))
			{
				unset($this->bounded[$key]);
			}

			return $this;
		}

		// Validate parameter type
		if (!isset($this->parameterMapping[$dataType]))
		{
			throw new \InvalidArgumentException(sprintf('Unsupported parameter type `%s`', $dataType));
		}

		$obj           = new \stdClass;
		$obj->value    = &$value;
		$obj->dataType = $this->parameterMapping[$dataType];

		// Case 3: Simply add the Key/Value into the bounded array
		$this->bounded[$key] = $obj;

		return $this;
	}

	/**
	 * Retrieves the bound parameters array when key is null and returns it by reference. If a key is provided then that item is
	 * returned.
	 *
	 * @param   mixed  $key  The bounded variable key to retrieve.
	 *
	 * @return  mixed
	 *
	 * @since   1.5.0
	 */
	public function &getBounded($key = null)
	{
		if (empty($key))
		{
			return $this->bounded;
		}

		if (isset($this->bounded[$key]))
		{
			return $this->bounded[$key];
		}
	}

	/**
	 * Clear data from the query or a specific clause of the query.
	 *
	 * @param   string  $clause  Optionally, the name of the clause to clear, or nothing to clear the whole query.
	 *
	 * @return  MysqliQuery  Returns this object to allow chaining.
	 *
	 * @since   1.5.0
	 */
	public function clear($clause = null)
	{
		switch ($clause)
		{
			case null:
				$this->bounded = array();
				break;
		}

		return parent::clear($clause);
	}

	/**
	 * Sets the offset and limit for the result set, if the database driver supports it.
	 *
	 * Usage:
	 * $query->setLimit(100, 0); (retrieve 100 rows, starting at first record)
	 * $query->setLimit(50, 50); (retrieve 50 rows, starting at 50th record)
	 *
	 * @param   integer  $limit   The limit for the result set
	 * @param   integer  $offset  The offset for the result set
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function setLimit($limit = 0, $offset = 0)
	{
		$this->limit  = (int) $limit;
		$this->offset = (int) $offset;

		return $this;
	}
}
