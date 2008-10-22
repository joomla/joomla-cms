<?php

jimport('joomla.error.profiler');

/**
 * Individual task entry
 * @since 1.6
 */
class JTask extends JTable {
	var $taskid = 0;
	var $tasksetid = 0;
	var $data = '';
	var $offset = 0;
	var $total = 0;
	var $_parent;

	function __construct(& $db, &$parent, $taskid = 0, $tasksetid = 0, $data = '') {
		$this->taskid = $taskid;
		$this->tasksetid = $tasksetid;
		$this->data = $data;
		$this->_parent =& $parent;
		parent::__construct( '#__tasks', 'taskid', $db );
	}
	
	function load($pid=null) {
		$res = parent::load($pid);
		if($res) $this->data = unserialize($this->data); // pull the data back out
		return $res;
	}
	
	function store($updateNulls=false) {
		$this->data = serialize($this->data);
		$res = parent::store($updateNulls);
		$this->data = unserialize($this->data);
		return $res;
	}


	// legacy functions, validate relevance
	function toString() {
		return 'Task ' . $this->taskid .' executing; please see task set '. $this->tasksetid .';<br />';
	}

	function execute($callback, &$context=null) {
		global $mainframe;
		// $run_time, $startTime;
		if($context) $return = $context->$callback($this); else $return = $callback($this);
		
		if($return) {
			if(!$this->total || $this->offset >= $this->total) { $this->delete(); return false; }
			
			$this->store();
			$checkTime = JProfiler :: getmicrotime();
			if (($checkTime - $this->_parent->_startTime) >= $this->_parent->_run_time) {
				$link = $this->_parent->executionpage .'&taskset='.$this->tasksetid;
				echo '<a href="'.$link.'">'.JText::_('Next').'</a>';
				// mark:javascript autoprogress
				echo "<script language=\"JavaScript\" type=\"text/javascript\">window.setTimeout('location.href=\"" . $link . "\";',1000);</script>\n";
				echo '</div>';
				$mainframe->close();
				return true;
			}
	
			//$this->delete() or die($this->_db->getErrorMsg());
			return true;
		} else {
			$this->delete();
			return false;
		}
	}
}