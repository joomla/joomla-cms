<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTTP
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * HTTP response data object class.
 *
 * @package     Joomla.Platform
 * @subpackage  HTTP
 * @since       11.3
 */
class JHttpResponse
{
	/**
	 * @var    integer  The server response code.
	 * @since  11.3
	 */
	public $code;

	/**
	 * @var    array  Response headers.
	 * @since  11.3
	 */
	public $headers = array();

	/**
	 * @var    string  Server response body.
	 * @since  11.3
	 */
	public $body;
}
