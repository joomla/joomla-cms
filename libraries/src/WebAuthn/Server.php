<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAuthn;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Cose\Algorithm\Algorithm;
use Cose\Algorithm\ManagerFactory;
use Cose\Algorithm\Signature\ECDSA;
use Cose\Algorithm\Signature\EdDSA;
use Cose\Algorithm\Signature\RSA;
use ParagonIE\ConstantTime\Base64;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\AttestationStatement\AndroidKeyAttestationStatementSupport;
use Webauthn\AttestationStatement\AppleAttestationStatementSupport;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\FidoU2FAttestationStatementSupport;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AttestationStatement\PackedAttestationStatementSupport;
use Webauthn\AttestationStatement\TPMAttestationStatementSupport;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\Exception\InvalidDataException;
use Webauthn\MetadataService\MetadataStatementRepository;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\TokenBinding\IgnoreTokenBindingHandler;
use Webauthn\TokenBinding\TokenBindingHandler;

/**
 * WebAuthn server abstraction Class.
 *
 * @since  5.0.0
 * @internal
 */
final class Server
{
    /**
     * Default WebAuthn timeout in milliseconds
     *
     * @var int
     * @since 5.0.0
     */
    public int $timeout = 60000;

    /**
     * Random challenge size in bytes
     *
     * @var int
     * @since 5.0.0
     */
    public int $challengeSize = 32;

    /**
     * The relaying party entity
     *
     * @var PublicKeyCredentialRpEntity
     * @since 5.0.0
     */
    private PublicKeyCredentialRpEntity $rpEntity;

    /**
     * COSE algorithm manager factory instance
     *
     * @var ManagerFactory
     * @since 5.0.0
     */
    private ManagerFactory $coseAlgorithmManagerFactory;

    /**
     * Public Key credential source respoitory instance
     *
     * @var PublicKeyCredentialSourceRepository
     * @since 5.0.0
     */
    private PublicKeyCredentialSourceRepository $publicKeyCredentialSourceRepository;

    /**
     * Token binding handler
     *
     * @var TokenBindingHandler
     * @since 5.0.0
     * @deprecated 6.0 Will be removed when we upgrade to WebAuthn library 5.0 or later
     */
    private TokenBindingHandler $tokenBindingHandler;

    /**
     * Authentication extension output checker
     *
     * @var ExtensionOutputCheckerHandler
     * @since 5.0.0
     */
    private ExtensionOutputCheckerHandler $extensionOutputCheckerHandler;

    /**
     * COSE algorithms supported
     *
     * @var string[]
     * @since 5.0.0
     */
    private array $selectedAlgorithms;

    /**
     * Metadata statement repository service
     *
     * @var MetadataStatementRepository|null
     * @since 5.0.0
     */
    private ?MetadataStatementRepository $metadataStatementRepository;

    /**
     * Constructor
     *
     * @param PublicKeyCredentialRpEntity $relayingParty The relaying party entity (server information)
     * @param PublicKeyCredentialSourceRepository $publicKeyCredentialSourceRepository Public Key repository service
     * @param MetadataStatementRepository|null $metadataStatementRepository Metadata Statement (MDS) service (optional)
     *
     * @since 5.0.0
     */
    public function __construct(PublicKeyCredentialRpEntity $relayingParty, PublicKeyCredentialSourceRepository $publicKeyCredentialSourceRepository, ?MetadataStatementRepository $metadataStatementRepository)
    {
        $this->rpEntity = $relayingParty;

        $this->coseAlgorithmManagerFactory = new ManagerFactory();
        $this->coseAlgorithmManagerFactory->add('RS1', new RSA\RS1());
        $this->coseAlgorithmManagerFactory->add('RS256', new RSA\RS256());
        $this->coseAlgorithmManagerFactory->add('RS384', new RSA\RS384());
        $this->coseAlgorithmManagerFactory->add('RS512', new RSA\RS512());
        $this->coseAlgorithmManagerFactory->add('PS256', new RSA\PS256());
        $this->coseAlgorithmManagerFactory->add('PS384', new RSA\PS384());
        $this->coseAlgorithmManagerFactory->add('PS512', new RSA\PS512());
        $this->coseAlgorithmManagerFactory->add('ES256', new ECDSA\ES256());
        $this->coseAlgorithmManagerFactory->add('ES256K', new ECDSA\ES256K());
        $this->coseAlgorithmManagerFactory->add('ES384', new ECDSA\ES384());
        $this->coseAlgorithmManagerFactory->add('ES512', new ECDSA\ES512());
        $this->coseAlgorithmManagerFactory->add('Ed25519', new EdDSA\Ed25519());

        $this->selectedAlgorithms                  = ['RS256', 'RS512', 'PS256', 'PS512', 'ES256', 'ES512', 'Ed25519'];
        $this->publicKeyCredentialSourceRepository = $publicKeyCredentialSourceRepository;
        $this->tokenBindingHandler                 = new IgnoreTokenBindingHandler();
        $this->extensionOutputCheckerHandler       = new ExtensionOutputCheckerHandler();
        $this->metadataStatementRepository         = $metadataStatementRepository;
    }

    /**
     * Set the allowed COSE algorithms
     *
     * @param string[] $selectedAlgorithms
     *
     * @return void
     * @since 5.0.0
     */
    public function setSelectedAlgorithms(array $selectedAlgorithms): void
    {
        $this->selectedAlgorithms = $selectedAlgorithms;
    }

    /**
     * Add an allowed COSE algorithm
     *
     * @param string $alias Alias for the algorithm, e.g. RS256
     * @param Algorithm $algorithm The algorithm object instance
     *
     * @return void
     * @since 5.0.0
     */
    public function addAlgorithm(string $alias, Algorithm $algorithm): void
    {
        $this->coseAlgorithmManagerFactory->add($alias, $algorithm);
        $this->selectedAlgorithms[] = $alias;
        $this->selectedAlgorithms   = array_unique($this->selectedAlgorithms);
    }

    /**
     * Set the authentication extension output checker
     *
     * @param ExtensionOutputCheckerHandler $extensionOutputCheckerHandler
     *
     * @return void
     * @since 5.0.0
     */
    public function setExtensionOutputCheckerHandler(ExtensionOutputCheckerHandler $extensionOutputCheckerHandler): void
    {
        $this->extensionOutputCheckerHandler = $extensionOutputCheckerHandler;
    }

    /**
     * Generate the Public Key credentials creation options.
     *
     * This is used when registering an authenticator.
     *
     * @param PublicKeyCredentialUserEntity $userEntity The user entity which will be bound to the authenticator.
     * @param string|null $attestationMode Attestation conveyance mode. Default: not conveyed.
     * @param array $excludedPublicKeyDescriptors List of PKs of authenticators already registered
     * @param AuthenticatorSelectionCriteria|null $criteria Criteria for selecting an authenticator
     * @param AuthenticationExtensionsClientInputs|null $extensions Allowed client inputs
     *
     * @return PublicKeyCredentialCreationOptions
     * @since 5.0.0
     *
     * @throws InvalidDataException
     */
    public function generatePublicKeyCredentialCreationOptions(PublicKeyCredentialUserEntity $userEntity, ?string $attestationMode = PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE, array $excludedPublicKeyDescriptors = [], ?AuthenticatorSelectionCriteria $criteria = null, ?AuthenticationExtensionsClientInputs $extensions = null): PublicKeyCredentialCreationOptions
    {
        $coseAlgorithmManager = $this->coseAlgorithmManagerFactory
            ->generate(...$this->selectedAlgorithms);

        $publicKeyCredentialParametersList = [];

        foreach ($coseAlgorithmManager->all() as $algorithm) {
            $publicKeyCredentialParametersList[] = new PublicKeyCredentialParameters(
                PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                $algorithm::identifier()
            );
        }

        $criteria   = $criteria ?? new AuthenticatorSelectionCriteria();
        $extensions = $extensions ?? new AuthenticationExtensionsClientInputs();
        $challenge  = random_bytes($this->challengeSize);

        return (new PublicKeyCredentialCreationOptions(
            $this->rpEntity,
            $userEntity,
            $challenge,
            $publicKeyCredentialParametersList
        ))
            ->setTimeout($this->timeout)
            ->excludeCredentials(...$excludedPublicKeyDescriptors)
            ->setAuthenticatorSelection($criteria)
            ->setAttestation($attestationMode)
            ->setExtensions($extensions);
    }

    /**
     * Generate Public Key credential request options
     *
     * @param string|null $userVerification User verification mode. Default: no preference.
     * @param array $allowedPublicKeyDescriptors PKs of already registered keys the user is allowed to use.
     * @param AuthenticationExtensionsClientInputs|null $extensions Allowed client inputs.
     *
     * @return PublicKeyCredentialRequestOptions
     * @since 5.0.0
     *
     * @throws InvalidDataException
     */
    public function generatePublicKeyCredentialRequestOptions(?string $userVerification = PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED, array $allowedPublicKeyDescriptors = [], ?AuthenticationExtensionsClientInputs $extensions = null): PublicKeyCredentialRequestOptions
    {
        return (new PublicKeyCredentialRequestOptions(random_bytes($this->challengeSize)))
            ->setTimeout($this->timeout)
            ->setRpId($this->rpEntity->getId())
            ->allowCredentials(...$allowedPublicKeyDescriptors)
            ->setUserVerification($userVerification)
            ->setExtensions($extensions ?? new AuthenticationExtensionsClientInputs());
    }

    /**
     * Check the attestation (authenticator registration) response data and determine if it's a valid key.
     *
     * @param string $data The data received from the browser
     * @param PublicKeyCredentialCreationOptions $publicKeyCredentialCreationOptions The PK creation options used to request attestation.
     * @param ServerRequestInterface $serverRequest Abstraction of the request data
     *
     * @return PublicKeyCredentialSource
     * @since 5.0.0
     *
     * @throws \JsonException
     * @throws \Throwable
     */
    public function loadAndCheckAttestationResponse(string $data, PublicKeyCredentialCreationOptions $publicKeyCredentialCreationOptions, ServerRequestInterface $serverRequest): PublicKeyCredentialSource
    {
        // Remove padding from the response data
        $temp                              = json_decode($data);
        $temp->response                    = $temp?->response ?? new \stdClass();
        $temp->response->clientDataJSON    = rtrim($temp?->response?->clientDataJSON ?? '', '=');
        $temp->response->attestationObject = rtrim($temp?->response?->attestationObject ?? '', '=');
        $data                              = json_encode($temp);

        $attestationStatementSupportManager = $this->getAttestationStatementSupportManager();
        $attestationObjectLoader            = new AttestationObjectLoader($attestationStatementSupportManager);
        $publicKeyCredentialLoader          = new PublicKeyCredentialLoader($attestationObjectLoader);

        $publicKeyCredential   = $publicKeyCredentialLoader->load($data);
        $authenticatorResponse = $publicKeyCredential->getResponse();

        if (!$authenticatorResponse instanceof AuthenticatorAttestationResponse) {
            throw new \RuntimeException('Not an authenticator attestation response');
        }

        $authenticatorAttestationResponseValidator = new AuthenticatorAttestationResponseValidator(
            $attestationStatementSupportManager,
            $this->publicKeyCredentialSourceRepository,
            $this->tokenBindingHandler,
            $this->extensionOutputCheckerHandler
        );

        /**
         * For our limited scope we only need an MDS repository without a statusReportRepository or a
         * certificateChainValidator. For this reason we can't call the enableMetadataStatementSupport method. Instead,
         * we use reflection to only set the metadataStatementRepository for partial support of MDS in the authenticator
         * attestation response validator.
         *
         * BTW, the documentation of the library is wrong...
         */
        if (!empty($this->metadataStatementRepository)) {
            $refObj  = new \ReflectionObject($authenticatorAttestationResponseValidator);
            $refProp = $refObj->getProperty('metadataStatementRepository');
            $refProp->setAccessible(true);
            $refProp->setValue($authenticatorAttestationResponseValidator, $this->metadataStatementRepository);
        }

        return $authenticatorAttestationResponseValidator
            ->check($authenticatorResponse, $publicKeyCredentialCreationOptions, $serverRequest);
    }

    /**
     * Check the assertion (authentication) response data and determine if it's valid for the user.
     *
     * @param string $data The data received from the browser
     * @param PublicKeyCredentialRequestOptions $publicKeyCredentialRequestOptions THE PK request options used during authentication
     * @param PublicKeyCredentialUserEntity|null $userEntity The user we are checking against
     * @param ServerRequestInterface $serverRequest Abstraction of the request data
     *
     * @return PublicKeyCredentialSource
     * @since 5.0.0
     *
     * @throws \JsonException
     * @throws \Throwable
     */
    public function loadAndCheckAssertionResponse(string $data, PublicKeyCredentialRequestOptions $publicKeyCredentialRequestOptions, ?PublicKeyCredentialUserEntity $userEntity, ServerRequestInterface $serverRequest): PublicKeyCredentialSource
    {
        /**
         * The library expects $data to be a JSON-encoded array with a 'response' key which is an array of Base64Url-
         * encoded values WITHOUT padding. However, all browsers return padded values for Base64 encoding. Therefore,
         * we need to manipulate $data to remove the padding which breaks the library.
         */
        try {
            $data = @json_decode($data, true) ?? [];
        } catch (\Exception $e) {
            $data = [];
        }

        $data['response'] = $data['response'] ?? [];

        foreach (['authenticatorData', 'clientDataJSON', 'signature', 'userHandle'] as $key) {
            try {
                $value = Base64::decode($data['response'][$key] ?? '');
            } catch (\Exception $e) {
                $value = '';
            }
            $data['response'][$key] = Base64UrlSafe::encodeUnpadded($value);
        }

        $data = json_encode($data);

        // Now, we can proceed with checking the assertion response.
        $attestationStatementSupportManager = $this->getAttestationStatementSupportManager();
        $attestationObjectLoader            = new AttestationObjectLoader($attestationStatementSupportManager);
        $publicKeyCredentialLoader          = new PublicKeyCredentialLoader($attestationObjectLoader);

        $publicKeyCredential   = $publicKeyCredentialLoader->load($data);
        $authenticatorResponse = $publicKeyCredential->getResponse();

        if (!$authenticatorResponse instanceof AuthenticatorAssertionResponse) {
            throw new \RuntimeException('Not an authenticator assertion response');
        }

        $authenticatorAssertionResponseValidator = new AuthenticatorAssertionResponseValidator(
            $this->publicKeyCredentialSourceRepository,
            null,
            $this->extensionOutputCheckerHandler,
            $this->coseAlgorithmManagerFactory->generate(...$this->selectedAlgorithms)
        );

        return $authenticatorAssertionResponseValidator->check(
            $publicKeyCredential->getRawId(),
            $authenticatorResponse,
            $publicKeyCredentialRequestOptions,
            $serverRequest,
            $userEntity?->getId()
        );
    }

    /**
     * Get the attestation statement support manager object.
     *
     * @return AttestationStatementSupportManager
     * @since 5.0.0
     */
    private function getAttestationStatementSupportManager(): AttestationStatementSupportManager
    {
        $coseAlgorithmManager               = $this->coseAlgorithmManagerFactory->generate(...$this->selectedAlgorithms);
        $attestationStatementSupportManager = new AttestationStatementSupportManager();

        $attestationStatementSupportManager->add(new NoneAttestationStatementSupport());
        $attestationStatementSupportManager->add(new FidoU2FAttestationStatementSupport());
        $attestationStatementSupportManager->add(new AppleAttestationStatementSupport());
        $attestationStatementSupportManager->add(new AndroidKeyAttestationStatementSupport());
        $attestationStatementSupportManager->add(new TPMAttestationStatementSupport());
        $attestationStatementSupportManager->add(new PackedAttestationStatementSupport($coseAlgorithmManager));

        return $attestationStatementSupportManager;
    }
}
