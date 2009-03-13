<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Backup
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */
 
defined('JPATH_ROOT') or die();

jimport('joomla.base.adapterinstance');
jimport('joomla.tasks.tasksuspendable');
jimport('joomla.backup.backupadapter');
jimport('joomla.filesystem.file');

/**
 * Filesystem backup adapter
 * @package Joomla.Framework
 * @subpackage Backup
 * @since 1.6
 */
class JBackupFilesystem extends JAdapterInstance implements JTaskSuspendable, JBackupAdapter {
	/**
	 * Database object
	 * @var JDatabase
	 */
	protected $db;
	
	/**
	 * Task Object
	 * @var JTask
	 */
	protected $task;
	
	/**
	 * Copy of options
	 * @var array
	 */
	protected $options;
	
	/**
	 * Current State
	 * @var string
	 */
	protected $state;
	
	/**
	 * Stack of items being worked upon
	 * @var array
	 */
	protected $stack;
	
	/**
	 * List of files to be backed up
	 * @var array
	 */
	protected $files;
	
	public function __construct(&$parent, &$db=null, $options=Array()) {
		parent::__construct($parent, $db);
		$this->options = $options;
		$this->state = Array('task'=>null,'options'=>Array(),'state'=>'initialised', 'entries'=>Array());
	}

	/**
	 * Suspend the filesystem backup (called from a yield)
	 * @return array Suspended items
	 */
	public function suspendTask() {
		// generate an array that can be fed back to the object
		return Array('options'=>$this->options, 'state'=>$this->state, 'stack'=>$this->stack, 'files'=>$this->files);
	}
	
	/**
	 * Restore this task back
	 * @param array the array that suspendTask generated
	 */
	public function restoreTask($options) {
		$this->setProperties($options); // cheat :D
	}
	
	/**
	 * set the task for this object
	 * @param JTask A reference to the task for this object
	 */ 
	public function setTask(&$task) {
		$this->task =& $task;
	}
	
	/**
	 * Execute the backup
	 * @param $options['destination'] string Location of where to backup
	 * @param $options['source'] array Location of items to backup
	 * @param $options['exclude'] array List of items to exclude
	 * @param $options['excludefilter'] array List of regexp's to run over to exclude items
	 * @param $options['root'] string Root of backup
	 * @param $options['filter'] string Regexp filter to handle inclusion (default is .)
	 * @return boolean result of backup
	 */
	public function backup($options=Array()) {
		// If the task isn't set in the state, set it
		if (!$this->state['task']) {
			$this->state['task'] = 'initialised';
			// validate there is a destination around
			if (!array_key_exists('destination', $options)) {
				return false; // bad fugu!
			}
			
			if (!file_exists($options['destination'])) {
				if (!JFolder::create($options['destination'])) {
					JError::raiseError(1000, JText::_('Failed to create backup destination'));
					return false;
				}
			}
			
			
			if (!array_key_exists('source', $options)) {
				return false; // we don't know where to start!
			}
			
			// a list of things we want to exclude
			if (!array_key_exists('exclude', $options)) {
				$options['exclude'] = Array('backups', '.svn', 'CVS', '.DS_Store', '__MACOSX');
			}
			// a list of filters we want to match against things we want to exclude
			if (!array_key_exists('excludefilter', $options)) {
				// TODO: Check if it needs to be \~ or if just ~ works properly
				// ignore hidden files and backups
				$options['excludefilter'] = Array('^\..*', '.*~$');
			}
			// where we start backing up from...
			if (!array_key_exists('root', $options)) {
				$options['root'] = JPATH_ROOT;
			}
			// a list of things we want
			if (!array_key_exists('filter', $options)) {
				$options['filter'] = '.';
			}
			// yay done
			$this->state['options'] = $options;
		}
		
		// validate that this exists
		if (!file_exists($options['root'])) {
			$this->setError('Invalid or non-existent root specified');
			return false;
		}
		
		// loop until we're done
		while($this->state['task'] != 'finished') {
			switch($this->state['task']) {
				case 'initialised': // when we're starting we need to find folders
					$this->_findFolders();
					$this->state['task'] = 'processdirectories';
					$this->task->yield(); // yield after this point before we copy directories
					break;
				case 'processdirectories': // once we've got a folder listing, process them
					$this->_processDirectories();
					$this->state['task'] = 'finished';
					// we don't yield here because we're done
					break;
				default:
					JError::raiseError(500, JText::sprintf('JBackupFileSystem quit due to invalid state "%s"', $this->state['task']));
					$this->state['task'] = 'finished';
					break;
			}
		}
		return true;
	}
	
	/**
	 * Find folders in the source
	 */
	private function _findFolders() {
		$options =& $this->state['options'];
		if (!is_array($options['source'])) {
			$folders = JFolder::folders($options['source'], $options['filter'], true, true, $options['exclude'], $options['excludefilter']);
		} else {
			$folders = Array();
			foreach($options['source'] as $source) {
				$folders = array_merge($folders, JFolder::folders($options['source'], $options['filter'], true, true, $options['exclude'], $options['excludefilter'])); 
			}
		}
		
		if (!is_array($folders)) {
			$folders = Array();
		}
		// ensure that the folder exists
		array_unshift($folders, $options['source']);
		$folders = array_unique($folders); // sort the folders and make sure they're unique
		rsort($folders);
		$this->stack = $folders; // reverse the array since array_pop is better than array_shift
	}
	
	/**
	 * Process directories looking for files in folders
	 */
	private function _processDirectories() {
		$options =& $this->state['options'];
		// get the last item on the stack but don't remove it until we're done
		while($directory = end($this->stack)) {
			// if the files list is empty, populate
			if (empty($this->files)) {
				$this->files = JFolder::files($directory, $options['filter'], false, true, $options['exclude'], $options['excludefilter']);
				rsort($this->files);
			}
			$target = $options['destination'].DS.str_replace($options['root'], '', $directory);
			$res = JFolder::create($target);
			if (!$res) {
				//echo 'Failed to create directory '. $target .'<br />';
				JError::raiseError(2, JText::sprintf('Failed to create directory: %s', $target));
				continue;
			}
			while(($file = array_pop($this->files)) != null) {
				$res = JFile::copy($file, $target.DS.basename($file));
				if (!$res) {
					JError::raiseWarning(1, JText::sprintf('Failed to backup "%s"', $file));
					//echo 'Failed to copy '. $files[$f].' to '. $target .'<br />';
				}
				if (count($this->files)) $this->task->yield();
			}
			// remove the item off the stack
			array_pop($this->stack);
		}
	}
	
	/**
	 * Restore the backup
	 * @param $options['destination'] string Location of where to backup
	 * @param $options['source'] array Location of items to backup
	 * @param $options['exclude'] array List of items to exclude
	 * @param $options['excludefilter'] array List of regexp's to run over to exclude items
	 * @param $options['root'] string Root of backup
	 * @param $options['filter'] string Regexp filter to handle inclusion (default is .)
	 * @param $options['mode'] string Mode that the restore is taking (merge or replace)
	 * @return boolean
	 */
	public function restore($options=Array()) {
		// If the task isn't set in the state, set it
		if(!$this->state['task']) {
			$this->state['task'] = 'initialised';
			// validate there is a destination around
			if(!array_key_exists('destination', $options)) {
				return false; // bad fugu!
			}
			
			if(!file_exists($options['destination'])) {
				return false; // the destination of the backup doesn't exist, can't restore
			}
			
			
			if(!array_key_exists('source', $options)) {
				return false; // we don't know where to dump stuff again
			}
			
			if(!array_key_exists('mode', $options)) {
				$options['mode'] = 'merge';
			}
			
			// convert the source to an array if it isn't already
			if(!is_array($options['source'])) {
				$options['source'] = array($options['source']);
			}
			
			// Replace mode works ala Mac OS X copying; the original folder is removed
			if($options['mode'] == 'replace') {
				foreach($options['source'] as $source) {
					// delete the folder
					JFolder::delete($source);
					// then put it back
					JFolder::create($source);
				}
			}
			
			// a list of things we want to exclude
			if (!array_key_exists('exclude', $options)) {
				$options['exclude'] = Array('backups', '.svn', 'CVS', '.DS_Store', '__MACOSX');
			}
			// a list of filters we want to match against things we want to exclude
			if (!array_key_exists('excludefilter', $options)) {
				// TODO: Check if it needs to be \~ or if just ~ works properly
				// ignore hidden files and backups
				$options['excludefilter'] = Array('^\..*', '.*~$');
			}
			// where we start backing up from...
			if (!array_key_exists('root', $options)) {
				$options['root'] = JPATH_ROOT;
			}
			// a list of things we want
			if (!array_key_exists('filter', $options)) {
				$options['filter'] = '.';
			}
			
			// Swap the root and destination around
			// We do this since the old root is where the files came from
			// and the destination is where they are now; so we do the
			// reverse of the backup operation; funky eh?
			$root = $options['root'];
			$destination = $options['destination'];
			$options['root'] = $destination;
			$options['destination'] = $root;
			$options['source'] = $destination;
			$this->state['options'] = $options;
		}
		
		// loop until we're done; this is the same as the normal backup
		while($this->state['task'] != 'finished') {
			switch($this->state['task']) {
				case 'initialised':
					$this->_findFolders();
					$this->state['task'] = 'processdirectories';
					$this->task->yield(); // yield after this point before we copy directories
					break;
				case 'processdirectories':
					$this->_processDirectories();
					$this->state['task'] = 'finished';
					// we don't yield here because we're done
					break;
				default:
					JError::raiseError(500, JText::sprintf('JBackupFileSystem quit due to invalid state "%s"', $this->state['task']));
					$this->state['task'] = 'finished';
					break;
			}
		}
		return true;
	}
	
	/**
	 * Remove a backup
	 * @param $options['destination'] string Destination of the backup (location to remove)
	 */
	public function remove($options=Array()) {
		return JFolder::delete($options['destination']);
	}
}
