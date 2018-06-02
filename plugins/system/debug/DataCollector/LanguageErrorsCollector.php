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
 * LanguageErrorsDataCollector
 *
 * @since  __DEPLOY_VERSION__
 */
class LanguageErrorsCollector extends AbstractDataCollector
{
	private $name = 'languageErrors';

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
			'errors'       => [
				'icon' => 'warning',
				'widget'  => 'PhpDebugBar.Widgets.KVListWidget',
				'map'     => $this->name . '.data',
				'default' => '',
			],
			'errors:badge' => [
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
		$errorFiles = \JFactory::getLanguage()->getErrorFiles();
		$errors     = [];

		if (count($errorFiles))
		{
			$count = 1;
			foreach ($errorFiles as $error)
			{
				$errors[$count] = $this->getDataFormatter()->formatPath($error);
				$count++;
			}
		}
		else
		{
			$errors[] = \JText::_('JNONE');
		}

		return $errors;
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
		return count(\JFactory::getLanguage()->getErrorFiles());
	}
}
