<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn;

use Joomla\Application\ApplicationInterface;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\WebAuthn\Server;
use Joomla\Session\SessionInterface;
use Laminas\Diactoros\ServerRequestFactory;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\MetadataService\MetadataStatementRepository;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper class to aid in credentials creation (link an authenticator to a user account)
 *
 * @since   4.2.0
 * @internal
 */
final class Authentication
{
    /**
     * The credentials repository
     *
     * @var   CredentialRepository
     * @since 4.2.0
     */
    private $credentialsRepository;

    /**
     * The application we are running in.
     *
     * @var   CMSApplication
     * @since 4.2.0
     */
    private $app;

    /**
     * The application session
     *
     * @var   SessionInterface
     * @since 4.2.0
     */
    private $session;

    /**
     * A simple metadata statement repository
     *
     * @var   MetadataStatementRepository
     * @since 4.2.0
     */
    private $metadataRepository;

    /**
     * Should I permit attestation support if a Metadata Statement Repository object is present and
     * non-empty?
     *
     * @var   boolean
     * @since 4.2.0
     */
    private $attestationSupport = true;

    /**
     * Public constructor.
     *
     * @param   ?ApplicationInterface                 $app       The app we are running in
     * @param   ?SessionInterface                     $session   The app session object
     * @param   ?PublicKeyCredentialSourceRepository  $credRepo  Credentials repo
     * @param   ?MetadataStatementRepository          $mdsRepo   Authenticator metadata repo
     *
     * @since   4.2.0
     */
    public function __construct(
        ?ApplicationInterface $app = null,
        ?SessionInterface $session = null,
        ?PublicKeyCredentialSourceRepository $credRepo = null,
        ?MetadataStatementRepository $mdsRepo = null
    ) {
        $this->app                   = $app;
        $this->session               = $session;
        $this->credentialsRepository = $credRepo;
        $this->metadataRepository    = $mdsRepo;
    }

    /**
     * Get the known FIDO authenticators and their metadata
     *
     * @return  object[]
     * @since   4.2.0
     */
    public function getKnownAuthenticators(): array
    {
        $return = (!empty($this->metadataRepository) && method_exists($this->metadataRepository, 'getKnownAuthenticators'))
            ? $this->metadataRepository->getKnownAuthenticators()
            : [];

        // Add a generic authenticator entry
        $image = HTMLHelper::_('image', 'plg_system_webauthn/fido.png', '', '', true, true);
        $image = $image ? JPATH_ROOT . substr($image, \strlen(Uri::root(true))) : (JPATH_BASE . '/media/plg_system_webauthn/images/fido.png');
        $image = file_exists($image) ? file_get_contents($image) : '';

        $return[''] = (object) [
            'description' => Text::_('PLG_SYSTEM_WEBAUTHN_LBL_DEFAULT_AUTHENTICATOR'),
            'icon'        => 'data:image/png;base64,' . base64_encode($image),
        ];

        return $return;
    }

    /**
     * Returns the Public Key credential source repository object
     *
     * @return  PublicKeyCredentialSourceRepository|null
     *
     * @since   4.2.0
     */
    public function getCredentialsRepository(): ?PublicKeyCredentialSourceRepository
    {
        return $this->credentialsRepository;
    }

    /**
     * Returns the authenticator metadata repository object
     *
     * @return  MetadataStatementRepository|null
     *
     * @since   4.2.0
     */
    public function getMetadataRepository(): ?MetadataStatementRepository
    {
        return $this->metadataRepository;
    }

    /**
     * Generate the public key creation options.
     *
     * This is used for the first step of attestation (key registration).
     *
     * The PK creation options and the user ID are stored in the session.
     *
     * @param   User  $user  The Joomla user to create the public key for
     *
     * @return  PublicKeyCredentialCreationOptions
     *
     * @throws  \Exception
     * @since   4.2.0
     */
    public function getPubKeyCreationOptions(User $user): PublicKeyCredentialCreationOptions
    {
        /**
         * We will only ask for attestation information if our MDS is guaranteed not empty.
         *
         * We check that by trying to load a known good AAGUID (Yubico Security Key NFC). If it's
         * missing, we have failed to load the MDS data e.g. we could not contact the server, it
         * was taking too long, the cache is unwritable etc. In this case asking for attestation
         * conveyance would cause the attestation to fail (since we cannot verify its signature).
         * Therefore we have to ask for no attestation to be conveyed. The downside is that in this
         * case we do not have any information about the make and model of the authenticator. So be
         * it! After all, that's a convenience feature for us.
         */
        $attestationMode = $this->hasAttestationSupport()
            ? PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_DIRECT
            : PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE;

        $publicKeyCredentialCreationOptions = $this->getWebauthnServer()->generatePublicKeyCredentialCreationOptions(
            $this->getUserEntity($user),
            $attestationMode,
            $this->getPubKeyDescriptorsForUser($user),
            new AuthenticatorSelectionCriteria(
                AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_NO_PREFERENCE,
                false,
                AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED
            ),
            new AuthenticationExtensionsClientInputs()
        );

        // Save data in the session
        $this->session->set('plg_system_webauthn.publicKeyCredentialCreationOptions', base64_encode(serialize($publicKeyCredentialCreationOptions)));
        $this->session->set('plg_system_webauthn.registration_user_id', $user->id);

        return $publicKeyCredentialCreationOptions;
    }

    /**
     * Get the public key request options.
     *
     * This is used in the first step of the assertion (login) flow.
     *
     * @param   User  $user  The Joomla user to get the PK request options for
     *
     * @return  ?PublicKeyCredentialRequestOptions
     *
     * @throws  \Exception
     * @since   4.2.0
     */
    public function getPubkeyRequestOptions(User $user): ?PublicKeyCredentialRequestOptions
    {
        Log::add('Creating PK request options', Log::DEBUG, 'webauthn.system');
        $publicKeyCredentialRequestOptions = $this->getWebauthnServer()->generatePublicKeyCredentialRequestOptions(
            PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED,
            $this->getPubKeyDescriptorsForUser($user)
        );

        // Save in session. This is used during the verification stage to prevent replay attacks.
        $this->session->set('plg_system_webauthn.publicKeyCredentialRequestOptions', base64_encode(serialize($publicKeyCredentialRequestOptions)));

        return $publicKeyCredentialRequestOptions;
    }

    /**
     * Validate the authenticator assertion.
     *
     * This is used in the second step of the assertion (login) flow. The server verifies that the
     * assertion generated by the authenticator has not been tampered with.
     *
     * @param   string  $data  The data
     * @param   User    $user  The user we are trying to log in
     *
     * @return  PublicKeyCredentialSource
     *
     * @throws \Exception
     * @since   4.2.0
     */
    public function validateAssertionResponse(string $data, User $user): PublicKeyCredentialSource
    {
        // Make sure the public key credential request options in the session are valid
        $encodedPkOptions                  = $this->session->get('plg_system_webauthn.publicKeyCredentialRequestOptions', null);
        $serializedOptions                 = base64_decode($encodedPkOptions);
        $publicKeyCredentialRequestOptions = unserialize($serializedOptions);

        if (
            !\is_object($publicKeyCredentialRequestOptions)
            || empty($publicKeyCredentialRequestOptions)
            || !($publicKeyCredentialRequestOptions instanceof PublicKeyCredentialRequestOptions)
        ) {
            Log::add('Cannot retrieve valid plg_system_webauthn.publicKeyCredentialRequestOptions from the session', Log::NOTICE, 'webauthn.system');
            throw new \RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
        }

        $data = base64_decode($data);

        if (empty($data)) {
            Log::add('No or invalid assertion data received from the browser', Log::NOTICE, 'webauthn.system');

            throw new \RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
        }

        return $this->getWebauthnServer()->loadAndCheckAssertionResponse(
            $data,
            $this->getPKCredentialRequestOptions(),
            $this->getUserEntity($user),
            ServerRequestFactory::fromGlobals()
        );
    }

    /**
     * Validate the authenticator attestation.
     *
     * This is used for the second step of attestation (key registration), when the user has
     * interacted with the authenticator and we need to validate the legitimacy of its response.
     *
     * An exception will be returned on error. Also, under very rare conditions, you may receive
     * NULL instead of a PublicKeyCredentialSource object which means that something was off in the
     * returned data from the browser.
     *
     * @param   string  $data  The data
     *
     * @return  PublicKeyCredentialSource
     *
     * @throws  \Exception
     * @since   4.2.0
     */
    public function validateAttestationResponse(string $data): PublicKeyCredentialSource
    {
        // Retrieve the PublicKeyCredentialCreationOptions object created earlier and perform sanity checks
        $encodedOptions = $this->session->get('plg_system_webauthn.publicKeyCredentialCreationOptions', null);

        if (empty($encodedOptions)) {
            Log::add('Cannot retrieve plg_system_webauthn.publicKeyCredentialCreationOptions from the session', Log::NOTICE, 'webauthn.system');

            throw new \RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_NO_PK'));
        }

        /** @var PublicKeyCredentialCreationOptions|null $publicKeyCredentialCreationOptions */
        try {
            $publicKeyCredentialCreationOptions = unserialize(base64_decode($encodedOptions));
        } catch (\Exception $e) {
            Log::add('The plg_system_webauthn.publicKeyCredentialCreationOptions in the session is invalid', Log::NOTICE, 'webauthn.system');
            $publicKeyCredentialCreationOptions = null;
        }

        if (!\is_object($publicKeyCredentialCreationOptions) || !($publicKeyCredentialCreationOptions instanceof PublicKeyCredentialCreationOptions)) {
            throw new \RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_NO_PK'));
        }

        // Retrieve the stored user ID and make sure it's the same one in the request.
        $storedUserId = $this->session->get('plg_system_webauthn.registration_user_id', 0);
        $myUser       = $this->app->getIdentity() ?? new User();
        $myUserId     = $myUser->id;

        if (($myUser->guest) || ($myUserId != $storedUserId)) {
            $message = \sprintf('Invalid user! We asked the authenticator to attest user ID %d, the current user ID is %d', $storedUserId, $myUserId);
            Log::add($message, Log::NOTICE, 'webauthn.system');

            throw new \RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_USER'));
        }

        // We init the PSR-7 request object using Diactoros
        return $this->getWebauthnServer()->loadAndCheckAttestationResponse(
            base64_decode($data),
            $publicKeyCredentialCreationOptions,
            ServerRequestFactory::fromGlobals()
        );
    }

    /**
     * Get the authentiactor attestation support.
     *
     * @return  boolean
     * @since   4.2.0
     */
    public function hasAttestationSupport(): bool
    {
        return $this->attestationSupport
            && ($this->metadataRepository instanceof MetadataStatementRepository)
            && $this->metadataRepository->findOneByAAGUID('6d44ba9b-f6ec-2e49-b930-0c8fe920cb73');
    }

    /**
     * Change the authenticator attestation support.
     *
     * @param   bool  $attestationSupport  The desired setting
     *
     * @return  void
     * @since   4.2.0
     */
    public function setAttestationSupport(bool $attestationSupport): void
    {
        $this->attestationSupport = $attestationSupport;
    }

    /**
     * Try to find the site's favicon in the site's root, images, media, templates or current
     * template directory.
     *
     * @return  string|null
     *
     * @since   4.2.0
     */
    private function getSiteIcon(): ?string
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

        try {
            $paths = [
                '/',
                '/images/',
                '/media/',
                '/templates/',
                '/templates/' . $this->app->getTemplate(),
            ];
        } catch (\Exception $e) {
            return null;
        }

        foreach ($paths as $path) {
            foreach ($filenames as $filename) {
                $relFile  = $path . $filename;
                $filePath = JPATH_BASE . $relFile;

                if (is_file($filePath)) {
                    break 2;
                }

                $relFile = null;
            }
        }

        if (!isset($relFile) || \is_null($relFile)) {
            return null;
        }

        return rtrim(Uri::base(), '/') . '/' . ltrim($relFile, '/');
    }

    /**
     * Returns a User Entity object given a Joomla user
     *
     * @param   User  $user  The Joomla user to get the user entity for
     *
     * @return  PublicKeyCredentialUserEntity
     *
     * @since   4.2.0
     */
    private function getUserEntity(User $user): PublicKeyCredentialUserEntity
    {
        $repository = $this->credentialsRepository;

        return new PublicKeyCredentialUserEntity(
            $user->username,
            $repository->getHandleFromUserId($user->id),
            $user->name,
            $this->getAvatar($user, 64)
        );
    }

    /**
     * Get the user's avatar (through Gravatar)
     *
     * @param   User  $user  The Joomla user object
     * @param   int   $size  The dimensions of the image to fetch (default: 64 pixels)
     *
     * @return  string  The URL to the user's avatar
     *
     * @since   4.2.0
     */
    private function getAvatar(User $user, int $size = 64)
    {
        $scheme    = Uri::getInstance()->getScheme();
        $subdomain = ($scheme == 'https') ? 'secure' : 'www';

        return \sprintf('%s://%s.gravatar.com/avatar/%s.jpg?s=%u&d=mm', $scheme, $subdomain, md5($user->email), $size);
    }

    /**
     * Returns an array of the PK credential descriptors (registered authenticators) for the given
     * user.
     *
     * @param   User  $user  The Joomla user to get the PK descriptors for
     *
     * @return  PublicKeyCredentialDescriptor[]
     *
     * @since   4.2.0
     */
    private function getPubKeyDescriptorsForUser(User $user): array
    {
        $userEntity  = $this->getUserEntity($user);
        $repository  = $this->credentialsRepository;
        $descriptors = [];
        $records     = $repository->findAllForUserEntity($userEntity);

        foreach ($records as $record) {
            $descriptors[] = $record->getPublicKeyCredentialDescriptor();
        }

        return $descriptors;
    }

    /**
     * Retrieve the public key credential request options saved in the session.
     *
     * If they do not exist or are corrupt it is a hacking attempt and we politely tell the
     * attacker to go away.
     *
     * @return  PublicKeyCredentialRequestOptions
     *
     * @throws  \Exception
     * @since   4.2.0
     */
    private function getPKCredentialRequestOptions(): PublicKeyCredentialRequestOptions
    {
        $encodedOptions = $this->session->get('plg_system_webauthn.publicKeyCredentialRequestOptions', null);

        if (empty($encodedOptions)) {
            Log::add('Cannot retrieve plg_system_webauthn.publicKeyCredentialRequestOptions from the session', Log::NOTICE, 'webauthn.system');

            throw new \RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
        }

        try {
            $publicKeyCredentialRequestOptions = unserialize(base64_decode($encodedOptions));
        } catch (\Exception $e) {
            Log::add('Invalid plg_system_webauthn.publicKeyCredentialRequestOptions in the session', Log::NOTICE, 'webauthn.system');

            throw new \RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
        }

        if (!\is_object($publicKeyCredentialRequestOptions) || !($publicKeyCredentialRequestOptions instanceof PublicKeyCredentialRequestOptions)) {
            throw new \RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREATE_INVALID_LOGIN_REQUEST'));
        }

        return $publicKeyCredentialRequestOptions;
    }

    /**
     * Get the WebAuthn library's Server object which facilitates WebAuthn operations
     *
     * @return  Server
     * @throws  \Exception
     * @since    4.2.0
     */
    private function getWebauthnServer(): Server
    {
        $siteName = $this->app->get('sitename');

        // Credentials repository
        $repository = $this->credentialsRepository;

        // Relaying Party -- Our site
        $rpEntity = new PublicKeyCredentialRpEntity(
            $siteName,
            Uri::getInstance()->toString(['host']),
            $this->getSiteIcon()
        );

        $server = new Server($rpEntity, $repository, $this->metadataRepository);

        // Ed25519 is only available with libsodium
        if (!\function_exists('sodium_crypto_sign_seed_keypair')) {
            $server->setSelectedAlgorithms(['RS256', 'RS512', 'PS256', 'PS512', 'ES256', 'ES512']);
        }

        return $server;
    }
}
