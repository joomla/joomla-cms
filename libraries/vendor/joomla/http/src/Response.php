<?php
/**
 * Part of the Joomla Framework Http Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Http;

use Laminas\Diactoros\Response as PsrResponse;

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
		switch (strtolower($name))
		{
			case 'body':
				return (string) $this->getBody();

			case 'code':
				return $this->getStatusCode();

			case 'headers':
				return $this->getHeaders();

			default:
				$trace = debug_backtrace();

				trigger_error(
					sprintf(
						'Undefined property via __get(): %s in %s on line %s',
						$name,
						$trace[0]['file'],
						$trace[0]['line']
					),
					E_USER_NOTICE
				);

				break;
		}
	}
}
