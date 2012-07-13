<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();
jimport('joomla.oauth.oauth1aClient');


/**
 * Joomla Platform class for generating Twitter API access token.
 *
 * @package     Joomla.Platform
 * @subpackage  Twitter
 *
 * @since       12.3
 */
class JTwitterOAuth extends JOAuth1aClient
{
	/**
	* @var JRegistry Options for the JTwitterOAuth object.
	* @since 12.3
	*/
	protected $options;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry     $options          JTwitterOAuth options object.
	 * @param   JTwitterHttp  $client           The HTTP client object.
	 *
	 * @since 12.3
	 */
	public function __construct(JRegistry $options = null, JTwitterHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;

		$this->setOption('accessTokenURL', 'https://api.twitter.com/oauth/access_token');
		$this->setOption('authenticateURL', 'https://api.twitter.com/oauth/authenticate');
		$this->setOption('authoriseURL', 'https://api.twitter.com/oauth/authorize');
		$this->setOption('requestTokenURL', 'https://api.twitter.com/oauth/request_token');

		// Call the JOAuth1aClient constructor to setup the object.
		parent::__construct($this->options, $client);
	}

	/**
	 * Method to verify if the access token is valid by making a request.
	 *
	 * @return  boolean  Returns true if the access token is valid and false otherwise.
	 *
	 * @since   12.3
	 */
	public function verifyCredentials()
	{
		// Set the parameters.
		$parameters = array('oauth_token' => $this->getToken('key'));

		// Set the API base
		$path = 'https://api.twitter.com/1/account/verify_credentials.json';

		// Send the request.
		$response = $this->oauthRequest($path, 'GET', $parameters);

		// Verify response
		if ($response->code == 200)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Ends the session of the authenticating user, returning a null cookie.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function endSession()
	{
		// Set parameters.
		$parameters = array('oauth_token' => $this->getToken('key'));

		// Set the API base
		$path = 'https://api.twitter.com/1/account/end_session.json';

		// Send the request.
		$response = $this->oauthRequest($path, 'POST', $parameters);
		return json_decode($response->body);
	}

	/**
	 * Method to validate a response.
	 *
	 * @param   string         $url       The request URL.
	 * @param   JHttpResponse  $response  The response to validate.
	 *
	 * @return  void
	 *
	 * @since  12.3
	 * @throws DomainException
	 */
	public function validateResponse($url, $response)
	{
		if (strpos($url, 'verify_credentials') === false && $response->code != 200)
		{
			$error = json_decode($response->body);

			if (property_exists($error, 'error'))
			{
				throw new DomainException($error->error);
			}
			else
			{
				$error = $error->errors;
				throw new DomainException($error[0]->message, $error[0]->code);
			}
		}
	}
}
