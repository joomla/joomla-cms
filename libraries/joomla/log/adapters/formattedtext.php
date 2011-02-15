<?php
defined('_JEXEC') or die();

jimport('joomla.log.logformat');

/**
 * Joomla! Formatted Text File Log class
 *
 * This class is designed to use as a base for building formatted
 * text files for output. By default it emulates the SysLog style
 * format output. This is a disk based output format.
 *
 * @package Joomla.Framework
 * @subpackage Log
 * @since 1.7
 */
class JLogFormattedText extends JLogFormat {
	/**
	 * Log File Pointer
	 * @var	resource
	 */
	protected $file;

	/**
	* Log File Path
	* @var	string
	*/
	protected $path;

	/**
	 * Log Format
	 * @var	string
	 */
	protected $_format = "{DATE}\t{TIME}\t{APPLICATION}\t{PRIORITY}\t{TYPE}\t{MESSAGE}";

	/**
	 * Set log file options
	 *
	 * @access	public
	 * @param	array	$options	Associative array of options to set
	 * @return	boolean				True if successful
	 * @since	1.5
	 */
	public function setProperties($options) {
		if (isset ($options['format'])) {
			$this->_format = $options['format'];
		}
		if (isset ($options['file'])) {
			$file = $options['file'];
		} else {
			$file = 'error.php';
		}

                $path = isset($options['path']) ? $options['path'] : null;
                // Set default path if not set
                if (!$path) {
                        $config = & JFactory :: getConfig();
                        $path = $config->getValue('log_path');
                }
                $this->path = $path .'/'. $file;


		return true;
	}

	public function addLogEntry($entry) 
	{
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
			if (!fputs($this->file, $line . "\n")) {
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
		if (is_resource($this->file)) {
			return true;
		}

		$date = date("Y-m-d");
		$time = date("H:i:s");
		
		if (!file_exists($this->path)) {
			jimport("joomla.filesystem.folder");
			if (!JFolder :: create(dirname($this->path))) {
				return false;
			}
			$header[] = "#<?php die('Direct Access To Log Files Not Permitted'); ?>";
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

		if (!$this->file = fopen($this->path, "a")) {
			return false;
		}
		if ($head) {
			if (!fputs($this->file, $head)) {
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
		if (is_resource($this->file)) {
			fclose($this->file);
		}
		return true;
	}
}
