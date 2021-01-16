<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  OAuth
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Inspector for the JOAuth1Client class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  OAuth
 *
 * @since       3.2.0
 */
class JOAuth1ClientInspector extends JOAuth1Client
{
	/**
	 * Mimic verifying credentials.
	 *
	 * @return void
	 *
	 * @since 3.2.0
	 */
	public function verifyCredentials()
	{
		if (!strcmp($this->token['key'], 'valid'))
		{
			return true;
		}

		return false;
	}

	/**
	 * Method to validate a response.
	 *
	 * @param   string         $url       The request URL.
	 * @param   JHttpResponse  $response  The response to validate.
	 *
	 * @return  void
	 *
	 * @since  3.2.0
	 * @throws DomainException
	 */
	public function validateResponse($url, $response)
	{
		if ($response->code < 200 || $response->code > 399)
		{
				throw new DomainException($response->body);
		}
	}
}
