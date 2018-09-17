<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug;

use Joomla\Database\QueryMonitorInterface;

defined('_JEXEC') or die;

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
	private $callStacks = [];

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
	 * The log of executed SQL statements memory usage (start and stop memory_get_usage) by the database driver.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $memoryLogs = [];

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
			$this->memoryLogs[] = memory_get_usage();
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
			$this->memoryLogs[] = memory_get_usage();
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

	/**
	 * Get the logged memory logs.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getMemoryLogs(): array
	{
		return $this->memoryLogs;
	}
}
