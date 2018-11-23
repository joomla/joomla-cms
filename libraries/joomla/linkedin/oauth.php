<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

use Joomla\Registry\Registry;

/**
 * Joomla Platform class for generating Linkedin API access token.
 *
 * @since  3.2.0
 */
class JLinkedinOauth extends JOAuth1Client
{
	/**
	* @var    Registry  Options for the JLinkedinOauth object.
	* @since  3.2.0
	*/
	protected $options;

	/**
	 * Constructor.
	 *
	 * @param   Registry  $options  JLinkedinOauth options object.
	 * @param   JHttp     $client   The HTTP client object.
	 * @param   JInput    $input    The input object
	 *
	 * @since   3.2.0
	 */
	public function __construct(Registry $options = null, JHttp $client = null, JInput $input = null)
	{
		$this->options = isset($options) ? $options : new Registry;

		$this->options->def('accessTokenURL', 'https://www.linkedin.com/uas/oauth/accessToken');
		$this->options->def('authenticateURL', 'https://www.linkedin.com/uas/oauth/authenticate');
		$this->options->def('authoriseURL', 'https://www.linkedin.com/uas/oauth/authorize');
		$this->options->def('requestTokenURL', 'https://www.linkedin.com/uas/oauth/requestToken');

		// Call the JOauthV1aclient constructor to setup the object.
		parent::__construct($this->options, $client, $input);
	}

	/**
	 * Method to verify if the access token is valid by making a request to an API endpoint.
	 *
	 * @return  boolean  Returns true if the access token is valid and false otherwise.
	 *
	 * @since   3.2.0
	 */
	public function verifyCredentials()
	{
		$token = $this->getToken();

		// Set parameters.
		$parameters = array(
			'oauth_token' => $token['key'],
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
	 * @since  3.2.0
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

	/**
	 * Method used to set permissions.
	 *
	 * @param   mixed  $scope  String or an array of string containing permissions.
	 *
	 * @return  JLinkedinOauth  This object for method chaining
	 *
	 * @link    https://developer.linkedin.com/documents/authentication
	 * @since   3.2.0
	 */
	public function setScope($scope)
	{
		$this->setOption('scope', $scope);

		return $this;
	}

	/**
	 * Method to get the current scope
	 *
	 * @return  string String or an array of string containing permissions.
	 *
	 * @since   3.2.0
	 */
	public function getScope()
	{
		return $this->getOption('scope');
	}
}
