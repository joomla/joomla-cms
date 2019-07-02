<?php
/**
 * @package   AkeebaPasswordlessLogin
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Passwordless\Webauthn\PluginTraits;

use Akeeba\Passwordless\Webauthn\CredentialRepository;
use Akeeba\Passwordless\Webauthn\Helper\Joomla;
use Exception;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialRequestOptions;

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Ajax handler for akaction=challenge
 *
 * Generates the public key and challenge which is used by the browser when logging in with Webauthn. This is the bit
 * which prevents tampering with the login process and replay attacks.
 */
trait AjaxHandlerChallenge
{
	/**
	 * Returns the public key set for the user and a unique challenge in a Public Key Credential Request encoded as
	 * JSON.
	 *
	 * @return   string  A JSON-encoded object or JSON-encoded false if the username is invalid or no credentials stored
	 *
	 * @throws   Exception
	 */
	public function onAjaxWebauthnChallenge()
	{
		// Initialize objects
		$input      = Joomla::getApplication()->input;
		$repository = new CredentialRepository();

		// Retrieve data from the request
		$username  = $input->getUsername('username', '');
		$returnUrl   = base64_encode(Joomla::getSessionVar('returnUrl', Uri::current(), 'plg_system_webauthn'));
		$returnUrl = $input->getBase64('returnUrl', $returnUrl);
		$returnUrl = base64_decode($returnUrl);

		// For security reasons the post-login redirection URL must be internal to the site.
		if (!Uri::isInternal($returnUrl))
		{
			// If the URL wasn't internal redirect to the site's root.
			$returnUrl = Uri::base();
		}

		Joomla::setSessionVar('returnUrl', $returnUrl, 'plg_system_webauthn');

		// Do I have a username?
		if (empty($username))
		{
			return json_encode(false);
		}

		// Is the username valid?
		try
		{
			$user_id = UserHelper::getUserId($username);
		}
		catch (Exception $e)
		{
			$user_id = 0;
		}

		if ($user_id <= 0)
		{
			return json_encode(false);
		}

		// Load the saved credentials into an array of PublicKeyCredentialDescriptor objects
		try
		{
			$credentials = $repository->getAll($user_id);
		}
		catch (Exception $e)
		{
			return json_encode(false);
		}

		foreach ($credentials as $record)
		{
			$credential = json_decode($record['credential'], true);

			if (!is_array($credential) || empty($credential) || !isset($credential['credentialPublicKey']))
			{
				continue;
			}

			try
			{
				$descriptor = new PublicKeyCredentialDescriptor(PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY, base64_decode($credential['credentialId']), [
					PublicKeyCredentialDescriptor::AUTHENTICATOR_TRANSPORT_USB,
					PublicKeyCredentialDescriptor::AUTHENTICATOR_TRANSPORT_NFC,
					PublicKeyCredentialDescriptor::AUTHENTICATOR_TRANSPORT_BLE,
					PublicKeyCredentialDescriptor::AUTHENTICATOR_TRANSPORT_INTERNAL,
				]);
			}
			catch (Exception $e)
			{
				continue;
			}

			$registeredPublicKeyCredentialDescriptors[] = $descriptor;
		}

		// Public Key Credential Request Options
		$publicKeyCredentialRequestOptions = new PublicKeyCredentialRequestOptions(
			random_bytes(32),
			60000,
			Uri::getInstance()->toString(['host']),
			$registeredPublicKeyCredentialDescriptors,
			PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED,
			new AuthenticationExtensionsClientInputs()
		);

		// Save in session. This is used during the verification stage to prevent replay attacks.
		Joomla::setSessionVar('publicKeyCredentialRequestOptions', base64_encode(serialize($publicKeyCredentialRequestOptions)), 'plg_system_webauthn');
		Joomla::setSessionVar('userHandle', $repository->getHandleFromUserId($user_id), 'plg_system_webauthn');
		Joomla::setSessionVar('userId', $user_id, 'plg_system_webauthn');

		// Return the JSON encoded data to the caller
		return json_encode($publicKeyCredentialRequestOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	}
}