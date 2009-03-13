<?php

defined('JPATH_BASE') or die();

jimport('joomla.base.adapterinstance');
jimport('joomla.tasks.tasksuspendable');
jimport('joomla.backup.backupadapter');

/**
 * SQL backup adapter
 * @package Joomla.Framework
 * @subpackage Backup
 * @since 1.6
 */
class JBackupSql extends JAdapterInstance implements JTaskSuspendable, JBackupAdapter {
	/**
	 * Amount of queries before yielding
	 * @var int
	 */
	protected $yield_amount = 100;
	
	/**
	 * A copy of the DB object
	 * @var JDatabase
	 */
	protected $db;
	
	/**
	 * A copy of the task instance
	 * @var JTask
	 */
	protected $task;
	
	/**
	 * The options for this instance
	 * @var array
	 */
	protected $options;
	
	/**
	 * Constructor
	 * @param object A reference to the parent adapter
	 * @param object A reference to the database
	 * @param array Options to use for this adapter
	 */
	public function __construct(&$parent, &$db=null, $options=Array()) {
		parent::__construct($parent, $db);
		$this->options = $options;
	}

	/**
	 * Set the task for the backup
	 * @param JTask A task object to set for this instance
	 */
	public function setTask(&$task) {
		$this->task =& $task;
	}

	/**
	 * Suspend the current task
	 */
	public function suspendTask() {
		return $this->options;
	}

	/**
	 * Return the task
	 * @param array Options to set for the task
	 */
	public function restoreTask($options) {
		$this->options = $options;
	}

	/**
	 * Run a back up with a set of tables
	 * @param $options['tables'] The tables to backup; if blank all tables
	 * @param $options['destination'] Destination folder to write files too; required
	 * @param $options['filename'] Backup filename; default is name of data.sql
	 * @param $options['create_table'] Dump out create table commands as well
	 * @param $options['replace_prefix'] Replace the DB prefix with #__
	 * @return bool Result of backups, true on success, false on failure
	 */
	public function backup($options=Array()) {
		// Do some simple param checks and settings
		if (!is_array($options)) return false; // if we don't have an array bail
		if (!isset($options['destination'])) return false; // if we don't have a dest bail
		if (!isset($options['filename'])) $options['filename'] = 'data.sql'; // and this
		if (!isset($options['replace_prefix'])) $options['replace_prefix'] = 0; // replace jos_ with #__
		if (!isset($options['create_table'])) $options['create_table'] = 1; // append create table
		if (!isset($options['droptable'])) $options['droptable'] = 1; // append drop table
		
		if (!file_exists($options['destination'])) {
			if (!JFolder::create($options['destination'])) {
				JError::raiseError(1000, JText::_('Failed to create backup destination'));
				return false;
			}
		}
		
		$this->db->setQuery('SET sql_quote_show_create = 1;');
		$this->db->Query();
		$config =& JFactory::getConfig();
		$prefix = $config->getValue('config.dbprefix');
		$tables = $this->db->getTableList(); // load all tables in database
		// check if this is set and contains rows otherwise set it to all of the tables
		if (!isset($options['tables']) || !count($options['tables'])) {
			$options['tables'] = Array(); // set this
			foreach($tables as $tn) {
				// make sure we get the right tables based on prefix
				if (preg_match("/^".$prefix."/i", $tn)) {
					$options['tables'][] = $tn;
				}
			}
		}
		$output =& JFactory::getStream();
		$filename = $options['destination'].DS.$options['filename'];
		// TODO: Change this to 'a' when we resume a task
		if (!$output->open($filename,'w')) return false;

		foreach($options['tables'] as $table) {
			// if the table isn't in our table list ignore it
			if (!in_array($table, $tables)) continue;

			if ($options['create_table']) {
				if ($options['droptable']) {
					$line  = "DROP TABLE IF EXISTS `";
					$line .= ($options['replace_prefix'] ? str_replace($prefix,'#__', $table) : $table);
					$line .= "`;\n";
					$output->write($line);
				}
				$this->db->setQuery('SHOW CREATE TABLE '. $table);
				$create = $this->db->loadRow();
				$create = $create[1].";\n\n"; // ignore the table name
				$create = $options['replace_prefix'] ? str_replace($prefix,'#__', $create) : $create;
				$output->write($create);
			}
			$this->db->setQuery('SELECT COUNT(*) FROM '. $table);
			$rows = $this->db->loadResult();
			$count = 0;
			if ($rows) {
				do {
					$this->db->setQuery('SELECT * FROM '. $table, $count, 1);
					$row = $this->db->loadRow();
					$tablename = $options['replace_prefix'] ? str_replace($prefix,'#__', $table) : $table;
					$line = 'INSERT INTO `'. $tablename .'` VALUES(';
					$line .= implode(',', array_map(array($this->db, 'Quote'), $row));
					$line .= ");\n";
					$output->write($line);
					$count++;
					if ($this->task && !($count % $this->yield_amount))  $this->task->yield(); // check if refresh/reload is required
				} while(($rows - $count) > 0);
			}
			$nl = "\n\n\n";
			$output->write($nl);
		}
		$output->close();
		return true;
	}


	/**
	 * Run a restore with a set of tables
	 * @param $options['tables'] The tables to restore; if blank all tables
	 * @param $options['prefix'] Table prefix of the backups
	 * @return bool Result of the restore: true on success, false on failure
	 */
	public function restore($options=Array()) {
		// Do some simple param checks and settings
		if (!is_array($options)) return false; // if we don't have an array bail
		if (!isset($options['destination'])) return false; // if we don't have a dest bail
		if (!isset($options['filename'])) $options['filename'] = 'data.sql'; // and this
		jimport('joomla.database.dataload');
		$loader =& JDataLoad::getInstance(Array('driver'=>'sql','filename'=>$options['destination'].DS.$options['filename']));
		if ($loader INSTANCEOF JException) {
			JError::raiseWarning(1001, JText::sprintf('Failed to create data load adapter "%s"', 'sql'));
			return false;
		} else {
			if (!$loader->load()) {
				JError::raiseWarning(1002, JText::sprintf('Data Load failed: %s', $loader->getError()));
				return false;
 			}
		}
		return true;
	}

	/**
	 * Remove a given backup
	 * @param $options['destination'] Destination folder to delete files from; required
	 * @param $options['filename'] Backup filename; default is name of data.sql	 *
	 */
	public function remove($options=Array()) {
		// Do some simple param checks and settings
		if (!is_array($options)) return false; // if we don't have an array bail
		if (!isset($options['destination'])) return false; // if we don't have a dest bail
		if (!isset($options['filename'])) $options['filename'] = 'data.sql'; // and this
		return JFile::delete($options['destination'].DS.$options['filename']);
	}
}