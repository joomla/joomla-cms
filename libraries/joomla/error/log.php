<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Error
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// No direct access
defined('JPATH_BASE') or die();

/**
 * Joomla! Logging class
 *
 * This class is designed to build log files based on the
 * W3C specification at: http://www.w3.org/TR/WD-logfile.html
 *
 * @package 	Joomla.Framework
 * @subpackage	Error
 * @since		1.5
 */
class JLog extends JObject
{
	/**
	 * Log File Pointer
	 * @var	resource
	 */
	protected $_file;

	/**
	 * Log File Path
	 * @var	string
	 */
	protected $_path;

	/**
	 * Log Format
	 * @var	string
	 */
	protected $_format = "{DATE}\t{TIME}\t{LEVEL}\t{C-IP}\t{STATUS}\t{COMMENT}";

	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	string	$path		Log file path
	 * @param	array	$options	Log file options
	 * @since	1.5
	 */
	protected function __construct($path, $options)
	{
		// Set default values
		$this->_path = $path;
		$this->setOptions($options);
	}

	/**
	 * Returns a reference to the global log object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $log = & JLog::getInstance();</pre>
	 *
	 * @access	public
	 * @static
	 * @return	object	The JLog object.
	 * @since	1.5
	 */
	public static function &getInstance($file = 'error.php', $options = null, $path = null)
	{
		static $instances;

		// Set default path if not set
		if (!$path)
		{
			$config =& JFactory::getConfig();
			$path = $config->getValue('config.log_path');
		}

		jimport('joomla.filesystem.path');
		$path = JPath::clean($path . DS . $file);
		$sig = md5($path);

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[$sig])) {
			$instances[$sig] = new JLog($path, $options);
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
	public function setOptions($options) {

		if (isset ($options['format'])) {
			$this->_format = $options['format'];
		}
		return true;
	}

	public function addEntry($entry)
	{
		// Set some default field values if not already set.
		$date =& JFactory::getDate();
		if (!isset ($entry['date'])) {

			$entry['date'] = $date->toFormat("%Y-%m-%d");
		}
		if (!isset ($entry['time'])) {

			$entry['time'] = $date->toFormat("%H:%M:%S");
		}
		if (!isset ($entry['c-ip'])) {
			$entry['c-ip'] = $_SERVER['REMOTE_ADDR'];
		}

		// Ensure that the log entry keys are all uppercase
		$entry = array_change_key_case($entry, CASE_UPPER);

		// Find all fields in the format string
		$fields = array ();
		$regex = "/{(.*?)}/i";
		preg_match_all($regex, $this->_format, $fields);

		// Fill in the field data
		$line = $this->_format;
		for ($i = 0; $i < count($fields[0]); $i++)
		{
			$line = str_replace($fields[0][$i], (isset ($entry[$fields[1][$i]])) ? $entry[$fields[1][$i]] : "-", $line);
		}

		// Write the log entry line
		if ($this->_openLog())
		{
			if (!fputs($this->_file, "\n" . $line)) {
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
	protected function _openLog()
	{
		// Only open if not already opened...
		if (is_resource($this->_file)) {
			return true;
		}

		$now =& JFactory::getDate();
		$date = $now->toMySQL();

		if (!file_exists($this->_path))
		{
			jimport("joomla.filesystem.folder");
			if (!JFolder :: create(dirname($this->_path))) {
				return false;
			}
			$header[] = "#<?php die('Direct Access To Log Files Not Permitted'); ?>";
			$header[] = "#Version: 1.0";
			$header[] = "#Date: " . $date;

			// Prepare the fields string
			$fields = str_replace("{", "", $this->_format);
			$fields = str_replace("}", "", $fields);
			$fields = strtolower($fields);
			$header[] = "#Fields: " . $fields;

			// Prepare the software string
			$version = new JVersion();
			$header[] = "#Software: " . $version->getLongVersion();

			$head = implode("\n", $header);
		} else {
			$head = false;
		}

		if (!$this->_file = fopen($this->_path, "a")) {
			return false;
		}
		if ($head)
		{
			if (!fputs($this->_file, $head)) {
				return false;
			}
		}

		// If we opened the file lets make sure we close it
		register_shutdown_function(array(&$this,'_closeLog'));
		return true;
	}

	/**
	 * Close the log file pointer
	 *
	 * @access 	public
	 * @return 	boolean	True on success
	 * @since	1.5
	 */
	public function _closeLog()
	{
		if (is_resource($this->_file)) {
			fclose($this->_file);
		}
		return true;
	}
}
