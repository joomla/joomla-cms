<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Oracle Query Building Class.
 *
 * @since  3.0.0
 */
class JDatabaseQueryOracle extends JDatabaseQueryPdo implements JDatabaseQueryPreparable, JDatabaseQueryLimitable
{
	/**
	 * @var    integer  The offset for the result set.
	 * @since  3.0.0
	 */
	protected $offset;

	/**
	 * @var    integer  The limit for the result set.
	 * @since  3.0.0
	 */
	protected $limit;

	/**
	 * @var    array  Bounded object array
	 * @since  3.0.0
	 */
	protected $bounded = array();

	/**
	 * Method to add a variable to an internal array that will be bound to a prepared SQL statement before query execution. Also
	 * removes a variable that has been bounded from the internal bounded array when the passed in value is null.
	 *
	 * @param   string|integer  $key            The key that will be used in your SQL query to reference the value. Usually of
	 *                                          the form ':key', but can also be an integer.
	 * @param   mixed           &$value         The value that will be bound. The value is passed by reference to support output
	 *                                          parameters such as those possible with stored procedures.
	 * @param   integer         $dataType       Constant corresponding to a SQL datatype.
	 * @param   integer         $length         The length of the variable. Usually required for OUTPUT parameters.
	 * @param   array           $driverOptions  Optional driver options to be used.
	 *
	 * @return  JDatabaseQueryOracle
	 *
	 * @since   3.0.0
	 */
	public function bind($key = null, &$value = null, $dataType = PDO::PARAM_STR, $length = 0, $driverOptions = array())
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

		$obj = new stdClass;

		$obj->value = &$value;
		$obj->dataType = $dataType;
		$obj->length = $length;
		$obj->driverOptions = $driverOptions;

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
	 * @since   3.0.0
	 */
	public function &getBounded($key = null)
	{
		if (empty($key))
		{
			return $this->bounded;
		}
		else
		{
			if (isset($this->bounded[$key]))
			{
				return $this->bounded[$key];
			}
		}
	}

	/**
	 * Clear data from the query or a specific clause of the query.
	 *
	 * @param   string  $clause  Optionally, the name of the clause to clear, or nothing to clear the whole query.
	 *
	 * @return  JDatabaseQueryOracle  Returns this object to allow chaining.
	 *
	 * @since   3.0.0
	 */
	public function clear($clause = null)
	{
		switch ($clause)
		{
			case null:
				$this->bounded = array();
				break;
		}

		parent::clear($clause);

		return $this;
	}

	/**
	 * Method to modify a query already in string format with the needed
	 * additions to make the query limited to a particular number of
	 * results, or start at a particular offset. This method is used
	 * automatically by the __toString() method if it detects that the
	 * query implements the JDatabaseQueryLimitable interface.
	 *
	 * @param   string   $query   The query in string format
	 * @param   integer  $limit   The limit for the result set
	 * @param   integer  $offset  The offset for the result set
	 *
	 * @return  string
	 *
	 * @since   3.0.0
	 */
	public function processLimit($query, $limit, $offset = 0)
	{
		// Check if we need to mangle the query.
		if ($limit || $offset)
		{
			$query = 'SELECT joomla2.*
		              FROM (
		                  SELECT joomla1.*, ROWNUM AS joomla_db_rownum
		                  FROM (
		                      ' . $query . '
		                  ) joomla1
		              ) joomla2';

			// Check if the limit value is greater than zero.
			if ($limit > 0)
			{
				$query .= ' WHERE joomla2.joomla_db_rownum BETWEEN ' . ($offset + 1) . ' AND ' . ($offset + $limit);
			}
			else
			{
				// Check if there is an offset and then use this.
				if ($offset)
				{
					$query .= ' WHERE joomla2.joomla_db_rownum > ' . ($offset + 1);
				}
			}
		}

		return $query;
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
	 * @return  JDatabaseQueryOracle  Returns this object to allow chaining.
	 *
	 * @since   3.0.0
	 */
	public function setLimit($limit = 0, $offset = 0)
	{
		$this->limit = (int) $limit;
		$this->offset = (int) $offset;

		return $this;
	}
}
