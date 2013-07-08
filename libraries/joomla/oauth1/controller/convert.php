<?php
/**
 * @package     Joomla.Platform
 * @subpackage  OAuth1
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/*
Step 3

   The callback request informs the client that Jane completed the
   authorization process.  The client then requests a set of token
   credentials using its temporary credentials (over a secure Transport
   Layer Security (TLS) channel):

     POST /token HTTP/1.1
     Host: photos.example.net
     Authorization: OAuth realm="Photos",
        oauth_consumer_key="dpf43f3p2l4k3l03",
        oauth_token="hh5s93j4hdidpola",
        oauth_signature_method="HMAC-SHA1",
        oauth_timestamp="137131201",
        oauth_nonce="walatlh",
        oauth_verifier="hfdp7dh39dks9884",
        oauth_signature="gKgrFCywp7rO0OXSjdot%2FIHF7IU%3D"

   The server validates the request and replies with a set of token
   credentials in the body of the HTTP response:

     HTTP/1.1 200 OK
     Content-Type: application/x-www-form-urlencoded

     oauth_token=nnch734d00sl2jdk&oauth_token_secret=pfkkdhi9sl3r4s00
 */

/**
 * OAuth Controller class for converting authorised credentials to token credentials for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  OAuth1
 * @since       12.1
 */
class JOAuth1ControllerConvert extends JControllerBase
{
	/**
	 * Create the credentials
	 *
	 * @return  JOAuth1Credentials
	 *
	 * @since   12.3
	 */
	protected function createCredentials()
	{
		return new JOAuth1Credentials;
	}

	/**
	 * Handle the request.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function execute()
	{
		// Verify that we have an OAuth 1.0 application.
		if ((!$this->app instanceof JOAuth1ApplicationWeb))
		{
			throw new LogicException('Cannot perform OAuth 1.0 authorisation without an OAuth 1.0 application.');
		}

		// Get the OAuth message from the appliation.
		$message = $this->app->getMessage();

		// We need a valid signature to do initialisation.
		if (!$message->signature)
		{
			$this->app->sendInvalidAuthMessage('Invalid OAuth request signature.');

			return 0;
		}

		// Get the credentials for the request.
		$credentials = $this->createCredentials();
		$credentials->load($message->token);

		// Ensure the credentials are authorised.
		if ($credentials->getType() === JOAuth1Credentials::TOKEN)
		{
			$this->app->setHeader('status', '400');
			$this->app->setBody('The token is not for a temporary credentials set.');

			return;
		}

		// Ensure the credentials are authorised.
		if ($credentials->getType() === JOAuth1Credentials::TEMPORARY)
		{
			$this->app->setHeader('status', '400');
			$this->app->setBody('The token has not been authorised by the resource owner.');

			return;
		}

		// Convert the credentials to valid Token credentials for requesting protected resources.
		$credentials->convert();

		// Build the response for the client.
		$response = array('oauth_token' => $credentials->getKey(), 'oauth_token_secret' => $credentials->getSecret());

		// Set the application response code and body.
		$this->app->setHeader('status', '200');
		$this->app->setBody(http_build_query($response));
	}
}
