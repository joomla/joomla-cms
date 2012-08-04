<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Oauth
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * HTTP client class for connecting to an Oauth instance.
 *
 * @package     Joomla.Platform
 * @subpackage  Oauth
 * @since       12.2
 */
class JOauthHttp extends JHttp
{
	/**
	 * Method to send the PATCH command to the server.
	 *
	 * @param   string  $url      Path to the resource.
	 * @param   mixed   $data     Either an associative array or a string to be sent with the request.
	 * @param   array   $headers  An array of name-value pairs to include in the header of the request.
	 *
	 * @return  JHttpResponse
	 *
	 * @since   12.2
	 */
	public function patch($url, $data, array $headers = null)
	{
		return $this->transport->request('PATCH', new JUri($url), $data, $headers);
	}
}
