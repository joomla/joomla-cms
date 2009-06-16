<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Table
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Abstract Table class
 *
 * Parent class to all tables.
 *
 * @abstract
 * @package 	Joomla.Framework
 * @subpackage	Table
 * @since		1.0
 * @tutorial	Joomla.Framework/jtable.cls
 * @link		http://docs.joomla.org/JTable
 */
abstract class JTable extends JObject
{
	/**
	 * Name of the database table to model.
	 *
	 * @var		string
	 * @since	1.0
	 */
	protected $_tbl	= '';

	/**
	 * Name of the primary key field in the table.
	 *
	 * @var		string
	 * @since	1.0
	 */
	protected $_tbl_key = '';

	/**
	 * JDatabase connector object.
	 *
	 * @var		object
	 * @since	1.0
	 */
	protected $_db = null;

	/**
	 * Object constructor to set table and key fields.  In most cases this will
	 * be overridden by child classes to explicitly set the table and key fields
	 * for a particular database table.
	 *
	 * @param	string Name of the table to model.
	 * @param	string Name of the primary key field in the table.
	 * @param	object JDatabase connector object.
	 * @since	1.0
	 */
	function __construct($table, $key, &$db)
	{
		// Set internal variables.
		$this->_tbl		= $table;
		$this->_tbl_key	= $key;
		$this->_db		= &$db;
	}

	/**
	 * Static method to get an instance of a JTable class if it can be found in
	 * the table include paths.  To add include paths for searching for JTable
	 * classes @see JTable::addIncludePath().
	 *
	 * @param	string	The type (name) of the JTable class to get an instance of.
	 * @param	string 	An optional prefix for the table class name.
	 * @param	array	An optional array of configuration values for the JTable object.
	 * @return	mixed	A JTable object if found or boolean false if one could not be found.
	 * @since	1.5
	 * @link	http://docs.joomla.org/JTable/getInstance
	*/
	public static function &getInstance($type, $prefix = 'JTable', $config = array())
	{
		// Initialize variables.
		$false = false;

		// Sanitize and prepare the table class name.
		$type = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$tableClass = $prefix.ucfirst($type);

		// Only try to load the class if it doesn't already exist.
		if (!class_exists($tableClass))
		{
			// Search for the class file in the JTable include paths.
			jimport('joomla.filesystem.path');
			if ($path = JPath::find(JTable::addIncludePath(), strtolower($type).'.php'))
			{
				// Import the class file.
				require_once $path;

				// If we were unable to load the proper class, raise a warning and return false.
				if (!class_exists($tableClass)) {
					JError::raiseWarning(0, 'Table class ' . $tableClass . ' not found in file.');
					return $false;
				}
			}
			else {
				// If we were unable to find the class file in the JTable include paths, raise a warning and return false.
				JError::raiseWarning(0, 'Table ' . $type . ' not supported. File not found.');
				return $false;
			}
		}

		// If a database object was passed in the configuration array use it, otherwise get the global one from JFactory.
		if (array_key_exists('dbo', $config))  {
			$db = &$config['dbo'];
		} else {
			$db = & JFactory::getDbo();
		}

		// Instantiate a new table class and return it.
		$instance = new $tableClass($db);

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
	 * Method to get the database table name for the class.
	 *
	 * @return	string	The name of the database table being modeled.
	 * @since	1.5
	 * @link	http://docs.joomla.org/JTable/getTableName
	 */
	public function getTableName()
	{
		return $this->_tbl;
	}

	/**
	 * Method to get the primary key field name for the table.
	 *
	 * @return	string	The name of the primary key for the table.
	 * @since	1.5
	 * @link	http://docs.joomla.org/JTable/getKeyName
	 */
	public function getKeyName()
	{
		return $this->_tbl_key;
	}

	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition.  It will ignore the primary key as well as any private class
	 * properties.
	 *
	 * @return	void
	 * @since	1.0
	 * @link	http://docs.joomla.org/JTable/reset
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
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param	mixed	An associative array or object to bind to the JTable instance.
	 * @param	mixed	An optional array or space separated list of properties
	 * 					to ignore while binding.
	 * @return	boolean	True on success.
	 * @since	1.0
	 * @link	http://docs.joomla.org/JTable/bind
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

		// If the ignore value is a string, explode it over spaces.
		if (!is_array($ignore)) {
			$ignore = explode(' ', $ignore);
		}

		// Bind the source value, excluding the ignored fields.
		foreach ($this->getProperties() as $k => $v)
		{
			// Only process fields not in the ignore array.
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
		// Initialize variables.
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
	 * Method to perform sanity checks on the JTable instance properties to ensure
	 * they are safe to store in the database.  Child classes should override this
	 * method to make sure the data they are storing in the database is safe and
	 * as expected before storage.
	 *
	 * @return	boolean	True if the instance is sane and able to be stored in the database.
	 * @since	1.0
	 * @link	http://docs.joomla.org/JTable/check
	 */
	public function check()
	{
		return true;
	}

	/**
	 * Method to store a row in the database from the JTable instance properties.
	 * If a primary key value is set the row with that primary key value will be
	 * updated with the instance property values.  If no primary key value is set
	 * a new row will be inserted into the database with the properties from the
	 * JTable instance.
	 *
	 * @param	boolean True to update fields even if they are null.
	 * @return	boolean	True on success.
	 * @since	1.0
	 * @link	http://docs.joomla.org/JTable/store
	 */
	public function store($updateNulls = false)
	{
		// Initialize variables.
		$k = $this->_tbl_key;

		// If a primary key exists update the object, otherwise insert it.
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
