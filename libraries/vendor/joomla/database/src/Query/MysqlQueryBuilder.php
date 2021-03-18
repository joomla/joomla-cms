<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Query;

/**
 * Trait for MySQL Query Building.
 *
 * @since  __DEPLOY_VERSION__
 */
trait MysqlQueryBuilder
{
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
	 * Method to modify a query already in string format with the needed additions to make the query limited to a particular number of
	 * results, or start at a particular offset.
	 *
	 * @param   string   $query   The query in string format
	 * @param   integer  $limit   The limit for the result set
	 * @param   integer  $offset  The offset for the result set
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function processLimit($query, $limit, $offset = 0)
	{
		if ($limit > 0 && $offset > 0)
		{
			$query .= ' LIMIT ' . $offset . ', ' . $limit;
		}
		elseif ($limit > 0)
		{
			$query .= ' LIMIT ' . $limit;
		}

		return $query;
	}

	/**
	 * Concatenates an array of column names or values.
	 *
	 * @param   string[]     $values     An array of values to concatenate.
	 * @param   string|null  $separator  As separator to place between each value.
	 *
	 * @return  string  The concatenated values.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function concatenate($values, $separator = null)
	{
		if ($separator !== null)
		{
			$statement = 'CONCAT_WS(' . $this->quote($separator);

			foreach ($values as $value)
			{
				$statement .= ', ' . $value;
			}

			return $statement . ')';
		}

		return 'CONCAT(' . implode(',', $values) . ')';
	}

	/**
	 * Aggregate function to get input values concatenated into a string, separated by delimiter
	 *
	 * Usage:
	 * $query->groupConcat('id', ',');
	 *
	 * @param   string  $expression  The expression to apply concatenation to, this may be a column name or complex SQL statement.
	 * @param   string  $separator   The delimiter of each concatenated value
	 *
	 * @return  string  Input values concatenated into a string, separated by delimiter
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function groupConcat($expression, $separator = ',')
	{
		return 'GROUP_CONCAT(' . $expression . ' SEPARATOR ' . $this->quote($separator) . ')';
	}

	/**
	 * Method to quote and optionally escape a string to database requirements for insertion into the database.
	 *
	 * This method is provided for use where the query object is passed to a function for modification.
	 * If you have direct access to the database object, it is recommended you use the quote method directly.
	 *
	 * Note that 'q' is an alias for this method as it is in DatabaseDriver.
	 *
	 * Usage:
	 * $query->quote('fulltext');
	 * $query->q('fulltext');
	 * $query->q(array('option', 'fulltext'));
	 *
	 * @param   array|string  $text    A string or an array of strings to quote.
	 * @param   boolean       $escape  True (default) to escape the string, false to leave it unchanged.
	 *
	 * @return  string  The quoted input string.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException if the internal db property is not a valid object.
	 */
	abstract public function quote($text, $escape = true);

	/**
	 * Get the regular expression operator
	 *
	 * Usage:
	 * $query->where('field ' . $query->regexp($search));
	 *
	 * @param   string  $value  The regex pattern.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function regexp($value)
	{
		return ' REGEXP ' . $value;
	}

	/**
	 * Get the function to return a random floating-point value
	 *
	 * Usage:
	 * $query->rand();
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function rand()
	{
		return ' RAND() ';
	}

	/**
	 * Find a value in a varchar used like a set.
	 *
	 * Ensure that the value is an integer before passing to the method.
	 *
	 * Usage:
	 * $query->findInSet((int) $parent->id, 'a.assigned_cat_ids')
	 *
	 * @param   string  $value  The value to search for.
	 * @param   string  $set    The set of values.
	 *
	 * @return  string  A representation of the MySQL find_in_set() function for the driver.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function findInSet($value, $set)
	{
		return ' find_in_set(' . $value . ', ' . $set . ')';
	}

	/**
	 * Return the number of the current row.
	 *
	 * Usage:
	 * $query->select('id');
	 * $query->selectRowNumber('ordering,publish_up DESC', 'new_ordering');
	 * $query->from('#__content');
	 *
	 * @param   string  $orderBy           An expression of ordering for window function.
	 * @param   string  $orderColumnAlias  An alias for new ordering column.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  \RuntimeException
	 */
	public function selectRowNumber($orderBy, $orderColumnAlias)
	{
		$this->validateRowNumber($orderBy, $orderColumnAlias);

		return $this->select("(SELECT @rownum := @rownum + 1 FROM (SELECT @rownum := 0) AS r) AS $orderColumnAlias");
	}

	/**
	 * Casts a value to a char.
	 *
	 * Ensure that the value is properly quoted before passing to the method.
	 *
	 * Usage:
	 * $query->select($query->castAs('CHAR', 'a'));
	 *
	 * @param   string  $type    The type of string to cast as.
	 * @param   string  $value   The value to cast as a char.
	 * @param   string  $length  The value to cast as a char.
	 *
	 * @return  string  SQL statement to cast the value as a char type.
	 *
	 * @since   1.0
	 */
	public function castAs(string $type, string $value, ?string $length = null)
	{
		switch (strtoupper($type))
		{
			case 'CHAR':
				if (!$length)
				{
					return $value;
				}
				else
				{
					return 'CAST(' . $value . ' AS CHAR(' . $length . '))';
				}

			case 'INT':
				return '(' . $value . ' + 0)';
		}

		return parent::castAs($type, $value, $length);
	}
}
