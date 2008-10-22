<?php

jimport('joomla.tasks.task');
/**
 * A set of tasks
 * @since 1.6
 */
class JTaskSet extends JTable { 
	var $tasksetid;
	var $taskname;
	var $extensionid;
	var $executionpage;
	var $landingpage;
	var $_startTime;
	
	function __construct(& $database) {
		parent::__construct('#__tasksets', 'tasksetid', $database);
		$app =& JFactory::getApplication();
		$this->_startTime = $app->get('startTime', JProfiler::getmicrotime());
		$max_php_run = ini_get('max_execution_time');
		if($max_php_run <> 0) {
			$this->_run_time = intval($max_php_run / 2);
		} else {
			$this->_run_time = 15;
		}
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
		$task = new Task($this->_db);
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
}