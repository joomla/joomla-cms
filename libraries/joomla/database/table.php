<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Abstract Table class
 *
 * Parent classes to all tables.
 *
 * @abstract
 * @author		Andrew Eddie <eddieajau@users.sourceforge.net>
 * @package 	Joomla.Framework
 * @subpackage 	Model
 * @since		1.0
 * @tutorial	Joomla.Framework/jtable.cls
 */
class JTable extends JObject
{
	/**
	 * Name of the table in the db schema relating to child class
	 *
	 * @var string
	 * @access protected
	 */
	var $_tbl		= '';

	/**
	 * Name of the primary key field in the table
	 *
	 * @var string
	 * @access protected
	 */
	var $_tbl_key	= '';

	/**
	 * Error message
	 *
	 * @var string
	 * @access protected
	 */
	var $_error		= '';

	/**
	 * Error number
	 *
	 * @var string
	 * @access protected
	 */
	var $_errorNum = 0;

	/**
	 * Database connector
	 *
	 * @var JDatabase
	 * @access protected
	 */
	var $_db		= null;

	/**
	* Object constructor to set table and key field
	*
	* Can be overloaded/supplemented by the child class
	*
	* @access protected
	* @param string $table name of the table in the db schema relating to child class
	* @param string $key name of the primary key field in the table
	*/
	function __construct( $table, $key, &$db )
	{
		$this->_tbl		= $table;
		$this->_tbl_key	= $key;
		$this->_db		=& $db;
	}

	/**
	 * Returns a reference to the a Model object, always creating it
	 *
	 * @param type $type The table type to instantiate
	 * @param object A JDatabase object
	 * @param string A prefix for the table class name
	 * @return database A database object
	 * @since 1.5
	*/
	function &getInstance( $type, &$db, $prefix='JTable' )
	{
		$adapter = $prefix.ucfirst($type);
		if (!class_exists( $adapter ))
		{
			$dirs = JTable::addTableDir();
			foreach( $dirs as $dir )
			{
				$tableFile = $dir.DS.strtolower($type).'.php';
				if (@include_once $tableFile)
				{
					break;
				}
			}
		}
		if (!class_exists( $adapter ))
		{
			return JError::raiseError(20, JText::sprintf('Database Table object [%s] does not exist', $prefix.$type));
		}
		else
		{
			$m = new $adapter($db);
		}
		return $m;
	}

	/**
	 * Get the internal database object
	 * 
	 * @return object A JDatabase based object
	 */
	function &getDBO() 
	{
		return $this->_db;
	}

	/**
	 * Gets the internal table name for the object
	 * 
	 * @return string
	 * @since 1.5
	 */
	function getTableName()
	{
		return $this->_tbl;
	}

	/**
	 * Gets the internal primary key name
	 * 
	 * @return string
	 * @since 1.5
	 */
	function getKeyName()
	{
		return $this->_tbl_key;
	}


	/**
	 * Returns the error message
	 *
	 * @return string
	 */
	function getError()
	{
		return $this->_error;
	}

	/**
	 * Returns the error number
	 *
	 * @return int The error number
	 */
	function getErrorNum()
	{
		return $this->_errorNum;
	}

	/**
	* Binds a named array/hash to this object
	*
	* can be overloaded/supplemented by the child class
	*
	* @acces public
	* @param $array  mixed Either and associative array or another object
	* @param $ignore string	Space separated list of fields not to bind
	* @return	boolean
	*/
	function bind( $from, $ignore='' )
	{
		if (!is_array( $from ) && !is_object( $from ))
		{
			$this->setError(strtolower(get_class( $this ))."::bind failed.");
			$this->setErrorNum(20);
			return false;
		}
		
		$fromArray = is_array( $from );
		$fromObject = is_object( $from );

		if ($fromArray || $fromObject)
		{
			foreach (get_object_vars($this) as $k => $v)
			{
				// only bind to public variables
				if( substr( $k, 0, 1 ) != '_' )
				{
					// internal attributes of an object are ignored
					if (strpos( $ignore, $k) === false)
					{
						$ak = $k;
				
						if ($fromArray && isset( $from[$ak] )) {
							$this->$k = $from[$ak];
						} else if ($fromObject && isset( $from->$ak )) {
							$this->$k = $from->$ak;
						}
					}
				}
			}
		}
		else
		{
			return false;
		}

		return true;
	}
	
	/**
	* Binds an array/hash to this object
	*
	* @access public
	* @param int $oid optional argument, if not specifed then the value of current key is used
	* @return boolean True if successful
	*/
	function load( $oid=null )
	{
		$k = $this->_tbl_key;

		if ($oid !== null)
		{
			$this->$k = $oid;
		}

		$oid = $this->$k;

		if ($oid === null)
		{
			return false;
		}

		$class_vars = get_class_vars(get_class($this));
		foreach ($class_vars as $name => $value)
		{
			if (($name != $k) and ($name != "_db") and ($name != "_tbl") and ($name != "_tbl_key"))
			{
				$this->$name = $value;
			}
		}

		$db =& $this->getDBO();

		$query = "SELECT *"
		. "\n FROM $this->_tbl"
		. "\n WHERE $this->_tbl_key = '$oid'"
		;
		$db->setQuery( $query );

		if ($result = $db->loadAssoc( ))
		{
			$this->bind($result);
			return true;
		}
		else
		{
			$this->setError( $db->getErrorMsg() );
			return false;
		}
	}

	/**
	* Generic check method
	*
	* Can be overloaded/supplemented by the child class
	*
	* @access public
	* @return boolean True if the object is ok
	*/
	function check()
	{
		return true;
	}

	/**
	* Inserts a new row if id is zero or updates an existing row in the database table
	*
	* Can be overloaded/supplemented by the child class
	*
	* @access public
	* @param boolean If false, null object variables are not updated
	* @return null|string null if successful otherwise returns and error message
	*/
	function store( $updateNulls=false )
	{
		$k = $this->_tbl_key;

		if( $this->$k)
		{
			$ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );
		}
		else
		{
			$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
		}
		if( !$ret )
		{
			$this->setError(strtolower(get_class( $this ))."::store failed <br />" . $this->_db->getErrorMsg());
			$this->setErrorNum($this->_db->getErrorNum());
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Description
	 *
	 * @access public
	 * @param
	 * @param
	 */
	function move( $dirn, $where='' )
	{
		$k = $this->_tbl_key;

		$sql = "SELECT $this->_tbl_key, ordering FROM $this->_tbl";

		if ($dirn < 0)
		{
			$sql .= "\n WHERE ordering < $this->ordering";
			$sql .= ($where ? "\n	AND $where" : '');
			$sql .= "\n ORDER BY ordering DESC";
		}
		else if ($dirn > 0)
		{
			$sql .= "\n WHERE ordering > $this->ordering";
			$sql .= ($where ? "\n	AND $where" : '');
			$sql .= "\n ORDER BY ordering";
		}
		else
		{
			$sql .= "\nWHERE ordering = $this->ordering";
			$sql .= ($where ? "\n AND $where" : '');
			$sql .= "\n ORDER BY ordering";
		}

		$this->_db->setQuery( $sql, 0, 1 );


		$row = null;
		$row = $this->_db->loadObject();
		if (isset($row))
		{
			$query = "UPDATE $this->_tbl"
			. "\n SET ordering = '$row->ordering'"
			. "\n WHERE $this->_tbl_key = '". $this->$k ."'"
			;
			$this->_db->setQuery( $query );

			if (!$this->_db->query())
			{
				$err = $this->_db->getErrorMsg();
				die( $err );
			}

			$query = "UPDATE $this->_tbl"
			. "\n SET ordering = '$this->ordering'"
			. "\n WHERE $this->_tbl_key = '". $row->$k. "'"
			;
			$this->_db->setQuery( $query );

			if (!$this->_db->query())
			{
				$err = $this->_db->getErrorMsg();
				die( $err );
			}

			$this->ordering = $row->ordering;
		}
		else
		{
			$query = "UPDATE $this->_tbl"
			. "\n SET ordering = '$this->ordering'"
			. "\n WHERE $this->_tbl_key = '". $this->$k ."'"
			;
			$this->_db->setQuery( $query );

			if (!$this->_db->query())
			{
				$err = $this->_db->getErrorMsg();
				die( $err );
			}
		}
	}

	/**
	* Compacts the ordering sequence of the selected records
	*
	* @access public
	* @param string Additional where query to limit ordering to a particular subset of records
	*/
	function reorder( $where='' )
	{
		$k = $this->_tbl_key;

		if (!array_key_exists( 'ordering', get_class_vars( strtolower(get_class( $this )) ) ))
		{
			$this->setError("WARNING: ".strtolower(get_class( $this ))." does not support ordering.");
			$this->setErrorNum(21);
			return false;
		}

		if ($this->_tbl == '#__content_frontpage')
		{
			$order2 = ", content_id DESC";
		}
		else
		{
			$order2 = "";
		}

		$query = "SELECT $this->_tbl_key, ordering"
		. "\n FROM $this->_tbl"
		. ( $where ? "\n WHERE $where" : '' )
		. "\n ORDER BY ordering$order2 "
		;
		$this->_db->setQuery( $query );
		if (!($orders = $this->_db->loadObjectList()))
		{
			$this->setError($this->_db->getErrorMsg());
			$this->setErrorNum($this->_db->getErrorNum());
			return false;
		}
		// first pass, compact the ordering numbers
		for ($i=0, $n=count( $orders ); $i < $n; $i++)
		{
			if ($orders[$i]->ordering >= 0)
			{
				$orders[$i]->ordering = $i+1;
			}
		}

		$shift = 0;
		$n=count( $orders );
		for ($i=0; $i < $n; $i++)
		{
			//echo "i=$i id=".$orders[$i]->$k." order=".$orders[$i]->ordering;
			if ($orders[$i]->$k == $this->$k)
			{
				// place 'this' record in the desired location
				$orders[$i]->ordering = min( $this->ordering, $n );
				$shift = 1;
			}
			else if ($orders[$i]->ordering >= $this->ordering && $this->ordering > 0)
			{
				$orders[$i]->ordering++;
			}
		}
	//echo '<pre>';print_r($orders);echo '</pre>';
		// compact once more until I can find a better algorithm
		for ($i=0, $n=count( $orders ); $i < $n; $i++)
		{
			if ($orders[$i]->ordering >= 0)
			{
				$orders[$i]->ordering = $i+1;
				$query = "UPDATE $this->_tbl"
				. "\n SET ordering = '". $orders[$i]->ordering ."'"
				. "\n WHERE $k = '". $orders[$i]->$k ."'"
				;
				$this->_db->setQuery( $query);
				$this->_db->query();
	//echo '<br />'.$this->_db->getQuery();
			}
		}

		// if we didn't reorder the current record, make it last
		if ($shift == 0)
		{
			$order = $n+1;
			$query = "UPDATE $this->_tbl"
			. "\n SET ordering = '$order'"
			. "\n WHERE $k = '". $this->$k ."'"
			;
			$this->_db->setQuery( $query );
			$this->_db->query();
	//echo '<br />'.$this->_db->getQuery();
		}
		return true;
	}

	/**
	* Generic check for whether dependancies exist for this object in the db schema
	*
	* can be overloaded/supplemented by the child class
	*
	* @access public
	* @param string $msg Error message returned
	* @param int Optional key index
	* @param array Optional array to compiles standard joins: format [label=>'Label',name=>'table name',idfield=>'field',joinfield=>'field']
	* @return true|false
	*/
	function canDelete( $oid=null, $joins=null )
	{
		$k = $this->_tbl_key;
		if ($oid)
		{
			$this->$k = intval( $oid );
		}
		if (is_array( $joins ))
		{
			$select = "$k";
			$join = "";
			foreach( $joins as $table )
			{
				$select .= ",\n COUNT(DISTINCT {$table['idfield']}) AS {$table['idfield']}";
				$join .= "\n LEFT JOIN {$table['name']} ON {$table['joinfield']} = $k";
			}

			$query = "SELECT $select"
			. "\n FROM $this->_tbl"
			. $join
			. "\n WHERE $k = ". $this->$k .""
			. "\n GROUP BY $k"
			;
			$this->_db->setQuery( $query );

			if ($obj = $this->_db->loadObject())
			{
				$this->setError($this->_db->getErrorMsg());
				$this->setErrorNum($db->getErrorNum());
				return false;
			}
			$msg = array();
			foreach( $joins as $table )
			{
				$k = $table['idfield'];
				if ($obj->$k)
				{
					$msg[] = JText::_( $table['label'] );
				}
			}

			if (count( $msg ))
			{
				$this->setError("noDeleteRecord" . ": " . implode( ', ', $msg ));
				$this->setErrorNum(22);
				return false;
			}
			else
			{
				return true;
			}
		}

		return true;
	}

	/**
	* Default delete method
	*
	* can be overloaded/supplemented by the child class
	*
	* @access public
	* @return true if successful otherwise returns and error message
	*/
	function delete( $oid=null )
	{
		//if (!$this->canDelete( $msg ))
		//{
		//	return $msg;
		//}

		$k = $this->_tbl_key;
		if ($oid)
		{
			$this->$k = intval( $oid );
		}

		$query = "DELETE FROM $this->_tbl"
		. "\n WHERE $this->_tbl_key = '". $this->$k ."'"
		;
		$this->_db->setQuery( $query );

		if ($this->_db->query())
		{
			return true;
		}
		else
		{
			$this->setError($this->_db->getErrorMsg());
			$this->setErrorNum($db->getErrorNum());
			return false;
		}
	}

	/**
	 * Description
	 *
	 * @access public
	 * @param
	 * @param
	 */
	function checkout( $who, $oid=null )
	{
		if (!isset($this->checked_out))
		{
			$this->setError("WARNING: ".strtolower(get_class( $this ))." does not support checkouts.");
			$this->setErrorNum(23);
			return false;
		}
		$k = $this->_tbl_key;
		if ($oid !== null)
		{
			$this->$k = $oid;
		}
		$time = date( 'Y-m-d H:i:s' );
		if (intval( $who ))
		{
			// new way of storing editor, by id
			$query = "UPDATE $this->_tbl"
			. "\n SET checked_out = $who, checked_out_time = '$time'"
			. "\n WHERE $this->_tbl_key = '". $this->$k ."'"
			;
			$this->_db->setQuery( $query );

			$this->checked_out = $who;
			$this->checked_out_time = $time;
		}
		else
		{
			// old way of storing editor, by name
			$query = "UPDATE $this->_tbl"
			. "\n SET checked_out = 1, checked_out_time = '$time', editor = '".$who."' "
			. "\n WHERE $this->_tbl_key = '". $this->$k ."'"
			;
			$this->_db->setQuery( $query );

			$this->checked_out = 1;
			$this->checked_out_time = $time;
			$this->checked_out_editor = $who;
		}

		return $this->_db->query();
	}

	/**
	 * Description
	 *
	 * @access public
	 * @param
	 * @param
	 */
	function checkin( $oid=null )
	{
		if (!array_key_exists( 'checked_out', get_class_vars( strtolower(get_class( $this )) ) ))
		{
			//$this->_error = "WARNING: ".strtolower(get_class( $this ))." does not support checkin.";
			return true;
		}
		$k = $this->_tbl_key;

		if ($oid !== null)
		{
			$this->$k = $oid;
		}
		if ($this->$k == NULL)
		{
			return false;
		}

		$query = "UPDATE $this->_tbl"
		. "\n SET checked_out = 0, checked_out_time = '$this->_db->_nullDate'"
		. "\n WHERE $this->_tbl_key = '". $this->$k ."'"
		;
		$this->_db->setQuery( $query );

		$this->checked_out = 0;
		$this->checked_out_time = '';

		return $this->_db->query();
	}

	/**
	 * Description
	 *
	 * @access public
	 * @param
	 * @param
	 */
	function hit( $oid=null, $log=false )
	{
		$k = $this->_tbl_key;

		if ($oid !== null)
		{
			$this->$k = intval( $oid );
		}

		$query = "UPDATE $this->_tbl"
		. "\n SET hits = ( hits + 1 )"
		. "\n WHERE $this->_tbl_key = '$this->id'"
		;
		$this->_db->setQuery( $query );
		$this->_db->query();

		if ($log)
		{
			$now = date( 'Y-m-d' );
			$query = "SELECT hits"
			. "\n FROM #__core_log_items"
			. "\n WHERE time_stamp = '$now'"
			. "\n AND item_table = '$this->_tbl'"
			. "\n AND item_id = ". $this->$k .""
			;
			$this->_db->setQuery( $query );
			$hits = intval( $this->_db->loadResult() );
			if ($hits)
			{
				$query = "UPDATE #__core_log_items"
				. "\n SET hits = ( hits + 1 )"
				. "\n WHERE time_stamp = '$now'"
				. "\n AND item_table = '$this->_tbl'"
				. "\n AND item_id = ".$this->$k.""
				;
				$this->_db->setQuery( $query );
				$this->_db->query();
			} else {
				$query = "INSERT INTO #__core_log_items"
				. "\n VALUES ( '$now', '$this->_tbl', ". $this->$k .", 1 )"
				;
				$this->_db->setQuery( $query );
				$this->_db->query();
			}
		}
	}

	/**
	 * Tests if item is checked out
	 *
	 * @access public
	 * @param int A user id
	 * @return boolean
	 */
	function isCheckedOut( $user_id=0 )
	{
		$checkedOut = $this->get( 'checked_out' );

		if ($user_id)
		{
			return ($checkedOut && $checkedOut <> $user_id);
		}
		else
		{
			return $checkedOut;
		}
	}

	/**
	* Generic save function
	*
	* @access public
	* @param array Source array for binding to class vars
	* @param string Filter for the order updating
	* @returns TRUE if completely successful, FALSE if partially or not succesful.
	*/
	function save( $source, $order_filter='' )
	{
		if (!$this->bind( $source ))
		{
			return false;
		}
		if (!$this->check())
		{
			return false;
		}
		if (!$this->store())
		{
			return false;
		}
		if (!$this->checkin())
		{
			return false;
		}
		if ($order_filter)
		{
			$filter_value = $this->$order_filter;
			$this->reorder( $order_filter ? "`$order_filter` = '$filter_value'" : '' );
		}
		$this->setError('');
		$this->setErrorNum(0);
		return true;
	}

	/**
	 * Sets the internal error message
	 * @param string The error message
	 * @since 1.5
	 */
	function setError( $value )
	{
		$this->_error = $value;
	}

	/**
	 * Sets the internal error number
	 * 
	 * @param int Set the error number with this value
	 */
	function setErrorNum( $value )
	{
		$this->_errorNum = $value;
	}

	/**
	 * Generic Publish/Unpublish function
	 * 
	 * @access public
	 * @param array An array of id numbers
	 * @param integer 0 if unpublishing, 1 if publishing
	 * @param integer The id of the user performnig the operation
	 * @since 1.0.4
	 */
	function publish( $cid=null, $publish=1, $user_id=0 )
	{
		JArrayHelper::toInteger( $cid, array() );
		$user_id	= (int) $user_id;
		$publish	= (int) $publish;
		$k			= $this->_tbl_key;

		if (count( $cid ) < 1)
		{
			$this->setError("No items selected.");
			$this->setErrorNum(24);
			return false;
		}

		$cids = $k . '=' . implode( ' OR ' . $k . '=', $cid );

		$query = "UPDATE $this->_tbl"
		. "\n SET published = " . (int) $publish
		. "\n WHERE ($cids)"
		;

		$checkin = array_key_exists( 'checked_out', get_class_vars( strtolower(get_class( $this )) ) );
		if ($checkin)
		{
			$query .= "\n AND (checked_out = 0 OR checked_out = $user_id)";
		}

		$this->_db->setQuery( $query );
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			$this->setErrorNum($this->_db->getErrorNum());
			return false;
		}

		if (count( $cid ) == 1 && $checkin)
		{
			$this->checkin( $cid[0] );
		}
		$this->setError('');
		$this->setErrorNum(0);
		return true;
	}

	/**
	* Export item list to xml
	*
	* @access public
	* @param boolean Map foreign keys to text values
	*/
	function toXML( $mapKeysToText=false )
	{
		$xml = '<record table="' . $this->_tbl . '"';

		if ($mapKeysToText)
		{
			$xml .= ' mapkeystotext="true"';
		}
		$xml .= '>';
		foreach (get_object_vars( $this ) as $k => $v)
		{
			if (is_array($v) or is_object($v) or $v === NULL)
			{
				continue;
			}
			if ($k[0] == '_')
			{ // internal field
				continue;
			}
			$xml .= '<' . $k . '><![CDATA[' . $v . ']]></' . $k . '>';
		}
		$xml .= '</record>';

		return $xml;
	}

	/**
	* Add a directory where JTable should search for table types. You may
	* either pass a string or an array of directories.
	*
	* @access	public
	* @param	string|array	directory or directories to search.
	* @return	array			An array with directory elements
	* @since 1.5
	*/
	function addTableDir( $dir = '' )
	{
		static $directories;

		if (!isset($directories))
		{
			$directories = array(JPATH_LIBRARIES.DS.'joomla'.DS.'database'.DS.'table');
		}

		// Handle datatype of $dir element (string/array)
		if( is_array( $dir ) )
		{
			$directories = array_merge( $directories, $dir );
		} else {
			if (!empty($dir))
			{
				array_push( $directories, $dir );
			}
		}

		return $directories;
	}
}
?>