<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   Copyright (C) 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug\DataCollector;

use DebugMonitor;
use Joomla\Plugin\System\Debug\AbstractDataCollector;
use Joomla\Registry\Registry;

/**
 * QueryDataCollector
 *
 * @since  __DEPLOY_VERSION__
 */
class QueryCollector extends AbstractDataCollector
{
	private $name = 'queries';

	/**
	 * The query monitor.
	 *
	 * @var    DebugMonitor
	 * @since  __DEPLOY_VERSION__
	 */
	private $queryMonitor;

	/**
	 * Constructor.
	 *
	 * @param   Registry      $params        Parameters.
	 * @param   DebugMonitor  $queryMonitor  Query monitor.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct(Registry $params, DebugMonitor $queryMonitor)
	{
		$this->queryMonitor = $queryMonitor;

		return parent::__construct($params);
	}


	/**
	 * Called by the DebugBar when data needs to be collected
	 *
	 * @since  __DEPLOY_VERSION__
	 *
	 * @return array Collected data
	 */
	public function collect()
	{
		return [
			'data'  => $this->getData(),
			'count' => $this->getCount(),
		];
	}

	/**
	 * Returns the unique name of the collector
	 *
	 * @since  __DEPLOY_VERSION__
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns a hash where keys are control names and their values
	 * an array of options as defined in {@see DebugBar\JavascriptRenderer::addControl()}
	 *
	 * @since  __DEPLOY_VERSION__
	 *
	 * @return array
	 */
	public function getWidgets()
	{
		return [
			'queries'       => [
				'icon' => 'database',
				'widget'  => 'PhpDebugBar.Widgets.VariableListWidget',
				'map'     => $this->name . '.data',
				'default' => '[]',
			],
			'queries:badge' => [
				'map'     => $this->name . '.count',
				'default' => 'null',
			],
		];
	}

	/**
	 * Collect data.
	 *
	 * @return array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private function getData()
	{
		return $this->queryMonitor->getLog();
	}

	/**
	 * Get a count value.
	 *
	 * @return int
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private function getCount()
	{
		return count($this->queryMonitor->getLog());
	}
}
