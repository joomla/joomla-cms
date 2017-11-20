<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Database\QueryMonitorInterface;

/**
 * Query monitor for the debug plugin
 *
 * @since  4.0.0
 */
final class DebugMonitor implements QueryMonitorInterface
{
	/**
	 * The log of executed SQL statements call stacks by the database driver.
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $callStacks = [];

	/**
	 * Flag if this monitor is collecting profile data
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	private $enabled;

	/**
	 * The log of executed SQL statements by the database driver.
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	private $log = [];

	/**
	 * The log of executed SQL statements timings (start and stop microtimes) by the database driver.
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	private $timings = [];

	/**
	 * Monitor constructor
	 *
	 * @param   boolean  $enabled  Flag if the monitor should collect profiler data
	 *
	 * @since   4.0.0
	 */
	public function __construct($enabled = false)
	{
		$this->enabled = $enabled;
	}

	/**
	 * Act on a query being started.
	 *
	 * @param   string  $sql  The SQL to be executed.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function startQuery(string $sql)
	{
		if ($this->enabled)
		{
			$this->log[]     = $sql;
			$this->timings[] = microtime(true);
		}
	}

	/**
	 * Act on a query being stopped.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function stopQuery()
	{
		if ($this->enabled)
		{
			$this->timings[]    = microtime(true);
			$this->callStacks[] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		}
	}

	/**
	 * Get the logged call stacks.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public function getCallStacks()
	{
		return $this->callStacks;
	}

	/**
	 * Get the logged queries.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public function getLog()
	{
		return $this->log;
	}

	/**
	 * Get the logged timings.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public function getTimings()
	{
		return $this->timings;
	}
}
