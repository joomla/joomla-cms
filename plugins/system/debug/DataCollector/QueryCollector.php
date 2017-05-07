<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   Copyright (C) 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug\DataCollector;

use Joomla\Plugin\System\Debug\AbstractDataCollector;

/**
 * QueryDataCollector
 *
 * @since  version
 */
class QueryCollector extends AbstractDataCollector
{
	private $name = 'queries';

	/**
	 * Called by the DebugBar when data needs to be collected
	 *
	 * @since  version
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
	 * @since  version
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
	 * @since  version
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
	 * @since version
	 */
	private function getData()
	{
		return \JFactory::getDbo()->getLog();
	}

	/**
	 * Get a count value.
	 *
	 * @return int
	 *
	 * @since version
	 */
	private function getCount()
	{
		return count(\JFactory::getDbo()->getLog());
	}
}
