<?php
/**
 * @version		$Id: acl.php 11140 2008-10-16 18:17:16Z ircmaxell $
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

/**
 * @package		Joomla.Framework
 * @subpackage	Table
 */
class JTableBackups extends JTable
{
/**
	 * @var int unsigned
	 */
	protected $backupid = 0;
	/**
	 * @var varchar
	 */
	protected $name = null;
	/**
	 * @var int unsigned
	 */
	protected $description = null;
	/**
	 * @var int unsigned
	 */
	protected $start = null;
	/**
	 * @var varchar
	 */
	protected $end = null;
	/**
	 * @var varchar
	 */
	protected $location = null;
	/**
	 * @var int unsigned
	 */
	protected $data = null;

	private $_entries = null;
	
	/*
	 * Constructor
	 * @param object Database object
	 */
	protected function __construct(&$db)
	{
		parent::__construct('#__backups', 'backupid', $db);
	}
	
	public function load($oid = null) {
		if(parent::load($oid)) {
			$this->loadEntries();
		}
	}
	
	public function loadEntries()  {
		$this->_entries = Array(); // reset this
		$this->_db->setQuery('SELECT * FROM #__backupentries WHERE backupid = '. $backupid);
		try {
			$results = $this->_db->loadAssocList();
			foreach($results as $result) {
				$tmp =& JFactory::getTable('backupentry');
				$tmp->setProperties($result);
				$this->_entries[] = clone($tmp);
				return true;
			}
		} catch (JException $e) {
			return false;
		}
	}
	
	public function &getEntries() {
		if($this->_entries != null) {
			$this->loadEntries();
		}
		return $this->_entries;
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
	public function store( $updateNulls=false )
	{
		$k = $this->_tbl_key;
		try {
			if( $this->$k)
			{
				$ret = $this->_db->updateObject( $this->_tbl, $this, $this->_tbl_key, $updateNulls );
			}
			else
			{
				$ret = $this->_db->insertObject( $this->_tbl, $this, $this->_tbl_key );
			}
			for($i = 0; $i < count($this->_entries); $i++) {
				$this->_entries[$i]->backupid = $this->backupid;
				$this->_entries[$i]->store();
			}
		} catch(JException $e) {
			$this->setError(get_class( $this ).'::store failed - '.$e->getMessage());
			return false;
		}
		return true;
	}
	
	public function &addEntry($name, $type, $source) {
		$entry =& JTable::getTable('backupentry');
		$entry->name = $name;
		$entry->type = $type;
		$entry->source = $source;
		$this->_entries[] =& $entry;
		return $entry;
	}
	
	public function removeEntry($name, $type=null, $source=null) {
		foreach($this->_entries as $key=>$value) {
			if($entry->name == $name 
				&& ($type === null || $entry->type == $type) 
				&& ($source === null || $entry->source == $source)) {
				unset($this->_entries[$key]);
				return true;
			}
		}
		return false;
	}
}
