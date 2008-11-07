<?php

jimport('joomla.tasks.task');
/**
 * A set of tasks
 * @since 1.6
 */
class JTaskSet extends JTable { 
	var $tasksetid;
	var $taskname;
	var $extension_id;
	var $executionpage;
	var $landingpage;
	var $_startTime;
	/** Time to run */
	protected $run_time;
	/** Maximum time to run */
	protected $max_time;
	/** Percentage Threshold */
	protected $threshold = 75;
	
	function __construct(& $database) {
		parent::__construct('#__tasksets', 'tasksetid', $database);
		$app =& JFactory::getApplication();
		$this->_startTime = $app->get('startTime', JProfiler::getmicrotime());
		$max_php_run = ini_get('max_execution_time');
		if($max_php_run > 0) {
			$this->max_time = $max_php_run;
			$this->run_time = intval($max_php_run * ($this->threshold / 100));
		} else {
			// set this to a safe default in case this version of PHP is buggy
			$this->max_time = 30;
			$this->run_time = 15;
		}
	}
	
	public function setThreshold($threshold) {
		$this->threshold = $threshold;
		$this->run_time = intval($this->max_time * ($this->threshold / 100));
	}

	function & getNextTask() {
		$this->_db->setQuery('SELECT taskid FROM #__tasks WHERE tasksetid = '. $this->tasksetid .' ORDER BY taskid LIMIT 0,1');
		$taskid = $this->_db->loadResult();// or die('Failed to find next task: ' . $this->db->getErrorMsg());
		$false = false;
		if(!$taskid) return $false;
		$task = new JTask($this->_db, $this);
		if($task->load($taskid)) return $task; else return $false; //die('Task '. $taskid .' failed to load:'. print_r($this,1));
	}

	function listAll() {
		$this->_db->setQuery('SELECT taskid FROM #__tasks WHERE tasksetid = '. $this->tasksetid.' ORDER BY taskid');
		$results = $this->_db->loadResultArray();
		$task = new JTask($this->_db);
		foreach ($results as $result) {
			$task->load($result);
			echo $task->toString();
		}
	}
	
	function countTasks() {
		$this->_db->setQuery('SELECT count(*) FROM #__tasks WHERE tasksetid = '. $this->tasksetid);
		return $this->_db->loadResult();
	}
	
	function run($callback, &$context=null) {
		while($task = $this->getNextTask()) $task->execute($callback, $context);
		$app =& JFactory::getApplication();
		$this->delete();
		if(!$this->landingpage) $this->landingpage = 'index.php';
		$app->redirect($this->landingpage);
	}
	
	public function &createTask() {
		$task = new JTask($this->_db, $this);
		$task->set('tasksetid', $this->tasksetid);
		return $task;
	}
	
	public function addTask($obj) {
		$task =& $this->createTask();
		$task->store();
		$task->setInstance($obj);
		$obj->setTask($task);
	}
	
	public function delete( $oid=null )
	{

		$k = $this->_tbl_key;
		if ($oid) {
			$this->$k = intval( $oid );
		}

		$query = 'DELETE FROM '.$this->_db->nameQuote( $this->_tbl ).
				' WHERE '.$this->_tbl_key.' = '. $this->_db->Quote($this->$k);
		$this->_db->setQuery( $query );

		try {
			$this->_db->query();
		} catch (JException $e) {
			$this->setError($e->getMessage());
			return false;
		}
		// Clean up any subtasks just in case
		$query = 'DELETE FROM #__tasks WHERE tasksetid = '. $this->_db->Quote($this->$k);
		$this->_db->setQuery($query);
		try {
			$this->_db->query();
			return true;
		} catch(JException $e) {
			$this->setError($e->getMessage());
			return false;
		}
	}
	
	public function redirect() {
		$app =& JFactory::getApplication();
		$app->redirect($this->landingpage);
	}
}