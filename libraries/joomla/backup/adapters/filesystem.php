<?php
/**
 * Document Description
 * 
 * Document Long Description 
 * 
 * PHP4/5
 *  
 * Created on Jan 16, 2009
 * 
 * @package package_name
 * @license GNU/GPL http://www.gnu.org/licenses/gpl.html
 * @copyright 2009 OpenSourceMatters 
 * @version SVN: $Id$    
 */
 
defined('JPATH_ROOT') or die();

jimport('joomla.base.adapterinstance');
jimport('joomla.tasks.tasksuspendable');
jimport('joomla.backup.backupadapter');
jimport('joomla.filesystem.file');

class JBackupFilesystem extends JAdapterInstance implements JTaskSuspendable, JBackupAdapter {
	protected $db;
	protected $task;
	protected $options;
	protected $state;
	protected $stack;
	
	public function __construct(&$parent, &$db=null, $options=Array()) {
		parent::__construct($parent, $db);
		$this->options = $options;
		$this->state = Array('task'=>null,'options'=>Array(),'state'=>'initialised', 'entries'=>Array());
	}

	// generate an array that can be fed back to the object
	public function suspendTask() {
		return Array('options'=>$this->options, 'state'=>$this->state, 'stack'=>$this->stack);
	}
	
	// the array that suspendTask generated
	public function restoreTask($options) {
		$this->setProperties($options); // cheat :D
	}
	
	// set the task for this object
	public function setTask(&$task) {
		$this->task =& $task;
	}
	
	public function backup($options=Array()) {
		// If the task isn't set in the state, set it
		if(!$this->state['task']) {
			$this->state['task'] = 'initialised';
			// validate there is a destination around
			if(!array_key_exists('destination', $options)) {
				return false; // bad fugu!
			}
			// a list of things we want to exclude
			if(!array_key_exists('exclude', $options)) {
				$options['exclude'] = Array('backups', '.svn', 'CVS', '.DS_Store', '__MACOSX');
			}
			// a list of filters we want to match against things we want to exclude
			if(!array_key_exists('excludefilter', $options)) {
				$options['excludefilter'] = Array('\..*');
			}
			// where we start backing up from...
			if(!array_key_exists('root', $options)) {
				$options['root'] = JPATH_ROOT;
			}
			// a list of things we want
			if(!array_key_exists('filter', $options)) {
				$options['filter'] = '.';
			}
			// yay done
			$this->state['options'] = $options;
		}
		
		// validate that this exists
		if(!file_exists($options['root'])) {
			$this->setError('Invalid or non-existent root specified');
			return false;
		}
		
		// loop until we're done
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
	
	private function _findFolders() {
		$options =& $this->state['options'];
		// TODO: Add support for multiple disjoint folders (e.g. admin and site for a component)
		$folders = JFolder::folders($options['root'], $options['filter'], true, true, $options['exclude'], $options['excludefilter']);
		if(!is_array($folders)) {
			$folders = Array();
		}
		// ensure that the folder exists
		array_unshift($folders, $options['root']);
		sort(array_unique($folders)); // sort the folders and make sure they're unique
		$this->stack = $folders;
	}
	
	// Should this be protected and let people override this with their own write file implementation?
	private function _processDirectories() {
		$directories = count($this->stack);
		$options =& $this->state['options'];
		for($i = 0; $i < $directories; $i++) {
			// TODO: Look for weirdness here
			$files = JFolder::files($this->stack[$i], $options['filter'], false, true, $options['exclude']); //, $options['excludefilter']);
			$target = $options['destination'].DS.str_replace(JPATH_BASE, '', $this->stack[$i]);
			$res = JFolder::create($target);
			if(!$res) {
				echo 'Failed to create directory '. $target .'<br />';
				continue;
			}
			$fc = count($files);
			for($f = 0; $f < $fc; $f++) {
				$res = JFile::copy($files[$f], $target.DS.basename($files[$f]));
				if(!$res) {
					//JError::raiseWarning(1, 'Failed to copy '. $files[$f]);
					echo 'Failed to copy '. $files[$f].' to '. $target .'<br />';
				}
			}
			// TODO: Write function to open files and write them somewhere else
		}
	}
	
	public function restore($options=Array()) {
		// TODO: Write restore function
	}
	
	public function remove($options=Array()) {
		// TODO: Write remove function
	}
	
	
}
