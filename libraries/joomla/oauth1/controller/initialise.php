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
Step 1

   Before 'printer.example.com' can ask Jane to grant it access to the
   photos, it must first establish a set of temporary credentials with
   'photos.example.net' to identify the delegation request.  To do so,
   the client sends the following HTTPS [RFC2818] request to the server:

     POST /initialize HTTP/1.1
     Host: photos.example.net
     Authorization: OAuth realm="Photos",
        oauth_consumer_key="dpf43f3p2l4k3l03",
        oauth_signature_method="HMAC-SHA1",
        oauth_timestamp="137131200",
        oauth_nonce="wIjqoS",
        oauth_callback="http%3A%2F%2Fprinter.example.com%2Fready",
        oauth_signature="74KNZJeDHnMBp0EMJ9ZHt%2FXKycU%3D"

   The server validates the request and replies with a set of temporary
   credentials in the body of the HTTP response (line breaks are for
   display purposes only):

     HTTP/1.1 200 OK
     Content-Type: application/x-www-form-urlencoded

     oauth_token=hh5s93j4hdidpola&oauth_token_secret=hdhd0244k9j7ao03&
     oauth_callback_confirmed=true
 */

/**
 * OAuth Controller class for initiating temporary credentials for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  OAuth1
 * @since       12.1
 */
class JOAuth1ControllerInitialise extends JControllerBase
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

		// Generate temporary credentials for the client.
		$credentials = $this->createCredentials();
		$credentials->initialise($message->consumerKey, $message->callback, $this->app->get('oauth.tokenlifetime', 3600));

		// Build the response for the client.
		$response = array(
			'oauth_token' => $credentials->getKey(),
			'oauth_token_secret' => $credentials->getSecret(),
			'oauth_callback_confirmed' => true
		);

		// Set the application response code and body.
		$this->app->setHeader('status', '200');
		$this->app->setBody(http_build_query($response));
	}
}
