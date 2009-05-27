<?php

/**
 * @version		$Id: profiler.php 10707 2008-08-21 09:52:47Z eddieajau $
 * @package		Joomla.Framework
 * @subpackage	Error
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;


/**
 * Utility class to assist in the process of benchmarking the execution
 * of sections of code to understand where time is being spent.
 *
 * @package 	Joomla.Framework
 * @subpackage	Error
 * @since 1.0
 */
class JProfiler extends JObject
{
	/**
	 *
	 * @var int
	 */
	var $_start = 0;

	/**
	 *
	 * @var string
	 */
	var $_prefix = '';

	/**
	 *
	 * @var array
	 */
	var $_buffer= null;

	/**
	 * Constructor
	 *
	 * @access protected
	 * @param string Prefix for mark messages
	 */
	function __construct($prefix = '')
	{
		$this->_start = $this->getmicrotime();
		$this->_prefix = $prefix;
		$this->_buffer = array();
	}

	/**
	 * Returns a reference to the global Profiler object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $browser = & JProfiler::getInstance($prefix);</pre>
	 *
	 * @access public
	 * @param string Prefix used to distinguish profiler objects.
	 * @return JProfiler  The Profiler object.
	 */
	function &getInstance($prefix = '')
	{
		static $instances;

		if (!isset($instances)) {
			$instances = array();
		}

		if (empty($instances[$prefix])) {
			$instances[$prefix] = new JProfiler($prefix);
		}

		return $instances[$prefix];
	}

	/**
	 * Output a time mark
	 *
	 * The mark is returned as text enclosed in <div> tags
	 * with a CSS class of 'profiler'.
	 *
	 * @access public
	 * @param string A label for the time mark
	 * @return string Mark enclosed in <div> tags
	 */
	function mark($label)
	{
		$mark	= $this->_prefix." $label: ";
		$mark	.= sprintf('%.3f', $this->getmicrotime() - $this->_start) . ' seconds';
		if (function_exists('memory_get_usage')) {
			$mark	.= ', '.sprintf('%0.2f', memory_get_usage() / 1048576).' MB';
		}

		$this->_buffer[] = $mark;
		return $mark;
	}

	/**
	 * Get the current time.
	 *
	 * @access public
	 * @return float The current time
	 */
	function getmicrotime()
	{
		list($usec, $sec) = explode(' ', microtime());
		return ((float)$usec + (float)$sec);
	}

	/**
	 * Get information about current memory usage.
	 *
	 * @access public
	 * @return int The memory usage
	 * @link PHP_MANUAL#memory_get_usage
	 */
	function getMemory()
	{
		static $isWin;

		if (function_exists('memory_get_usage')) {
			return memory_get_usage();
		} else {
			// Determine if a windows server
			if (is_null($isWin)) {
				$isWin = (substr(PHP_OS, 0, 3) == 'WIN');
			}

			// Initialize variables
			$output = array();
			$pid = getmypid();

			if ($isWin) {
				// Windows workaround
				@exec('tasklist /FI "PID eq ' . $pid . '" /FO LIST', $output);
				if (!isset($output[5])) {
					$output[5] = null;
				}
				return substr($output[5], strpos($output[5], ':') + 1);
			} else {
				@exec("ps -o rss -p $pid", $output);
				return $output[1] *1024;
			}
		}
	}

	/**
	 * Get all profiler marks.
	 *
	 * Returns an array of all marks created since the Profiler object
	 * was instantiated.  Marks are strings as per {@link JProfiler::mark()}.
	 *
	 * @access public
	 * @return array Array of profiler marks
	 */
	function getBuffer() {
		return $this->_buffer;
	}
}