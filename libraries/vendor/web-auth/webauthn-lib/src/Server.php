<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2019 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace Webauthn;

use Assert\Assertion;
use Cose\Algorithm\Algorithm;
use Cose\Algorithm\ManagerFactory;
use Cose\Algorithm\Signature\ECDSA;
use Cose\Algorithm\Signature\EdDSA;
use Cose\Algorithm\Signature\RSA;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\AttestationStatement\AndroidKeyAttestationStatementSupport;
use Webauthn\AttestationStatement\AndroidSafetyNetAttestationStatementSupport;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\FidoU2FAttestationStatementSupport;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AttestationStatement\PackedAttestationStatementSupport;
use Webauthn\AttestationStatement\TPMAttestationStatementSupport;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\MetadataService\MetadataStatementRepository;
use Webauthn\TokenBinding\TokenBindingNotSupportedHandler;

class Server
{
    /**
     * @var int
     */
    public $timeout = 60000;

    /**
     * @var int
     */
    public $challengeSize = 32;

    /**
     * @var PublicKeyCredentialRpEntity
     */
    private $rpEntity;

    /**
     * @var ManagerFactory
     */
    private $coseAlgorithmManagerFactory;

    /**
     * @var PublicKeyCredentialSourceRepository
     */
    private $publicKeyCredentialSourceRepository;

    /**
     * @var TokenBindingNotSupportedHandler
     */
    private $tokenBindingHandler;

    /**
     * @var ExtensionOutputCheckerHandler
     */
    private $extensionOutputCheckerHandler;

    /**
     * @var string[]
     */
    private $selectedAlgorithms;

    /**
     * @var MetadataStatementRepository|null
     */
    private $metadataStatementRepository;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var string
     */
    private $googleApiKey;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

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

        $this->selectedAlgorithms = ['RS256', 'RS512', 'PS256', 'PS512', 'ES256', 'ES512', 'Ed25519'];
        $this->publicKeyCredentialSourceRepository = $publicKeyCredentialSourceRepository;
        $this->tokenBindingHandler = new TokenBindingNotSupportedHandler();
        $this->extensionOutputCheckerHandler = new ExtensionOutputCheckerHandler();
        $this->metadataStatementRepository = $metadataStatementRepository;
    }

    /**
     * @param string[] $selectedAlgorithms
     */
    public function setSelectedAlgorithms(array $selectedAlgorithms): void
    {
        $this->selectedAlgorithms = $selectedAlgorithms;
    }

    public function setTokenBindingHandler(TokenBindingNotSupportedHandler $tokenBindingHandler): void
    {
        $this->tokenBindingHandler = $tokenBindingHandler;
    }

    public function addAlgorithm(string $alias, Algorithm $algorithm): void
    {
        $this->coseAlgorithmManagerFactory->add($alias, $algorithm);
        $this->selectedAlgorithms[] = $alias;
        $this->selectedAlgorithms = array_unique($this->selectedAlgorithms);
    }

    public function setExtensionOutputCheckerHandler(ExtensionOutputCheckerHandler $extensionOutputCheckerHandler): void
    {
        $this->extensionOutputCheckerHandler = $extensionOutputCheckerHandler;
    }

    /**
     * @param PublicKeyCredentialDescriptor[] $excludedPublicKeyDescriptors
     */
    public function generatePublicKeyCredentialCreationOptions(PublicKeyCredentialUserEntity $userEntity, ?string $attestationMode = PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE, array $excludedPublicKeyDescriptors = [], ?AuthenticatorSelectionCriteria $criteria = null, ?AuthenticationExtensionsClientInputs $extensions = null): PublicKeyCredentialCreationOptions
    {
        $coseAlgorithmManager = $this->coseAlgorithmManagerFactory->create($this->selectedAlgorithms);
        $publicKeyCredentialParametersList = [];
        foreach ($coseAlgorithmManager->all() as $algorithm) {
            $publicKeyCredentialParametersList[] = new PublicKeyCredentialParameters(
                PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                $algorithm::identifier()
            );
        }
        $criteria = $criteria ?? new AuthenticatorSelectionCriteria();
        $extensions = $extensions ?? new AuthenticationExtensionsClientInputs();
        $challenge = random_bytes($this->challengeSize);

        return new PublicKeyCredentialCreationOptions(
            $this->rpEntity,
            $userEntity,
            $challenge,
            $publicKeyCredentialParametersList,
            $this->timeout,
            $excludedPublicKeyDescriptors,
            $criteria,
            $attestationMode,
            $extensions
        );
    }

    /**
     * @param PublicKeyCredentialDescriptor[] $allowedPublicKeyDescriptors
     */
    public function generatePublicKeyCredentialRequestOptions(?string $userVerification = PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_PREFERRED, array $allowedPublicKeyDescriptors = [], ?AuthenticationExtensionsClientInputs $extensions = null): PublicKeyCredentialRequestOptions
    {
        return new PublicKeyCredentialRequestOptions(
            random_bytes($this->challengeSize),
            $this->timeout,
            $this->rpEntity->getId(),
            $allowedPublicKeyDescriptors,
            $userVerification,
            $extensions ?? new AuthenticationExtensionsClientInputs()
        );
    }

    public function loadAndCheckAttestationResponse(string $data, PublicKeyCredentialCreationOptions $publicKeyCredentialCreationOptions, ServerRequestInterface $serverRequest): PublicKeyCredentialSource
    {
        $attestationStatementSupportManager = $this->getAttestationStatementSupportManager();
        $attestationObjectLoader = new AttestationObjectLoader($attestationStatementSupportManager);
        $publicKeyCredentialLoader = new PublicKeyCredentialLoader($attestationObjectLoader);

        $publicKeyCredential = $publicKeyCredentialLoader->load($data);
        $authenticatorResponse = $publicKeyCredential->getResponse();
        Assertion::isInstanceOf($authenticatorResponse, AuthenticatorAttestationResponse::class, 'Not an authenticator attestation response');

        $authenticatorAttestationResponseValidator = new AuthenticatorAttestationResponseValidator(
            $attestationStatementSupportManager,
            $this->publicKeyCredentialSourceRepository,
            $this->tokenBindingHandler,
            $this->extensionOutputCheckerHandler
        );

        return $authenticatorAttestationResponseValidator->check($authenticatorResponse, $publicKeyCredentialCreationOptions, $serverRequest);
    }

    public function loadAndCheckAssertionResponse(string $data, PublicKeyCredentialRequestOptions $publicKeyCredentialRequestOptions, ?PublicKeyCredentialUserEntity $userEntity, ServerRequestInterface $serverRequest): PublicKeyCredentialSource
    {
        $attestationStatementSupportManager = $this->getAttestationStatementSupportManager();
        $attestationObjectLoader = new AttestationObjectLoader($attestationStatementSupportManager);
        $publicKeyCredentialLoader = new PublicKeyCredentialLoader($attestationObjectLoader);

        $publicKeyCredential = $publicKeyCredentialLoader->load($data);
        $authenticatorResponse = $publicKeyCredential->getResponse();
        Assertion::isInstanceOf($authenticatorResponse, AuthenticatorAssertionResponse::class, 'Not an authenticator assertion response');

        $authenticatorAssertionResponseValidator = new AuthenticatorAssertionResponseValidator(
            $this->publicKeyCredentialSourceRepository,
            null,
            $this->tokenBindingHandler,
            $this->extensionOutputCheckerHandler,
            $this->coseAlgorithmManagerFactory->create($this->selectedAlgorithms)
        );

        return $authenticatorAssertionResponseValidator->check(
            $publicKeyCredential->getRawId(),
            $authenticatorResponse,
            $publicKeyCredentialRequestOptions,
            $serverRequest,
            null !== $userEntity ? $userEntity->getId() : null
        );
    }

    public function enforceAndroidSafetyNetVerification(ClientInterface $client, string $apiKey, RequestFactoryInterface $requestFactory): void
    {
        $this->httpClient = $client;
        $this->googleApiKey = $apiKey;
        $this->requestFactory = $requestFactory;
    }

    private function getAttestationStatementSupportManager(): AttestationStatementSupportManager
    {
        $attestationStatementSupportManager = new AttestationStatementSupportManager();
        $attestationStatementSupportManager->add(new NoneAttestationStatementSupport());
        if (null !== $this->metadataStatementRepository) {
            $coseAlgorithmManager = $this->coseAlgorithmManagerFactory->create($this->selectedAlgorithms);
            $attestationStatementSupportManager->add(new FidoU2FAttestationStatementSupport(null, $this->metadataStatementRepository));
            $attestationStatementSupportManager->add(new AndroidSafetyNetAttestationStatementSupport($this->httpClient, $this->googleApiKey, $this->requestFactory, 2000, 60000, $this->metadataStatementRepository));
            $attestationStatementSupportManager->add(new AndroidKeyAttestationStatementSupport(null, $this->metadataStatementRepository));
            $attestationStatementSupportManager->add(new TPMAttestationStatementSupport($this->metadataStatementRepository));
            $attestationStatementSupportManager->add(new PackedAttestationStatementSupport(null, $coseAlgorithmManager, $this->metadataStatementRepository));
        }

        return $attestationStatementSupportManager;
    }
}
