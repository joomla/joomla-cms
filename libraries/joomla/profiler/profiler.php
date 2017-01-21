<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Profiler
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class to assist in the process of benchmarking the execution
 * of sections of code to understand where time is being spent.
 *
 * @since  11.1
 */
class JProfiler
{
	/**
	 * @var    integer  The start time.
	 * @since  12.1
	 */
	protected $start = 0;

	/**
	 * @var    string  The prefix to use in the output
	 * @since  12.1
	 */
	protected $prefix = '';

	/**
	 * @var    array  The buffer of profiling messages.
	 * @since  12.1
	 */
	protected $buffer = null;

	/**
	 * @var    array  The profiling messages.
	 * @since  12.1
	 */
	protected $marks = null;

	/**
	 * @var    float  The previous time marker
	 * @since  12.1
	 */
	protected $previousTime = 0.0;

	/**
	 * @var    float  The previous memory marker
	 * @since  12.1
	 */
	protected $previousMem = 0.0;

	/**
	 * @var    array  JProfiler instances container.
	 * @since  11.3
	 */
	protected static $instances = array();

	/**
	 * Constructor
	 *
	 * @param   string  $prefix  Prefix for mark messages
	 *
	 * @since   11.1
	 */
	public function __construct($prefix = '')
	{
		$this->start = microtime(1);
		$this->prefix = $prefix;
		$this->marks = array();
		$this->buffer = array();
	}

	/**
	 * Returns the global Profiler object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param   string  $prefix  Prefix used to distinguish profiler objects.
	 *
	 * @return  JProfiler  The Profiler object.
	 *
	 * @since   11.1
	 */
	public static function getInstance($prefix = '')
	{
		if (empty(self::$instances[$prefix]))
		{
			self::$instances[$prefix] = new static($prefix);
		}

		return self::$instances[$prefix];
	}

	/**
	 * Output a time mark
	 *
	 * @param   string  $label  A label for the time mark
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public function mark($label)
	{
		$current = microtime(1) - $this->start;
		$currentMem = memory_get_usage() / 1048576;

		$m = (object) array(
			'prefix' => $this->prefix,
			'time' => ($current > $this->previousTime ? '+' : '-') . (($current - $this->previousTime) * 1000),
			'totalTime' => ($current * 1000),
			'memory' => ($currentMem > $this->previousMem ? '+' : '-') . ($currentMem - $this->previousMem),
			'totalMemory' => $currentMem,
			'label' => $label,
		);
		$this->marks[] = $m;

		$mark = sprintf(
			'%s %.3f seconds (%.3f); %0.2f MB (%0.3f) - %s',
			$m->prefix,
			$m->totalTime / 1000,
			$m->time / 1000,
			$m->totalMemory,
			$m->memory,
			$m->label
		);
		$this->buffer[] = $mark;

		$this->previousTime = $current;
		$this->previousMem = $currentMem;

		return $mark;
	}

	/**
	 * Get the current time.
	 *
	 * @return  float The current time
	 *
	 * @since   11.1
	 * @deprecated  12.3 (Platform) & 4.0 (CMS) - Use PHP's microtime(1)
	 */
	public static function getmicrotime()
	{
		list ($usec, $sec) = explode(' ', microtime());

		return (float) $usec + (float) $sec;
	}

	/**
	 * Get information about current memory usage.
	 *
	 * @return  integer  The memory usage
	 *
	 * @link    PHP_MANUAL#memory_get_usage
	 * @since   11.1
	 * @deprecated  12.3 (Platform) & 4.0 (CMS) - Use PHP's native memory_get_usage()
	 */
	public function getMemory()
	{
		return memory_get_usage();
	}

	/**
	 * Get all profiler marks.
	 *
	 * Returns an array of all marks created since the Profiler object
	 * was instantiated.  Marks are objects as per {@link JProfiler::mark()}.
	 *
	 * @return  array  Array of profiler marks
	 *
	 * @since   11.1
	 */
	public function getMarks()
	{
		return $this->marks;
	}

	/**
	 * Get all profiler mark buffers.
	 *
	 * Returns an array of all mark buffers created since the Profiler object
	 * was instantiated.  Marks are strings as per {@link JProfiler::mark()}.
	 *
	 * @return  array  Array of profiler marks
	 *
	 * @since   11.1
	 */
	public function getBuffer()
	{
		return $this->buffer;
	}

	/**
	 * Sets the start time.
	 *
	 * @param   double  $startTime  Unix timestamp in microseconds for setting the Profiler start time.
	 * @param   int     $startMem   Memory amount in bytes for setting the Profiler start memory.
	 *
	 * @return  $this   For chaining
	 *
	 * @since   12.1
	 */
	public function setStart($startTime = 0, $startMem = 0)
	{
		$this->start       = (double) $startTime;
		$this->previousMem = (int) $startMem / 1048576;

		return $this;
	}
}
