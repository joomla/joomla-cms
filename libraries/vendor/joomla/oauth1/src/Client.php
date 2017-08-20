<?php
/**
 * Part of the Joomla Framework OAuth1 Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\OAuth1;

use Joomla\Http\Http;
use Joomla\Input\Input;
use Joomla\Application\AbstractWebApplication;

/**
 * Joomla Framework class for interacting with an OAuth 1.0 and 1.0a server.
 *
 * @since  1.0
 */
abstract class Client
{
	/**
	 * Options for the Client object.
	 *
	 * @var    array|\ArrayAccess
	 * @since  1.0
	 */
	protected $options;

	/**
	 * Contains access token key, secret and verifier.
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $token = [];

	/**
	 * The HTTP client object to use in sending HTTP requests.
	 *
	 * @var    Http
	 * @since  1.0
	 */
	protected $client;

	/**
	 * The input object to use in retrieving GET/POST data.
	 *
	 * @var    Input
	 * @since  1.0
	 */
	protected $input;

	/**
	 * The application object to send HTTP headers for redirects.
	 *
	 * @var    AbstractWebApplication
	 * @since  1.0
	 */
	protected $application;

	/**
	 * Selects which version of OAuth to use: 1.0 or 1.0a.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $version;

	/**
	 * Constructor.
	 *
	 * @param   AbstractWebApplication  $application  The application object
	 * @param   Http                    $client       The HTTP client object.
	 * @param   Input                   $input        The input object
	 * @param   array|\ArrayAccess      $options      OAuth1 Client options.
	 * @param   string                  $version      Specify the OAuth version. By default we are using 1.0a.
	 *
	 * @since   1.0
	 */
	public function __construct(AbstractWebApplication $application, Http $client = null, Input $input = null, $options = [], $version = '1.0a')
	{
		if (!is_array($options) && !($options instanceof \ArrayAccess))
		{
			throw new \InvalidArgumentException(
				'The options param must be an array or implement the ArrayAccess interface.'
			);
		}

		$this->application = $application;
		$this->client      = $client ?: HttpFactory::getHttp($options);
		$this->input       = $input ?: $application->input;
		$this->options     = $options;
		$this->version     = $version;
	}

	/**
	 * Method to form the oauth flow.
	 *
	 * @return  string  The access token.
	 *
	 * @since   1.0
	 * @throws  \DomainException
	 */
	public function authenticate()
	{
		// Already got some credentials stored?
		if ($this->token)
		{
			$response = $this->verifyCredentials();

			if ($response)
			{
				return $this->token;
			}

			$this->token = null;
		}

		// Check for callback.
		if (strcmp($this->version, '1.0a') === 0)
		{
			$verifier = $this->input->get('oauth_verifier');
		}
		else
		{
			$verifier = $this->input->get('oauth_token');
		}

		if (!empty($verifier))
		{
			$session = $this->application->getSession();

			// Get token form session.
			$this->token = [
				'key'    => $session->get('oauth_token.key'),
				'secret' => $session->get('oauth_token.secret')
			];

			// Verify the returned request token.
			if (strcmp($this->token['key'], $this->input->get('oauth_token')) !== 0)
			{
				throw new \DomainException('Bad session!');
			}

			// Set token verifier for 1.0a.
			if (strcmp($this->version, '1.0a') === 0)
			{
				$this->token['verifier'] = $this->input->get('oauth_verifier');
			}

			// Generate access token.
			$this->generateAccessToken();

			// Return the access token.
			return $this->token;
		}

		// Generate a request token.
		$this->generateRequestToken();

		// Authenticate the user and authorise the app.
		$this->authorise();
	}

	/**
	 * Method used to get a request token.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \DomainException
	 */
	private function generateRequestToken()
	{
		$parameters = [];

		// Set the callback URL.
		if ($this->getOption('callback'))
		{
			$parameters['oauth_callback'] = $this->getOption('callback');
		}

		// Make an OAuth request for the Request Token.
		$response = $this->oauthRequest($this->getOption('requestTokenURL'), 'POST', $parameters);

		parse_str($response->body, $params);

		if (strcmp($this->version, '1.0a') === 0 && strcmp($params['oauth_callback_confirmed'], 'true') !== 0)
		{
			throw new \DomainException('Bad request token!');
		}

		// Save the request token.
		$this->token = ['key' => $params['oauth_token'], 'secret' => $params['oauth_token_secret']];

		// Save the request token in session
		$session = $this->application->getSession();
		$session->set('oauth_token.key', $this->token['key']);
		$session->set('oauth_token.secret', $this->token['secret']);
	}

	/**
	 * Method used to authorise the application.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function authorise()
	{
		$url = $this->getOption('authoriseURL') . '?oauth_token=' . $this->token['key'];

		if ($this->getOption('scope'))
		{
			$scope = is_array($this->getOption('scope')) ? implode(' ', $this->getOption('scope')) : $this->getOption('scope');
			$url .= '&scope=' . urlencode($scope);
		}

		if ($this->getOption('sendheaders'))
		{
			$this->application->redirect($url);
		}
	}

	/**
	 * Method used to get an access token.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function generateAccessToken()
	{
		// Set the parameters.
		$parameters = [
			'oauth_token' => $this->token['key']
		];

		if (strcmp($this->version, '1.0a') === 0)
		{
			$parameters = array_merge($parameters, ['oauth_verifier' => $this->token['verifier']]);
		}

		// Make an OAuth request for the Access Token.
		$response = $this->oauthRequest($this->getOption('accessTokenURL'), 'POST', $parameters);

		parse_str($response->body, $params);

		// Save the access token.
		$this->token = ['key' => $params['oauth_token'], 'secret' => $params['oauth_token_secret']];
	}

	/**
	 * Method used to make an OAuth request.
	 *
	 * @param   string  $url         The request URL.
	 * @param   string  $method      The request method.
	 * @param   array   $parameters  Array containing request parameters.
	 * @param   mixed   $data        The POST request data.
	 * @param   array   $headers     An array of name-value pairs to include in the header of the request
	 *
	 * @return  \Joomla\Http\Response
	 *
	 * @since   1.0
	 * @throws  \DomainException
	 */
	public function oauthRequest($url, $method, $parameters, $data = [], $headers = [])
	{
		// Set the parameters.
		$defaults = [
			'oauth_consumer_key'     => $this->getOption('consumer_key'),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_version'          => '1.0',
			'oauth_nonce'            => $this->generateNonce(),
			'oauth_timestamp'        => time()
		];

		$parameters = array_merge($parameters, $defaults);

		// Do not encode multipart parameters. Do not include $data in the signature if $data is not array.
		if (isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'multipart/form-data') !== false || !is_array($data))
		{
			$oauth_headers = $parameters;
		}
		else
		{
			// Use all parameters for the signature.
			$oauth_headers = array_merge($parameters, $data);
		}

		// Sign the request.
		$oauth_headers = $this->signRequest($url, $method, $oauth_headers);

		// Get parameters for the Authorisation header.
		if (is_array($data))
		{
			$oauth_headers = array_diff_key($oauth_headers, $data);
		}

		// Send the request.
		switch ($method)
		{
			case 'GET':
				$url      = $this->toUrl($url, $data);
				$response = $this->client->get($url, ['Authorization' => $this->createHeader($oauth_headers)]);
				break;

			case 'POST':
			case 'PUT':
				$headers  = array_merge($headers, ['Authorization' => $this->createHeader($oauth_headers)]);
				$response = $this->client->{strtolower($method)}($url, $data, $headers);
				break;

			case 'DELETE':
				$headers  = array_merge($headers, ['Authorization' => $this->createHeader($oauth_headers)]);
				$response = $this->client->delete($url, $headers);
				break;
		}

		// Validate the response code.
		$this->validateResponse($url, $response);

		return $response;
	}

	/**
	 * Method to validate a response.
	 *
	 * @param   string    $url       The request URL.
	 * @param   Response  $response  The response to validate.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 * @throws  \DomainException
	 */
	abstract public function validateResponse($url, $response);

	/**
	 * Method used to create the header for the POST request.
	 *
	 * @param   array  $parameters  Array containing request parameters.
	 *
	 * @return  string  The header.
	 *
	 * @since   1.0
	 */
	private function createHeader(array $parameters): string
	{
		$header = 'OAuth ';

		foreach ($parameters as $key => $value)
		{
			if (!strcmp($header, 'OAuth '))
			{
				$header .= $key . '="' . $this->safeEncode($value) . '"';
			}
			else
			{
				$header .= ', ' . $key . '="' . $value . '"';
			}
		}

		return $header;
	}

	/**
	 * Method to create the URL formed string with the parameters.
	 *
	 * @param   string  $url         The request URL.
	 * @param   array   $parameters  Array containing request parameters.
	 *
	 * @return  string  The formed URL.
	 *
	 * @since   1.0
	 */
	public function toUrl($url, $parameters)
	{
		foreach ($parameters as $key => $value)
		{
			if (is_array($value))
			{
				foreach ($value as $k => $v)
				{
					if (strpos($url, '?') === false)
					{
						$url .= '?';
					}
					else
					{
						$url .= '&';
					}

					$url .= $key . '=' . $v;
				}
			}
			else
			{
				if (strpos($value, ' ') !== false)
				{
					$value = $this->safeEncode($value);
				}

				if (strpos($url, '?') === false)
				{
					$url .= '?';
				}
				else
				{
					$url .= '&';
				}

				$url .= $key . '=' . $value;
			}
		}

		return $url;
	}

	/**
	 * Method used to sign requests.
	 *
	 * @param   string  $url         The URL to sign.
	 * @param   string  $method      The request method.
	 * @param   array   $parameters  Array containing request parameters.
	 *
	 * @return  array  The array containing the request parameters, including signature.
	 *
	 * @since   1.0
	 */
	private function signRequest(string $url, string $method, array $parameters): array
	{
		// Create the signature base string.
		$base = $this->baseString($url, $method, $parameters);

		$parameters['oauth_signature'] = $this->safeEncode(
			base64_encode(
				hash_hmac('sha1', $base, $this->prepareSigningKey(), true)
			)
		);

		return $parameters;
	}

	/**
	 * Prepare the signature base string.
	 *
	 * @param   string  $url         The URL to sign.
	 * @param   string  $method      The request method.
	 * @param   array   $parameters  Array containing request parameters.
	 *
	 * @return  string  The base string.
	 *
	 * @since   1.0
	 */
	private function baseString(string $url, string $method, array $parameters): string
	{
		// Sort the parameters alphabetically
		uksort($parameters, 'strcmp');

		// Encode parameters.
		foreach ($parameters as $key => $value)
		{
			$key = $this->safeEncode($key);

			if (is_array($value))
			{
				foreach ($value as $k => $v)
				{
					$v    = $this->safeEncode($v);
					$kv[] = "{$key}={$v}";
				}
			}
			else
			{
				$value = $this->safeEncode($value);
				$kv[]  = "{$key}={$value}";
			}
		}

		// Form the parameter string.
		$params = implode('&', $kv);

		// Signature base string elements.
		$base = [
			$method,
			$url,
			$params
		];

		// Return the base string.
		return implode('&', $this->safeEncode($base));
	}

	/**
	 * Encodes the string or array passed in a way compatible with OAuth.
	 * If an array is passed each array value will will be encoded.
	 *
	 * @param   mixed  $data  The scalar or array to encode.
	 *
	 * @return  string  $data encoded in a way compatible with OAuth.
	 *
	 * @since   1.0
	 */
	public function safeEncode($data)
	{
		if (is_array($data))
		{
			return array_map([$this, 'safeEncode'], $data);
		}

		if (is_scalar($data))
		{
			return str_ireplace(
				['+', '%7E'],
				[' ', '~'],
				rawurlencode($data)
			);
		}

		return '';
	}

	/**
	 * Method used to generate the current nonce.
	 *
	 * @return  string  The current nonce.
	 *
	 * @since   1.0
	 */
	public static function generateNonce()
	{
		// The md5s look nicer than numbers.
		return md5(microtime() . random_bytes(16));
	}

	/**
	 * Prepares the OAuth signing key.
	 *
	 * @return  string  The prepared signing key.
	 *
	 * @since   1.0
	 */
	private function prepareSigningKey(): string
	{
		return $this->safeEncode($this->getOption('consumer_secret')) . '&' . $this->safeEncode(($this->token) ? $this->token['secret'] : '');
	}

	/**
	 * Returns an HTTP 200 OK response code and a representation of the requesting user if authentication was successful;
	 * returns a 401 status code and an error message if not.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   1.0
	 */
	abstract public function verifyCredentials();

	/**
	 * Get an option from the OAuth1 Client instance.
	 *
	 * @param   string  $key      The name of the option to get
	 * @param   mixed   $default  Optional default value if the option does not exist
	 *
	 * @return  mixed  The option value
	 *
	 * @since   1.0
	 */
	public function getOption($key, $default = null)
	{
		return $this->options[$key] ?? $default;
	}

	/**
	 * Set an option for the OAuth1 Client instance.
	 *
	 * @param   string  $key    The name of the option to set
	 * @param   mixed   $value  The option value to set
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function setOption($key, $value)
	{
		$this->options[$key] = $value;

		return $this;
	}

	/**
	 * Get the oauth token key or secret.
	 *
	 * @return  array  The oauth token key and secret.
	 *
	 * @since   1.0
	 */
	public function getToken()
	{
		return $this->token;
	}

	/**
	 * Set the oauth token.
	 *
	 * @param   array  $token  The access token key and secret.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function setToken($token)
	{
		$this->token = $token;

		return $this;
	}
}
