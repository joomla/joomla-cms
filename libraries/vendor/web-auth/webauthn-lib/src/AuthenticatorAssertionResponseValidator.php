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
use CBOR\Decoder;
use CBOR\OtherObject\OtherObjectManager;
use CBOR\Tag\TagObjectManager;
use Cose\Algorithm\Manager;
use Cose\Algorithm\Signature\Signature;
use Cose\Key\Key;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientOutputs;
use Webauthn\AuthenticationExtensions\ExtensionOutputCheckerHandler;
use Webauthn\TokenBinding\TokenBindingHandler;
use Webauthn\Util\CoseSignatureFixer;

class AuthenticatorAssertionResponseValidator
{
    /**
     * @var PublicKeyCredentialSourceRepository
     */
    private $publicKeyCredentialSourceRepository;

    /**
     * @var Decoder
     */
    private $decoder;

    /**
     * @var TokenBindingHandler
     */
    private $tokenBindingHandler;

    /**
     * @var ExtensionOutputCheckerHandler
     */
    private $extensionOutputCheckerHandler;

    /**
     * @var Manager|null
     */
    private $algorithmManager;

    public function __construct(PublicKeyCredentialSourceRepository $publicKeyCredentialSourceRepository, ?Decoder $decoder, TokenBindingHandler $tokenBindingHandler, ExtensionOutputCheckerHandler $extensionOutputCheckerHandler, Manager $algorithmManager)
    {
        if (null !== $decoder) {
            @trigger_error('The argument "$decoder" is deprecated since 2.1 and will be removed in v3.0. Set null instead', E_USER_DEPRECATED);
        }
        $this->publicKeyCredentialSourceRepository = $publicKeyCredentialSourceRepository;
        $this->decoder = $decoder ?? new Decoder(new TagObjectManager(), new OtherObjectManager());
        $this->tokenBindingHandler = $tokenBindingHandler;
        $this->extensionOutputCheckerHandler = $extensionOutputCheckerHandler;
        $this->algorithmManager = $algorithmManager;
    }

    /**
     * @see https://www.w3.org/TR/webauthn/#verifying-assertion
     */
    public function check(string $credentialId, AuthenticatorAssertionResponse $authenticatorAssertionResponse, PublicKeyCredentialRequestOptions $publicKeyCredentialRequestOptions, ServerRequestInterface $request, ?string $userHandle): PublicKeyCredentialSource
    {
        /* @see 7.2.1 */
        if (0 !== \count($publicKeyCredentialRequestOptions->getAllowCredentials())) {
            Assertion::true($this->isCredentialIdAllowed($credentialId, $publicKeyCredentialRequestOptions->getAllowCredentials()), 'The credential ID is not allowed.');
        }

        /* @see 7.2.2 */
        $publicKeyCredentialSource = $this->publicKeyCredentialSourceRepository->findOneByCredentialId($credentialId);
        Assertion::notNull($publicKeyCredentialSource, 'The credential ID is invalid.');

        /* @see 7.2.3 */
        $attestedCredentialData = $publicKeyCredentialSource->getAttestedCredentialData();
        $credentialUserHandle = $publicKeyCredentialSource->getUserHandle();
        $responseUserHandle = $authenticatorAssertionResponse->getUserHandle();

        /* @see 7.2.2 User Handle*/
        if (null !== $userHandle) { //If the user was identified before the authentication ceremony was initiated,
            Assertion::eq($credentialUserHandle, $userHandle, 'Invalid user handle');
            if (null !== $responseUserHandle && '' !== $responseUserHandle) {
                Assertion::eq($credentialUserHandle, $responseUserHandle, 'Invalid user handle');
            }
        } else {
            Assertion::notEmpty($responseUserHandle, 'User handle is mandatory');
            Assertion::eq($credentialUserHandle, $responseUserHandle, 'Invalid user handle');
        }

        $credentialPublicKey = $attestedCredentialData->getCredentialPublicKey();
        Assertion::notNull($credentialPublicKey, 'No public key available.');
        $stream = new StringStream($credentialPublicKey);
        $credentialPublicKeyStream = $this->decoder->decode($stream);
        Assertion::true($stream->isEOF(), 'Invalid key. Presence of extra bytes.');
        $stream->close();

        /** @see 7.2.4 */
        /** @see 7.2.5 */
        //Nothing to do. Use of objects directly

        /** @see 7.2.6 */
        $C = $authenticatorAssertionResponse->getClientDataJSON();

        /* @see 7.2.7 */
        Assertion::eq('webauthn.get', $C->getType(), 'The client data type is not "webauthn.get".');

        /* @see 7.2.8 */
        Assertion::true(hash_equals($publicKeyCredentialRequestOptions->getChallenge(), $C->getChallenge()), 'Invalid challenge.');

        /** @see 7.2.9 */
        $rpId = $publicKeyCredentialRequestOptions->getRpId() ?? $request->getUri()->getHost();
        $rpIdLength = mb_strlen($rpId);
        $parsedRelyingPartyId = parse_url($C->getOrigin());
        Assertion::isArray($parsedRelyingPartyId, 'Invalid origin');
        $scheme = $parsedRelyingPartyId['scheme'] ?? '';
        Assertion::eq('https', $scheme, 'Invalid scheme. HTTPS required.');
        $clientDataRpId = $parsedRelyingPartyId['host'] ?? '';
        Assertion::notEmpty($clientDataRpId, 'Invalid origin rpId.');
        Assertion::eq(mb_substr($clientDataRpId, -$rpIdLength), $rpId, 'rpId mismatch.');

        /* @see 7.2.10 */
        if (null !== $C->getTokenBinding()) {
            $this->tokenBindingHandler->check($C->getTokenBinding(), $request);
        }

        /** @see 7.2.11 */
        $facetId = $this->getFacetId($rpId, $publicKeyCredentialRequestOptions->getExtensions(), $authenticatorAssertionResponse->getAuthenticatorData()->getExtensions());
        $rpIdHash = hash('sha256', $rpId, true);
        Assertion::true(hash_equals($rpIdHash, $authenticatorAssertionResponse->getAuthenticatorData()->getRpIdHash()), 'rpId hash mismatch.');

        /* @see 7.2.12 */
        /* @see 7.2.13 */
        if (AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED === $publicKeyCredentialRequestOptions->getUserVerification()) {
            Assertion::true($authenticatorAssertionResponse->getAuthenticatorData()->isUserPresent(), 'User was not present');
            Assertion::true($authenticatorAssertionResponse->getAuthenticatorData()->isUserVerified(), 'User authentication required.');
        }

        /* @see 7.2.14 */
        $extensions = $authenticatorAssertionResponse->getAuthenticatorData()->getExtensions();
        if (null !== $extensions) {
            $this->extensionOutputCheckerHandler->check($extensions);
        }

        /** @see 7.2.15 */
        $getClientDataJSONHash = hash('sha256', $authenticatorAssertionResponse->getClientDataJSON()->getRawData(), true);

        /* @see 7.2.16 */
        $dataToVerify = $authenticatorAssertionResponse->getAuthenticatorData()->getAuthData().$getClientDataJSONHash;
        $signature = $authenticatorAssertionResponse->getSignature();
        $coseKey = new Key($credentialPublicKeyStream->getNormalizedData());
        $algorithm = $this->algorithmManager->get($coseKey->alg());
        Assertion::isInstanceOf($algorithm, Signature::class, 'Invalid algorithm identifier. Should refer to a signature algorithm');
        $signature = CoseSignatureFixer::fix($signature, $algorithm);
        Assertion::true($algorithm->verify($dataToVerify, $coseKey, $signature), 'Invalid signature.');

        /* @see 7.2.17 */
        $storedCounter = $publicKeyCredentialSource->getCounter();
        $currentCounter = $authenticatorAssertionResponse->getAuthenticatorData()->getSignCount();
        if (0 !== $currentCounter || 0 !== $storedCounter) {
            Assertion::greaterThan($currentCounter, $storedCounter, 'Invalid counter.');
        }
        $publicKeyCredentialSource->setCounter($currentCounter);
        $this->publicKeyCredentialSourceRepository->saveCredentialSource($publicKeyCredentialSource);

        /* @see 7.2.18 */
        //All good. We can continue.
        return $publicKeyCredentialSource;
    }

    private function isCredentialIdAllowed(string $credentialId, array $allowedCredentials): bool
    {
        foreach ($allowedCredentials as $allowedCredential) {
            if (hash_equals($allowedCredential->getId(), $credentialId)) {
                return true;
            }
        }

        return false;
    }

    private function getFacetId(string $rpId, AuthenticationExtensionsClientInputs $authenticationExtensionsClientInputs, ?AuthenticationExtensionsClientOutputs $authenticationExtensionsClientOutputs): string
    {
        switch (true) {
            case !$authenticationExtensionsClientInputs->has('appid'):
                return $rpId;
            case null === $authenticationExtensionsClientOutputs:
                return $rpId;
            case !$authenticationExtensionsClientOutputs->has('appid'):
                return $rpId;
            case true !== $authenticationExtensionsClientOutputs->get('appid'):
                return $rpId;
            default:
                return $authenticationExtensionsClientInputs->get('appid');
        }
    }
}
