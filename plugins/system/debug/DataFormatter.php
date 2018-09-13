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
	 * Strip the root path.
	 *
	 * @param   string  $path         The path.
	 * @param   string  $replacement  The replacement
	 *
	 * @return string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function formatPath($path, $replacement = ''): string
	{
		return str_replace(JPATH_ROOT, $replacement, $path);
	}

	/**
	 * Format a string from back trace.
	 *
	 * @param   array  $call  The array to format
	 *
	 * @return string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function formatCallerInfo(array $call): string
	{
		$string = '';

		if (isset($call['class']))
		{
			// If entry has Class/Method print it.
			$string .= htmlspecialchars($call['class'] . $call['type'] . $call['function']) . '()';
		}
		elseif (isset($call['args']))
		{
			// If entry has args is a require/include or a call_user_func_array.
			$args = \is_array($call['args'][0]) ? '(' . implode(', ', $call['args'][0]) . ')' : ' ' . $call['args'][0];
			$string .= htmlspecialchars($call['function']) . $args;
		}
		else
		{
			// It's a function.
			$string .= htmlspecialchars($call['function']) . '()';
		}

		return $string;
	}
}
