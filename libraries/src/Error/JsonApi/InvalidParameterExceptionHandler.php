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
use Tobscure\JsonApi\Exception\Handler\ResponseBag;

/**
 * Handler for invalid param
 *
 * @since  4.0.0
 */
class InvalidParameterExceptionHandler extends \Tobscure\JsonApi\Exception\Handler\InvalidParameterExceptionHandler
{
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
		$status = 400;
		$error = ['title' => $e->getMessage()];

		$code = $e->getCode();

		if ($code)
		{
			$error['code'] = $code;
		}

		return new ResponseBag($status, [$error]);
	}
}
