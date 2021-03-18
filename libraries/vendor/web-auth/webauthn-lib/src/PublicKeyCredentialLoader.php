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
use Base64Url\Base64Url;
use CBOR\Decoder;
use CBOR\MapObject;
use CBOR\OtherObject\OtherObjectManager;
use CBOR\Tag\TagObjectManager;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Webauthn\AttestationStatement\AttestationObjectLoader;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientOutputsLoader;

class PublicKeyCredentialLoader
{
    private const FLAG_AT = 0b01000000;
    private const FLAG_ED = 0b10000000;

    /**
     * @var AttestationObjectLoader
     */
    private $attestationObjectLoader;

    /**
     * @var Decoder
     */
    private $decoder;

    public function __construct(AttestationObjectLoader $attestationObjectLoader, ?Decoder $decoder = null)
    {
        if (null !== $decoder) {
            @trigger_error('The argument "$decoder" is deprecated since 2.1 and will be removed in v3.0. Set null instead', E_USER_DEPRECATED);
        }
        $this->decoder = $decoder ?? new Decoder(new TagObjectManager(), new OtherObjectManager());
        $this->attestationObjectLoader = $attestationObjectLoader;
    }

    public function loadArray(array $json): PublicKeyCredential
    {
        foreach (['id', 'rawId', 'type'] as $key) {
            Assertion::keyExists($json, $key, sprintf('The parameter "%s" is missing', $key));
            Assertion::string($json[$key], sprintf('The parameter "%s" shall be a string', $key));
        }
        Assertion::keyExists($json, 'response', 'The parameter "response" is missing');
        Assertion::isArray($json['response'], 'The parameter "response" shall be an array');
        Assertion::eq($json['type'], 'public-key', sprintf('Unsupported type "%s"', $json['type']));

        $id = Base64Url::decode($json['id']);
        $rawId = Base64Url::decode($json['rawId']);
        Assertion::true(hash_equals($id, $rawId));

        $publicKeyCredential = new PublicKeyCredential(
            $json['id'],
            $json['type'],
            $rawId,
            $this->createResponse($json['response'])
        );

        return $publicKeyCredential;
    }

    public function load(string $data): PublicKeyCredential
    {
        $json = json_decode($data, true);
        Assertion::eq(JSON_ERROR_NONE, json_last_error(), 'Invalid data');

        return $this->loadArray($json);
    }

    private function createResponse(array $response): AuthenticatorResponse
    {
        Assertion::keyExists($response, 'clientDataJSON');
        switch (true) {
            case \array_key_exists('attestationObject', $response):
                $attestationObject = $this->attestationObjectLoader->load($response['attestationObject']);

                return new AuthenticatorAttestationResponse(CollectedClientData::createFormJson($response['clientDataJSON']), $attestationObject);
            case \array_key_exists('authenticatorData', $response) && \array_key_exists('signature', $response):
                $authData = Base64Url::decode($response['authenticatorData']);

                $authDataStream = new StringStream($authData);
                $rp_id_hash = $authDataStream->read(32);
                $flags = $authDataStream->read(1);
                $signCount = $authDataStream->read(4);
                $signCount = unpack('N', $signCount)[1];

                $attestedCredentialData = null;
                if (0 !== (\ord($flags) & self::FLAG_AT)) {
                    $aaguid = Uuid::fromBytes($authDataStream->read(16));
                    $credentialLength = $authDataStream->read(2);
                    $credentialLength = unpack('n', $credentialLength)[1];
                    $credentialId = $authDataStream->read($credentialLength);
                    $credentialPublicKey = $this->decoder->decode($authDataStream);
                    Assertion::isInstanceOf($credentialPublicKey, MapObject::class, 'The data does not contain a valid credential public key.');
                    $attestedCredentialData = new AttestedCredentialData($aaguid, $credentialId, (string) $credentialPublicKey);
                }

                $extension = null;
                if (0 !== (\ord($flags) & self::FLAG_ED)) {
                    $extension = $this->decoder->decode($authDataStream);
                    $extension = AuthenticationExtensionsClientOutputsLoader::load($extension);
                }
                Assertion::true($authDataStream->isEOF(), 'Invalid authentication data. Presence of extra bytes.');
                $authDataStream->close();
                $authenticatorData = new AuthenticatorData($authData, $rp_id_hash, $flags, $signCount, $attestedCredentialData, $extension);

                return new AuthenticatorAssertionResponse(
                    CollectedClientData::createFormJson($response['clientDataJSON']),
                    $authenticatorData,
                    Base64Url::decode($response['signature']),
                    $response['userHandle'] ?? null
                );
            default:
                throw new InvalidArgumentException('Unable to create the response object');
        }
    }
}
