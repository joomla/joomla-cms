<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;
jimport('joomla.oauth.oauth');

/**
 * Google OAuth authentication class
 *
 * @package     Joomla.Platform
 * @subpackage  Google
 * @since       1234
 */
class JGoogleAuthOauth extends JGoogleAuth
{
	/**
	 * @var    JRegistry  Options for the Google authentication object.
	 * @since  1234
	 */
	protected $options;

	/**
	 * @var    JOauth2client  OAuth client for the Google authentication object.
	 * @since  1234
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param   JOauth2client  $client  OAuth client for Google authentication.
	 *
	 * @since   1234
	 */
	public function __construct(JOauth2client $client = null)
	{
		$this->client = isset($client) ? $client : new JOauth2client;
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
		$this->client->auth();
	}

	/**
	 * Method to retrieve data from Google
	 *
	 * @param   string  $url      The URL for the request.
	 * @param   mixed   $data     The data to include in the request.
	 * @param   array   $headers  The headers to send with the request.
	 *
	 * @return  mixed  Data from Google.
	 *
	 * @since   1234
	 */
	public function query($url, $data = null, $headers = null)
	{
		$this->client->query($url, $data, $headers);
	}

	/**
	 * Get an option from the Oauth2client instance.
	 *
	 * @param   string  $key  The name of the option to get.
	 *
	 * @return  mixed  The option value.
	 *
	 * @since   1234
	 */
	public function getOption($key)
	{
		return $this->client->getOption($key);
	}

	/**
	 * Set an option for the Oauth2client instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JGoogleAuthOauth  This object for method chaining.
	 *
	 * @since   1234
	 */
	public function setOption($key, $value)
	{
		$this->client->setOption($key, $value);

		return $this;
	}
}
