<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Cli\Output\Processor;

/**
 * Class ProcessorInterface.
 *
 * @since  1.1.0
 */
interface ProcessorInterface
{
	/**
	 * Process the provided output into a string.
	 *
	 * @param   string  $output  The string to process.
	 *
	 * @return  string
	 *
	 * @since   1.1.0
	 */
	public function process($output);
}
