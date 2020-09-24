<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Http;

defined('JPATH_PLATFORM') or die;

/**
 * HTTP response data object class.
 *
 * @since  1.7.3
 */
class Response
{
	/**
	 * @var    integer  The server response code.
	 * @since  1.7.3
	 */
	public $code;

	/**
	 * @var    array  Response headers.
	 * @since  1.7.3
	 */
	public $headers = array();

	/**
	 * @var    string  Server response body.
	 * @since  1.7.3
	 */
	public $body;
}
