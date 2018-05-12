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
use DebugBar\DataFormatter\DataFormatterInterface;
use Joomla\Registry\Registry;

/**
 * AbstractDataCollector
 *
 * @since  version
 */
abstract class AbstractDataCollector extends DataCollector implements Renderable
{
	/**
	 * @var Registry
	 * @since version
	 */
	protected $params;

	/**
	 * @var DataFormatter
	 * @since version
	 */
	private static $defaultDataFormatter;

	/**
	 * AbstractDataCollector constructor.
	 *
	 * @param   Registry  $params  Parameters.
	 *
	 * @since version
	 */
	public function __construct(Registry $params)
	{
		$this->params = $params;
	}

	/**
	 * Get a data formatter.
	 *
	 * @since version
	 * @return DataFormatterInterface
	 */
	public function getDataFormatter()
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
	 * @since version
	 * @return DataFormatterInterface
	 */
	public static function getDefaultDataFormatter()
	{
		if (self::$defaultDataFormatter === null)
		{
			self::$defaultDataFormatter = new DataFormatter;
		}

		return self::$defaultDataFormatter;
	}
}
