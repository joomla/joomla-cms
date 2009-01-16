<?php

jimport('joomla.error.profiler');

/**
 * Individual task entry
 * @since 1.6
 */
class JTask extends JTable {
	private $_quanta = Array();
	private $_lasttick = 0;
	/** Duration between yields; overwritten each yield; Requires two yields to calculated */
	private $_duration = 0;
	private $_instance = null;

	protected $taskid = 0;
	protected $tasksetid = 0;
	protected $data = '';
	protected $offset = 0;
	protected $total = 0;
	protected $params = '';
	protected $type = '';
	/** The parent task set for this task */
	private $_parent;

	public function __construct(&$db=null, &$parent=null, $taskid = 0, $tasksetid = 0, $data = '') {
		if($db != null) {
			$this->taskid = $taskid;
			$this->tasksetid = $tasksetid;
			$this->data = $data;
			if($parent != null) {
				$this->_parent =& $parent;
			} else if($tasksetid) {
				$this->_parent = new JTaskSet($db);
				$this->_parent->load($tasksetid);
			}
			parent::__construct( '#__tasks', 'taskid', $db );
		}
	}

	public function setParent(&$parent) {
		$this->_parent = $parent;
	}

	public function setInstance(&$instance, $restore=false) {
		$this->_instance =& $instance;
		$this->_instance->setTask($this);
		if($restore) {
			$this->_instance->restoreTask($this->data);
		}
	}

	public function load($pid=null) {
		$res = parent::load($pid);
		if($res) {
			$this->data = unserialize($this->data); // pull the data back out
			$this->params = unserialize($this->params); // params too
		}
		return $res;
	}

	public function store($updateNulls=false) {
		$this->params = serialize($this->params);
		$this->data = serialize($this->data);
		$res = parent::store($updateNulls);
		$this->data = unserialize($this->data);
		$this->params = unserialize($this->params);
		return $res;
	}

	public function yield() {
		$now = JProfiler::getmicrotime();
		if($this->_lasttick) {
			$this->_quanta[] = $now - $this->_lasttick;
			$this->duration = ceil(array_sum($this->_quanta) / count($this->_quanta));
		}
		$this->_lasttick = $now; // set the last tick
		
		// check if we're over the run time now
		// OR if now plus our average duration will put us over the max time
		if (($now - $this->_parent->_startTime) >= $this->_parent->get('run_time',15)
			|| (($now - $this->_parent->_startTime) + $this->_duration) > $this->_parent->get('max_time', 30)) {
				$this->reload();
		}
	}

	// TODO: redo this function
	public function reload() {
		if($this->_instance) $this->data = $this->_instance->suspendTask();
		$this->store(); // save ourselves before we reload
		$link = $this->_parent->execution_page .'&taskset='.$this->tasksetid;
		echo '<a href="'.$link.'">'.JText::_('Next').'</a>';
		// mark:javascript autoprogress
		echo "<script language=\"JavaScript\" type=\"text/javascript\">window.setTimeout('location.href=\"" . $link . "\";',1000);</script>\n";
		echo '</div>';
		$mainframe =& JFactory::getApplication();
		$mainframe->close();
	}
}