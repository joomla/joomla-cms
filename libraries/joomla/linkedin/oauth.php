<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();
jimport('joomla.oauth.oauth1aClient');

/**
 * Joomla Platform class for generating Linkedin API access token.
 *
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 *
 * @since       12.3
 */
class JLinkedinOauth extends JOauth1aClient
{
	/**
	* @var    JRegistry  Options for the JLinkedinOauth object.
	* @since  12.3
	*/
	protected $options;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry      $options  JLinkedinOauth options object.
	 * @param   JLinkedinHttp  $client   The HTTP client object.
	 *
	 * @since 12.3
	 */
	public function __construct(JRegistry $options = null, JLinkedinHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry;

		$this->setOption('accessTokenURL', 'https://www.linkedin.com/uas/oauth/accessToken');
		$this->setOption('authenticateURL', 'https://www.linkedin.com/uas/oauth/authenticate');
		$this->setOption('authoriseURL', 'https://www.linkedin.com/uas/oauth/authorize');
		$this->setOption('requestTokenURL', 'https://www.linkedin.com/uas/oauth/requestToken');

		// Call the JOauth1aClient constructor to setup the object.
		parent::__construct($this->options, $client);
	}

	/**
	 * Method to verify if the access token is valid by making a request to an API endpoint.
	 *
	 * @return  boolean  Returns true if the access token is valid and false otherwise.
	 *
	 * @since   12.3
	 */
	public function verifyCredentials()
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $this->getToken('key')
		);

		$data['format'] = 'json';

		// Set the API url.
		$path = 'https://api.linkedin.com/v1/people::(~)';

		// Send the request.
		$response = $this->oauthRequest($path, 'GET', $parameters, $data);

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
		if (!$code = $this->getOption('success_code'))
		{
			$code = 200;
		}

		if (strpos($url, '::(~)') === false && $response->code != $code)
		{
			if ($error = json_decode($response->body))
			{
				throw new DomainException('Error code ' . $error->errorCode . ' received with message: ' . $error->message . '.');
			}
			else
			{
				throw new DomainException($response->body);
			}
		}
	}
}
