<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug\DataCollector;

use Joomla\CMS\Factory;
use Joomla\Plugin\System\Debug\AbstractDataCollector;

/**
 * LanguageStringsDataCollector
 *
 * @since  __DEPLOY_VERSION__
 */
class LanguageStringsCollector extends AbstractDataCollector
{
	/**
	 * Collector name.
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	private $name = 'languageStrings';

	/**
	 * Called by the DebugBar when data needs to be collected
	 *
	 * @since  __DEPLOY_VERSION__
	 *
	 * @return array Collected data
	 */
	public function collect(): array
	{
		return [
			'data' => $this->getData(),
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
			'untranslated' => [
				'icon' => 'question-circle',
				'widget' => 'PhpDebugBar.Widgets.KVListWidget',
				'map' => $this->name . '.data',
				'default' => ''
			],
			'untranslated:badge' => [
				'map' => $this->name . '.count',
				'default' => 'null'
			]
		];
	}

	/**
	 * Collect data.
	 *
	 * @return array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private function getData(): array
	{
		$orphans = Factory::getLanguage()->getOrphans();

		$data = [];

		foreach ($orphans as $orphan => $occurrences)
		{
			$data[$orphan] = $occurrences[0]['file'] ?? 'n/a';
		}

		return $data;
	}

	/**
	 * Get a count value.
	 *
	 * @return integer
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private function getCount(): int
	{
		return \count(Factory::getLanguage()->getOrphans());
	}
}
