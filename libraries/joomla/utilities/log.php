<?php

/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Utilities
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Joomla! Log Class
 *
 * This class hook0s into the global log configuration
 * settings to allow for user configured logging events to be sent
 * to where the user wishes it to be sent. On high load sites
 * SysLog is probably the best (pure PHP function), then the text
 * file based formats (CSV, W3C or plain FormattedText) and finally
 * MySQL offers the most features (e.g. rapid searching) but will incur
 * a performance hit due to INSERT being issued.
 *
 * @author Sam Moffatt <sam.moffatt@joomla.org>
 * @author Louis Landry <louis.landry@joomla.org>
 * @package Joomla.Framework
 * @subpackage Utilities
 * @final
 * @since 1.5
 */
class JLog extends JObject {
	
	/** @var array formats references to formatting objects
	 *  @access private */
	var $_formats = Array();
	/** @var array entries a list of logged entries
	 * @access protected
	 */
	var $_entries = Array();
	
	/**
	 * Returns a reference to the global log object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $log = & JLog::getInstance();</pre>
	 *
	 * @static
	 * @return	object	The JLog object.
	 * @since	1.5
	 */
	function & getInstance($options = null, $formats = null) {
		static $instances;
		$config = & JFactory :: getConfig();
		if(!$options) {
			$options = $config->getValue('config.log_options');
		}
		
		if(!$formats) { 
			$formats = $config->getValue('config.log_formats');
		}
		
		$sig = md5(print_r($options,1).print_r($formats,1));		
//		jimport('joomla.filesystem.path');
//		$path = JPath :: clean($path . DS . $file, false);
//		$sig = md5($path);

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[$sig])) {
			$instances[$sig] = new JLog($options, $formats);
		}

		return $instances[$sig];
	}
	
	function JLog($options, $formats) {
		if(is_string($formats)) {
			$formats = explode(',', $formats);
			foreach($formats as $key=>$format) {
				$formats[$key] = trim($format);
			}
		}
		if(is_array($formats)) {
			// We should have an array here
			// Params allow for CSV or Array
			foreach($formats as $format) {
				if($format) {
					eval('$this->_formats[] = JLog'.$format.'::getInstance($options);');
				}
			}
		}
	}
	
	/**
	 * Adds a log entry to sub formats and log cache
	 */
	function addLogEntry($entry) {
		foreach($this->_formats as $format) {
			$format->addLogEntry($entry);
		}
		$this->_entries[] = $entry;
	}
}

/**
 * Joomla! Log Entry class
 *
 * This class is designed to hold log entries for either writing
 * to an engine, or for supported engines, retrieving lists 
 * and building in memory (PHP based) search operations
 * 
 * @author Samuel Moffatt <sam.moffatt@joomla.org>
 * @package Joomla.Framework
 * @subpackage Utilities
 * @since 1.5
 */
class JLogEntry extends JObject {
	/** @var int logid Log Entry ID */
	var $logid = 0;
	/** @var string application Application responsible for log entry */
	var $application = '';
	/** @var string type Type of Entry */
	var $type = '';
	/** @var string priority Priority of entry ('panic', 'emerg', 'alert', 'crit', 'err', 'error', 'warn', 'warning', 'notice', 'info', 'debug', 'none') */
	var $priority = 'info';
	/** @var date entrydate Date of Entry */
	var $entrydate = '0000-00-00 00:00:00'; // YYYY-MM-DD HH:MM:SS
	/** @var string message Message to be logged */
	var $message = '';

	function JLogEntry($application='',$type='',$priority = 'info', $message = '', $entrydate = '', $logid = 0) {
		// TODO: Rewrite this because I stole it from 1.0.x
		// TODO: where I had a function that did the same
		//$this->mosDBTable('#__jlogger_entries', 'logid', $db);
		$this->application = $application;
		$this->type = $type;
		$this->priority = $priority;
		$this->message = $message;
		if(!$entrydate) {
			$this->entrydate = date('Y-m-d H:i:s');
		}
		$this->logid = $logid;
	}
}

/**
 * Joomla! Logging Format Base Class
 * 
 * This class is used to be the basis of logging format classes
 * to allow for defined functions to exist regardless of the
 * child class
 *
 * @author Sam Moffatt <sam.moffatt@joomla.org>
 * @package Joomla.Framework
 * @ssubpackage Utilities
 * @since 1.5
 */

class JLogFormat extends JObject {

	function JLogFormat() {

	}

	/**
	 * Defines the fields available for listing
	 * @return array an array of fields available for searching
	 */
	function getFields() {
		return Array ();
	}

	/**
	 * Determines if the format can handle search operations
	 * @return bool
	 */
	function isSearchable() {
		return false;
	}

	/**
	 * Determines if the format is writeable
	 * (e.g. a file may not be writable but is readable)
	 * @return bool status of write ability
	 */
	function isWriteable() {
		return false;
	}

	/**
	 * Determines if the format is readable
	 * A format may not be readable (e.g. syslog)
	 * @return bool status of read ability
	 */
	function isReadable() {
		return false;
	}

	/**
	 * Determines if the format can return an array of JLogEntries
	 * @return bool status of parseability
	 */
	function isParseable() {
		return false;
	}

	/**
	 * Adds a log entry to the format
	 */
	function addLogEntry($logentry) {

	}

	/**
	 * Set options
	 * @param array options Set internal options
	 * @abstract
	 */
	function setOpions($options) {

	}

}

/**
 * Joomla! MySQL Database Log class
 *
 * This class is designed to output logs to a specific MySQL database
 * table. Fields in this table are based on the SysLog style of
 * log output. This is designed to allow quick and easy searching.
 *
 * @author Sam Moffatt <sam.moffatt@joomla.org>
 * @package Joomla.Framework
 * @subpackage Utilities
 * @since 1.5
 */
 
class JLogMySQL extends JLogFormat {
	
	function JLogMySQL() {
		
	}
	
	function &getInstance() { 
		static $instances;
		$sig = md5('jlogmysql');
		if(!is_object($instances[$sig])) {
			$instances[$sig] = new JLogMySQL();
		}
		return $instances[$sig];
	}

/*	function &getInstance() {
		return new JLogMySQL();
	}*/
/*	function &getInstance() { 
		static $instances;
		$sig = md5('jlogmysql');
		if(!is_object($instances[$sig])) {
			$instances = new JLogMySQL();
		}
		return $instances[$sig];
	}*/

	function addLogEntry($entry) {
		$db =& JFactory::getDBO();
		$query = 'INSERT INTO #__log_entries VALUES (0,"'.$entry->application.'", "'.$entry->type.'", "'.$entry->priority.'", "'. $entry->entrydate .'", "'.$entry->message.'")';
		$db->setQuery($query);
		$db->Query();
	}
}

/**
 * Joomla! SysLog Log class
 *
 * This class is designed to call the PHP syslog function call which
 * is then sent to the system wide log system. For Linux/Unix based
 * systems this is the syslog subsystem, for the Windows based
 * implementations this can be found in the Event Log. For Windows,
 * permissions may prevent PHP from properly outputting messages.
 *
 * @author Sam Moffatt <sam.moffatt@joomla.org>
 * @package Joomla.Framework
 * @subpackage Utilities
 * @since 1.5
 */
class JLogSysLog extends JLogFormat {
	function JLogSysLog() {
		
	}
	
	function &getInstance() { 
		static $instances;
		$sig = md5('jlogsyslog');
		if(!is_object($instances[$sig])) {
			$instances[$sig] = new JLogSysLog();
		}
		return $instances[$sig];
	}

	function addLogEntry($entry) {
		syslog(constant('LOG_'.strtoupper($entry->priority)), $entry->application . ' ' . $entry->type . ': ' . $entry->message) or die('Syslog failed');
	}
}

/**
 * Joomla! Formatted Text File Log class
 *
 * This class is designed to use as a base for building formatted
 * text files for output. By default it emulates the SysLog style
 * format output. This is a disk based output format.
 *
 * Code borrowed from Louis's original implementation
 *
 * @author Sam Moffatt <sam.moffatt@joomla.org>
 * @author Louis Landry <louis.landry@joomla.org>
 * @package Joomla.Framework
 * @subpackage Utilities
 * @since 1.5
 */
class JLogFormattedText extends JLogFormat {
	/**
	 * Log File Pointer
	 * @var	resource
	 */
	var $_file;

	/**
	* Log File Path
	* @var	string
	*/
	var $_path;

	/**
	 * Log Format
	 * @var	string
	 */
	var $_format = "{DATE}\t{TIME}\t{APPLICATION}\t{PRIORITY}\t{TYPE}\t{MESSAGE}";

	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	string	$path		Log file path
	 * @param	array	$options	Log file options
	 * @since	1.5
	 */
	function JLogFormattedText($options) {
		// Set default values
		$this->setOptions($options);
	}

	/**
	 * Returns a reference to the global log object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $log = & JLogFormattedText::getInstance();</pre>
	 *
	 * @static
	 * @return	object	The JLog object.
	 * @since	1.5
	 */
	function & getInstance($options = null) {
		static $instances;

		$file = $options['file'] ? $options['file'] : 'error.log'; 
		$path = $options['path'] ? $options['path'] : null;

		// Set default path if not set
		if (!$path) {
			$config = & JFactory :: getConfig();
			$path = $config->getValue('config.log_path');
		}

		jimport('joomla.filesystem.path');
		$path = JPath :: clean($path . DS . $file, false);
		$options['path'] = $path;
		$sig = md5($path);
		$options['path'] = $path;
		
		if (!isset ($instances)) {
			$instances = array ();
		}
		
		if (empty ($instances[$sig])) {
			$instances[$sig] = new JLogFormattedText($options);
		}

		return $instances[$sig];
	}

	/**
	 * Set log file options
	 *
	 * @access	public
	 * @param	array	$options	Associative array of options to set
	 * @return	boolean				True if successful
	 * @since	1.5
	 */
	function setOptions($options) {
		if (isset ($options['format'])) {
			$this->_format = $options['format'];
		}
		if (isset ($options['path'])) {
			$this->_path = $options['path'];
		}
		//if (isset ($options['file'])) {
		//	$this->_file = $options['file'];
		//}
		return true;
	}

	function addLogEntry($entry) {
		// Set some default field values if not already set.
		if (!isset ($entry->clientip)) {
			$entry->clientip = $_SERVER['REMOTE_ADDR'];
		}
		if (!isset ($entry->date) || !isset($entry->time)) {
			if(isset($entry->entrydate)) {
				$dt = explode(' ', $entry->entrydate);
				$entry->date = $dt[0];
				$entry->time = $dt[1];
			}
		}

		// Ensure that the log entry keys are all uppercase
		$tmpentry = get_object_vars($entry);
		$tmpentry = array_change_key_case($tmpentry, CASE_UPPER);

		// Find all fields in the format string
		$fields = array ();
		$regex = "/{(.*?)}/i";
		preg_match_all($regex, $this->_format, $fields);

		// Fill in the field data
		$line = $this->_format;
		//print_r($tmpentry);
		//print_r($tmpentry);
		for ($i = 0; $i < count($fields[0]); $i++) {
			$line = str_replace($fields[0][$i], (isset ($tmpentry[$fields[1][$i]])) ? $tmpentry[$fields[1][$i]] : "-", $line);
		}

		// Write the log entry line
		if ($this->_openLog()) {
			if (!fputs($this->_file, $line . "\n")) {
				return false;
			}
		} else {
			return false;
		}
		return true;
	}

	/**
	 * Open the log file pointer and create the file if it doesn't exist
	 *
	 * @access 	public
	 * @return 	boolean	True on success
	 * @since	1.5
	 */
	function _openLog() {
		// Only open if not already opened...
		if (is_resource($this->_file)) {
			return true;
		}

		$date = date("Y-m-d");
		$time = date("H:i:s");
		
		if (!file_exists($this->_path)) {
			jimport("joomla.filesystem.folder");
			if (!JFolder :: create(dirname($this->_path))) {
				return false;
			}
			$header[] = "#Version: 1.0";
			$header[] = "#Date: " . $date . " " . $time;

			// Prepare the fields string
			$fields = str_replace("{", "", $this->_format);
			$fields = str_replace("}", "", $fields);
			$fields = strtolower($fields);
			$header[] = "#Fields: " . $fields;

			// Prepare the software string
			jimport("joomla.version");
			$version = new JVersion();
			$header[] = "#Software: " . $version->getLongVersion();

			$head = implode("\n", $header);
			$head .= "\n";
		} else {
			$head = false;
		}

		if (!$this->_file = fopen($this->_path, "a")) {
			return false;
		}
		if ($head) {
			if (!fputs($this->_file, $head)) {
				return false;
			}
		}
		// If we opened the file lets make sure we close it
		register_shutdown_function(array (
			& $this,
			'_closeLog'
		));
		return true;
	}

	/**
	 * Close the log file pointer
	 *
	 * @access 	public
	 * @return 	boolean	True on success
	 * @since	1.5
	 */
	function _closeLog() {
		if (is_resource($this->_file)) {
			fclose($this->_file);
		}
		return true;
	}
}

/**
 * Joomla! W3C Logging class
 *
 * This class is designed to build log files based on the
 * W3C specification at: http://www.w3.org/TR/WD-logfile.html
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage	Utilities
 * @since		1.5
 */
class JLogW3C extends JLogFormattedText {
	/**
	 * Log Format
	 * @var	string
	 */
	var $_format = "{DATE}\t{TIME}\t{PRIORITY}\t{CLIENTIP}\t{TYPE}\t{MESSAGE}";

	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	array	$options	Log file options
	 * @since	1.5
	 */
	function JLogW3C($options) {
		// Set default values
		//$this->_path = $path; //* @param	string	$path		Log file path
		$this->setOptions($options);
	}

	function & getInstance($options = null) {
		static $instances;
		
		$file = $options['file'] ? $options['file'] : 'error.w3c.log'; 
		$path = $options['path'] ? $options['path'] : null;
		
		// Set default path if not set
		if (!$path) {
			$config = & JFactory :: getConfig();
			$path = $config->getValue('config.log_path');
		}

		jimport('joomla.filesystem.path');
		$path = JPath :: clean($path . DS . $file, false);
		$sig = md5($path);
		$options['path'] = $path;
		
		if (!isset ($instances)) {
			$instances = array ();
		}
		
		if (empty ($instances[$sig])) {
			$instances[$sig] = new JLogW3C($options);
		}

		return $instances[$sig];
	}
}
?>
