<?php
/**
 * SQL File loader
 *
 * Loads an SQL file into a database
 * Heavily borrowed from Alexey Ozerov (BigDump v0.29b)
 *
 * PHP4/5
 *
 * Created on Oct 30, 2008
 *
 * @package Joomla.Framework
 * @subpackage Database.Loader
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 * @version SVN: $Id$
 */

// No direct access
defined('JPATH_BASE') or die();

jimport('joomla.tasks.task');
jimport('joomla.tasks.tasksuspendable');

class JDataLoaderSql extends JDataLoad implements JTaskSuspendable {
	/** JStream internal stream object */
	private $_stream;
	/** DBO */
	private $_dbo;
	/** Task Entry */
	private $_task;
	/** @var int How many chars are read per time */
	protected $data_chunk_length = 16384;
	/** @var int How many lines may be considered to be one query (except text lines) */
	protected $max_query_lines = 300;
	/** @var JStream Log file */
	private $_logger;

	// TODO: Document this
	protected $lines_per_session = 3000;
	protected $delay_per_session = 0;
	protected $yield_amount = 100; // run yield for this duration
	protected $total_queries = 0;
	protected $filename = '';
	protected $offset = 0;
	protected $start = 0;
	protected $_queries = 0;
	protected $_linenumber = 0;
	protected $comment = Array('#', '--' ,'/*!');
	protected $taskid = 0;
	protected $taskset = 0;

	/** Constructor */
	public function __construct($options) {
		if(!isset($options['filename'])) {
			$this->setError(42, 'Filename not set');
			return false;
		}

		$this->_stream =& JFactory::getStream();
		$this->setProperties($options);
		if(!$this->_stream->open($this->filename)) {
			$this->setError(43, 'Failed to open file: '. $this->_stream->getError());
			return false;
		}

		// Open up log file
		//$this->_logger =& JFactory::getStream();
		//$this->_logger->open('/tmp/datalog','a');

		$this->_dbo =& JFactory::getDBO();


		if($this->taskset || $this->taskid) {
			// TODO: If there is a task system available, redirect through that
			if($this->taskset && !$this->taskid) {
				// Add a new task to the task set and transfer control to the task set
				// and set the taskid
				$taskset = new JTaskSet($this->_dbo);
				$this->_task = $taskset->createTask();
			} else if($this->taskid) {
				// We have a task ID, so use that. We can find the taskset from the task
				$this->_task = new JTask($this->_dbo);
				$this->_task->load($this->taskid);
			}
		}

		// Hope these work
		@ini_set('auto_detect_line_endings', true);
		@set_time_limit(0);
	}

	/** Destructor */
	public function __destruct() {
		//$this->_logger->close();
		$this->_stream->close();
	}

	/** Suspend the task; required by JTaskSuspendable */
	public function suspendTask() {
		// TODO: Fill this function in
		$this->start = $this->_linenumber;
		$data = get_object_vars($this);
		$result = Array();
		foreach($data as $key=>$value) {
			if($key[0] == '_') continue;
			$result[$key] = $value;
		}
		return $result;
	}

	/** Restore the task;  required by JTaskSuspendable */
	public function restoreTask($options) {
		$this->setProperties($options);
	}

	/** Set the task; required by JTaskSuspendable */
	public function setTask(&$task) {
		$this->_task = $task;
		$this->taskid = $task->taskid;
		$this->taskset = $task->tasksetid;
	}

	/** Load the data */
	public function load($offset=-1) {
		if($offset > -1) $this->offset = $offset;
		if(!$this->_stream->seek($this->offset)) {
			$this->setError('JLoaderSQL::load: Failed to seek SQL file to '. $this->offset);
			return false;
		}
		$query = '';
		$querylines = 0;
		$inparents = false;
		$dropline = false;
		$this->_linenumber = $this->start;
		// Stay processing as long as the $linespersession is not reached or the query is still incomplete
		// or if the last query started with 'drop', the next one should be a create
		while($this->_linenumber < ($this->start + $this->lines_per_session) || $query != "" || $dropline) {
			// Read the whole next line
			$dumpline = "";
			while(!$this->_stream->eof() && substr($dumpline, -1) != "\n") {
				$dumpline .= $this->_stream->gets($this->data_chunk_length);
			}
			if($dumpline === "") break;
			// Handle DOS and Mac encoded linebreaks (I don't know if it will work on Win32 or Mac Servers)

			$dumpline = str_replace("\r\n", "\n", $dumpline);
			$dumpline = str_replace("\r", "\n", $dumpline);

			// Skip comments and blank lines only if NOT in parents
			if (!$inparents) {
				$skipline = false;
				reset($this->comment);
				foreach ($this->comment as $comment_value) {
					if (!$inparents && (trim($dumpline) == "" || strpos($dumpline, $comment_value) === 0)) {
						$skipline = true;
						break;
					}
				}
				if ($skipline) {
					$this->_linenumber++;
					continue;
				}

				// check for drop statements
				if(strpos($dumpline, 'DROP') === 0) {
					// if they're dropping something, don't yield as this may cause weird errors
					// hopefully the next valid line should contain a valid create statement
					$dropline = true;
				}
				// if we see a create or an insert then reset dropline
				// note: this is for testing, if not the above should work as an else
				if(strpos($dumpline, 'CREATE') === 0 || strpos($dumpline, 'INSERT') === 0) {
					$dropline = false;
				}
			}

			// Remove double back-slashes from the dumpline prior to count the quotes ('\\' can only be within strings)
			$dumpline_deslashed = str_replace ("\\\\","",$dumpline);

			// Count ' and \' in the dumpline to avoid query break within a text field ending by ;
			// Please don't use double quotes ('"')to surround strings, it wont work

      		$parents=substr_count ($dumpline_deslashed, "'")-substr_count ($dumpline_deslashed, "\\'");
      		if ($parents % 2 != 0) $inparents=!$inparents;

      		// Add the line to query
			$query .= $dumpline;
			// Don't count the line if in parents (text fields may include unlimited linebreaks)
      		if (!$inparents) $querylines++;

      		// Stop if query contains more lines as defined by $this->max_query_lines
			if ($querylines>$this->max_query_lines) {
				$this->setError('JLoaderSQL::load: Oversized query on line '. $this->_linenumber);
				return false;
			}

			// Execute query if end of query detected (; as last character) AND NOT in parents
			if (ereg(";$",trim($dumpline)) && !$inparents) {
				$this->offset = $this->_stream->tell(); // update the current location when we've finished a query
				$this->_dbo->setQuery(trim($query));
				//$line = $this->_dbo->getQuery()."\n";
				//$this->_logger->write($line);
				try {
					$this->_dbo->Query();
				} catch (JException $e) {
					echo '<p>'. $e->getMessage() .'</p>';
        			echo ("<p class=\"error\">Error at the line $this->_linenumber: ". trim($dumpline)."</p>\n");
          			echo ("<p>Query: ".trim(nl2br(htmlentities($query)))."</p>\n");
          			echo ("<p>MySQL: ".$db->getError()."</p>\n");
          			return false;
        		}
		        $this->total_queries++;
		        $this->_queries++;
		        $query='';
		        $querylines=0;
		        if(!$dropline && $this->_task && $this->_queries && $this->_queries % $this->yield_amount == 0) {
		        	$this->_task->yield();
		        }
      		}
      		$this->_linenumber++;
		}

		if ($this->_linenumber < ($this->start+$this->lines_per_session)) {
			if($this->_task) $this->_task->delete(); // remove this task when its done
			return true; // we're all finished!
		} else {
			if($this->taskid) {
				$this->_task->reload(); // force a task reload
			} else {
				JError::raiseError(500, JText::_('JLoaderSQL::load: Ran out of time during load'));
				return false;
			}
		}
	}
}
