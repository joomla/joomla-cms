<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   Copyright (C) 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace plgSystemDebug;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
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
	 * @since 4.0
	 */
	protected $params;

	/**
	 * AbstractDataCollector constructor.
	 *
	 * @param   Registry  $params  Parameters.
	 *
	 * @since 4.0
	 */
	public function __construct(Registry $params)
	{
		$this->params = $params;
	}

	/**
	 * Strip the Joomla! root path.
	 *
	 * @param   string  $path  The path.
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	protected function stripRoot($path)
	{
		return str_replace(JPATH_ROOT, 'JROOT', $path);
	}
}
