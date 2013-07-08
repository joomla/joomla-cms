<?php
/**
 * @package     Joomla.Platform
 * @subpackage  OAuth1
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * OAuth Web Application class for the Joomla Platform
 *
 * @package     Joomla.Platform
 * @subpackage  OAuth1
 * @since       12.1
 */
class JOAuth1ApplicationWeb extends JApplicationWeb
{
	/**
	 * @var    JOAuth1Message  The found OAuth 1.0 message object found in the request.
	 * @since  12.1
	 */
	protected $message;

	/**
	 * @var    string  The optional authorization realm for the application.
	 * @since  12.1
	 */
	protected $realm;

	/**
	 * Method to get the application OAuth 1.0 message object.
	 *
	 * @return  JOAuth1Message  The OAuth 1.0 message object
	 *
	 * @since   12.1
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * Allows the application to load a custom or default identity.
	 *
	 * The logic and options for creating this object are adequately generic for default cases
	 * but for many applications it will make sense to override this method and create an identity,
	 * if required, based on more specific needs.
	 *
	 * @param   JIdentity  $identity  An optional identity object. If omitted, the factory user is created.
	 *
	 * @return  JApplicationBase This method is chainable.
	 *
	 * @since   12.1
	 */
	public function loadIdentity(JIdentity $identity = null)
	{
		if ($identity === null)
		{
			// Get the authenticated identity if it exists.
			$identityId = $this->doOAuthAuthentication();

			if (!$identityId)
			{
				// Let's fallback on HTTP Basic authentication if an OAuth 1.0 identity isn't found.
				$identityId = $this->doBasicAuthentication();
			}

			// If we found an authenticated identity id setup the identity.
			if ($identityId)
			{
				$this->identity = JUser::getInstance($identityId);
			}
			else
			// If we don't have an authenticated identity setup a guest identity.
			{
				$this->identity = new JUser;
			}

			// Cache the user in the session if it exists.
			if ($this->session)
			{
				$this->session->set('user', $this->identity);
			}
		}
		else
		// Use the given identity object.
		{
			$this->identity = $identity;
		}

		return $this;
	}

	/**
	 * Send a WWW-Authenticate response with a status 401 to the client.
	 *
	 * @param   string  $message  The body message to send with the response.
	 * @param   string  $method   The authentication method. eg. Basic, OAuth, Digest
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function sendInvalidAuthMessage($message, $method = 'OAuth')
	{
		// Build the authenticate realm string if we have one.
		$realm = empty($this->realm) ? '' : ' realm="' . $this->realm . '"';

		// Set the authenticate header and body message.
		$this->setHeader('WWW-Authenticate', ucfirst($method) . $realm);
		$this->setHeader('Status', '401 Unauthorized');
		$this->setBody($message);

		// Trigger the onBeforeRespond event.
		$this->triggerEvent('onBeforeRespond');

		// Send the application response.
		$this->respond();

		// Trigger the onAfterRespond event.
		$this->triggerEvent('onAfterRespond');

		// Close the application.
		$this->close();
	}

	/**
	 * Authenticate an identity using HTTP Basic authentication for the request.
	 *
	 * @return  integer  Identity ID for the authenticated identity.
	 *
	 * @since   12.1
	 */
	protected function doBasicAuthentication()
	{
		// If we have basic auth information attempt to authenticate.
		$username = $this->input->server->getString('PHP_AUTH_USER');

		if ($username)
		{
			$password = $this->input->server->getString('PHP_AUTH_PW');

			// TODO: Do actual password authentication.
			if ($password != md5($username))
			{
				$this->sendInvalidAuthMessage('Incorrect Password.', 'Basic');

				return;
			}

			return (int) JUserHelper::getUserId($username);
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Message stub.
	 *
	 * @return  JOAuth1Message
	 *
	 * @since   12.3
	 */
	protected function createMessage()
	{
		return new JOAuth1Message;
	}

	/**
	 * Authenticate an identity using OAuth 1.0.  This will validate an OAuth 1.0 message for a valid client,
	 * credentials if present and signature.  If the message is valid and the credentials are token credentials
	 * then the resource owner id is returned as the authenticated identity.
	 *
	 * @return  integer  Identity ID for the identity that owns the verified token credentials if they exist.
	 *
	 * @since   12.1
	 */
	protected function doOAuthAuthentication()
	{
		$this->message = $this->createMessage();

		// Get the OAuth 1.0 message from the request if there is one.
		$found = $this->_fetchMessageFromRequest($this->message);

		if (!$found)
		{
			$this->message = null;

			return 0;
		}

		try
		{
			// Get the OAuth client for the request.
			$client = $this->_fetchClient($this->message->consumerKey);

			// Get the OAuth credentials for the request.
			$credentials = $this->_fetchCredentials($this->message->token, $this->message->consumerKey);
		}
		catch (InvalidArgumentException $e)
		{
			$this->sendInvalidAuthMessage($e->getMessage());

			return 0;
		}

		// Atempt to validate the OAuth message signature.
		$valid = $this->message->isValid(
				$this->get('uri.request'),
				$this->input->getMethod(),
				$client->secret,
				$credentials ? $credentials->getSecret() : null,
				$credentials ? $credentials->getVerifierKey() : null
		);

		// If the OAuth message signature isn't valid set the failure message and return.
		if (!$valid)
		{
			$this->sendInvalidAuthMessage('Invalid OAuth request signature.');

			return 0;
		}

		// If the credentials are valid token credentials let's get the resource owner identity id.
		if ($credentials && ($credentials->getType() === JOAuth1Credentials::TOKEN))
		{
			return $credentials->getResourceOwnerId();
		}

		return 0;
	}

	/**
	 * Get the HTTP request headers.  Header names have been normalized, stripping
	 * the leading 'HTTP_' if present, and capitalizing only the first letter
	 * of each word.
	 *
	 * @return  string  The Authorization header if it has been set.
	 */
	private function _fetchAuthorizationHeader()
	{
		// The simplest case is if the apache_request_headers() function exists.
		if (function_exists('apache_request_headers'))
		{
			$headers = apache_request_headers();

			if (isset($headers['Authorization']))
			{
				return trim($headers['Authorization']);
			}
		}
		elseif ($this->input->server->getString('HTTP_AUTHORIZATION', false))
		// Otherwise we need to look in the $_SERVER superglobal.
		{
			// This doesn't work.
			return trim($this->input->server->getString('HTTP_AUTHORIZATION'));
		}

		return false;
	}

	/**
	 * Get an OAuth 1.0 client object based on the request message.
	 *
	 * @param   string  $consumerKey  The OAuth 1.0 consumer_key parameter for which to load the client.
	 *
	 * @return  JOAuth1Client
	 *
	 * @since   12.3
	 * @throws  InvalidArgumentException
	 */
	private function _fetchClient($consumerKey)
	{
		// Ensure there is a consumer key.
		if (empty($consumerKey))
		{
			throw new InvalidArgumentException('There is no OAuth consumer key in the request.');
		}

		// Get an OAuth client object and load it using the incoming client key.
		$client = $this->createClient();
		$client->loadByKey($consumerKey);

		// Verify the client key for the message.
		if ($client->key != $consumerKey)
		{
			throw new InvalidArgumentException('The OAuth consumer key is not valid.');
		}

		return $client;
	}

	/**
	 * This is a stub method to allow you to provide an alternative JOAuth1TableCredentials object in order
	 * to allow customisation of the storage method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @throws  InvalidArgumentException
	 */
	protected function createCredentials()
	{
		return new JOAuth1Credentials;
	}

	/**
	 * This is a stub method to allow you to provide an alternative JOAuth1TableClient object in order
	 * to allow customisation of the storage method.
	 *
	 * @return  void
	 *
	 * @since   13.1
	 * @throws  InvalidArgumentException
	 */
	protected function createClient()
	{
		return new JOAuth1Client;
	}

	/**
	 * Get an OAuth 1.0 credentials object based on the request message.
	 *
	 * @param   string  $token        The OAuth 1.0 token parameter for which to load the credentials.
	 * @param   string  $consumerKey  The OAuth 1.0 consumer_key parameter for which to load the credentials.
	 *
	 * @return  mixed  JOAuth1Credentials or boolean false if none exists.
	 *
	 * @since   12.1
	 * @throws  InvalidArgumentException
	 */
	private function _fetchCredentials($token, $consumerKey = null)
	{
		// If there is no credentials token then return false.
		if (empty($token))
		{
			return false;
		}

		// Get an OAuth credentials object and load it using the incoming token.
		$credentials = $this->createCredentials();
		$credentials->load($token);

		// If there is an expiration date set and it is less than the current time, then the token is no longer good.
		if ($credentials->getExpirationDate() > 0 && $credentials->getExpirationDate() < time())
		{
			$credentials->clean();

			$this->app->setHeader('status', '400');
			$this->app->setBody('The token has expired.');
		}

		// Verify the credentials exist.
		if (!$credentials->getKey())
		{
			throw new InvalidArgumentException('Not a valid OAuth credentials token.');
		}

		// Verify that the consumer key matches for the request and credentials.
		if ($credentials->getClientKey() != $consumerKey)
		{
			throw new InvalidArgumentException('The OAuth credentials token is invalid.  Consumer key does not match.');
		}

		return $credentials;
	}

	/**
	 * Check if the incoming request is signed using OAuth 1.0.  To determine this, OAuth parameters are searched
	 * for in the order of precedence as follows:
	 *
	 *   * Authorization header.
	 *   * POST variables.
	 *   * GET query string variables.
	 *
	 * @param   JOAuth1Message  $message  A JOAuth1Message object to populate with parameters.
	 *
	 * @return  boolean  True if parameters found, false otherwise.
	 *
	 * @since   12.1
	 */
	private function _fetchMessageFromRequest(JOAuth1Message $message)
	{
		// First we look and see if we have an appropriate Authorization header.
		$header = $this->_fetchAuthorizationHeader();

		if ($header)
		{
			$parameters = $this->_processAuthorizationHeader($header);

			if ($parameters)
			{
				// Bind the found parameters to the OAuth 1.0 message.
				$message->bind($parameters);

				return true;
			}
		}

		// If we didn't find an Authorization header or didn't find anything in it try the POST variables.
		$parameters = $this->_processPostVars();

		if ($parameters)
		{
			// Bind the found parameters to the OAuth 1.0 message.
			$message->bind($parameters);

			return true;
		}

		// If we didn't find an Authorization header or didn't find anything in it or in the POST variables, try the GET variables.
		$parameters = $this->_processGetVars();

		// We ignore the message if we are relying on GET and there is no consumer key.  This is done to allow the flow to pass through to the
		// authorise controller.
		if ($parameters && isset($parameters['oauth_consumer_key']))
		{
			// Bind the found parameters to the OAuth 1.0 message.
			$message->bind($parameters);

			return true;
		}

		return false;
	}

	/**
	 * Parse an OAuth authorization header and set any found OAuth parameters.
	 *
	 * @param   string  $header  Authorization header.
	 *
	 * @return  mixed  Array of OAuth 1.0 parameters if found or boolean false otherwise.
	 *
	 * @since   12.1
	 */
	private function _processAuthorizationHeader($header)
	{
		// Initialise variables.
		$parameters = array();

		// Get the OAuth 1.0 parameters.
		$reserved = JOAuth1Message::getReservedParameters();

		if (strncasecmp($header, 'OAuth ', 6) === 0)
		{
			$vs = explode(',', $header);

			foreach ($vs as $v)
			{
				if (strpos($v, '=') !== false)
				{
					$v = trim($v);
					list ($name, $value) = explode('=', $v, 2);

					if (!empty($value) && $value{0} == '"' && substr($value, -1) == '"')
					{
						$value = rawurldecode(substr($value, 1, -1));
					}

					if (in_array($name, $reserved))
					{
						$parameters[$name] = $value;
					}
				}
			}
		}

		// If we didn't find anything return false.
		if (empty($parameters))
		{
			return false;
		}

		return $parameters;
	}

	/**
	 * Parse the request query string for OAuth parameters.
	 *
	 * @return  mixed  Array of OAuth 1.0 parameters if found or boolean false otherwise.
	 *
	 * @since   12.1
	 */
	private function _processGetVars()
	{
		// Initialise variables.
		$parameters = array();

		// Iterate over the reserved parameters and look for them in the query string variables.
		foreach (JOAuth1Message::getReservedParameters() as $k)
		{
			if (isset($_GET[$k]))
			{
				$parameters[$k] = trim($this->input->get->getString($k));
			}
		}

		// If we didn't find anything return false.
		if (empty($parameters))
		{
			return false;
		}

		return $parameters;
	}

	/**
	 * Parse the request POST variables for OAuth parameters.
	 *
	 * @return  mixed  Array of OAuth 1.0 parameters if found or boolean false otherwise.
	 *
	 * @since   12.1
	 */
	private function _processPostVars()
	{
		// If we aren't handling a post request with urlencoded vars then there is nothing to do.
		// This used to enforce url encoded vars but this was relaxed to accomodate submitting files
		// from the browser which has to be (or maybe is) done via a form.
		if (strtoupper($this->input->getMethod()) != 'POST')
		{
			return false;
		}

		// Initialise variables.
		$parameters = array();

		// Iterate over the reserved parameters and look for them in the POST variables.
		foreach (JOAuth1Message::getReservedParameters() as $k)
		{
			if ($this->input->post->getString($k, false))
			{
				$parameters[$k] = trim($this->input->post->getString($k));
			}
		}

		// If we didn't find anything return false.
		if (empty($parameters))
		{
			return false;
		}

		return $parameters;
	}
}
