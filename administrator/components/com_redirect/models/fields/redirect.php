<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

/**
 * A dropdown containing all valid HTTP 1.1 response codes.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 * @since       3.4
 */
class JFormFieldRedirect extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.4
	 */
	protected $type = 'Redirect';

	/**
	 * A map of integer HTTP 1.1 response codes to the full HTTP Status for the headers.
	 *
	 * @var    object
	 * @since  3.4
	 * @link   http://www.iana.org/assignments/http-status-codes/
	 */
	protected $responseMap = array(
		100 => 'HTTP/1.1 100 Continue',
		101 => 'HTTP/1.1 101 Switching Protocols',
		102 => 'HTTP/1.1 102 Processing',
		200 => 'HTTP/1.1 200 OK',
		201 => 'HTTP/1.1 201 Created',
		202 => 'HTTP/1.1 202 Accepted',
		203 => 'HTTP/1.1 203 Non-Authoritative Information',
		204 => 'HTTP/1.1 204 No Content',
		205 => 'HTTP/1.1 205 Reset Content',
		206 => 'HTTP/1.1 206 Partial Content',
		207 => 'HTTP/1.1 207 Multi-Status',
		208 => 'HTTP/1.1 208 Already Reported',
		226 => 'HTTP/1.1 226 IM Used',
		300 => 'HTTP/1.1 300 Multiple Choices',
		301 => 'HTTP/1.1 301 Moved Permanently',
		302 => 'HTTP/1.1 302 Found',
		303 => 'HTTP/1.1 303 See other',
		304 => 'HTTP/1.1 304 Not Modified',
		305 => 'HTTP/1.1 305 Use Proxy',
		306 => 'HTTP/1.1 306 (Unused)',
		307 => 'HTTP/1.1 307 Temporary Redirect',
		308 => 'HTTP/1.1 308 Permanent Redirect',
		400 => 'HTTP/1.1 400 Bad Request',
		401 => 'HTTP/1.1 401 Unauthorized',
		402 => 'HTTP/1.1 402 Payment Required',
		403 => 'HTTP/1.1 403 Forbidden',
		404 => 'HTTP/1.1 404 Not Found',
		405 => 'HTTP/1.1 405 Method Not Allowed',
		406 => 'HTTP/1.1 406 Not Acceptable',
		407 => 'HTTP/1.1 407 Proxy Authentication Required',
		408 => 'HTTP/1.1 408 Request Timeout',
		409 => 'HTTP/1.1 409 Conflict',
		410 => 'HTTP/1.1 410 Gone',
		411 => 'HTTP/1.1 411 Length Required',
		412 => 'HTTP/1.1 412 Precondition Failed',
		413 => 'HTTP/1.1 413 Payload Too Large',
		414 => 'HTTP/1.1 414 URI Too Long',
		415 => 'HTTP/1.1 415 Unsupported Media Type',
		416 => 'HTTP/1.1 416 Requested Range Not Satisfiable',
		417 => 'HTTP/1.1 417 Expectation Failed',
		418 => 'HTTP/1.1 418 I\'m a teapot',
		422 => 'HTTP/1.1 422 Unprocessable Entity',
		423 => 'HTTP/1.1 423 Locked',
		424 => 'HTTP/1.1 424 Failed Dependency',
		425 => 'HTTP/1.1 425 Reserved for WebDAV advanced collections expired proposal',
		426 => 'HTTP/1.1 426 Upgrade Required',
		428 => 'HTTP/1.1 428 Precondition Required',
		429 => 'HTTP/1.1 429 Too Many Requests',
		431 => 'HTTP/1.1 431 Request Header Fields Too Large',
		451 => 'HTTP/1.1 451 Unavailable For Legal Reasons',
		500 => 'HTTP/1.1 500 Internal Server Error',
		501 => 'HTTP/1.1 501 Not Implemented',
		502 => 'HTTP/1.1 502 Bad Gateway',
		503 => 'HTTP/1.1 503 Service Unavailable',
		504 => 'HTTP/1.1 504 Gateway Timeout',
		505 => 'HTTP/1.1 505 HTTP Version Not Supported',
		506 => 'HTTP/1.1 506 Variant Also Negotiates (Experimental)',
		507 => 'HTTP/1.1 507 Insufficient Storage',
		508 => 'HTTP/1.1 508 Loop Detected',
		510 => 'HTTP/1.1 510 Not Extended',
		511 => 'HTTP/1.1 511 Network Authentication Required',
	);

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 *
	 * @since   3.4
	 */
	protected function getOptions()
	{
		$options = array();

		foreach ($this->responseMap as $key => $value)
		{
			$options[] = JHtml::_('select.option', $key, $value);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
