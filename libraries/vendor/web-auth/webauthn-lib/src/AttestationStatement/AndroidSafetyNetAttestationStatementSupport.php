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

namespace Webauthn\AttestationStatement;

use Assert\Assertion;
use InvalidArgumentException;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\Util\JsonConverter;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Webauthn\AuthenticatorData;
use Webauthn\CertificateToolbox;
use Webauthn\MetadataService\MetadataStatementRepository;
use Webauthn\TrustPath\CertificateTrustPath;

final class AndroidSafetyNetAttestationStatementSupport implements AttestationStatementSupport
{
    /**
     * @var string|null
     */
    private $apiKey;

    /**
     * @var ClientInterface|null
     */
    private $client;

    /**
     * @var CompactSerializer
     */
    private $jwsSerializer;

    /**
     * @var JWSVerifier|null
     */
    private $jwsVerifier;

    /**
     * @var RequestFactoryInterface|null
     */
    private $requestFactory;

    /**
     * @var int
     */
    private $leeway;

    /**
     * @var int
     */
    private $maxAge;

    /**
     * @var MetadataStatementRepository|null
     */
    private $metadataStatementRepository;

    public function __construct(?ClientInterface $client = null, ?string $apiKey = null, ?RequestFactoryInterface $requestFactory = null, int $leeway = 0, int $maxAge = 60000, ?MetadataStatementRepository $metadataStatementRepository = null)
    {
        foreach ([Algorithm\RS256::class] as $algorithm) {
            if (!class_exists($algorithm)) {
                throw new RuntimeException('The algorithms RS256 is missing. Did you forget to install the package web-token/jwt-signature-algorithm-rsa?');
            }
        }
        $this->jwsSerializer = new CompactSerializer();
        $this->apiKey = $apiKey;
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->initJwsVerifier();
        $this->leeway = $leeway;
        $this->maxAge = $maxAge;
        $this->metadataStatementRepository = $metadataStatementRepository;
    }

    public function name(): string
    {
        return 'android-safetynet';
    }

    public function load(array $attestation): AttestationStatement
    {
        Assertion::keyExists($attestation, 'attStmt', 'Invalid attestation object');
        foreach (['ver', 'response'] as $key) {
            Assertion::keyExists($attestation['attStmt'], $key, sprintf('The attestation statement value "%s" is missing.', $key));
            Assertion::notEmpty($attestation['attStmt'][$key], sprintf('The attestation statement value "%s" is empty.', $key));
        }
        $jws = $this->jwsSerializer->unserialize($attestation['attStmt']['response']);
        $jwsHeader = $jws->getSignature(0)->getProtectedHeader();
        Assertion::keyExists($jwsHeader, 'x5c', 'The response in the attestation statement must contain a "x5c" header.');
        Assertion::notEmpty($jwsHeader['x5c'], 'The "x5c" parameter in the attestation statement response must contain at least one certificate.');
        $certificates = $this->convertCertificatesToPem($jwsHeader['x5c']);
        $attestation['attStmt']['jws'] = $jws;

        return AttestationStatement::createBasic(
            $this->name(),
            $attestation['attStmt'],
            new CertificateTrustPath($certificates)
        );
    }

    public function isValid(string $clientDataJSONHash, AttestationStatement $attestationStatement, AuthenticatorData $authenticatorData): bool
    {
        $trustPath = $attestationStatement->getTrustPath();
        Assertion::isInstanceOf($trustPath, CertificateTrustPath::class, 'Invalid trust path');
        $certificates = $trustPath->getCertificates();
        if (null !== $this->metadataStatementRepository) {
            $certificates = CertificateToolbox::checkAttestationMedata(
                $attestationStatement,
                $authenticatorData->getAttestedCredentialData()->getAaguid()->toString(),
                $certificates,
                $this->metadataStatementRepository
            );
        }

        $parsedCertificate = openssl_x509_parse(current($certificates));
        Assertion::isArray($parsedCertificate, 'Invalid attestation object');
        Assertion::keyExists($parsedCertificate, 'subject', 'Invalid attestation object');
        Assertion::keyExists($parsedCertificate['subject'], 'CN', 'Invalid attestation object');
        Assertion::eq($parsedCertificate['subject']['CN'], 'attest.android.com', 'Invalid attestation object');

        /** @var JWS $jws */
        $jws = $attestationStatement->get('jws');
        $payload = $jws->getPayload();
        $this->validatePayload($payload, $clientDataJSONHash, $authenticatorData);

        //Check the signature
        $this->validateSignature($jws, $trustPath);

        //Check against Google service
        $this->validateUsingGoogleApi($attestationStatement);

        return true;
    }

    private function validatePayload(?string $payload, string $clientDataJSONHash, AuthenticatorData $authenticatorData): void
    {
        Assertion::notNull($payload, 'Invalid attestation object');
        $payload = JsonConverter::decode($payload);
        Assertion::isArray($payload, 'Invalid attestation object');
        Assertion::keyExists($payload, 'nonce', 'Invalid attestation object. "nonce" is missing.');
        Assertion::eq($payload['nonce'], base64_encode(hash('sha256', $authenticatorData->getAuthData().$clientDataJSONHash, true)), 'Invalid attestation object. Invalid nonce');
        Assertion::keyExists($payload, 'ctsProfileMatch', 'Invalid attestation object. "ctsProfileMatch" is missing.');
        Assertion::true($payload['ctsProfileMatch'], 'Invalid attestation object. "ctsProfileMatch" value is false.');
        Assertion::keyExists($payload, 'timestampMs', 'Invalid attestation object. Timestamp is missing.');
        Assertion::integer($payload['timestampMs'], 'Invalid attestation object. Timestamp shall be an integer.');
        $currentTime = time() * 1000;
        Assertion::lessOrEqualThan($payload['timestampMs'], $currentTime + $this->leeway, sprintf('Invalid attestation object. Issued in the future. Current time: %d. Response time: %d', $currentTime, $payload['timestampMs']));
        Assertion::lessOrEqualThan($currentTime - $payload['timestampMs'], $this->maxAge, sprintf('Invalid attestation object. Too old. Current time: %d. Response time: %d', $currentTime, $payload['timestampMs']));
    }

    private function validateSignature(JWS $jws, CertificateTrustPath $trustPath): void
    {
        $jwk = JWKFactory::createFromCertificate($trustPath->getCertificates()[0]);
        $isValid = $this->jwsVerifier->verifyWithKey($jws, $jwk, 0);
        Assertion::true($isValid, 'Invalid response signature');
    }

    private function validateUsingGoogleApi(AttestationStatement $attestationStatement): void
    {
        if (null === $this->client || null === $this->apiKey || null === $this->requestFactory) {
            return;
        }
        $uri = sprintf('https://www.googleapis.com/androidcheck/v1/attestations/verify?key=%s', urlencode($this->apiKey));
        $requestBody = sprintf('{"signedAttestation":"%s"}', $attestationStatement->get('response'));
        $request = $this->requestFactory->createRequest('POST', $uri);
        $request = $request->withHeader('content-type', 'application/json');
        $request->getBody()->write($requestBody);

        $response = $this->client->sendRequest($request);
        $this->checkGoogleApiResponse($response);
        $responseBody = $this->getResponseBody($response);
        $responseBodyJson = json_decode($responseBody, true);
        Assertion::eq(JSON_ERROR_NONE, json_last_error(), 'Invalid response.');
        Assertion::keyExists($responseBodyJson, 'isValidSignature', 'Invalid response.');
        Assertion::boolean($responseBodyJson['isValidSignature'], 'Invalid response.');
        Assertion::true($responseBodyJson['isValidSignature'], 'Invalid response.');
    }

    private function getResponseBody(ResponseInterface $response): string
    {
        $responseBody = '';
        $response->getBody()->rewind();
        do {
            $tmp = $response->getBody()->read(1024);
            if ('' === $tmp) {
                break;
            }
            $responseBody .= $tmp;
        } while (true);

        return $responseBody;
    }

    private function checkGoogleApiResponse(ResponseInterface $response): void
    {
        Assertion::eq(200, $response->getStatusCode(), 'Request did not succeeded');
        Assertion::true($response->hasHeader('content-type'), 'Unrecognized response');

        foreach ($response->getHeader('content-type') as $header) {
            if (0 === mb_strpos($header, 'application/json')) {
                return;
            }
        }

        throw new InvalidArgumentException('Unrecognized response');
    }

    private function convertCertificatesToPem(array $certificates): array
    {
        foreach ($certificates as $k => $v) {
            $certificates[$k] = CertificateToolbox::fixPEMStructure($v);
        }

        return $certificates;
    }

    private function initJwsVerifier(): void
    {
        $algorithmClasses = [
            Algorithm\RS256::class, Algorithm\RS384::class, Algorithm\RS512::class,
            Algorithm\PS256::class, Algorithm\PS384::class, Algorithm\PS512::class,
            Algorithm\ES256::class, Algorithm\ES384::class, Algorithm\ES512::class,
            Algorithm\EdDSA::class,
        ];
        $algorithms = [];
        foreach ($algorithmClasses as $key => $algorithm) {
            if (class_exists($algorithm)) {
                $algorithms[] = new $algorithm();
            }
        }
        $algorithmManager = new AlgorithmManager($algorithms);
        $this->jwsVerifier = new JWSVerifier($algorithmManager);
    }
}
