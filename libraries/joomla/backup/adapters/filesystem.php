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
			$this->state['task'] = 'backup';
			$this->state['options'] = $options;
		}
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
			}
		}
		return true;
	}
	
	private function _findFolders() {
		$options =& $this->state['options'];
		// TODO: Add support for multiple disjoint folders (e.g. admin and site for a component)
		$folders = JFolder::folders($options['root'], $options['filter'], true, true, $options['exclude'], $options['excludefilter']);
		sort($folders);
		$this->stack = $folders;
	}
	
	// Should this be protected and let people override this with their own write file implementation?
	private function _processDirectories() {
		$directories = count($this->stack);
		for($i = 0; $i < $directories; $i++) {
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
