<?php
/**
 * @package     Joomla.Platform
 * @subpackage  OAuth1
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/*
Step 2

   The client redirects Jane's user-agent to the server's Resource Owner
   Authorization endpoint to obtain Jane's approval for accessing her
   private photos:

     https://photos.example.net/authorise?oauth_token=hh5s93j4hdidpola

   The server requests Jane to sign in using her username and password
   and if successful, asks her to approve granting 'printer.example.com'
   access to her private photos.  Jane approves the request and her
   user-agent is redirected to the callback URI provided by the client
   in the previous request (line breaks are for display purposes only):

     http://printer.example.com/ready?
     oauth_token=hh5s93j4hdidpola&oauth_verifier=hfdp7dh39dks9884

 */

/**
 * OAuth Controller class for authorising temporary credentials for the Joomla Platform.
 *
 * According to RFC 5849, this must be handled using a GET request, so route accordingly. When implementing this in your own
 * app you should provide some means of protection against CSRF attacks.
 *
 * @package     Joomla.Platform
 * @subpackage  OAuth1
 * @since       12.1
 */
class JOAuth1ControllerAuthorise extends JControllerBase
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

		// Get the credentials for the request.
		$credentials = $this->createCredentials();
		$credentials->load($this->input->get->get('oauth_token'));

		// Ensure the credentials are temporary.
		if ($credentials->getType() !== JOAuth1Credentials::TEMPORARY)
		{
			$this->app->setHeader('status', '400');
			$this->app->setBody('The token is not for a temporary credentials set.');

			return;
		}

		// Verify that we have a signed in user.
		if ($this->app->getIdentity()->get('guest'))
		{
			$this->app->setHeader('status', '400');
			$this->app->setBody('You must first sign in.');

			return;
		}

		// Attempt to authorise the credentials for the current user.
		$credentials->authorise($this->app->getIdentity()->get('id'));

		if ($credentials->getCallbackUrl() && $credentials->getCallbackUrl() != 'oob')
		{
			$this->app->redirect($credentials->getCallbackUrl());

			return;
		}

		$response = new stdClass;
		$response->status = 'Credentials authorised';
		$response->oauth_token = $credentials->getKey();
		$response->oauth_verifier = $credentials->getVerifierKey();

		$this->app->setBody(json_encode($response));
	}
}
