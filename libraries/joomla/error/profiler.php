<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Error
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class to assist in the process of benchmarking the execution
 * of sections of code to understand where time is being spent.
 *
 * @package		Joomla.Platform
 * @subpackage	Error
 * @since		1.0
 */
class JProfiler extends JObject
{
	/**
	 * The start time.
	 *
	 * @var int
	 */
	protected $_start = 0;

	/**
	 * The prefix to use in the output
	 *
	 * @var string
	 */
	protected $_prefix = '';

	/**
	 * The buffer of profiling messages.
	 *
	 * @var array
	 */
	protected $_buffer= null;

	/**
	 * @var float
	 * @since 1.6
	 */
	protected $_previous_time = 0.0;

	/**
	 * @var float
	 * @since 1.6
	 */
	protected $_previous_mem = 0.0;

	/**
	 * Boolean if the OS is Windows.
	 *
	 * @var boolean
	 * @since 1.6
	 */
	protected $_iswin = false;

	/**
	 * Constructor
	 *
	 * @param string Prefix for mark messages
	 */
	public function __construct($prefix = '')
	{
		$this->_start	= $this->getmicrotime();
		$this->_prefix	= $prefix;
		$this->_buffer	= array();
		$this->_iswin	= (substr(PHP_OS, 0, 3) == 'WIN');
	}

	/**
	 * Returns the global Profiler object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param	string		Prefix used to distinguish profiler objects.
	 * @return	JProfiler	The Profiler object.
	 */
	public static function getInstance($prefix = '')
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
	 * @param string A label for the time mark
	 * @return string Mark enclosed in <div> tags
	 */
	public function mark($label)
	{
		$current = self::getmicrotime() - $this->_start;
		if (function_exists('memory_get_usage')) {
			$current_mem = memory_get_usage() / 1048576;
			$mark = sprintf(
					'<code>%s %.3f seconds (+%.3f); %0.2f MB (+%0.2f) - %s</code>',
					$this->_prefix,
					$current,
					$current - $this->_previous_time,
					$current_mem,
					$current_mem - $this->_previous_mem,
					$label
					);
		}
		else {
			$mark = sprintf(
					'<code>%s %.3f seconds (+%.3f) - %s</code>',
					$this->_prefix,
					$current,
					$current - $this->_previous_time,
					$label
					);
		}

		$this->_previous_time = $current;
		$this->_previous_mem = $current_mem;
		$this->_buffer[] = $mark;

		return $mark;
	}

	/**
	 * Get the current time.
	 *
	 * @return float The current time
	 */
	public static function getmicrotime()
	{
		list($usec, $sec) = explode(' ', microtime());

		return ((float)$usec + (float)$sec);
	}

	/**
	 * Get information about current memory usage.
	 *
	 * @return	int		The memory usage
	 * @link	PHP_MANUAL#memory_get_usage
	 */
	public function getMemory()
	{
		if (function_exists('memory_get_usage')) {
			return memory_get_usage();
		}
		else {
			// Initialise variables.
			$output	= array();
			$pid	= getmypid();

			if ($this->_iswin) {
				// Windows workaround
				@exec('tasklist /FI "PID eq ' . $pid . '" /FO LIST', $output);
				if (!isset($output[5])) {
					$output[5] = null;
				}
				return substr($output[5], strpos($output[5], ':') + 1);
			}
			else {
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
	 * @return	array	Array of profiler marks
	 */
	public function getBuffer()
	{
		return $this->_buffer;
	}
}