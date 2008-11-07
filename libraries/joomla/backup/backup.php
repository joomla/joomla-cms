<?php
/**
 * Joomla! Backup System
 * 
 * Handles backups 
 * 
 * PHP5
 *  
 * Created on Oct 29, 2008
 * 
 * @package Joomla.Framework
 * @subpackage Backup
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * @version SVN: $Id:$    
 */
 
defined('JPATH_BASE') or die();

jimport('joomla.base.adapter');

class JBackup extends JAdapter {

	private $_db;
	protected $taskset;
	protected $backup;
	
	public function __construct(&$db, $backupid=0, $tasksetid=0)
	{
		$this->_db =& $db;	
		$this->setBackupID($backupid);
		$this->setTaskSetID($tasksetid);
		parent::__construct(dirname(__FILE__),'JBackup');
	}
	
	public function setTaskSetID($tasksetid) {
		$this->taskset = new JTaskSet($db);
		if ($tasksetid) {
			$this->taskset->load($tasksetid);
		} // if its zero create a new taskset; no work required
	}
	
	public function setBackupID($backupid) {
		$this->backup =& JFactory::getTable();
		if($backupid) {
			$this->backup->load($backupid);
			$this->entries = $this->backup->getEntries();
		}
	}
	
	/**
	 * Finishes the backup removing any extra data (e.g. tasksets)
	 *
	 */
	public function finish() {
		 if($this->taskset) $this->taskset->delete();
	}
	
	/**
	 * Set the task set of the backup
	 *
	 * @param unknown_type $taskset
	 */
	public function setTaskSet(&$taskset) {
		$this->taskset =& $taskset;
	}
	
	
	/**
	 * Adds an entry to the backup queue
	 *
	 * @param unknown_type $type
	 * @param unknown_type $source
	 */
	public function addEntry($name, $type, $source) {
		return $this->backup->addEntry($name, $type, $source);
	}
	
	/**
	 * Removes an entry from the backup queue
	 *
	 * @param unknown_type $type
	 * @param unknown_type $source
	 */
	public function removeEntry($name, $type, $source) {
		return $this->backup->removeEntry($name, $type, $source);
	}
	
	/**
	 * Runs a backup
	 *
	 */
	public function execute() {	
		while($task =& $tasket->getNextTask()) {
			
		}
	}
	
}