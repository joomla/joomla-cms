<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Monitor;

use Joomla\Database\QueryMonitorInterface;

/**
 * Chained query monitor allowing multiple monitors to be executed.
 *
 * @since  __DEPLOY_VERSION__
 */
class ChainedMonitor implements QueryMonitorInterface
{
	/**
	 * The query monitors stored to this chain
	 *
	 * @var    QueryMonitorInterface[]
	 * @since  __DEPLOY_VERSION__
	 */
	private $monitors = [];

	/**
	 * Register a monitor to the chain.
	 *
	 * @param   QueryMonitorInterface  $monitor  The monitor to add.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addMonitor(QueryMonitorInterface $monitor)
	{
		$this->monitors[] = $monitor;
	}

	/**
	 * Act on a query being started.
	 *
	 * @param   string  $sql  The SQL to be executed.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function startQuery(string $sql)
	{
		foreach ($this->monitors as $monitor)
		{
			$monitor->startQuery($sql);
		}
	}

	/**
	 * Act on a query being stopped.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function stopQuery()
	{
		foreach ($this->monitors as $monitor)
		{
			$monitor->stopQuery();
		}
	}
}
