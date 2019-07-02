<?php
/**
 * @package   AkeebaPasswordlessLogin
 * @copyright Copyright (c)2018-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Passwordless\Webauthn\Helper;

use Akeeba\Passwordless\Webauthn\CredentialRepository;
use CBOR\Decoder;
use CBOR\OtherObject\OtherObjectManager;
use CBOR\Tag\TagObjectManager;
use Cose\Algorithm\Manager;
use Exception;
use Joomla\CMS\Crypt\Crypt;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use RuntimeException;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\FidoU2FAttestationStatementSupport;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AttestationStatement\PackedAttestationStatementSupport;
use Webauthn\AttestedCredentialData;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\TokenBinding\TokenBindingNotSupportedHandler;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Helper class to aid in credentials creation (link an authenticator to a user account)
 */
abstract class CredentialsCreation
{
	/**
	 * Create a public key for credentials creation. The result is a JSON string which can be used in Javascript code
	 * with navigator.credentials.create().
	 *
	 * @param   User  $user The Joomla user to create the public key for
	 *
	 * @return  string
	 */
	public static function createPublicKey(User $user): string
	{
		// Credentials repository
		$repository = new CredentialRepository();

		// Relaying Party -- Our site
		$rpEntity = new PublicKeyCredentialRpEntity(
			Joomla::getConfig()->get('sitename'),
			Uri::getInstance()->toString(['host']),
			self::getSiteIcon()
		);

		// User Entity
		$userEntity = new PublicKeyCredentialUserEntity(
			$user->username,
			$user->id,
			$user->name,
			self::getAvatar($user, 64)
		);

		// Challenge
		try
		{
			$challenge = random_bytes(32);
		}
		catch (Exception $e)
		{
			$challenge = Crypt::genRandomBytes(32);
		}

		// Public Key Credential Parameters
		$publicKeyCredentialParametersList = [
			new PublicKeyCredentialParameters('public-key', PublicKeyCredentialParameters::ALGORITHM_ES256),
		];

		// Timeout: 60 seconds (given in milliseconds)
		$timeout = 60000;

		// Devices to exclude (already set up authenticators)
		$excludedPublicKeyDescriptors = [];
		$records = $repository->getAll($user->id);

		foreach ($records as $record)
		{
			$data = @json_decode($record['credential'], true);

			if (is_null($data) || !is_array($data) || !isset($data['credentialPublicKey']))
			{
				continue;
			}

			$excludedPublicKeyDescriptors[] = new PublicKeyCredentialDescriptor(PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY, $data['credentialPublicKey']);
		}

		// Authenticator Selection Criteria (we used default values)
		$authenticatorSelectionCriteria = new AuthenticatorSelectionCriteria();

		// Extensions
		$publicKeyCredentialCreationOptions = new PublicKeyCredentialCreationOptions(
			$rpEntity,
			$userEntity,
			$challenge,
			$publicKeyCredentialParametersList,
			$timeout,
			$excludedPublicKeyDescriptors,
			$authenticatorSelectionCriteria,
			PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
			null
		);

		// Save data in the session
		Joomla::setSessionVar('publicKeyCredentialCreationOptions', base64_encode(serialize($publicKeyCredentialCreationOptions)), 'plg_system_webauthn');
		Joomla::setSessionVar('registration_user_id', $user->id, 'plg_system_webauthn');

		return json_encode($publicKeyCredentialCreationOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	}

	/**
	 * Validate the authentication data returned by the device and return the attested credential data on success.
	 *
	 * An exception will be returned on error. Also, under very rare conditions, you may receive NULL instead of
	 * attested credential data which means that something was off in the returned data from the browser.
	 *
	 * @param   string  $data  The JSON-encoded data returned by the browser during the authentication flow
	 *
	 * @return  AttestedCredentialData|null
	 */
	public static function validateAuthenticationData(string $data): ?AttestedCredentialData
	{
		// Retrieve the PublicKeyCredentialCreationOptions object created earlier and perform sanity checks
		$encodedOptions = Joomla::getSessionVar('publicKeyCredentialCreationOptions', null, 'plg_system_webauthn');

		if (empty($encodedOptions))
		{
			throw new RuntimeException(Joomla::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_NO_PK'));
		}

		try
		{
			$publicKeyCredentialCreationOptions = unserialize(base64_decode($encodedOptions));
		}
		catch (Exception $e)
		{
			$publicKeyCredentialCreationOptions = null;
		}

		if (!is_object($publicKeyCredentialCreationOptions) || !($publicKeyCredentialCreationOptions instanceof PublicKeyCredentialCreationOptions))
		{
			throw new RuntimeException(Joomla::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_NO_PK'));
		}

		// Retrieve the stored user ID and make sure it's the same one in the request.
		$storedUserId = Joomla::getSessionVar('registration_user_id', 0, 'plg_system_webauthn');
		$myUser       = Joomla::getUser();
		$myUserId     = $myUser->id;

		if (($myUser->guest) || ($myUserId != $storedUserId))
		{
			throw new RuntimeException(Joomla::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_USER'));
		}

		// Create a CBOR Decoder object
		$otherObjectManager = new OtherObjectManager();
		$tagObjectManager   = new TagObjectManager();
		$decoder            = new Decoder($tagObjectManager, $otherObjectManager);

		// The token binding handler
		$tokenBindingHandler = new TokenBindingNotSupportedHandler();

		// Attestation Statement Support Manager
		$coseAlgorithmManager               = new Manager();
		$attestationStatementSupportManager = new AttestationStatementSupportManager();
		$attestationStatementSupportManager->add(new NoneAttestationStatementSupport());
		$attestationStatementSupportManager->add(new FidoU2FAttestationStatementSupport($decoder));
		$attestationStatementSupportManager->add(new PackedAttestationStatementSupport($decoder, $coseAlgorithmManager));

		// Attestation Object Loader
		$attestationObjectLoader = new AttestationObjectLoader($attestationStatementSupportManager, $decoder);

		// Public Key Credential Loader
		$publicKeyCredentialLoader = new PublicKeyCredentialLoader($attestationObjectLoader, $decoder);

		// Credential Repository
		$credentialRepository = new CredentialRepository();

		// Extension output checker handler
		$extensionOutputCheckerHandler = new ExtensionOutputCheckerHandler();

		// Authenticator Attestation Response Validator
		$authenticatorAttestationResponseValidator = new AuthenticatorAttestationResponseValidator(
			$attestationStatementSupportManager,
			$credentialRepository,
			$tokenBindingHandler,
			$extensionOutputCheckerHandler
		);

		// Any Throwable from this point will bubble up to the GUI

		// We init the PSR-7 request object using Diactoros
		$request = ServerRequestFactory::fromGlobals();

		// Load the data
		$publicKeyCredential = $publicKeyCredentialLoader->load(base64_decode($data));
		$response            = $publicKeyCredential->getResponse();

		// Check if the response is an Authenticator Attestation Response
		if (!$response instanceof AuthenticatorAttestationResponse)
		{
			throw new RuntimeException('Not an authenticator attestation response');
		}

		// Check the response against the request
		$authenticatorAttestationResponseValidator->check($response, $publicKeyCredentialCreationOptions, $request);

		// Everything is OK here. You can get the PublicKeyCredentialDescriptor.
		$publicKeyCredentialDescriptor = $publicKeyCredential->getPublicKeyCredentialDescriptor();

		// Normally this condition should be true. Just make sure you received the credential data
		$attestedCredentialData = null;

		if ($response->getAttestationObject()->getAuthData()->hasAttestedCredentialData())
		{
			$attestedCredentialData = $response->getAttestationObject()->getAuthData()->getAttestedCredentialData();
		}

		return $attestedCredentialData;
	}

	/**
	 * Try to find the site's favicon in the site's root, images, media, templates or current template directory.
	 *
	 * @return  string|null
	 */
	protected static function getSiteIcon(): ?string
	{
		$filenames = [
			'apple-touch-icon.png',
			'apple_touch_icon.png',
			'favicon.ico',
			'favicon.png',
			'favicon.gif',
			'favicon.bmp',
			'favicon.jpg',
			'favicon.svg',
		];

		try
		{
			$paths = [
				'/',
				'/images/',
				'/media/',
				'/templates/',
				'/templates/' . Joomla::getApplication()->getTemplate(),
			];
		}
		catch (Exception $e)
		{
			return null;
		}

		foreach ($paths as $path)
		{
			foreach ($filenames as $filename)
			{
				$relFile  = $path . $filename;
				$filePath = JPATH_BASE . $relFile;

				if (is_file($filePath))
				{
					break 2;
				}

				$relFile = null;
			}
		}

		if (is_null($relFile))
		{
			return null;
		}

		return rtrim(Uri::base(), '/') . '/' . ltrim($relFile, '/');
	}

	/**
	 * Get the user's avatar (through Gravatar)
	 *
	 * @param   User  $user  The Joomla user object
	 * @param   int   $size  The dimensions of the image to fetch (default: 64 pixels)
	 *
	 * @return  string  The URL to the user's avatar
	 */
	public static function getAvatar(User $user, int $size = 64)
	{
		$scheme = Uri::getInstance()->getScheme();
		$subdomain = ($scheme == 'https') ? 'secure' : 'www';

		return sprintf('%s://%s.gravatar.com/avatar/%s.jpg?s=%u&d=mm', $scheme, $subdomain, md5($user->email), $size);
	}
}