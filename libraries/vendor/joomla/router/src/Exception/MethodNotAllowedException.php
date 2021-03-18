<?php
/**
 * Part of the Joomla Framework Router Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Router\Exception;

/**
 * Exception defining a method not allowed error.
 *
 * @since  __DEPLOY_VERSION__
 */
class MethodNotAllowedException extends \RuntimeException
{
	/**
	 * Allowed methods for the given route
	 *
	 * @var    string[]
	 * @since  __DEPLOY_VERSION__
	 */
	protected $allowedMethods = [];

	/**
	 * Constructor.
	 *
	 * @param   array       $allowedMethods  The allowed methods for the route.
	 * @param   null        $message         The Exception message to throw.
	 * @param   integer     $code            The Exception code.
	 * @param   \Exception  $previous        The previous throwable used for the exception chaining.
	 */
	public function __construct(array $allowedMethods, $message = null, $code = 405, \Exception $previous = null)
	{
		$this->allowedMethods = array_map('strtoupper', $allowedMethods);

		parent::__construct($message, $code, $previous);
	}

	/**
	 * Gets the allowed HTTP methods.
	 *
	 * @return  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getAllowedMethods(): array
	{
		return $this->allowedMethods;
	}
}
