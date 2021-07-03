<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Error\JsonApi;

\defined('JPATH_PLATFORM') or die;

use Exception;
use Joomla\CMS\Access\Exception\AuthenticationFailed;
use Tobscure\JsonApi\Exception\Handler\ExceptionHandlerInterface;
use Tobscure\JsonApi\Exception\Handler\ResponseBag;

/**
 * Handler for permission errors that should give a 401
 *
 * @since  4.0.0
 */
class AuthenticationFailedExceptionHandler implements ExceptionHandlerInterface
{
	/**
	 * If the exception handler is able to format a response for the provided exception,
	 * then the implementation should return true.
	 *
	 * @param   \Exception  $e  The exception to be handled
	 *
	 * @return boolean
	 *
	 * @since  4.0.0
	 */
	public function manages(Exception $e)
	{
		return $e instanceof AuthenticationFailed;
	}

	/**
	 * Handle the provided exception.
	 *
	 * @param   Exception  $e  The exception being handled
	 *
	 * @return  \Tobscure\JsonApi\Exception\Handler\ResponseBag
	 *
	 * @since  4.0.0
	 */
	public function handle(Exception $e)
	{
		$status = 401;
		$error = ['title' => 'Forbidden'];

		$code = $e->getCode();

		if ($code)
		{
			$error['code'] = $code;
		}

		return new ResponseBag($status, [$error]);
	}
}
