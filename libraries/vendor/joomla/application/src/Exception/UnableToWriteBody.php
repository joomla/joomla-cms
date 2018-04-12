<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Exception;

/**
 * Exception thrown when the application can't write to the response body
 *
 * @since  __DEPLOY_VERSION__
 */
class UnableToWriteBody extends \DomainException
{
	/**
	 * Constructor.
	 *
	 * @param   string      $message   The Exception message to throw.
	 * @param   int         $code      The Exception code.
	 * @param   \Exception  $previous  The previous exception used for the exception chaining.
	 *
	 * @since   2.0.0
	 */
	public function __construct($message = '', $code = 500, \Exception $previous = null)
	{
	}
}
