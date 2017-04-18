<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   Copyright (C) 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace plgSystemDebug\DataCollector;

use plgSystemDebug\AbstractDataCollector;

/**
 * LanguageFilesDataCollector
 *
 * @since  version
 */
class LanguageFilesDataCollector extends AbstractDataCollector
{
	private $name = 'languageFiles';

	/**
	 * Called by the DebugBar when data needs to be collected
	 *
	 * @since  version
	 *
	 * @return array Collected data
	 */
	public function collect()
	{
		$loaded = [];
		$statuses = [
			\JText::_('PLG_DEBUG_LANG_NOT_LOADED'),
			\JText::_('PLG_DEBUG_LANG_LOADED'),
		];

		foreach (\JFactory::getLanguage()->getPaths() as $extension => $files)
		{
			$count = 1;
			foreach ($files as $file => $status)
			{
				$loaded[$count . ' ' . $extension] = $this->stripRoot($file) . ' - ' . $statuses[(int) $status];
				$count ++;
			}
		}

		return ['loaded' => $loaded];
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
			'loaded' => [
				'widget' => 'PhpDebugBar.Widgets.KVListWidget',
				'map' => $this->name . '.loaded',
				'default' => '[]'
			]
		];
	}
}
