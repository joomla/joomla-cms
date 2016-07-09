<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database;

/**
 * Joomla Framework Query Building Interface.
 *
 * @since  __DEPLOY_VERSION__
 */
interface QueryInterface
{
	/**
	 * Convert the query object to a string.
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __toString();

	/**
	 * Add a single column, or array of columns to the CALL clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 * The call method can, however, be called multiple times in the same query.
	 *
	 * Usage:
	 * $query->call('a.*')->call('b.id');
	 * $query->call(array('a.*', 'b.id'));
	 *
	 * @param   array|string  $columns  A string or an array of field names.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function call($columns);

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
	 * @return  string  SQL statement to cast the value as a char type.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function castAsChar($value);

	/**
	 * Gets the number of characters in a string.
	 *
	 * Note, use 'length' to find the number of bytes in a string.
	 *
	 * Usage:
	 * $query->select($query->charLength('a'));
	 *
	 * @param   string  $field      A value.
	 * @param   string  $operator   Comparison operator between charLength integer value and $condition
	 * @param   string  $condition  Integer value to compare charLength with.
	 *
	 * @return  string  SQL statement to get the length of a character.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function charLength($field, $operator = null, $condition = null);

	/**
	 * Clear data from the query or a specific clause of the query.
	 *
	 * @param   string  $clause  Optionally, the name of the clause to clear, or nothing to clear the whole query.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function clear($clause = null);

	/**
	 * Adds a column, or array of column names that would be used for an INSERT INTO statement.
	 *
	 * @param   array|string  $columns  A column name, or array of column names.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function columns($columns);

	/**
	 * Concatenates an array of column names or values.
	 *
	 * Usage:
	 * $query->select($query->concatenate(array('a', 'b')));
	 *
	 * @param   array   $values     An array of values to concatenate.
	 * @param   string  $separator  As separator to place between each value.
	 *
	 * @return  string  SQL statement representing the concatenated values.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function concatenate($values, $separator = null);

	/**
	 * Gets the current date and time.
	 *
	 * Usage:
	 * $query->where('published_up < '.$query->currentTimestamp());
	 *
	 * @return  string  SQL statement to get the current timestamp.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function currentTimestamp();

	/**
	 * Add a table name to the DELETE clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 *
	 * Usage:
	 * $query->delete('#__a')->where('id = 1');
	 *
	 * @param   string  $table  The name of the table to delete from.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function delete($table = null);

	/**
	 * Add a single column, or array of columns to the EXEC clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 * The exec method can, however, be called multiple times in the same query.
	 *
	 * Usage:
	 * $query->exec('a.*')->exec('b.id');
	 * $query->exec(array('a.*', 'b.id'));
	 *
	 * @param   array|string  $columns  A string or an array of field names.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function exec($columns);

	/**
	 * Add a table to the FROM clause of the query.
	 *
	 * Note that while an array of tables can be provided, it is recommended you use explicit joins.
	 *
	 * Usage:
	 * $query->select('*')->from('#__a');
	 *
	 * @param   array|string  $tables         A string or array of table names.  This can be a QueryInterface object (or a child of it) when used
	 *                                        as a subquery in FROM clause along with a value for $subQueryAlias.
	 * @param   string        $subQueryAlias  Alias used when $tables is a QueryInterface.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function from($tables, $subQueryAlias = null);

	/**
	 * Used to get a string to extract year from date column.
	 *
	 * Usage:
	 * $query->select($query->year($query->quoteName('dateColumn')));
	 *
	 * @param   string  $date  Date column containing year to be extracted.
	 *
	 * @return  string  SQL statement to get the year from a date value.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function year($date);

	/**
	 * Used to get a string to extract month from date column.
	 *
	 * Usage:
	 * $query->select($query->month($query->quoteName('dateColumn')));
	 *
	 * @param   string  $date  Date column containing month to be extracted.
	 *
	 * @return  string  SQL statement to get the month from a date value.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function month($date);

	/**
	 * Used to get a string to extract day from date column.
	 *
	 * Usage:
	 * $query->select($query->day($query->quoteName('dateColumn')));
	 *
	 * @param   string  $date  Date column containing day to be extracted.
	 *
	 * @return  string  SQL statement to get the day from a date value.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function day($date);

	/**
	 * Used to get a string to extract hour from date column.
	 *
	 * Usage:
	 * $query->select($query->hour($query->quoteName('dateColumn')));
	 *
	 * @param   string  $date  Date column containing hour to be extracted.
	 *
	 * @return  string  SQL statement to get the hour from a date/time value.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function hour($date);

	/**
	 * Used to get a string to extract minute from date column.
	 *
	 * Usage:
	 * $query->select($query->minute($query->quoteName('dateColumn')));
	 *
	 * @param   string  $date  Date column containing minute to be extracted.
	 *
	 * @return  string  SQL statement to get the minute from a date/time value.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function minute($date);

	/**
	 * Used to get a string to extract seconds from date column.
	 *
	 * Usage:
	 * $query->select($query->second($query->quoteName('dateColumn')));
	 *
	 * @param   string  $date  Date column containing second to be extracted.
	 *
	 * @return  string  SQL statement to get the second from a date/time value.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function second($date);

	/**
	 * Add a grouping column to the GROUP clause of the query.
	 *
	 * Usage:
	 * $query->group('id');
	 *
	 * @param   array|string  $columns  A string or array of ordering columns.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function group($columns);

	/**
	 * A conditions to the HAVING clause of the query.
	 *
	 * Usage:
	 * $query->group('id')->having('COUNT(id) > 5');
	 *
	 * @param   array|string  $conditions  A string or array of columns.
	 * @param   string        $glue        The glue by which to join the conditions. Defaults to AND.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function having($conditions, $glue = 'AND');

	/**
	 * Add a table name to the INSERT clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 *
	 * Usage:
	 * $query->insert('#__a')->set('id = 1');
	 * $query->insert('#__a')->columns('id, title')->values('1,2')->values('3,4');
	 * $query->insert('#__a')->columns('id, title')->values(array('1,2', '3,4'));
	 *
	 * @param   string   $table           The name of the table to insert data into.
	 * @param   boolean  $incrementField  The name of the field to auto increment.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function insert($table, $incrementField = false);

	/**
	 * Add a JOIN clause to the query.
	 *
	 * Usage:
	 * $query->join('INNER', 'b ON b.id = a.id);
	 *
	 * @param   string        $type        The type of join. This string is prepended to the JOIN keyword.
	 * @param   array|string  $conditions  A string or array of conditions.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function join($type, $conditions);

	/**
	 * Add an ordering column to the ORDER clause of the query.
	 *
	 * Usage:
	 * $query->order('foo')->order('bar');
	 * $query->order(array('foo','bar'));
	 *
	 * @param   array|string  $columns  A string or array of ordering columns.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function order($columns);

	/**
	 * Add a single column, or array of columns to the SELECT clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 * The select method can, however, be called multiple times in the same query.
	 *
	 * Usage:
	 * $query->select('a.*')->select('b.id');
	 * $query->select(array('a.*', 'b.id'));
	 *
	 * @param   array|string  $columns  A string or an array of field names.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function select($columns);

	/**
	 * Add a single condition string, or an array of strings to the SET clause of the query.
	 *
	 * Usage:
	 * $query->set('a = 1')->set('b = 2');
	 * $query->set(array('a = 1', 'b = 2');
	 *
	 * @param   array|string  $conditions  A string or array of string conditions.
	 * @param   string        $glue        The glue by which to join the condition strings. Defaults to `,`.
	 *                                     Note that the glue is set on first use and cannot be changed.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function set($conditions, $glue = ',');

	/**
	 * Add a table name to the UPDATE clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 *
	 * Usage:
	 * $query->update('#__foo')->set(...);
	 *
	 * @param   string  $table  A table to update.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function update($table);

	/**
	 * Adds a tuple, or array of tuples that would be used as values for an INSERT INTO statement.
	 *
	 * Usage:
	 * $query->values('1,2,3')->values('4,5,6');
	 * $query->values(array('1,2,3', '4,5,6'));
	 *
	 * @param   array|string  $values  A single tuple, or array of tuples.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function values($values);

	/**
	 * Add a single condition, or an array of conditions to the WHERE clause of the query.
	 *
	 * Usage:
	 * $query->where('a = 1')->where('b = 2');
	 * $query->where(array('a = 1', 'b = 2'));
	 *
	 * @param   array|string  $conditions  A string or array of where conditions.
	 * @param   string        $glue        The glue by which to join the conditions. Defaults to AND.
	 *                                     Note that the glue is set on first use and cannot be changed.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function where($conditions, $glue = 'AND');

	/**
	 * Extend the WHERE clause with a single condition or an array of conditions, with a potentially different logical operator from the one in the
	 * current WHERE clause.
	 *
	 * Usage:
	 * $query->where(array('a = 1', 'b = 2'))->extendWhere('XOR', array('c = 3', 'd = 4'));
	 * will produce: WHERE ((a = 1 AND b = 2) XOR (c = 3 AND d = 4)
	 *
	 * @param   string  $outerGlue   The glue by which to join the conditions to the current WHERE conditions.
	 * @param   mixed   $conditions  A string or array of WHERE conditions.
	 * @param   string  $innerGlue   The glue by which to join the conditions. Defaults to AND.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function extendWhere($outerGlue, $conditions, $innerGlue = 'AND');

	/**
	 * Add a query to UNION with the current query.
	 * Multiple unions each require separate statements and create an array of unions.
	 *
	 * Usage:
	 * $query->union('SELECT name FROM  #__foo')
	 * $query->union('SELECT name FROM  #__foo','distinct')
	 * $query->union(array('SELECT name FROM  #__foo', 'SELECT name FROM  #__bar'))
	 *
	 * @param   QueryInterface|string  $query     The QueryInterface object or string to union.
	 * @param   boolean                $distinct  True to only return distinct rows from the union.
	 * @param   string                 $glue      The glue by which to join the conditions.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function union($query, $distinct = false, $glue = '');
}
