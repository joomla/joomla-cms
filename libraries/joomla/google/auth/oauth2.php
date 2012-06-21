<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Google
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;
jimport('joomla.oauth.oauth2client');

/**
 * Google OAuth authentication class
 *
 * @package     Joomla.Platform
 * @subpackage  Google
 * @since       1234
 */
class JGoogleAuthOauth2 extends JGoogleAuth
{
	/**
	 * @var    JOauth2client  OAuth client for the Google authentication object.
	 * @since  1234
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry      $options  JGoogleAuth options object.
	 * @param   JOauth2client  $client   OAuth client for Google authentication.
	 *
	 * @since   1234
	 */
	public function __construct(JRegistry $options = null, JOauthOauth2client $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		if (isset($client))
		{
			$this->client = $client;
		}
		else
		{
			$http = new JGoogleHttp;
			$this->client = new JOauthOauth2client($this->options, $http);
		}
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
		$this->googlize();
		return $this->client->auth();
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
	public function query($url, $data = null, $headers = null, $method = 'post')
	{
		$this->googlize();
		return $this->client->query($url, $data, $headers, $method);
	}

	/**
	 * Method to fill in Google-specific OAuth settings
	 *
	 * @return  JOauth2client  Google-configured Oauth2 client.
	 *
	 * @since   1234
	 */
	protected function googlize()
	{
		if (!$this->client->getOption('authurl'))
		{
			$this->client->setOption('authurl', 'https://accounts.google.com/o/oauth2/auth');
		}
		if (!$this->client->getOption('tokenurl'))
		{
			$this->client->setOption('tokenurl', 'https://accounts.google.com/o/oauth2/token');
		}
		if (!$this->client->getOption('requestparams'))
		{
			$this->client->setOption('requestparams', Array());
		}
		$params = $this->client->getOption('requestparams');
		if (!array_key_exists('access_type', $params))
		{
			$params['access_type'] = 'offline';
		}
		if (!array_key_exists('approval_prompt', $params))
		{
			$params['approval_prompt'] = 'auto';
		}
		$this->client->setOption('requestparams', $params);

		return $this->client;
	}
}
