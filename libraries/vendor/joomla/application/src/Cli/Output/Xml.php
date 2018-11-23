<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Cli\Output;

use Joomla\Application\Cli\CliOutput;

/**
 * Class Xml.
 *
 * @since       1.0
 * @deprecated  2.0  Use the `joomla/console` package instead
 */
class Xml extends CliOutput
{
	/**
	 * Write a string to standard output.
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \RuntimeException
	 * @codeCoverageIgnore
	 */
	public function out($text = '', $nl = true)
	{
		fwrite(STDOUT, $text . ($nl ? "\n" : null));
	}
}
