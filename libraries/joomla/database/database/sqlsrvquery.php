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
 * @package		Joomla.Platform
 * @subpackage	Database
 * @since		11.1
 */
class JDatabaseQuerySQLSrv extends JDatabaseQuery
{
	/**
	 * @var    string  The character(s) used to quote SQL statement names such as table names or field names,
	 *                 etc.  The child classes should define this as necessary.  If a single character string the
	 *                 same character is used for both sides of the quoted name, else the first character will be
	 *                 used for the opening quote and the second for the closing quote.
	 * @since  11.1
	 */
	protected $name_quotes = '`';

	/**
	 * @var    string  The null or zero representation of a timestamp for the database driver.  This should be
	 *                 defined in child classes to hold the appropriate value for the engine.
	 * @since  11.1
	 */
	protected $null_date = '1900-01-01 00:00:00';

	/**
	 * Magic function to convert the query to a string.
	 *
	 * @return  string	The completed query.
	 * @since   11.1
	 */
	public function __toString()
	{
		$query = '';

		switch ($this->type)
		{
			case 'insert':
				$query .= (string) $this->insert;

				// Set method
				if ($this->set) {
					$query .= (string) $this->set;
				}
				// Columns-Values method
				else if ($this->values) {
					if ($this->columns) {
						$query .= (string) $this->where;
					}

					$tableName = array_shift($this->insert->getElements());

					$query .= 'VALUES ';
					$query .= (string) $this->values;

					$query = 'SET IDENTITY_INSERT '.$tableName.' ON;' .
						$query .
						'SET IDENTITY_INSERT '.$tableName.' OFF;';
				}

				break;

			default:
				$query = parent::__toString();
				break;
		}

		return $query;
	}

	/**
	 * Casts a value to a char.
	 *
	 * Ensure that the value is properly quoted before passing to the method.
	 *
	 * @param   string  $value  The value to cast as a char.
	 *
	 * @return  string  Returns the cast value.
	 * @since   11.1
	 */
	function castAsChar($value)
	{
		return 'CAST('.$value.' as NVARCHAR(10))';
	}

	/**
	 * Gets the function to determine the length of a character string.
	 *
	 * @param   string  $value  A value.
	 *
	 * @return  string  The required char lenght call.
	 *
	 * @since 11.1
	 */
	function charLength($field)
	{
		return 'DATALENGTH('.$field.') IS NOT NULL';
	}

	/**
	 * Concatenates an array of column names or values.
	 *
	 * @param   array   $values     An array of values to concatenate.
	 * @param   string  $separator  As separator to place between each value.
	 *
	 * @return  string  The concatenated values.
	 *
	 * @since   11.1
	 */
	function concatenate($values, $separator = null)
	{
		if ($separator) {
			return '('.implode('+'.$this->quote($separator).'+', $values).')';
		}
		else{
			return '('.implode('+', $values).')';
		}
	}

	/**
	 * Gets the current date and time.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	function currentTimestamp()
	{
		return 'GETDATE()';
	}

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * The escaping for MSSQL isn't handled in the driver though that would be nice.  Because of this we need
	 * to handle the escaping ourselves.
	 *
	 * @param   string  The string to be escaped.
	 * @param   bool    Optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 *
	 * @since   11.1
	 */
	public function escape($text, $extra = false)
	{
		// TODO: MSSQL Compatible escaping
		$result = addslashes($text);
		$result = str_replace("\'", "''", $result);
		$result = str_replace('\"', '"', $result);
		//$result = str_replace("\\", "''", $result);

		if ($extra) {
			// We need the below str_replace since the search in sql server doesnt recognize _ character.
			$result = str_replace('_', '[_]', $result);
		}

		return $result;
	}

	/**
	 * Get the length of a a string in bytes.
	 *
	 * @param   string  $value  The string to measure.
	 *
	 * @return  int
	 *
	 * @since   11.1
	 */
	function length($value)
	{
		return 'LEN('.$value.')';
	}
}