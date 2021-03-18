<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Monitor;

use Joomla\Database\QueryMonitorInterface;

/**
 * Query monitor handling logging of queries.
 *
 * @since  __DEPLOY_VERSION__
 */
final class DebugMonitor implements QueryMonitorInterface
{
	/**
	 * The log of executed SQL statements call stacks by the database driver.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $callStacks = [];

	/**
	 * The log of executed SQL statements by the database driver.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $logs = [];

	/**
	 * The log of executed SQL statements memory usage (start and stop memory_get_usage) by the database driver.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $memoryLogs = [];

	/**
	 * The log of executed SQL statements timings (start and stop microtimes) by the database driver.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $timings = [];

	/**
	 * Act on a query being started.
	 *
	 * @param   string  $sql  The SQL to be executed.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function startQuery(string $sql): void
	{
		$this->logs[]       = $sql;
		$this->callStacks[] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$this->memoryLogs[] = memory_get_usage();
		$this->timings[]    = microtime(true);
	}

	/**
	 * Act on a query being stopped.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function stopQuery(): void
	{
		$this->timings[]    = microtime(true);
		$this->memoryLogs[] = memory_get_usage();
	}

	/**
	 * Get the logged call stacks.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getCallStacks(): array
	{
		return $this->callStacks;
	}

	/**
	 * Get the logged queries.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getLogs(): array
	{
		return $this->logs;
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

	/**
	 * Get the logged timings.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getTimings(): array
	{
		return $this->timings;
	}
}
