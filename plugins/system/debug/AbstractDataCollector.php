<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   Copyright (C) 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Joomla\Registry\Registry;

/**
 * AbstractDataCollector
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class AbstractDataCollector extends DataCollector implements Renderable
{
	/**
	 * Parameters.
	 *
	 * @var   Registry
	 * @since __DEPLOY_VERSION__
	 */
	protected $params;

	/**
	 * The default formatter.
	 *
	 * @var   DataFormatter
	 * @since __DEPLOY_VERSION__
	 */
	private static $defaultDataFormatter;

	/**
	 * AbstractDataCollector constructor.
	 *
	 * @param   Registry  $params  Parameters.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct(Registry $params)
	{
		$this->params = $params;
	}

	/**
	 * Get a data formatter.
	 *
	 * @since  __DEPLOY_VERSION__
	 * @return DataFormatter
	 */
	public function getDataFormatter(): DataFormatter
	{
		if ($this->dataFormater === null)
		{
			$this->dataFormater = self::getDefaultDataFormatter();
		}

		return $this->dataFormater;
	}

	/**
	 * Returns the default data formater
	 *
	 * @since  __DEPLOY_VERSION__
	 * @return DataFormatter
	 */
	public static function getDefaultDataFormatter(): DataFormatter
	{
		if (self::$defaultDataFormatter === null)
		{
			self::$defaultDataFormatter = new DataFormatter;
		}

		return self::$defaultDataFormatter;
	}

	/**
	 * Strip the Joomla! root path.
	 *
	 * @param   string  $path  The path.
	 *
	 * @return string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function formatPath($path): string
	{
		return $this->getDataFormatter()->formatPath($path);
	}


	/**
	 * Format a string from back trace.
	 *
	 * @param   array  $call  The array to format
	 *
	 * @return string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function formatCallerInfo(array $call): string
	{
		return $this->getDataFormatter()->formatCallerInfo($call);
	}
}
