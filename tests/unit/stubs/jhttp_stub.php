<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Http
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * This is a stub file to aid in testing the JHttp transports. The idea is to echo
 * back data that is sent from the transports so that assertions can be made and
 * it can be ensured that proper data is being sent in the request.
 *
 * This file must be placed on a webserver in a location that can be accessed
 * by the system that the test is running.
 */

$response = new stdClass;

$response->method = getVar($_SERVER, 'REQUEST_METHOD');
$response->http_user_agent = getVar($_SERVER, 'HTTP_USER_AGENT');
$response->request_uri = getVar($_SERVER, 'REQUEST_URI');
$response->query_string = getVar($_SERVER, 'QUERY_STRING');
$response->http_accept = getVar($_SERVER, 'HTTP_ACCEPT');
$response->http_accept_charset = getVar($_SERVER, 'HTTP_ACCEPT_CHARSET');
$response->http_accept_encoding = getVar($_SERVER, 'HTTP_ACCEPT_ENCODING');

$response->http_referer = getVar($_SERVER, 'HTTP_REFERER');

$response->get = $_GET;
$response->post = $_POST;
$response->files = $_FILES;
$response->cookies = $_COOKIE;

echo json_encode($response);


/**
 * Retrieves a value from an array, returning a default value if not present
 *
 * @param   array   $array    The array from which to retrieve a value.
 * @param   string  $key      The value to retrieve.
 * @param   mixed   $default  The value to return if the key isn't present.
 *
 * @return  mixed
 *
 * @since   3.4
 */
function getVar($array, $key, $default = '')
{
	return isset($array[$key]) ? $array[$key] : $default;
}
