<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Google API data class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Google
 * @since       1234
 */
abstract class JGoogleData
{
	/**
	 * @var    JRegistry  Options for the Google data object.
	 * @since  1234
	 */
	protected $options;

	/**
	 * @var    JGoogleAuth  Authentication client for the Google data object.
	 * @since  1234
	 */
	protected $auth;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry    $options  Google options object.
	 * @param   JGoogleAuth  $auth     Google data http client object.
	 *
	 * @since   1234
	 */
	public function __construct(JRegistry $options = null, JGoogleAuth $auth = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->auth = isset($auth) ? $auth : new JGoogleAuthOauth2($this->options);
	}

	/**
	 * Method to authenticate to Google
	 *
	 * @return  bool  True on success.
	 *
	 * @since   1234
	 */
	public function auth()
	{
		return $this->auth->auth();
	}

	/**
	 * Check authentication
	 *
	 * @return  bool  True if authenticated.
	 *
	 * @since   1234
	 */
	public function authenticated()
	{
		return $this->auth->isAuth();
	}

	/**
	 * Method to retrieve a list of Picasa Albums
	 *
	 * @param   string  $data  XML data to be parsed
	 *
	 * @return  SimpleXMLElement  XMLElement of parsed data
	 *
	 * @since   1234
	 * @throws UnexpectedValueException
	 */
	protected static function safeXML($data)
	{
		try
		{
			return new SimpleXMLElement($data, LIBXML_NOWARNING | LIBXML_NOERROR);
		}
		catch (Exception $e)
		{
			throw new UnexpectedValueException("Unexpected data received from Google: `$data`.");
		}
	}

	/**
	 * Method to retrieve data from Google
	 *
	 * @param   string  $url      The URL for the request.
	 * @param   mixed   $data     The data to include in the request.
	 * @param   array   $headers  The headers to send with the request.
	 * @param   string  $method   The type of http request to send.
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   1234
	 */
	protected function query($url, $data = null, $headers = null, $method = 'post')
	{
		$this->client->query($url, $data, $headers, $method);
	}
}
