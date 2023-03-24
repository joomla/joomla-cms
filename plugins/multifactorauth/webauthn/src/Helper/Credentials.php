<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Multifactorauth.webauthn
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Multifactorauth\Webauthn\Helper;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Plugin\Multifactorauth\Webauthn\CredentialRepository;
use Joomla\Plugin\Multifactorauth\Webauthn\Hotfix\Server;
use Joomla\Session\SessionInterface;
use Laminas\Diactoros\ServerRequestFactory;
use ReflectionClass;
use RuntimeException;
use Webauthn\AttestedCredentialData;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper class to aid in credentials creation (link an authenticator to a user account)
 *
 * @since 4.2.0
 */
abstract class Credentials
{
    /**
     * Authenticator registration step 1: create a public key for credentials attestation.
     *
     * The result is a JSON string which can be used in Javascript code with navigator.credentials.create().
     *
     * @param   User   $user   The Joomla user to create the public key for
     *
     * @return  string
     * @throws  Exception  On error
     * @since   4.2.0
     */
    public static function requestAttestation(User $user): string
    {
        $publicKeyCredentialCreationOptions = self::getWebauthnServer($user->id)
            ->generatePublicKeyCredentialCreationOptions(
                self::getUserEntity($user),
                PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
                self::getPubKeyDescriptorsForUser($user),
                new AuthenticatorSelectionCriteria(
                    AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_NO_PREFERENCE,
                    false,
                    AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED
                ),
                new AuthenticationExtensionsClientInputs()
            );

        // Save data in the session
        $session = Factory::getApplication()->getSession();

        $session->set(
            'plg_multifactorauth_webauthn.publicKeyCredentialCreationOptions',
            base64_encode(serialize($publicKeyCredentialCreationOptions))
        );
        $session->set('plg_multifactorauth_webauthn.registration_user_id', $user->id);

        return json_encode($publicKeyCredentialCreationOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Authenticator registration step 2: verify the credentials attestation by the authenticator
     *
     * This returns the attested credential data on success.
     *
     * An exception will be returned on error. Also, under very rare conditions, you may receive NULL instead of
     * attested credential data which means that something was off in the returned data from the browser.
     *
     * @param   string   $data   The JSON-encoded data returned by the browser during the authentication flow
     *
     * @return  AttestedCredentialData|null
     * @throws  Exception  When something does not check out
     * @since   4.2.0
     */
    public static function verifyAttestation(string $data): ?PublicKeyCredentialSource
    {
        $session = Factory::getApplication()->getSession();

        // Retrieve the PublicKeyCredentialCreationOptions object created earlier and perform sanity checks
        $encodedOptions = $session->get('plg_multifactorauth_webauthn.publicKeyCredentialCreationOptions', null);

        if (empty($encodedOptions)) {
            throw new RuntimeException(Text::_('PLG_MULTIFACTORAUTH_WEBAUTHN_ERR_CREATE_NO_PK'));
        }

        try {
            $publicKeyCredentialCreationOptions = unserialize(base64_decode($encodedOptions));
        } catch (Exception $e) {
            $publicKeyCredentialCreationOptions = null;
        }

        if (!is_object($publicKeyCredentialCreationOptions) || !($publicKeyCredentialCreationOptions instanceof PublicKeyCredentialCreationOptions)) {
            throw new RuntimeException(Text::_('PLG_MULTIFACTORAUTH_WEBAUTHN_ERR_CREATE_NO_PK'));
        }

        // Retrieve the stored user ID and make sure it's the same one in the request.
        $storedUserId = $session->get('plg_multifactorauth_webauthn.registration_user_id', 0);
        $myUser       = Factory::getApplication()->getIdentity()
            ?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);
        $myUserId     = $myUser->id;

        if (($myUser->guest) || ($myUserId != $storedUserId)) {
            throw new RuntimeException(Text::_('PLG_MULTIFACTORAUTH_WEBAUTHN_ERR_CREATE_INVALID_USER'));
        }

        return self::getWebauthnServer($myUser->id)->loadAndCheckAttestationResponse(
            base64_decode($data),
            $publicKeyCredentialCreationOptions,
            ServerRequestFactory::fromGlobals()
        );
    }

    /**
     * Authentication step 1: create a challenge for key verification
     *
     * @param   int  $userId  The user ID to create a WebAuthn PK for
     *
     * @return  string
     * @throws  Exception  On error
     * @since   4.2.0
     */
    public static function requestAssertion(int $userId): string
    {
        $user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId);

        $publicKeyCredentialRequestOptions = self::getWebauthnServer($userId)
            ->generatePublicKeyCredentialRequestOptions(
                PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED,
                self::getPubKeyDescriptorsForUser($user)
            );

        // Save in session. This is used during the verification stage to prevent replay attacks.
        /** @var SessionInterface $session */
        $session = Factory::getApplication()->getSession();
        $session->set('plg_multifactorauth_webauthn.publicKeyCredentialRequestOptions', base64_encode(serialize($publicKeyCredentialRequestOptions)));
        $session->set('plg_multifactorauth_webauthn.userHandle', $userId);
        $session->set('plg_multifactorauth_webauthn.userId', $userId);

        // Return the JSON encoded data to the caller
        return json_encode($publicKeyCredentialRequestOptions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Authentication step 2: Checks if the browser's response to our challenge is valid.
     *
     * @param   string   $response   Base64-encoded response
     *
     * @return  void
     * @throws  Exception  When something does not check out.
     * @since   4.2.0
     */
    public static function verifyAssertion(string $response): void
    {
        /** @var SessionInterface $session */
        $session = Factory::getApplication()->getSession();

        $encodedPkOptions = $session->get('plg_multifactorauth_webauthn.publicKeyCredentialRequestOptions', null);
        $userHandle       = $session->get('plg_multifactorauth_webauthn.userHandle', null);
        $userId           = $session->get('plg_multifactorauth_webauthn.userId', null);

        $session->set('plg_multifactorauth_webauthn.publicKeyCredentialRequestOptions', null);
        $session->set('plg_multifactorauth_webauthn.userHandle', null);
        $session->set('plg_multifactorauth_webauthn.userId', null);

        if (empty($userId)) {
            throw new RuntimeException(Text::_('PLG_MULTIFACTORAUTH_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
        }

        // Make sure the user exists
        $user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId);

        if ($user->id != $userId) {
            throw new RuntimeException(Text::_('PLG_MULTIFACTORAUTH_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
        }

        // Make sure the user is ourselves (we cannot perform MFA on behalf of another user!)
        $currentUser = Factory::getApplication()->getIdentity()
            ?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);

        if ($currentUser->id != $userId) {
            throw new RuntimeException(Text::_('PLG_MULTIFACTORAUTH_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
        }

        // Make sure the public key credential request options in the session are valid
        $serializedOptions                 = base64_decode($encodedPkOptions);
        $publicKeyCredentialRequestOptions = unserialize($serializedOptions);

        if (
            !is_object($publicKeyCredentialRequestOptions)
            || empty($publicKeyCredentialRequestOptions)
            || !($publicKeyCredentialRequestOptions instanceof PublicKeyCredentialRequestOptions)
        ) {
            throw new RuntimeException(Text::_('PLG_MULTIFACTORAUTH_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
        }

        // Unserialize the browser response data
        $data = base64_decode($response);

        self::getWebauthnServer($user->id)->loadAndCheckAssertionResponse(
            $data,
            $publicKeyCredentialRequestOptions,
            self::getUserEntity($user),
            ServerRequestFactory::fromGlobals()
        );
    }

    /**
     * Get the user's avatar (through Gravatar)
     *
     * @param   User   $user   The Joomla user object
     * @param   int    $size   The dimensions of the image to fetch (default: 64 pixels)
     *
     * @return  string  The URL to the user's avatar
     *
     * @since 4.2.0
     */
    private static function getAvatar(User $user, int $size = 64)
    {
        $scheme    = Uri::getInstance()->getScheme();
        $subdomain = ($scheme == 'https') ? 'secure' : 'www';

        return sprintf('%s://%s.gravatar.com/avatar/%s.jpg?s=%u&d=mm', $scheme, $subdomain, md5($user->email), $size);
    }

    /**
     * Get a WebAuthn user entity for a Joomla user
     *
     * @param   User   $user  The user to get an entity for
     *
     * @return  PublicKeyCredentialUserEntity
     * @since   4.2.0
     */
    private static function getUserEntity(User $user): PublicKeyCredentialUserEntity
    {
        return new PublicKeyCredentialUserEntity(
            $user->username,
            $user->id,
            $user->name,
            self::getAvatar($user, 64)
        );
    }

    /**
     * Get the WebAuthn library server object
     *
     * @param   int|null  $userId  The user ID holding the list of valid authenticators
     *
     * @return  Server
     * @since   4.2.0
     */
    private static function getWebauthnServer(?int $userId): Server
    {
        /** @var CMSApplication $app */
        try {
            $app      = Factory::getApplication();
            $siteName = $app->get('sitename');
        } catch (Exception $e) {
            $siteName = 'Joomla! Site';
        }

        // Credentials repository
        $repository = new CredentialRepository($userId);

        // Relaying Party -- Our site
        $rpEntity = new PublicKeyCredentialRpEntity(
            $siteName ?? 'Joomla! Site',
            Uri::getInstance()->toString(['host']),
            ''
        );

        $refClass       = new ReflectionClass(Server::class);
        $refConstructor = $refClass->getConstructor();
        $params         = $refConstructor->getParameters();

        if (count($params) === 3) {
            // WebAuthn library 2, 3
            $server = new Server($rpEntity, $repository, null);
        } else {
            // WebAuthn library 4 (based on the deprecated comments in library version 3)
            $server = new Server($rpEntity, $repository);
        }

        // Ed25519 is only available with libsodium
        if (!function_exists('sodium_crypto_sign_seed_keypair')) {
            $server->setSelectedAlgorithms(['RS256', 'RS512', 'PS256', 'PS512', 'ES256', 'ES512']);
        }

        return $server;
    }

    /**
     * Returns an array of the PK credential descriptors (registered authenticators) for the given user.
     *
     * @param   User   $user  The user to get the descriptors for
     *
     * @return  PublicKeyCredentialDescriptor[]
     * @since   4.2.0
     */
    private static function getPubKeyDescriptorsForUser(User $user): array
    {
        $userEntity  = self::getUserEntity($user);
        $repository  = new CredentialRepository($user->id);
        $descriptors = [];
        $records     = $repository->findAllForUserEntity($userEntity);

        foreach ($records as $record) {
            $descriptors[] = $record->getPublicKeyCredentialDescriptor();
        }

        return $descriptors;
    }
}
