<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Table
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Abstract Table class
 *
 * Parent classes to all tables.
 *
 * @abstract
 * @package 	Joomla.Framework
 * @subpackage	Table
 * @since		1.0
 * @tutorial	Joomla.Framework/jtable.cls
 */
abstract class JTable extends JObject
{
	/**
	 * Name of the table in the db schema relating to child class
	 *
	 * @var 	string
	 */
	protected $_tbl	= '';

	/**
	 * Name of the primary key field in the table
	 *
	 * @var		string
	 */
	protected $_tbl_key = '';

	/**
	 * Database connector
	 *
	 * @var		JDatabase
	 */
	protected $_db = null;

	/**
	 * Object constructor to set table and key field
	 *
	 * Can be overloaded/supplemented by the child class
	 *
	 * @param string $table name of the table in the db schema relating to child class
	 * @param string $key name of the primary key field in the table
	 * @param object $db JDatabase object
	 */
	function __construct($table, $key, &$db)
	{
		$this->_tbl		= $table;
		$this->_tbl_key	= $key;
		$this->_db		= &$db;
	}

	/**
	 * Returns a reference to the a Table object, always creating it
	 *
	 * @param type 		$type 	 The table type to instantiate
	 * @param string 	$prefix	 A prefix for the table class name. Optional.
	 * @param array		$options Configuration array for model. Optional.
	 * @return database A database object
	 * @since 1.5
	*/
	public static function &getInstance($type, $prefix = 'JTable', $config = array())
	{
		$false = false;

		$type = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$tableClass = $prefix.ucfirst($type);

		if (!class_exists($tableClass))
		{
			jimport('joomla.filesystem.path');
			if ($path = JPath::find(JTable::addIncludePath(), strtolower($type).'.php'))
			{
				require_once $path;

				if (!class_exists($tableClass))
				{
					JError::raiseWarning(0, 'Table class ' . $tableClass . ' not found in file.');
					return $false;
				}
			}
			else
			{
				JError::raiseWarning(0, 'Table ' . $type . ' not supported. File not found.');
				return $false;
			}
		}

		//Make sure we are returning a DBO object
		if (array_key_exists('dbo', $config))  {
			$db = &$config['dbo'];
		} else {
			$db = & JFactory::getDbo();
		}

		$instance = new $tableClass($db);
		//$instance->setDBO($db);

		return $instance;
	}

	/**
	 * Get the internal database object
	 *
	 * @return object A JDatabase based object
	 */
	public function &getDbo()
	{
		return $this->_db;
	}

	/**
	 * Set the internal database object
	 *
	 * @param	object	$db	A JDatabase based object
	 * @return	void
	 */
	public function setDbo(&$db)
	{
		$this->_db = &$db;
	}

	/**
	 * Gets the internal table name for the object
	 *
	 * @return string
	 * @since 1.5
	 */
	public function getTableName()
	{
		return $this->_tbl;
	}

	/**
	 * Gets the internal primary key name
	 *
	 * @return string
	 * @since 1.5
	 */
	public function getKeyName()
	{
		return $this->_tbl_key;
	}

	/**
	 * Resets the default properties
	 * @return	void
	 */
	public function reset()
	{
		$k = $this->_tbl_key;
		foreach ($this->getProperties() as $name => $value)
		{
			if ($name != $k)
			{
				$this->$name	= $value;
			}
		}
	}

	/**
	 * Binds a named array/hash to this object
	 *
	 * Can be overloaded/supplemented by the child class
	 *
	 * @param	$from	mixed	An associative array or object
	 * @param	$ignore	mixed	An array or space separated list of fields not to bind
	 * @return	boolean
	 */
	public function bind($from, $ignore=array())
	{
		$fromArray	= is_array($from);
		$fromObject	= is_object($from);

		if (!$fromArray && !$fromObject)
		{
			$this->setError(get_class($this).'::bind failed. Invalid from argument');
			return false;
		}
		if (!is_array($ignore)) {
			$ignore = explode(' ', $ignore);
		}
		foreach ($this->getProperties() as $k => $v)
		{
			// internal attributes of an object are ignored
			if (!in_array($k, $ignore))
			{
				if ($fromArray && isset($from[$k])) {
					$this->$k = $from[$k];
				} else if ($fromObject && isset($from->$k)) {
					$this->$k = $from->$k;
				}
			}
		}
		return true;
	}

	/**
	 * Loads a row from the database and binds the fields to the object properties
	 *
	 * @param	mixed	Optional primary key.  If not specifed, the value of current key is used
	 *
	 * @return	boolean	True if successful
	 */
	public function load($oid=null)
	{
		$k = $this->_tbl_key;

		if ($oid !== null) {
			$this->$k = $oid;
		}

		$oid = $this->$k;

		if ($oid === null) {
			return false;
		}
		$this->reset();

		$db = &$this->getDbo();

		$query = 'SELECT *'
		. ' FROM '.$this->_tbl
		. ' WHERE '.$this->_tbl_key.' = '.$db->Quote($oid);
		$db->setQuery($query);

		if ($result = $db->loadAssoc()) {
			return $this->bind($result);
		}
		else
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
	}

	/**
	 * Generic check method
	 *
	 * Can be overloaded/supplemented by the child class
	 *
	 * @return boolean True if the object is ok
	 */
	public function check()
	{
		return true;
	}

	/**
	 * Inserts a new row if id is zero or updates an existing row in the database table
	 *
	 * Can be overloaded/supplemented by the child class
	 *
	 * @param boolean If false, null object variables are not updated
	 * @return null|string null if successful otherwise returns and error message
	 */
	public function store($updateNulls=false)
	{
		$k = $this->_tbl_key;

		if ($this->$k)
		{
			$ret = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls);
		}
		else
		{
			$ret = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key);
		}
		if (!$ret)
		{
			$this->setError(get_class($this).'::store failed - '.$this->_db->getErrorMsg());
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
	 * @param $dirn
	 * @param $where
	 */
	public function move($dirn, $where='')
	{
		if (!in_array('ordering',  array_keys($this->getProperties())))
		{
			$this->setError(get_class($this).' does not support ordering');
			return false;
		}

		$k = $this->_tbl_key;

		$sql = "SELECT $this->_tbl_key, ordering FROM $this->_tbl";

		if ($dirn < 0)
		{
			$sql .= ' WHERE ordering < '.(int) $this->ordering;
			$sql .= ($where ? ' AND '.$where : '');
			$sql .= ' ORDER BY ordering DESC';
		}
		else if ($dirn > 0)
		{
			$sql .= ' WHERE ordering > '.(int) $this->ordering;
			$sql .= ($where ? ' AND '. $where : '');
			$sql .= ' ORDER BY ordering';
		}
		else
		{
			$sql .= ' WHERE ordering = '.(int) $this->ordering;
			$sql .= ($where ? ' AND '.$where : '');
			$sql .= ' ORDER BY ordering';
		}

		$this->_db->setQuery($sql, 0, 1);


		$row = null;
		$row = $this->_db->loadObject();
		if (isset($row))
		{
			$query = 'UPDATE '. $this->_tbl
			. ' SET ordering = '. (int) $row->ordering
			. ' WHERE '. $this->_tbl_key .' = '. $this->_db->Quote($this->$k)
			;
			$this->_db->setQuery($query);

			if (!$this->_db->query())
			{
				$err = $this->_db->getErrorMsg();
				JError::raiseError(500, $err);
			}

			$query = 'UPDATE '.$this->_tbl
			. ' SET ordering = '.(int) $this->ordering
			. ' WHERE '.$this->_tbl_key.' = '.$this->_db->Quote($row->$k)
			;
			$this->_db->setQuery($query);

			if (!$this->_db->query())
			{
				$err = $this->_db->getErrorMsg();
				JError::raiseError(500, $err);
			}

			$this->ordering = $row->ordering;
		}
		else
		{
			$query = 'UPDATE '. $this->_tbl
			. ' SET ordering = '.(int) $this->ordering
			. ' WHERE '. $this->_tbl_key .' = '. $this->_db->Quote($this->$k)
			;
			$this->_db->setQuery($query);

			if (!$this->_db->query())
			{
				$err = $this->_db->getErrorMsg();
				JError::raiseError(500, $err);
			}
		}
		return true;
	}

	/**
	 * Returns the ordering value to place a new item last in its group
	 *
	 * @param string query WHERE clause for selecting MAX(ordering).
	 */
	public function getNextOrder ($where='')
	{
		if (!in_array('ordering', array_keys($this->getProperties())))
		{
			$this->setError(get_class($this).' does not support ordering');
			return false;
		}

		$query = 'SELECT MAX(ordering)' .
				' FROM ' . $this->_tbl .
				($where ? ' WHERE '.$where : '');

		$this->_db->setQuery($query);
		$maxord = $this->_db->loadResult();

		if ($this->_db->getErrorNum())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return $maxord + 1;
	}

	/**
	 * Compacts the ordering sequence of the selected records
	 *
	 * @param string Additional where query to limit ordering to a particular subset of records
	 */
	public function reorder($where='')
	{
		$k = $this->_tbl_key;

		if (!in_array('ordering', array_keys($this->getProperties())))
		{
			$this->setError(get_class($this).' does not support ordering');
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

		$query = 'SELECT '.$this->_tbl_key.', ordering'
		. ' FROM '. $this->_tbl
		. ' WHERE ordering >= 0' . ($where ? ' AND '. $where : '')
		. ' ORDER BY ordering'.$order2
		;
		$this->_db->setQuery($query);
		if (!($orders = $this->_db->loadObjectList()))
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		// compact the ordering numbers
		for ($i=0, $n=count($orders); $i < $n; $i++)
		{
			if ($orders[$i]->ordering >= 0)
			{
				if ($orders[$i]->ordering != $i+1)
				{
					$orders[$i]->ordering = $i+1;
					$query = 'UPDATE '.$this->_tbl
					. ' SET ordering = '. (int) $orders[$i]->ordering
					. ' WHERE '. $k .' = '. $this->_db->Quote($orders[$i]->$k)
					;
					$this->_db->setQuery($query);
					$this->_db->query();
				}
			}
		}

		return true;
	}

	/**
	 * Generic check for whether dependancies exist for this object in the db schema
	 *
	 * can be overloaded/supplemented by the child class
	 *
	 * @param string $msg Error message returned
	 * @param int Optional key index
	 * @param array Optional array to compiles standard joins: format [label=>'Label',name=>'table name',idfield=>'field',joinfield=>'field']
	 * @return true|false
	 */
	public function canDelete($oid=null, $joins=null)
	{
		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval($oid);
		}

		if (is_array($joins))
		{
			$select = "$k";
			$join = "";
			foreach($joins as $table)
			{
				$select .= ', COUNT(DISTINCT '.$table['idfield'].') AS '.$table['idfield'];
				$join .= ' LEFT JOIN '.$table['name'].' ON '.$table['joinfield'].' = '.$k;
			}

			$query = 'SELECT '. $select
			. ' FROM '. $this->_tbl
			. $join
			. ' WHERE '. $k .' = '. $this->_db->Quote($this->$k)
			. ' GROUP BY '. $k
			;
			$this->_db->setQuery($query);

			if (!$obj = $this->_db->loadObject())
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			$msg = array();
			$i = 0;
			foreach($joins as $table)
			{
				$k = $table['idfield'] . $i;
				if ($obj->$k)
				{
					$msg[] = JText::_($table['label']);
				}
				$i++;
			}

			if (count($msg))
			{
				$this->setError("noDeleteRecord" . ": " . implode(', ', $msg));
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
	 * @return true if successful otherwise returns and error message
	 */
	public function delete($oid=null)
	{
		//if (!$this->canDelete($msg))
		//{
		//	return $msg;
		//}

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval($oid);
		}

		$query = 'DELETE FROM '.$this->_db->nameQuote($this->_tbl).
				' WHERE '.$this->_tbl_key.' = '. $this->_db->Quote($this->$k);
		$this->_db->setQuery($query);

		if ($this->_db->query())
		{
			return true;
		}
		else
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
	}

	/**
	 * Checks out a row
	 *
	 * @param	integer	The id of the user
	 * @param 	mixed	The primary key value for the row
	 * @return	boolean	True if successful, or if checkout is not supported
	 */
	public function checkout($who, $oid = null)
	{
		if (!in_array('checked_out', array_keys($this->getProperties()))) {
			return true;
		}

		$k = $this->_tbl_key;
		if ($oid !== null) {
			$this->$k = $oid;
		}

		$date = &JFactory::getDate();
		$time = $date->toMysql();

		$query = 'UPDATE '.$this->_db->nameQuote($this->_tbl) .
			' SET checked_out = '.(int)$who.', checked_out_time = '.$this->_db->Quote($time) .
			' WHERE '.$this->_tbl_key.' = '. $this->_db->Quote($this->$k);
		$this->_db->setQuery($query);

		$this->checked_out = $who;
		$this->checked_out_time = $time;

		return $this->_db->query();
	}

	/**
	 * Checks in a row
	 *
	 * @param	mixed	The primary key value for the row
	 * @return	boolean	True if successful, or if checkout is not supported
	 */
	public function checkin($oid=null)
	{
		if (!(
			in_array('checked_out', array_keys($this->getProperties())) ||
	 		in_array('checked_out_time', array_keys($this->getProperties()))
		)) {
			return true;
		}

		$k = $this->_tbl_key;

		if ($oid !== null) {
			$this->$k = $oid;
		}

		if ($this->$k == NULL) {
			return false;
		}

		$query = 'UPDATE '.$this->_db->nameQuote($this->_tbl).
				' SET checked_out = 0, checked_out_time = '.$this->_db->Quote($this->_db->getNullDate()) .
				' WHERE '.$this->_tbl_key.' = '. $this->_db->Quote($this->$k);
		$this->_db->setQuery($query);

		$this->checked_out = 0;
		$this->checked_out_time = '';

		return $this->_db->query();
	}

	/**
	 * Description
	 *
	 * @param $oid
	 * @param $log
	 */
	public function hit($oid=null, $log=false)
	{
		if (!in_array('hits', array_keys($this->getProperties()))) {
			return;
		}

		$k = $this->_tbl_key;

		if ($oid !== null) {
			$this->$k = intval($oid);
		}

		$query = 'UPDATE '. $this->_tbl
		. ' SET hits = (hits + 1)'
		. ' WHERE '. $this->_tbl_key .'='. $this->_db->Quote($this->$k);
		$this->_db->setQuery($query);
		$this->_db->query();
		$this->hits++;
	}

	/**
	 * Check if an item is checked out
	 *
	 * This function can be used as a static function too, when you do so you need to also provide the
	 * a value for the $against parameter.
	 *
	 * @static
	 * @param integer  $with  	The userid to preform the match with, if an item is checked out
	 * 							by this user the function will return false
	 * @param integer  $against 	The userid to perform the match against when the function is used as
	 * 							a static function.
	 * @return boolean
	 */
	public function isCheckedOut($with = 0, $against = null)
	{
		if (isset($this) && is_a($this, 'JTable') && is_null($against)) {
			$against = $this->get('checked_out');
		}

		//item is not checked out, or being checked out by the same user
		if (!$against || $against == $with) {
			return  false;
		}

		$session = &JTable::getInstance('session');
		return $session->exists($against);
	}

	/**
	 * Generic save function
	 *
	 * @param	array	Source array for binding to class vars
	 * @param	string	Filter for the order updating
	 * @param	mixed	An array or space separated list of fields not to bind
	 * @returns TRUE if completely successful, FALSE if partially or not succesful.
	 */
	public function save($source, $order_filter='', $ignore='')
	{
		if (!$this->bind($source, $ignore)) {
			return false;
		}
		if (!$this->check()) {
			return false;
		}
		if (!$this->store()) {
			return false;
		}
		if (!$this->checkin()) {
			return false;
		}
		if ($order_filter)
		{
			$filter_value = $this->$order_filter;
			$this->reorder($order_filter ? $this->_db->nameQuote($order_filter).' = '.$this->_db->Quote($filter_value) : '');
		}
		$this->setError('');
		return true;
	}

	/**
	 * Generic Publish/Unpublish function
	 *
	 * @param array An array of id numbers
	 * @param integer 0 if unpublishing, 1 if publishing
	 * @param integer The id of the user performnig the operation
	 * @since 1.0.4
	 */
	public function publish($cid=null, $publish=1, $user_id=0)
	{
		JArrayHelper::toInteger($cid);
		$user_id	= (int) $user_id;
		$publish	= (int) $publish;
		$k			= $this->_tbl_key;

		if (count($cid) < 1)
		{
			if ($this->$k) {
				$cid = array($this->$k);
			} else {
				$this->setError("No items selected.");
				return false;
			}
		}

		$cids = $k . '=' . implode(' OR ' . $k . '=', $cid);

		$query = 'UPDATE '. $this->_tbl
		. ' SET published = ' . (int) $publish
		. ' WHERE ('.$cids.')'
		;

		$checkin = in_array('checked_out', array_keys($this->getProperties()));
		if ($checkin)
		{
			$query .= ' AND (checked_out = 0 OR checked_out = '.(int) $user_id.')';
		}

		$this->_db->setQuery($query);
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if (count($cid) == 1 && $checkin)
		{
			if ($this->_db->getAffectedRows() == 1) {
				$this->checkin($cid[0]);
				if ($this->$k == $cid[0]) {
					$this->published = $publish;
				}
			}
		}
		$this->setError('');
		return true;
	}

	/**
	 * Export item list to xml
	 *
	 * @param boolean Map foreign keys to text values
	 */
	public function toXML($mapKeysToText=false)
	{
		$xml = '<record table="' . $this->_tbl . '"';

		if ($mapKeysToText)
		{
			$xml .= ' mapkeystotext="true"';
		}
		$xml .= '>';
		foreach (get_object_vars($this) as $k => $v)
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
	 * @param	string	A path to search.
	 * @return	array	An array with directory elements
	 * @since 1.5
	 */
	public static function addIncludePath($path=null)
	{
		static $paths;

		if (!isset($paths)) {
			$paths = array(dirname(__FILE__).DS.'table');
		}

		// just force path to array
		settype($path, 'array');

		if (!empty($path) && !in_array($path, $paths))
		{
			// loop through the path directories
			foreach ($path as $dir)
			{
				// no surrounding spaces allowed!
				$dir = trim($dir);

				// add to the top of the search dirs
				// so that custom paths are searched before core paths
				array_unshift($paths, $dir);
			}
		}
		return $paths;
	}
}
