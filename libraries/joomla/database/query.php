<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Class JQueryElement
 * 
 * @package 	Joomla.Framework
 * @subpackage 	Database
 * @since 1.1
 */
class JQueryElement {
	/** @var string The name of the element */
	var $_name = null;
	/** @var array An array of elements */
	var $_elements = null;
	/** @var string Glue piece */
	var $_glue = null;

	/**
	 * Constructor
	 * @param string The name of the element
	 * @param mixed String or array
	 * @param string The glue for elements
	 */
	function JQueryElement( $name, $elements, $glue=',' ) {
		$this->_elements = array();
		$this->_name = $name;
		$this->append( $elements );
		$this->_glue = $glue;
	}

	/**
	 * Appends element parts to the internal list
	 * @param mixed String or array
	 */
	function append( $elements ) {
		if (is_array( $elements )) {
			$this->_elements = array_merge( $this->_elements, $elements );
		} else {
			$this->_elements = array_merge( $this->_elements, array( $elements ) );
		}
	}

	/**
	 * Render the query element
	 * @return string
	 */
	function toString() {
		return "\n{$this->_name} " . implode( $this->_glue, $this->_elements );
	}
}

/**
 * Class QueryBuilder
 * 
 * @package 	Joomla.Framework
 * @subpackage 	Database
 * @since 1.1
 */
class JQuery 
{
	/** @var string The query type */
	var $_type = '';
	/** @var object The select element */
	var $_select = null;
	/** @var object The from element */
	var $_from = null;
	/** @var object The join element */
	var $_join = null;
	/** @var object The where element */
	var $_where = null;
	/** @var object The where element */
	var $_group = null;
	/** @var object The where element */
	var $_order = null;

	/**
	 * Constructor
	 */
	function JQuery() {
	}

	/**
	 * @param mixed A string or an array of field names
	 */
	function select( $columns ) {
		$this->_type = 'select';
		if (is_null( $this->_select )) {
			$this->_select = new JQueryElement( 'SELECT', $columns );
		} else {
			$this->_select->append( $columns );
		}
	}

	/**
	 * @param mixed A string or array of table names
	 */
	function from( $tables ) {
		if (is_null( $this->_from )) {
			$this->_from = new JQueryElement( 'FROM', $tables );
		} else {
			$this->_from->append( $tables );
		}
	}

	/**
	 * @param mixed A string or array of join conditions
	 */
	function join( $type, $conditions ) {
		if (is_null( $this->_join )) {
			$this->_join = array();
		}
		$this->_join[] = new JQueryElement( strtoupper( $type ) . ' JOIN', $conditions );
	}

	/**
	 * @param mixed A string or array of where conditions
	 */
	function where( $conditions, $glue='AND' ) {
		if (is_null( $this->_where )) {
			$glue = strtoupper( $glue );
			$this->_where = new JQueryElement(  'WHERE', $conditions, "\n\t$glue " );
		} else {
			$this->_where->append( $conditions );
		}
	}

	/**
	 * @param mixed A string or array of ordering columns
	 */
	function group( $columns ) {
		if (is_null( $this->_group )) {
			$this->_group = new JQueryElement( 'GROUP BY', $columns );
		} else {
			$this->_group->append( $columns );
		}
	}

	/**
	 * @param mixed A string or array of ordering columns
	 */
	function order( $columns ) {
		if (is_null( $this->_order )) {
			$this->_order = new JQueryElement( 'ORDER BY', $columns );
		} else {
			$this->_order->append( $columns );
		}
	}

	/**
	 * @return string The completed query
	 */
	function toString() {
		$query = '';

		switch ($this->_type) {
			case 'select':
				$query .= $this->_select->toString();
				$query .= $this->_from->toString();
				if ($this->_join) {
					// special case for joins
					foreach ($this->_join as $join) {
						$query .= $join->toString();
					}
				}
				if ($this->_where) {
					$query .= $this->_where->toString();
				}
				if ($this->_group) {
					$query .= $this->_group->toString();
				}
				if ($this->_order) {
					$query .= $this->_order->toString();
				}
				break;
		}

		return $query;
	}
}
?>