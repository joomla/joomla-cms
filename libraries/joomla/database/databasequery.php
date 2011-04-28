<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Database
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Query Element Class.
 *
 * @package		Joomla.Platform
 * @subpackage	Database
 * @since		11.1
 */
class JDatabaseQueryElement
{
	/**
	 * @var		string	The name of the element.
	 * @since	11.1
	 */
	protected $_name = null;

	/**
	 * @var		array	An array of elements.
	 * @since	11.1
	 */
	protected $_elements = null;

	/**
	 * @var		string	Glue piece.
	 * @since	11.1
	 */
	protected $_glue = null;

	/**
	 * Constructor.
	 *
	 * @param	string	$name		The name of the element.
	 * @param	mixed	$elements	String or array.
	 * @param	string	$glue		The glue for elements.
	 *
	 * @return	JDatabaseQueryElement
	 * @since	11.1
	 */
	public function __construct($name, $elements, $glue = ',')
	{
		$this->_elements	= array();
		$this->_name		= $name;
		$this->_glue		= $glue;

		$this->append($elements);
	}

	/**
	 * Magic function to convert the query element to a string.
	 *
	 * @return	string
	 * @since	11.1
	 */
	public function __toString()
	{
		return PHP_EOL.$this->_name.' '.implode($this->_glue, $this->_elements);
	}

	/**
	 * Appends element parts to the internal list.
	 *
	 * @param	mixed	String or array.
	 *
	 * @return	void
	 * @since	11.1
	 */
	public function append($elements)
	{
		if (is_array($elements)) {
			$this->_elements = array_merge($this->_elements, $elements);
		}
		else {
			$this->_elements = array_merge($this->_elements, array($elements));
		}
	}
}

/**
 * Query Building Class.
 *
 * @package		Joomla.Platform
 * @subpackage	Database
 * @since		11.1
 */
abstract class JDatabaseQuery
{
	/**
	 * @var		string	The query type.
	 * @since	11.1
	 */
	protected $_type = '';

	/**
	 * @var		object	The select element.
	 * @since	11.1
	 */
	protected $_select = null;

	/**
	 * @var		object	The delete element.
	 * @since	11.1
	 */
	protected $_delete = null;

	/**
	 * @var		object	The update element.
	 * @since	11.1
	 */
	protected $_update = null;

	/**
	 * @var		object	The insert element.
	 * @since	11.1
	 */
	protected $_insert = null;

	/**
	 * @var		object	The from element.
	 * @since	11.1
	 */
	protected $_from = null;

	/**
	 * @var		object	The join element.
	 * @since	11.1
	 */
	protected $_join = null;

	/**
	 * @var		object	The set element.
	 * @since	11.1
	 */
	protected $_set = null;

	/**
	 * @var		object	The where element.
	 * @since	11.1
	 */
	protected $_where = null;

	/**
	 * @var		object	The group by element.
	 * @since	11.1
	 */
	protected $_group = null;

	/**
	 * @var		object	The having element.
	 * @since	11.1
	 */
	protected $_having = null;

	/**
	 * @var		object	The order element.
	 * @since	11.1
	 */
	protected $_order = null;

	/**
	 * @var   object  The show table element.
	 * @since 11.1
	 */
	protected $_show_tables = null;

	/**
	 * @var   object  The drop table element.
	 * @since 11.1
	 */
	protected $_drop = null;

	/**
	 * @var   object  The rename table element.
	 * @since 11.1
	 */
	protected $_rename = null;

	/**
	 * @var   object  The insert element.
	 * @since 11.1
	 */
	protected $_insert_into = null;

	/**
	 * @var   object  The insert value element.
	 * @since 11.1
	 */
	protected $_values = null;

	/**
	 * @var   object  The insert field element.
	 * @since 11.1
	 */
	protected $_fields = null;

	/**
	 * @var   object  The auto increment insert field element.
	 * @since 11.1
	 */
	protected $_auto_increment_field = null;

	/**
	 * Clear data from the query or a specific clause of the query.
	 *
	 * @param	string	$clear	Optionally, the name of the clause to clear, or nothing to clear the whole query.
	 *
	 * @return	void
	 * @since	11.1
	 */
	public function clear($clause = null)
	{
		switch ($clause)
		{
			case 'select':
				$this->_select = null;
				$this->_type = null;
				break;

			case 'delete':
				$this->_delete = null;
				$this->_type = null;
				break;

			case 'update':
				$this->_update = null;
				$this->_type = null;
				break;

			case 'insert':
				$this->_insert = null;
				$this->_type = null;
				break;

			case 'from':
				$this->_from = null;
				break;

			case 'join':
				$this->_join = null;
				break;

			case 'set':
				$this->_set = null;
				break;

			case 'where':
				$this->_where = null;
				break;

			case 'group':
				$this->_group = null;
				break;

			case 'having':
				$this->_having = null;
				break;

			case 'order':
				$this->_order = null;
				break;

			case 'showTables':
				$this->_show_tables = null;
				break;

			case 'drop':
				$this->_drop = null;
				break;

			case 'rename':
				$this->_rename = null;
				break;

			case 'insert_into':
				$this->_insert_into = null;
				$this->_fields = null;
				$this->_values = null;
				$this->_auto_increment_field = null;
				break;

			default:
				$this->_type = null;
				$this->_select = null;
				$this->_delete = null;
				$this->_udpate = null;
				$this->_insert = null;
				$this->_from = null;
				$this->_join = null;
				$this->_set = null;
				$this->_where = null;
				$this->_group = null;
				$this->_having = null;
				$this->_order = null;
				$this->_show_tables = null;
				$this->_drop = null;
				$this->_rename = null;
				$this->_insert_into = null;
				$this->_values = null;
				$this->_fields = null;
				$this->_auto_increment_field = null;
				break;
		}

		return $this;
	}


	/**
	 * @param	mixed	$columns	A string or an array of field names.
	 *
	 * @return	JDatabaseQuery	Returns this object to allow chaining.
	 * @since	11.1
	 */
	abstract public function select($columns);

	/**
	 * @param	string	$table	The name of the table to delete from.
	 *
	 * @return	JDatabaseQuery	Returns this object to allow chaining.
	 * @since	11.1
	 */
	abstract public function delete($table = null);

	/**
	 * @param	mixed	$tables	A string or array of table names.
	 *
	 * @return	JDatabaseQuery	Returns this object to allow chaining.
	 * @since	11.1
	 */
	abstract public function insert($tables);

	/**
	 * @param	mixed	$tables	A string or array of table names.
	 *
	 * @return	JDatabaseQuery	Returns this object to allow chaining.
	 * @since	11.1
	 */
	abstract public function update($tables);

	/**
	 * @param	mixed	A string or array of table names.
	 *
	 * @return	JDatabaseQuery	Returns this object to allow chaining.
	 * @since	11.1
	 */
	abstract public function from($tables);

	/**
	 * @param	string	$type
	 * @param	string	$conditions
	 *
	 * @return	JDatabaseQuery	Returns this object to allow chaining.
	 * @since	11.1
	 */
	abstract public function join($type, $conditions);

	/**
	 * @param	string	$conditions
	 *
	 * @return	JDatabaseQuery	Returns this object to allow chaining.
	 * @since	11.1
	 */
	abstract public function innerJoin($conditions);

	/**
	 * @param	string	$conditions
	 *
	 * @return	JDatabaseQuery	Returns this object to allow chaining.
	 * @since	11.1
	 */
	abstract public function outerJoin($conditions);

	/**
	 * @param	string	$conditions
	 *
	 * @return	JDatabaseQuery	Returns this object to allow chaining.
	 * @since	11.1
	 */
	abstract public function leftJoin($conditions);

	/**
	 * @param	string	$conditions
	 *
	 * @return	JDatabaseQuery	Returns this object to allow chaining.
	 * @since	11.1
	 */
	abstract public function rightJoin($conditions);

	/**
	 * @param	mixed	$conditions	A string or array of conditions.
	 * @param	string	$glue
	 *
	 * @return	JDatabaseQuery	Returns this object to allow chaining.
	 * @since	11.1
	 */
	abstract public function set($conditions, $glue=',');

	/**
	 * @param	mixed	$conditions	A string or array of where conditions.
	 * @param	string	$glue
	 *
	 * @return	JDatabaseQuery	Returns this object to allow chaining.
	 * @since	11.1
	 */
	abstract public function where($conditions, $glue='AND');

	/**
	 * @param	mixed	$columns	A string or array of ordering columns.
	 *
	 * @return	JDatabaseQuery	Returns this object to allow chaining.
	 * @since	11.1
	 */
	abstract public function group($columns);

	/**
	 * @param	mixed	$conditions	A string or array of columns.
	 * @param	string	$glue
	 *
	 * @return	JDatabaseQuery	Returns this object to allow chaining.
	 * @since	11.1
	 */
	abstract public function having($conditions, $glue='AND');

	/**
	 * @param	mixed	$columns	A string or array of ordering columns.
	 *
	 * @return	JDatabaseQuery	Returns this object to allow chaining.
	 * @since	11.1
	 */
	abstract public function order($columns);

	/**
	 * @param string $name  A string
	 *
	 * @return  Show table query syntax
	 * @since 11.1
	 */
	abstract public function showTables($name);

	/**
	 * @param string $table_name  A string
	 *
	 * @return  Drop if exists syntax
	 * @since 11.1
	 */
	abstract public function dropIfExists($table_name);

	/**
	 * @param string $table_name  A string
	 * @param object $db  Database object
	 * @param string $prefix  A string
	 * @param string $backup  A string
	 *
	 * @return  Rename table syntax
	 * @since 11.1
	 */
	abstract public function renameTable($table_name, &$db, $prefix = null, $backup = null);

	/**
	 * @param string $table_name  A string
	 * @param boolean $increment_field Provinding value for autoincrement primary key or not
	 * @return  JDatabaseQuery  Returns this object to allow chaining.
	 * @since 11.1
	 */
	abstract public function insertInto($table_name, $increment_field=false);

	/**
	 * @param string $fields  A string
	 *
	 * @since 11.1
	 */
	abstract public function fields($fields);

	/**
	 * @param string $values  A string
	 *
	 * @since 11.1
	 */
	abstract public function values($values);

	/**
	 * @param string $query A string
	 *
	 * @return The auto increment syntax
	 * @since 11.1
	 */
	abstract public function auto_increment($query);

	/**
	 * @param $field A string
	 *
	 * @return  JDatabaseQuery  Returns this object to allow chaining.
	 * @since 11.1
	 */
	abstract public function castToChar($field);

	/**
	 * @param $field A string
	 *
	 * @return  JDatabaseQuery  Returns this object to allow chaining.
	 * @since 11.1
	 */
	abstract public function charLength($field);

	/**
	 * @param array $fields
	 *
	 * @param string separator
	 * @return  String concantenaation of all the fields
	 * @since 11.1
	 */
	abstract public function concat($fields, $separator = null);

	/**
	 * @param string $field
	 *
	 * @param string separator
	 * @return  Length function for the field
	 * @since 11.1
	 */
	abstract public function length($field);

	/**
	 *
	 * @return  NOW function
	 * @since 11.1
	 */
	abstract public function now();

	/**
	 * Method to lock the database table for writing.
	 *
	 * @return	boolean	True on success.
	 * @since	11.1
	 */
	abstract public function lock($table_name, &$db);
	/**
	 * Method to unlock the database table for writing.
	 *
	 * @return	boolean	True on success.
	 * @since	11.1
	 */
	abstract public function unlock(&$db);

	/**
	 * Magic function to convert the query to a string.
	 *
	 * @return	string	The completed query.
	 * @since	11.1
	 */
	public function __toString()
	{
		$query = '';

		switch ($this->_type)
		{
			case 'select':
				$query .= (string) $this->_select;
				$query .= (string) $this->_from;
				if ($this->_join) {
					// special case for joins
					foreach ($this->_join as $join) {
						$query .= (string) $join;
					}
				}

				if ($this->_where) {
					$query .= (string) $this->_where;
				}

				if ($this->_group) {
					$query .= (string) $this->_group;
				}

				if ($this->_having) {
					$query .= (string) $this->_having;
				}

				if ($this->_order) {
					$query .= (string) $this->_order;
				}

				break;

			case 'delete':
				$query .= (string) $this->_delete;
				$query .= (string) $this->_from;

				if ($this->_join) {
					// special case for joins
					foreach ($this->_join as $join) {
						$query .= (string) $join;
					}
				}

				if ($this->_where) {
					$query .= (string) $this->_where;
				}

				break;

			case 'update':
				$query .= (string) $this->_update;
				$query .= (string) $this->_set;

				if ($this->_where) {
					$query .= (string) $this->_where;
				}

				break;

			case 'insert':
				$query .= (string) $this->_insert;
				$query .= (string) $this->_set;

				if ($this->_where) {
					$query .= (string) $this->_where;
				}

				break;

			case 'showTables':
				$query .= (string) $this->_show_tables;

				break;

			case 'drop':
				$query .= (string) $this->_drop;

				break;

			case 'rename':
				$query .= (string) $this->_rename;

				break;

			case 'insert_into':
				$query .= (string) $this->_insert_into;

				if ($this->_fields) {
					$query .= (string) $this->_fields;
					$query .= ')';
				}

				$query .= (string) $this->_values;
				$query .= ')';

				if($this->_auto_increment_field)
				$query = $this->auto_increment($query);

				break;
		}

		return $query;
	}
}