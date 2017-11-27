<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Error\Renderer;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Error\AbstractRenderer;

/**
 * Cli error renderer
 *
 * @since  4.0.0
 */
class CliRenderer extends AbstractRenderer
{
	/**
	 * The format (type)
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $type = 'cli';

	/**
	 * Render the error for the given object.
	 *
	 * @param   \Throwable  $error  The error object to be rendered
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function render(\Throwable $error): string
	{
		$buffer = PHP_EOL . 'Error occurred: ' . $error->getMessage() . PHP_EOL . $this->getTrace($error);

		if ($prev = $error->getPrevious())
		{
			$buffer .= PHP_EOL . PHP_EOL . 'Previous Exception: ' . $prev->getMessage() . PHP_EOL . $this->getTrace($prev);
		}

		return $buffer;
	}

	/**
	 * Returns a trace for the given error.
	 *
	 * @param   \Throwable  $error  The error
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	private function getTrace(\Throwable $error): string
	{
		// Include the stack trace only if in debug mode
		if (!JDEBUG)
		{
			return '';
		}

		return PHP_EOL . $error->getTraceAsString() . PHP_EOL;
	}
}
