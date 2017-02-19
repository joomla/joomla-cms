<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Cli\Output;

use Joomla\Application\Cli\CliOutput;

/**
 * Output handler for writing command line output to the stdout interface
 *
 * @since  1.0
 */
class Stdout extends CliOutput
{
	/**
	 * Write a string to standard output
	 *
	 * @param   string   $text  The text to display.
	 * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
	 *
	 * @return  $this
	 *
	 * @codeCoverageIgnore
	 * @since   1.0
	 */
	public function out($text = '', $nl = true)
	{
		fwrite(STDOUT, $this->getProcessor()->process($text) . ($nl ? "\n" : null));

		return $this;
	}
}
