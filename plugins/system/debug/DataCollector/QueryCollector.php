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
use Joomla\CMS\Factory;
use Joomla\Plugin\System\Debug\AbstractDataCollector;
use Joomla\Registry\Registry;

/**
 * QueryDataCollector
 *
 * @since  __DEPLOY_VERSION__
 */
class QueryCollector extends AbstractDataCollector
{
	/**
	 * Collector name.
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	private $name = 'queries';

	/**
	 * The query monitor.
	 *
	 * @var    DebugMonitor
	 * @since  __DEPLOY_VERSION__
	 */
	private $queryMonitor;

	/**
	 * Profile data.
	 *
	 * @var   array
	 * @since __DEPLOY_VERSION__
	 */
	private $profiles;

	/**
	 * Explain data.
	 *
	 * @var   array
	 * @since __DEPLOY_VERSION__
	 */
	private $explains;

	/**
	 * Constructor.
	 *
	 * @param   Registry      $params        Parameters.
	 * @param   DebugMonitor  $queryMonitor  Query monitor.
	 * @param   array         $profiles      Profile data.
	 * @param   array         $explains      Explain data
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct(Registry $params, DebugMonitor $queryMonitor, array $profiles, array $explains)
	{
		$this->queryMonitor = $queryMonitor;

		parent::__construct($params);

		$this->profiles = $profiles;
		$this->explains = $explains;
	}


	/**
	 * Called by the DebugBar when data needs to be collected
	 *
	 * @since  __DEPLOY_VERSION__
	 *
	 * @return array Collected data
	 */
	public function collect(): array
	{
		// @todo fetch the database object in a non deprecated way..
		$database = Factory::$database;

		return [
			'data'  => $this->queryMonitor->getLog(),
			'count' => \count($this->queryMonitor->getLog()),
			'prefix' => $database->getPrefix(),
			'serverType' => $database->getServerType(),
			'timings' => $this->queryMonitor->getTimings(),
			'stacks' => $this->queryMonitor->getCallStacks(),
			'profiles' => $this->profiles,
			'explains' => $this->explains,
		];
	}

	/**
	 * Returns the unique name of the collector
	 *
	 * @since  __DEPLOY_VERSION__
	 *
	 * @return string
	 */
	public function getName(): string
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
	public function getWidgets(): array
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
}
