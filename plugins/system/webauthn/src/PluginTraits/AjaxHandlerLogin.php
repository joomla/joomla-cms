<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn\PluginTraits;

// Protect from unauthorized access
\defined('_JEXEC') or die();

use CBOR\Decoder;
use CBOR\OtherObject\OtherObjectManager;
use CBOR\Tag\TagObjectManager;
use Cose\Algorithm\Manager;
use Cose\Algorithm\Signature\ECDSA;
use Cose\Algorithm\Signature\EdDSA;
use Cose\Algorithm\Signature\RSA;
use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Plugin\System\Webauthn\CredentialRepository;
use Joomla\Plugin\System\Webauthn\Helper\Joomla;
use Laminas\Diactoros\ServerRequestFactory;
use RuntimeException;
use Throwable;
use Webauthn\AttestationStatement\AndroidKeyAttestationStatementSupport;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\FidoU2FAttestationStatementSupport;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AttestationStatement\PackedAttestationStatementSupport;
use Webauthn\AttestationStatement\TPMAttestationStatementSupport;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\TokenBinding\TokenBindingNotSupportedHandler;

/**
 * Ajax handler for akaction=login
 *
 * Verifies the response received from the browser and logs in the user
 *
 * @since  4.0.0
 */
trait AjaxHandlerLogin
{
	/**
	 * Returns the public key set for the user and a unique challenge in a Public Key Credential Request encoded as
	 * JSON.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since   4.0.0
	 */
	public function onAjaxWebauthnLogin(): void
	{
		// Load the language files
		$this->loadLanguage();

		$returnUrl = Joomla::getSessionVar('returnUrl', Uri::base(), 'plg_system_webauthn');
		$userId    = Joomla::getSessionVar('userId', 0, 'plg_system_webauthn');

		try
		{
			// Sanity check
			if (empty($userId))
			{
				throw new RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
			}

			// Make sure the user exists
			$user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId);

			if ($user->id != $userId)
			{
				throw new RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
			}

			// Validate the authenticator response
			$this->validateResponse();

			// Login the user
			Joomla::log('system', "Logging in the user", Log::INFO);
			Joomla::loginUser((int) $userId);
		}
		catch (Throwable $e)
		{
			Joomla::setSessionVar('publicKeyCredentialRequestOptions', null, 'plg_system_webauthn');
			Joomla::setSessionVar('userHandle', null, 'plg_system_webauthn');

			$response                = Joomla::getAuthenticationResponseObject();
			$response->status        = Authentication::STATUS_UNKNOWN;
			// phpcs:ignore
			$response->error_message = $e->getMessage();

			Joomla::log('system', sprintf("Received login failure. Message: %s", $e->getMessage()), Log::ERROR);

			// This also enqueues the login failure message for display after redirection. Look for JLog in that method.
			Joomla::processLoginFailure($response, null, 'system');
		}
		finally
		{
			/**
			 * This code needs to run no matter if the login succeeded or failed. It prevents replay attacks and takes
			 * the user back to the page they started from.
			 */

			// Remove temporary information for security reasons
			Joomla::setSessionVar('publicKeyCredentialRequestOptions', null, 'plg_system_webauthn');
			Joomla::setSessionVar('userHandle', null, 'plg_system_webauthn');
			Joomla::setSessionVar('returnUrl', null, 'plg_system_webauthn');
			Joomla::setSessionVar('userId', null, 'plg_system_webauthn');

			// Redirect back to the page we were before.
			Factory::getApplication()->redirect($returnUrl);
		}
	}

	/**
	 * Validate the authenticator response sent to us by the browser.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   4.0.0
	 */
	private function validateResponse(): void
	{
		// Initialize objects
		/** @var CMSApplication $app */
		$app                  = Factory::getApplication();
		$input                = $app->input;
		$credentialRepository = new CredentialRepository;

		// Retrieve data from the request and session
		$data = $input->getBase64('data', '');
		$data = base64_decode($data);

		if (empty($data))
		{
			throw new RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
		}

		$publicKeyCredentialRequestOptions = $this->getPKCredentialRequestOptions();

		// Cose Algorithm Manager
		$coseAlgorithmManager = new Manager;
		$coseAlgorithmManager->add(new ECDSA\ES256);
		$coseAlgorithmManager->add(new ECDSA\ES512);
		$coseAlgorithmManager->add(new EdDSA\EdDSA);
		$coseAlgorithmManager->add(new RSA\RS1);
		$coseAlgorithmManager->add(new RSA\RS256);
		$coseAlgorithmManager->add(new RSA\RS512);

		// Create a CBOR Decoder object
		$otherObjectManager = new OtherObjectManager;
		$tagObjectManager   = new TagObjectManager;
		$decoder            = new Decoder($tagObjectManager, $otherObjectManager);

		// Attestation Statement Support Manager
		$attestationStatementSupportManager = new AttestationStatementSupportManager;
		$attestationStatementSupportManager->add(new NoneAttestationStatementSupport);
		$attestationStatementSupportManager->add(new FidoU2FAttestationStatementSupport($decoder));

		/*
		$attestationStatementSupportManager->add(
			new AndroidSafetyNetAttestationStatementSupport(
				HttpFactory::getHttp(), 'GOOGLE_SAFETYNET_API_KEY', new RequestFactory
			)
		);
		*/
		$attestationStatementSupportManager->add(new AndroidKeyAttestationStatementSupport($decoder));
		$attestationStatementSupportManager->add(new TPMAttestationStatementSupport);
		$attestationStatementSupportManager->add(new PackedAttestationStatementSupport($decoder, $coseAlgorithmManager));

		// Attestation Object Loader
		$attestationObjectLoader = new AttestationObjectLoader($attestationStatementSupportManager, $decoder);

		// Public Key Credential Loader
		$publicKeyCredentialLoader = new PublicKeyCredentialLoader($attestationObjectLoader, $decoder);

		// The token binding handler
		$tokenBindingHandler = new TokenBindingNotSupportedHandler;

		// Extension Output Checker Handler
		$extensionOutputCheckerHandler = new ExtensionOutputCheckerHandler;

		// Authenticator Assertion Response Validator
		$authenticatorAssertionResponseValidator = new AuthenticatorAssertionResponseValidator(
			$credentialRepository,
			$decoder,
			$tokenBindingHandler,
			$extensionOutputCheckerHandler,
			$coseAlgorithmManager
		);

		// We init the Symfony Request object
		$request = ServerRequestFactory::fromGlobals();

		// Load the data
		$publicKeyCredential = $publicKeyCredentialLoader->load($data);
		$response            = $publicKeyCredential->getResponse();

		// Check if the response is an Authenticator Assertion Response
		if (!$response instanceof AuthenticatorAssertionResponse)
		{
			throw new RuntimeException('Not an authenticator assertion response');
		}

		// Check the response against the attestation request
		$userHandle = Joomla::getSessionVar('userHandle', null, 'plg_system_webauthn');
		/** @var AuthenticatorAssertionResponse $authenticatorAssertionResponse */
		$authenticatorAssertionResponse = $publicKeyCredential->getResponse();
		$authenticatorAssertionResponseValidator->check(
			$publicKeyCredential->getRawId(),
			$authenticatorAssertionResponse,
			$publicKeyCredentialRequestOptions,
			$request,
			$userHandle
		);
	}

	/**
	 * Retrieve the public key credential request options saved in the session. If they do not exist or are corrupt it
	 * is a hacking attempt and we politely tell the hacker to go away.
	 *
	 * @return  PublicKeyCredentialRequestOptions
	 *
	 * @since   4.0.0
	 */
	private function getPKCredentialRequestOptions(): PublicKeyCredentialRequestOptions
	{
		$encodedOptions = Joomla::getSessionVar('publicKeyCredentialRequestOptions', null, 'plg_system_webauthn');

		if (empty($encodedOptions))
		{
			throw new RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
		}

		try
		{
			$publicKeyCredentialCreationOptions = unserialize(base64_decode($encodedOptions));
		}
		catch (Exception $e)
		{
			throw new RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
		}

		if (!\is_object($publicKeyCredentialCreationOptions)
			|| !($publicKeyCredentialCreationOptions instanceof PublicKeyCredentialRequestOptions))
		{
			throw new RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
		}

		return $publicKeyCredentialCreationOptions;
	}
}
