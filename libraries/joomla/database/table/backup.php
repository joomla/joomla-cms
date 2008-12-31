<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

/**
 * @package		Joomla.Framework
 * @subpackage	Table
 */
class JTableBackup extends JTable
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

	private $_entries = Array();

	/*
	 * Constructor
	 * @param object Database object
	 */
	protected function __construct(&$db)
	{
		parent::__construct('#__backups', 'backupid', $db);
	}

	public function loadEntries()  {
		$this->_entries = Array(); // reset this
		$this->_db->setQuery('SELECT * FROM #__backup_entries WHERE backupid = '. $this->backupid);
		try {
			$results = $this->_db->loadAssocList();
			foreach($results as $result) {
				$tmp =& JTable::getInstance('backupentry');
				$tmp->setProperties($result);
				$this->_entries[] = clone($tmp);
				return true;
			}
		} catch (JException $e) {
			return false;
		}
	}

	public function load($oid=null) {
		$res = parent::load($oid);
		if($res) {
			$res = $this->loadEntries();
		}
		return $res;
	}

	public function &getEntries() {
		if(!count($this->_entries)) {
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

	public function &addEntry($name, $type, $params) {
		$entry =& JTable::getInstance('backupentry');
		$entry->name = $name;
		$entry->type = $type;
		$entry->params = $params;
		$this->_entries[] =& $entry;
		return $entry;
	}

	public function removeEntry($name, $type=null) {
		foreach($this->_entries as $key=>$value) {
			if($entry->name == $name
				&& ($type === null || $entry->type == $type)
			) {
				unset($this->_entries[$key]);
				return true;
			}
		}
		return false;
	}
}
