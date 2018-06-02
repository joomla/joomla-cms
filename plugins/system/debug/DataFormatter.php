<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   Copyright (C) 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug;

use DebugBar\DataFormatter\DataFormatter as DebugBarDataFormatter;

/**
 * DataFormatter
 *
 * @since  __DEPLOY_VERSION__
 */
class DataFormatter extends DebugBarDataFormatter
{
	/**
	 * Strip the Joomla! root path.
	 *
	 * @param   string  $path  The path.
	 *
	 * @return string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function formatPath($path)
	{
		return str_replace(JPATH_ROOT, 'JROOT', $path);
	}
}
