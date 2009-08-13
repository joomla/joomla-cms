<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Query Element Class.
 *
 * @package		Joomla.Framework
 * @subpackage	Database
 * @since		1.6
 */
class JQueryElement
{
	/** @var string The name of the element */
	protected $_name = null;
	
	/** @var array An array of elements */
	protected $_elements = null;
	
	/** @var string Glue piece */
	protected $_glue = null;

	/**
	 * Constructor.
	 * 
	 * @param	string	The name of the element.
	 * @param	mixed	String or array.
	 * @param	string	The glue for elements.
	 */
	public function __construct($name, $elements, $glue=',')
	{
		$this->_elements	= array();
		$this->_name		= $name;		
		$this->_glue		= $glue;
		
		$this->append($elements);
	}
	
	public function __toString()
	{
		return PHP_EOL.$this->_name.' '.implode($this->_glue, $this->_elements);
	}
	
	/**
	 * Appends element parts to the internal list.
	 * 
	 * @param	mixed	String or array.
	 */
	public function append($elements)
	{
		if (is_array($elements)) {
			$this->_elements = array_unique(array_merge($this->_elements, $elements));
		} else {
			$this->_elements = array_unique(array_merge($this->_elements, array($elements)));
		}
	}
}

/**
 * Query Building Class.
 *
 * @package		Joomla.Framework
 * @subpackage	Database
 * @since		1.6
 */
class JQuery
{
	/** @var string The query type */
	protected $_type = '';
	
	/** @var object The select element */
	protected $_select = null;
	
	/** @var object The from element */
	protected $_from = null;
	
	/** @var object The join element */
	protected $_join = null;
	
	/** @var object The where element */
	protected $_where = null;
	
	/** @var object The where element */
	protected $_group = null;
	
	/** @var object The where element */
	protected $_having = null;
	
	/** @var object The where element */
	protected $_order = null;

	/**
	 * @param	mixed	A string or an array of field names
	 */
	public function select($columns)
	{
		$this->_type = 'select';
		if (is_null($this->_select)) {
			$this->_select = new JQueryElement('SELECT', $columns);
		} else {
			$this->_select->append($columns);
		}

		return $this;
	}

	/**
	 * @param	mixed	A string or array of table names
	 */
	public function from($tables)
	{
		if (is_null($this->_from)) {
			$this->_from = new JQueryElement('FROM', $tables);
		} else {
			$this->_from->append($tables);
		}

		return $this;
	}

	/**
	 * @param	string
	 * @param	string
	 */
	public function join($type, $conditions)
	{
		if (is_null($this->_join)) {
			$this->_join = array();
		}
		$this->_join[] = new JQueryElement(strtoupper($type) . ' JOIN', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 */
	public function &innerJoin($conditions)
	{
		$this->join('INNER', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 */
	public function &outerJoin($conditions)
	{
		$this->join('OUTER', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 */
	public function &leftJoin($conditions)
	{
		$this->join('LEFT', $conditions);

		return $this;
	}

	/**
	 * @param	string
	 */
	public function &rightJoin($conditions)
	{
		$this->join('RIGHT', $conditions);

		return $this;
	}

	/**
	 * @param	mixed	A string or array of where conditions
	 * @param	string
	 */
	public function where($conditions, $glue='AND')
	{
		if (is_null($this->_where)) {
			$glue = strtoupper($glue);
			$this->_where = new JQueryElement('WHERE', $conditions, "\n\t$glue ");
		} else {
			$this->_where->append($conditions);
		}

		return $this;
	}

	/**
	 * @param	mixed	A string or array of ordering columns
	 */
	public function group($columns)
	{
		if (is_null($this->_group)) {
			$this->_group = new JQueryElement('GROUP BY', $columns);
		} else {
			$this->_group->append($columns);
		}

		return $this;
	}

	/**
	 * @param	mixed	A string or array of ordering columns
	 */
	public function having($columns)
	{
		if (is_null($this->_having)) {
			$this->_having = new JQueryElement('HAVING', $columns);
		} else {
			$this->_having->append($columns);
		}

		return $this;
	}

	/**
	 * @param	mixed	A string or array of ordering columns
	 */
	public function order($columns)
	{
		if (is_null($this->_order)) {
			$this->_order = new JQueryElement('ORDER BY', $columns);
		} else {
			$this->_order->append($columns);
		}

		return $this;
	}

	/**
	 * @return	string	The completed query
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
		}

		return $query;
	}
}