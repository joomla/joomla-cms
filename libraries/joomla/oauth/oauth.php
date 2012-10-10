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
 * Joomla Platform class for interacting with an OAuth 2.0 server.
 *
 * @package     Joomla.Platform
 * @subpackage  Oauth
 * @since       1234
 */
class JOauth2client
{
	/**
	 * @var    JRegistry  Options for the OAuth2Client object.
	 * @since  1234
	 */
	protected $options;

	/**
	 * @var    JHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  1234
	 */
	protected $client;

	/**
	 * @var    JInput  The input object to use in retrieving GET/POST data.
	 * @since  1234
	 */
	protected $input;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry  $options  OAuth2Client options object.
	 * @param   JHttp      $client   The HTTP client object.
	 *
	 * @since   1234
	 */
	public function __construct(JRegistry $options = null, JHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;
		$this->client  = isset($client) ? $client : new JHttp($this->options);
		$this->input = JFactory::getApplication()->input;
	}

	/**
	 * Get the access token or redict to the authentication URL.
	 *
	 * @return  string  The access token.
	 *
	 * @since   1234
	 */
	public function auth()
	{
		if ($data['code'] = $this->input->get('code'))
		{
			$data['grant_type'] = 'authorization_code';
			$data['redirect_uri'] = $this->getOption('redirect');
			$data['client_id'] = $this->getOption('clientid');
			$data['client_secret'] = $this->getOption('clientsecret');
			$response = $this->client->post($this->getOption('redirect'), $data);

			if ($response->code == 200)
			{
				$token = array_merge(json_decode($response->body, true), array('created' => time()));
				$this->setToken($token);
				return $token;
			}
			else
			{
				// Exception
			}
		}

		header('Location: ' . $this->createUrl($scope));
		return false;
	}

	/**
	 * Create the URL for authentication.
	 *
	 * @return  JHttpResponse  The HTTP response.
	 *
	 * @since   1234
	 */
	public function createUrl()
	{
		$url = $this->getOption('authurl');
		if (!$url)
		{
			return false;
		}
		if (!($this->getOption('redirect') && $this->getOption('clientid') && $this->getOption('scope') && $this->getOption('accesstype') && $this->getOption('prompt')))
		{
			return false;
		}
		$url .= '?response_type=code';
		$url .= '&redircet_uri=' . urlencode($this->getOption('redirect'));
		$url .= '&client_id=' . urlencode($this->getOption('clientid'));
		$url .= '&scope=' . urlencode($this->getOption('scope'));
		$url .= 'access_type' . urlencode($this->getOption('accesstype'));
		$url .= 'approval_prompt' . urlencode($this->getOption('prompt'));

		return $url;
	}

	/**
	 * Send a signed Oauth request.
	 *
	 * @param   string  $url      The URL for the request.
	 * @param   mixed   $data     The data to include in the request.
	 * @param   array   $headers  The headers to send with the request.
     *
	 * @return  string  The URL.
	 *
	 * @since   1234
	 */
	public function query($url, $data = null, $headers = null)
	{
		if (!$headers)
		{
			$headers = Array();
		}

		if ($this->getOption('devkey') && !strpos($url, '?key=') && !strpos($url, '&key='))
		{
			if (strpos($url, '?'))
			{
				$url .= '&';
			}
			else
			{
				$url .= '?';
			}

			$url .= 'key=' . $this->getOption('devkey');
		}

		$token = $this->getOption('accesstoken');
		if ($token['created'] + $token['expires_in'] < time() + 20)
		{
			$token = $this->refreshToken($token['refresh_token']);
		}

		$headers['Authorization'] = 'Bearer ' . $token['access_token'];
		return $this->client->post($url, $data, $headers);
	}

	/**
	 * Get an option from the JOauth2client instance.
	 *
	 * @param   string  $key  The name of the option to get.
	 *
	 * @return  mixed  The option value.
	 *
	 * @since   1234
	 */
	public function getOption($key)
	{
		return $this->options->get($key);
	}

	/**
	 * Set an option for the JOauth2client instance.
	 *
	 * @param   string  $key    The name of the option to set.
	 * @param   mixed   $value  The option value to set.
	 *
	 * @return  JOauth2client  This object for method chaining.
	 *
	 * @since   1234
	 */
	public function setOption($key, $value)
	{
		$this->options->set($key, $value);
		return $this;
	}

	/**
	 * Get the access token from the JOauth2client instance.
	 *
	 * @return  array  The access token.
	 *
	 * @since   1234
	 */
	public function getToken()
	{
		return $this->options->get('accesstoken');
	}

	/**
	 * Set an option for the JOauth2client instance.
	 *
	 * @param   array  $value  The access token.
	 *
	 * @return  JOauth2client  This object for method chaining.
	 *
	 * @since   1234
	 */
	public function setToken($value)
	{
		$this->options->set('accesstoken', $value);
		return $this;
	}

	/**
	 * Refresh the access token instance.
	 *
	 * @param   string  $token  The refresh token.
	 *
	 * @return  array  The new access token.
	 *
	 * @since   1234
	 */
	public function refreshToken($token)
	{
		$data['grant_type'] = 'refresh_token';
		$data['refresh_token'] = $token;
		$data['client_id'] = $this->getOption('clientid');
		$data['client_secret'] = $this->getOption('clientsecret');
		$response = $this->client->post($this->getOption('refreshurl'), $data);

		if ($response->code == 200)
		{
			$token = array_merge(json_decode($response->body, true), array('created' => time()));
			$this->setToken($token);
			return $token;
		}
		else
		{
			// Exception
		}
	}
}
