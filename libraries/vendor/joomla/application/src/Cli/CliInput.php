<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Cli;

/**
 * Class CliInput
 *
 * @since       1.6.0
 * @deprecated  2.0  Use the `joomla/console` package instead
 */
class CliInput
{
	/**
	 * Get a value from standard input.
	 *
	 * @return  string  The input string from standard input.
	 *
	 * @codeCoverageIgnore
	 * @since   1.6.0
	 */
	public function in()
	{
		return rtrim(fread(STDIN, 8192), "\n\r");
	}
}
