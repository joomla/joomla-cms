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
 * Query Element Class.
 *
 * @package		Joomla.Platform
 * @subpackage	Database
 * @since		11.1
 */
class JDatabaseQueryElementMySQLi extends JDatabaseQueryElement
{
	/**
	 * Constructor.
	 *
	 * @param	string	$name		The name of the element.
	 * @param	mixed	$elements	String or array.
	 * @param	string	$glue		The glue for elements.
	 *
	 * @return	JDatabaseQueryElementMySQL
	 * @since	11.1
	 */
	public function __construct($name, $elements, $glue = ',')
	{
		parent::__construct($name, $elements, $glue);
	}
}

/**
 * Query Building Class.
 *
 * @package		Joomla.Platform
 * @subpackage	Database
 * @since		11.1
 */
class JDatabaseQueryMySQLi extends JDatabaseQuery
{
	/**
	 * @param	mixed	$columns	A string or an array of field names.
	 *
	 * @return	JDatabaseQueryMySQLi Returns this object to allow chaining.
	 * @since	11.1
	 */
	public function select($columns)
	{
		$this->_type = 'select';

		if (is_null($this->_select)) {
			$this->_select = new JDatabaseQueryElementMySQLi('SELECT', $columns);
		}
		else {
			$this->_select->append($columns);
		}

		return $this;
	}

	/**
	 * @param	string	$table	The name of the table to delete from.
	 *
	 * @return	JDatabaseQueryMySQLi Returns this object to allow chaining.
	 * @since	11.1
	 */
	public function delete($table = null)
	{
		$this->_type	= 'delete';
		$this->_delete	= new JDatabaseQueryElementMySQLi('DELETE', null);

		if (!empty($table)) {
			$this->from($table);
		}

		return $this;
	}

	/**
	 * @param	mixed	$tables	A string or array of table names.
	 *
	 * @return	JDatabaseQueryMySQLi 	Returns this object to allow chaining.
	 * @since	11.1
	 */
	public function insert($tables)
	{
		$this->_type	= 'insert';
		$this->_insert	= new JDatabaseQueryElementMySQLi('INSERT INTO', $tables);

		return $this;
	}

	/**
	 * @param	mixed	$tables	A string or array of table names.
	 *
	 * @return	JDatabaseQueryMySQLi 	Returns this object to allow chaining.
	 * @since	11.1
	 */
	public function update($tables)
	{
		$this->_type = 'update';
		$this->_update = new JDatabaseQueryElementMySQLi('UPDATE', $tables);

		return $this;
	}

	/**
	 * @param	mixed	A string or array of table names.
	 *
	 * @return	JDatabaseQueryMySQLi 	Returns this object to allow chaining.
	 * @since	11.1
	 */
	public function from($tables)
	{
		if (is_null($this->_from)) {
			$this->_from = new JDatabaseQueryElementMySQLi('FROM', $tables);
		}
		else {
			$this->_from->append($tables);
		}

		return $this;
	}

	/**
	 * @param	string	$type
	 * @param	string	$conditions
	 *
	 * @return	JDatabaseQueryMySQLi 	Returns this object to allow chaining.
	 * @since	11.1
	 */
	public function join($type, $conditions)
	{
		if (is_null($this->_join)) {
			$this->_join = array();
		}
		$this->_join[] = new JDatabaseQueryElementMySQLi(strtoupper($type) . ' JOIN', $conditions);

		return $this;
	}

	/**
	 * @param	string	$conditions
	 *
	 * @return	JDatabaseQueryMySQLi 	Returns this object to allow chaining.
	 * @since	11.1
	 */
	public function innerJoin($conditions)
	{
		$this->join('INNER', $conditions);

		return $this;
	}

	/**
	 * @param	string	$conditions
	 *
	 * @return	JDatabaseQueryMySQLi 	Returns this object to allow chaining.
	 * @since	11.1
	 */
	public function outerJoin($conditions)
	{
		$this->join('OUTER', $conditions);

		return $this;
	}

	/**
	 * @param	string	$conditions
	 *
	 * @return	JDatabaseQueryMySQLi 	Returns this object to allow chaining.
	 * @since	11.1
	 */
	public function leftJoin($conditions)
	{
		$this->join('LEFT', $conditions);

		return $this;
	}

	/**
	 * @param	string	$conditions
	 *
	 * @return	JDatabaseQueryMySQLi 	Returns this object to allow chaining.
	 * @since	11.1
	 */
	public function rightJoin($conditions)
	{
		$this->join('RIGHT', $conditions);

		return $this;
	}

	/**
	 * @param	mixed	$conditions	A string or array of conditions.
	 * @param	string	$glue
	 *
	 * @return	JDatabaseQueryMySQLi 	Returns this object to allow chaining.
	 * @since	11.1
	 */
	public function set($conditions, $glue=',')
	{
		if (is_null($this->_set)) {
			$glue = strtoupper($glue);
			$this->_set = new JDatabaseQueryElementMySQLi('SET', $conditions, "\n\t$glue ");
		}
		else {
			$this->_set->append($conditions);
		}

		return $this;
	}

	/**
	 * @param	mixed	$conditions	A string or array of where conditions.
	 * @param	string	$glue
	 *
	 * @return	JDatabaseQueryMySQLi 	Returns this object to allow chaining.
	 * @since	11.1
	 */
	public function where($conditions, $glue='AND')
	{
		if (is_null($this->_where)) {
			$glue = strtoupper($glue);
			$this->_where = new JDatabaseQueryElementMySQLi('WHERE', $conditions, " $glue ");
		}
		else {
			$this->_where->append($conditions);
		}

		return $this;
	}

	/**
	 * @param	mixed	$columns	A string or array of ordering columns.
	 *
	 * @return	JDatabaseQueryMySQLi 	Returns this object to allow chaining.
	 * @since	11.1
	 */
	public function group($columns)
	{
		if (is_null($this->_group)) {
			$this->_group = new JDatabaseQueryElementMySQLi('GROUP BY', $columns);
		}
		else {
			$this->_group->append($columns);
		}

		return $this;
	}

	/**
	 * @param	mixed	$conditions	A string or array of columns.
	 * @param	string	$glue
	 *
	 * @return	JDatabaseQueryMySQLi 	Returns this object to allow chaining.
	 * @since	11.1
	 */
	public function having($conditions, $glue='AND')
	{
		if (is_null($this->_having)) {
			$glue = strtoupper($glue);
			$this->_having = new JDatabaseQueryElementMySQLi('HAVING', $conditions, " $glue ");
		}
		else {
			$this->_having->append($conditions);
		}

		return $this;
	}

	/**
	 * @param	mixed	$columns	A string or array of ordering columns.
	 *
	 * @return	JDatabaseQueryMySQLi 	Returns this object to allow chaining.
	 * @since	11.1
	 */
	public function order($columns)
	{
		if (is_null($this->_order)) {
			$this->_order = new JDatabaseQueryElementMySQLi('ORDER BY', $columns);
		}
		else {
			$this->_order->append($columns);
		}

		return $this;
	}

   /**
   * @param string $name  A string
   *
   * @return  Show table query syntax
   * @since 11.1
   */
   function showTables($name)
   {
      $this->_type = 'showTables';

      $this->_show_tables = new JDatabaseQueryElementMySQLi('SHOW TABLES FROM', $name);

      return $this;
   }

   /**
   * @param string $table_name  A string
   *
   * @return  Drop if exists syntax
   * @since 11.1
   */
   function dropIfExists($table_name)
   {
     $this->_type = 'drop';

      if (is_null($this->_drop)) {
        $this->_drop = new JDatabaseQueryElementMySQLi('DROP TABLE IF EXISTS', $table_name);
      }
      else {
        $this->_drop->append($table_name);
      }

      return $this;
   }

   /**
	 * @param string $table_name  A string
	 * @param object $db  Database object
	 * @param string $prefix  A string
	 * @param string $backup  A string
	 *
	 * @return  Rename table syntax
	 * @since 11.1
	 */
   function renameTable($table_name, &$db, $prefix = null, $backup = null)
   {
     $this->_type = 'rename';

      if (is_null($this->_rename)) {
        $this->_rename = new JDatabaseQueryElementMySQLi('RENAME TABLE', $table_name, ' TO ');
      }
      else {
        $this->_rename->append($table_name);
      }

      return $this;
   }

   /**
   * @param string $table_name  A string
   * @param boolean $increment_field Provinding value for autoincrement primary key or not
   * @return  JDatabaseQueryMySQLi   Returns this object to allow chaining.
   * @since 11.1
   */
   function insertInto($table_name, $increment_field=false)
   {
     $this->_type = 'insert_into';
     $this->_insert_into = new JDatabaseQueryElementMySQLi('INSERT INTO', $table_name);

      return $this;
   }

   /**
   * @param string $fields A string
   *
   * @return  JDatabaseQueryMySQLi   Returns this object to allow chaining.
   * @since 11.1
   */
   function fields($fields)
   {
     if (is_null($this->_fields)) {
      $this->_fields = new JDatabaseQueryElementMySQLi('(', $fields);
    }
    else {
      $this->_fields->append($fields);
    }

    return $this;
   }

   /**
   * @param string $values  A string
   *
   * @return  JDatabaseQueryMySQLi   Returns this object to allow chaining.
   * @since 11.1
   */
   function values($values)
   {
     if (is_null($this->_values)) {
      $this->_values = new JDatabaseQueryElementMySQLi('VALUES (', $values);
    }
    else {
      $this->_values->append($values);
    }

    return $this;
   }

   /**
   * @param string $query A string
   *
   * @return  JDatabaseQueryMySQLi   Returns this object to allow chaining.
   * @since 11.1
   */
   function auto_increment($query)
   {
     return $query;
   }


   /**
   * @param $field A string
   *
   * @return  JDatabaseQueryMySQLi   Returns this object to allow chaining.
   * @since 11.1
   */
   function castToChar($field)
   {
     return $field;
   }

   /**
   * @param $field A string
   *
   * @return  JDatabaseQueryMySQLi   Returns this object to allow chaining.
   * @since 11.1
   */
   function charLength($field)
   {
     return 'CHAR_LENGTH('.$field.')';
   }

  /**
   * @param array $fields
   *
   * @param string separator
   * @return  String concantenaation of all the fields
   * @since 11.1
   */
   function concat($fields, $separator = null)
   {
     if($separator)
     {
       $concat_string = "CONCAT_WS('".$separator."'";
       foreach($fields as $field)
       {
         $concat_string .= ', '.$field;
       }
       return $concat_string.')';
     }else{
       return 'CONCAT('.implode(',', $fields).')';
     }
   }

   /**
   * @param string $field
   *
   * @param string separator
   * @return  Length function for the field
   * @since 11.1
   */
   function length($field)
   {
     return 'LENGTH('.$field.')';
   }

   /**
   *
   * @return  NOW function
   * @since 11.1
   */
   function now()
   {
   	 return 'NOW()';
   }

   /**
   * Method to lock the database table for writing.
   *
	* @return	boolean	True on success.
	 * @since	11.1
	 */
	public function lock($table_name, &$db)
	{
		// Lock the table for writing.
		$db->setQuery('LOCK TABLES `'.$table_name.'` WRITE');
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum()) {
			//$this->setError($db->getErrorMsg());

			return false;
		}

		return true;
	}

	/**
	 * Method to unlock the database table for writing.
	 *
	 * @return	boolean	True on success.
	 * @since	11.1
	 */
	public function unlock(&$db)
	{
		// Unlock the table.
		$db->setQuery('UNLOCK TABLES');
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum()) {
			//$this->setError($db->getErrorMsg());

			return false;
		}

		return true;
	}
}