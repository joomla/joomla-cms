<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.database.databasequery');

/**
 * Query Building Class.
 *
 * @package     Joomla.Platform
 * @subpackage  Database
 * @since       11.3
 */
class JDatabaseQueryPostgreSQL extends JDatabaseQuery
{
	/**
	 * @var    object  The FOR UPDATE element used in "FOR UPDATE"  lock
	 * @since  11.3
	 */
	protected $forUpdate = null;

	/**
	 * @var    object  The FOR SHARE element used in "FOR SHARE"  lock
	 * @since  11.3
	 */
	protected $forShare = null;

	/**
	 * @var    object  The NOWAIT element used in "FOR SHARE" and "FOR UPDATE" lock
	 * @since  11.3
	 */
	protected $noWait = null;

	/**
	 * @var    object  The LIMIT element
	 * @since  11.3
	 */
	protected $limit = null;

	/**
	 * @var    object  The OFFSET element
	 * @since  11.3
	 */
	protected $offset = null;

	/**
	 * @var    object  The RETURNING element of INSERT INTO
	 * @since  11.3
	 */
	protected $returning = null;

	/**
	 * Magic function to convert the query to a string, only for postgresql specific query
	 *
	 * @return  string	The completed query.
	 *
	 * @since   11.3
	 */
	public function __toString()
	{
		$query = '';

		switch ($this->type)
		{
			case 'select':
				$query .= (string) $this->select;
				$query .= (string) $this->from;
				if ($this->join)
				{
					// special case for joins
					foreach ($this->join as $join)
					{
						$query .= (string) $join;
					}
				}

				if ($this->where)
				{
					$query .= (string) $this->where;
				}

				if ($this->group)
				{
					$query .= (string) $this->group;
				}

				if ($this->having)
				{
					$query .= (string) $this->having;
				}

				if ($this->order)
				{
					$query .= (string) $this->order;
				}

				if ($this->limit)
				{
					$query .= (string) $this->limit;
				}

				if ($this->offset)
				{
					$query .= (string) $this->offset;
				}

				if ($this->forUpdate)
				{
					$query .= (string) $this->forUpdate;
				}
				else
				{
					if ($this->forShare)
					{
						$query .= (string) $this->forShare;
					}
				}

				if ($this->noWait)
				{
					$query .= (string) $this->noWait;
				}

				break;

			case 'update':
				$query .= (string) $this->update;
				$query .= (string) $this->set;

				if ($this->join)
				{
					$onWord = ' ON ';

					// workaround for special case of JOIN with UPDATE
					foreach ($this->join as $join)
					{
						$joinElem = $join->getElements();

						$joinArray = explode($onWord, $joinElem[0]);

						$this->from($joinArray[0]);
						$this->where($joinArray[1]);
					}

					$query .= (string) $this->from;
				}

				if ($this->where)
				{
					$query .= (string) $this->where;
				}

				break;

			case 'insert':
				$query .= (string) $this->insert;

				if ($this->values)
				{
					if ($this->columns)
					{
						$query .= (string) $this->columns;
					}

					$query .= ' VALUES ';
					$query .= (string) $this->values;

					if ($this->returning)
					{
						$query .= (string) $this->returning;
					}
				}

				break;

			case 'lock':
				$query .= (string) $this->lock;
				break;

			default:
				$query = parent::__toString();
				break;

		}

		return $query;
	}

	/**
	 * Clear data from the query or a specific clause of the query.
	 *
	 * @param   string  $clause  Optionally, the name of the clause to clear, or nothing to clear the whole query.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function clear($clause = null)
	{
		switch ($clause)
		{
			case 'limit':
				$this->limit = null;
				break;

			case 'offset':
				$this->offset = null;
				break;

			case 'forUpdate':
				$this->forUpdate = null;
				break;

			case 'forShare':
				$this->forShare = null;
				break;

			case 'noWait':
				$this->noWait = null;
				break;

			case 'returning':
				$this->returning = null;
				break;

			default:
				$this->type = null;
				$this->limit = null;
				$this->offset = null;
				$this->forUpdate = null;
				$this->forShare = null;
				$this->noWait = null;
				$this->returning = null;
				parent::clear($clause);
				break;
		}

		return $this;
	}

	/**
	 * Casts a value to a char.
	 *
	 * Ensure that the value is properly quoted before passing to the method.
	 *
	 * Usage:
	 * $query->select($query->castAsChar('a'));
	 *
	 * @param   string  $value  The value to cast as a char.
	 *
	 * @return  string  Returns the cast value.
	 *
	 * @since   11.1
	 */
	public function castAsChar($value)
	{
		return $this->db->quote($value);
	}

	/**
	 * Concatenates an array of column names or values.
	 *
	 * Usage:
	 * $query->select($query->concatenate(array('a', 'b')));
	 *
	 * @param   array   $values     An array of values to concatenate.
	 * @param   string  $separator  As separator to place between each value.
	 *
	 * @return  string  The concatenated values.
	 *
	 * @since   11.3
	 */
	public function concatenate($values, $separator = null)
	{
		if ($separator)
		{
			return implode(' || ' . $this->quote($separator) . ' || ', $values);
		}
		else
		{
			return implode(' || ', $values);
		}
	}

	/**
	 * Gets the current date and time.
	 *
	 * @return  string  Return string used in query to obtain 
	 *
	 * @since   11.3
	 */
	public function currentTimestamp()
	{
		return 'NOW()';
	}

	/**
	 * Sets the FOR UPDATE lock on select's output row
	 * 
	 * @param   string   $table_name  The table to lock
	 * @param   boolean  $glue        The glue by which to join the conditions. Defaults to ',' .
	 * 
	 * @return  JDatabaseQuery  FOR UPDATE query element
	 * 
	 * @since   11.3
	 */
	public function forUpdate ($table_name, $glue = ',')
	{
		$this->type = 'forUpdate';

		if ( is_null($this->forUpdate) )
		{
			$glue = strtoupper($glue);
			$this->forUpdate = new JDatabaseQueryElement('FOR UPDATE', ' OF ' . $table_name, " $glue ");
		}
		else
		{
			$this->forUpdate->append(' OF ' . $table_name);
		}
	}

	/**
	 * Sets the FOR SHARE lock on select's output row
	 * 
	 * @param   string   $table_name  The table to lock
	 * @param   boolean  $glue        The glue by which to join the conditions. Defaults to ',' .
	 * 
	 * @return  JDatabaseQuery  FOR SHARE query element
	 * 
	 * @since   11.3
	 */
	public function forShare ($table_name, $glue = ',')
	{
		$this->type = 'forShare';

		if ( is_null($this->forShare) )
		{
			$glue = strtoupper($glue);
			$this->forShare = new JDatabaseQueryElement('FOR SHARE', ' OF ' . $table_name, " $glue ");
		}
		else
		{
			$this->forShare->append(' OF ' . $table_name);
		}
	}

	/**
	 * Sets the NOWAIT lock on select's output row
	 * 
	 * @return  JDatabaseQuery  NO WAIT query element
	 * 
	 * @since   11.3
	 */
	public function noWait ()
	{
		$this->type = 'noWait';

		if ( is_null($this->noWait) )
		{
			$this->noWait = new JDatabaseQueryElement('NOWAIT', null);
		}
	}

	/**
	 * Set the LIMIT clause to the query
	 * 
	 * @param   int  $limit  An int of how many row will be returned
	 *
	 * @return  JDatabaseQuery  Returns this object to allow chaining.
	 *
	 * @since   11.3
	 */
	public function limit( $limit = 0 )
	{
		if (is_null($this->limit))
		{
			$this->limit = new JDatabaseQueryElement('LIMIT', (int) $limit);
		}

		return $this;
	}

	/**
	 * Set the OFFSET clause to the query
	 * 
	 * @param   int  $offset  An int for skipping row
	 *
	 * @return  JDatabaseQuery  Returns this object to allow chaining.
	 *
	 * @since   11.3
	 */
	public function offset( $offset = 0 )
	{
		if (is_null($this->offset))
		{
			$this->offset = new JDatabaseQueryElement('OFFSET', (int) $offset);
		}

		return $this;
	}

	/**
	 * Add the RETURNING element to INSERT INTO statement.
	 *
	 * @param   mixed  $pkCol  The name of the primary key column.
	 *
	 * @return  JDatabaseQuery  Returns this object to allow chaining.
	 *
	 * @since   11.3
	 */
	public function returning( $pkCol )
	{
		if (is_null($this->returning))
		{
			$this->returning = new JDatabaseQueryElement('RETURNING', $pkCol);
		}

		return $this;
	}
}
