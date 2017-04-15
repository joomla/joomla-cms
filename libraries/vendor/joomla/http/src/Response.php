<?php
/**
 * Part of the Joomla Framework Http Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http;

use Zend\Diactoros\Response as PsrResponse;

/**
 * HTTP response data object class.
 *
 * @property-read  string   $body     The response body as a string
 * @property-read  integer  $code     The status code of the response
 * @property-read  array    $headers  The headers as an array
 *
 * @since  1.0
 */
class Response extends PsrResponse
{
	/**
	 * Magic getter for backward compatibility with the 1.x API
	 *
	 * @param   string  $name  The variable to return
	 *
	 * @return  mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 * @deprecated  3.0  Access data via the PSR-7 ResponseInterface instead
	 */
	public function __get($name)
	{
		if (strtolower($name) === 'body')
		{
			return (string) $this->getBody();
		}

		if (strtolower($name) === 'code')
		{
			return $this->getStatusCode();
		}

		if (strtolower($name) === 'headers')
		{
			return $this->getHeaders();
		}

		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE
		);

		return;
	}
}
