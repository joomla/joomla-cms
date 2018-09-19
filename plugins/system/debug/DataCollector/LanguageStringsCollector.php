<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug\DataCollector;

use DebugBar\DataCollector\AssetProvider;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\Plugin\System\Debug\AbstractDataCollector;

/**
 * LanguageStringsDataCollector
 *
 * @since  __DEPLOY_VERSION__
 */
class LanguageStringsCollector extends AbstractDataCollector implements AssetProvider
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
			'untranslated'       => [
				'icon'    => 'question-circle',
				'widget'  => 'PhpDebugBar.Widgets.languageStringsWidget',
				'map'     => $this->name . '.data',
				'default' => ''
			],
			'untranslated:badge' => [
				'map'     => $this->name . '.count',
				'default' => 'null'
			]
		];
	}

	/**
	 * Returns an array with the following keys:
	 *  - base_path
	 *  - base_url
	 *  - css: an array of filenames
	 *  - js: an array of filenames
	 *
	 * @since  __DEPLOY_VERSION__
	 * @return array
	 */
	public function getAssets(): array
	{
		return array(
			'js'  => \JUri::root(true) . '/media/plg_system_debug/widgets/languageStrings/widget.min.js',
			'css' => \JUri::root(true) . '/media/plg_system_debug/widgets/languageStrings/widget.min.css',
		);
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
			$data[$orphan] = [];

			foreach ($occurrences as $occurrence)
			{
				$item = [];

				$item['string'] = $occurrence['string'] ?? 'n/a';
				$item['trace']  = [];
				$item['caller'] = '';

				if (isset($occurrence['trace']))
				{
					$cnt            = 0;
					$trace          = [];
					$callerLocation = '';

					array_shift($occurrence['trace']);

					foreach ($occurrence['trace'] as $i => $stack)
					{
						$class = $stack['class'] ?? '';
						$file  = $stack['file'] ?? '';
						$line  = $stack['line'] ?? '';

						$caller   = $this->formatCallerInfo($stack);
						$location = $file && $line ? "$file:$line" : 'same';

						$isCaller = 0;

						if (!$callerLocation && $class !== Language::class && !strpos($file, 'Text.php'))
						{
							$callerLocation = $location;
							$isCaller       = 1;
						}

						$trace[] = [
							\count($occurrence['trace']) - $cnt,
							$isCaller,
							$caller,
							$file,
							$line,
						];

						$cnt++;
					}

					$item['trace']  = $trace;
					$item['caller'] = $callerLocation;
				}

				$data[$orphan][] = $item;
			}
		}

		return [
			'orphans'    => $data,
			'jroot'      => JPATH_ROOT,
			'xdebugLink' => $this->getXdebugLinkTemplate(),
		];
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
