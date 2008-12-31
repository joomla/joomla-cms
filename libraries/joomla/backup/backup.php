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
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 * @version SVN: $Id:$
 */

defined('JPATH_BASE') or die();

jimport('joomla.base.adapter');

class JBackup extends JAdapter {

	private $_taskset;
	private $_backup;
	private $_mode = 'backup'; // this needs to be retained in the task set!
	protected $execution_page = ''; //
	protected $landing_page = '';

	/**
	 * Sets the mode of the backup
	 * Note: This will reset the backup and backup id potentially losing data
	 * @param enum['backup','restore','remove'] Mode to use
	 * @param integer Backup ID to load up; required for restore or remove
	 * @return boolean result of operation; this will be false for invalid data
	 */
	public function setMode($mode, $backupid=0) {
		$this->_backup =& JTable::getInstance('backups');
		switch($mode) {
			case 'backup':
				// clear the backup data
				if($backupid) {
					$this->_backup->load($backupid);
				}
				break;
			case 'remove':
			case 'restore':
				if(!$backupid) {
					return false;
				}
				$this->_backup->load($backupid);
				break;
			default:
				return false;
				break;
		}
		$this->_mode = $mode;
	}

	public function __construct(&$db, $backupid=0, $tasksetid=0)
	{
		parent::__construct(dirname(__FILE__),'JBackup');
		$this->_db =& $db;
		$this->setBackupID($backupid);
		$this->setTaskSetID($tasksetid);
	}

	public function setTaskSetID($tasksetid) {
		$this->_taskset = new JTaskSet($this->_db);
		if ($tasksetid) {
			$this->_taskset->load($tasksetid);
		} // if its zero create a new taskset; no work required
	}

	public function setBackupID($backupid) {
		$this->_backup =& JTable::getInstance('backup');
		if($backupid) {
			$this->_backup->load($backupid);
			$this->entries = $this->_backup->getEntries();
		}
	}

	/**
	 * Finishes the backup removing any extra data (e.g. tasksets)
	 *
	 */
	public function finish() {
		 if($this->_taskset) $this->_taskset->delete();
	}

	/**
	 * Set the task set of the backup
	 *
	 * @param unknown_type $taskset
	 */
	public function setTaskSet(&$taskset) {
		$this->_taskset =& $taskset;
		if(!$this->_backup && intval($taskset->fkey)) {
			$this->_backup->load(intval($tasket->fkey));
		}
	}


	/**
	 * Adds an entry to the backup queue
	 *
	 * @param unknown_type $type
	 * @param unknown_type $source
	 */
	public function addEntry($name, $type, $params=Array()) {
		if($this->_mode != 'backup') {
			JError::raiseError(101, JText::_('Cannot add entries to non-backup jobs'));
			return false;
		}
		return $this->_backup->addEntry($name, $type, $params);
	}

	/**
	 * Removes an entry from the backup queue
	 *
	 * @param unknown_type $type
	 * @param unknown_type $source
	 */
	public function removeEntry($name, $type) {
		if($this->_mode != 'backup') {
			JError::raiseError(101, JText::_('Cannot add entries to non-backup jobs'));
			return false;
		}
		return $this->_backup->removeEntry($name, $type);
	}

	public function getExecutionPage() {
		if($this->execution_page) {
			return $this->execution_page;
		}
		// TODO: Set this to com_backups
		return JURI::base().'?option=com_test&role=backup';
		//return JURI::base().'?option=com_backups';
	}

	public function getLandingPage() {
		if($this->landing_page) {
			return $this->landing_page;
		}
		$url = JURI::current();
		$query = JURI::getQuery();
		return $url; // send them to where they are now snas the query string - index page probably
		return $query ? $url . '?' . $query : $url; // send the user back to where they came from
	}

	private function _buildTaskSet() {
		jimport('joomla.filesystem.folder');
		// Set the destination on any backup entries that don't have it
		// Called from the execute function and needs the backup to have been stored appropriately
		$destination = JPATH_BACKUPS.DS.$this->_backup->get('backupid');
		JFolder::create($destination);
		$entries =& $this->_backup->getEntries();
		$this->_taskset = new JTaskSet($this->_db);
		$this->_taskset->set('tasksetname', ucfirst($this->_mode) .' run ' . $this->_backup->get('start'));
		$this->_taskset->set('extensionid', '139'); // TODO: Swap this in with something better
		$this->_taskset->set('execution_page', $this->getExecutionPage());
		$this->_taskset->set('landing_page', $this->getLandingPage());
		if(!$this->_taskset->store()) {
			JError::raiseWarning(40,JText::_('Failed to store task set'));
			return false;
		}
		foreach($entries as &$entry) {
			$params = $entry->params; // yay for php5 and automatic references!
			if(!array_key_exists('destination', $params)) {
				$params['destination'] =  $destination;
			}
			$task = $this->_taskset->createTask();
			$task->set('type', $entry->type);
			$task->set('params',$params);
			if(!$task->store()) {
				die('Failed to store task: '. $this->_db->getErrorMSG());
			}
		}
		unset($entry); // remove this reference
		return true;
	}


	/**
	 * Runs a backup
	 * Implicitly does the following tasks:
	 * 	- Saves the backup and any entries
	 *  - Builds up a task set if it doesn't exist
	 * -
	 */
	public function execute() {
		if($this->_mode == 'backup' && !$this->_backup->get('start',null)) {
			$date = new JDate();
			$this->_backup->set('start',$date->toMySQL());
		}
		$this->_backup->store();
		if(!$this->_taskset->get('tasksetid')) {
			if(!$this->_buildTaskSet()) {
				JError::raiseError(41, JText::_('Failed to create task set for backup'));
				return false;
			}
		}
		while($task =& $this->_taskset->getNextTask()) {
			$type = $task->get('type','');
			if(!$type) {
				JError::raiseWarning(100, JText::_('Invalid type used for backup task'));
				$task->delete();
				continue;
			}
			$instance =& $this->getAdapter($task->type);
			if(!$instance) {
				JError::raiseWarning(42, JText::_('Failed to load backup adapter for task').': '. $task->type);
				$task->delete();
				continue; // move to the next iteration
			}

			$task->setInstance($instance, true);
			$instance->{$this->_mode}($task->params);
			// we're all done
			$task->delete();
		}

		if($this->_mode == 'backup') {
			$date = new JDate();
			$this->_backup->set('end',$date->toMySQL());
			$this->_backup->store();
		}
		$this->_taskset->delete();
	}

}