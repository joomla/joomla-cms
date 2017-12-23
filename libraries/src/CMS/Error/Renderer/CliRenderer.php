<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Error\Renderer;

use Joomla\CMS\Error\AbstractRenderer;

/**
 * Cli error renderer
 *
 * @since  __DEPLOY_VERSION__
 */
class CliRenderer extends AbstractRenderer
{
	/**
	 * The format (type)
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'cli';

	/**
	 * Render the error for the given object.
	 *
	 * @param   \Throwable|\Exception  $error  The error object to be rendered
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function doRender($error)
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
	 * @param   \Throwable|\Exception  $error  The error
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getTrace($error)
	{
		// Include the stack trace only if in debug mode
		if (!JDEBUG)
		{
			return '';
		}

		return PHP_EOL . $error->getTraceAsString() . PHP_EOL;
	}
}
