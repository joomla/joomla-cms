<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug\DataCollector;

use Joomla\CMS\Factory;
use Joomla\Plugin\System\Debug\AbstractDataCollector;

/**
 * SessionDataCollector
 *
 * @since  4.0.0
 */
class SessionCollector  extends AbstractDataCollector
{
	/**
	 * Collector name.
	 *
	 * @var   string
	 * @since 4.0.0
	 */
	private $name = 'session';

	/**
	 * Called by the DebugBar when data needs to be collected
	 *
	 * @since  4.0.0
	 *
	 * @return array Collected data
	 */
	public function collect()
	{
		$data = [];

		foreach (Factory::getApplication()->getSession()->all() as $key => $value)
		{
			$data[$key] = $this->getDataFormatter()->formatVar($value);
		}

		return ['data' => $data];
	}

	/**
	 * Returns the unique name of the collector
	 *
	 * @since  4.0.0
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns a hash where keys are control names and their values
	 * an array of options as defined in {@see \DebugBar\JavascriptRenderer::addControl()}
	 *
	 * @since  4.0.0
	 *
	 * @return array
	 */
	public function getWidgets()
	{
		return [
			'session' => [
				'icon' => 'key',
				'widget' => 'PhpDebugBar.Widgets.VariableListWidget',
				'map' => $this->name . '.data',
				'default' => '[]',
			],
		];
	}
}
