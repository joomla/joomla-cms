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
	protected $files;
	
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
	
	// Should this be protected and let people override this with their own write file implementation?
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
	
	public function restore($options=Array()) {
		// TODO: Write restore function
	}
	
	public function remove($options=Array()) {
		// TODO: Write remove function
	}
	
	
}
