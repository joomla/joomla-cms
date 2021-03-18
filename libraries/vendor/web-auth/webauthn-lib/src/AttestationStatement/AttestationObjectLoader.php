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
use Base64Url\Base64Url;
use CBOR\Decoder;
use CBOR\MapObject;
use CBOR\OtherObject\OtherObjectManager;
use CBOR\Tag\TagObjectManager;
use Ramsey\Uuid\Uuid;
use Webauthn\AttestedCredentialData;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientOutputsLoader;
use Webauthn\AuthenticatorData;
use Webauthn\StringStream;

class AttestationObjectLoader
{
    private const FLAG_AT = 0b01000000;
    private const FLAG_ED = 0b10000000;

    /**
     * @var Decoder
     */
    private $decoder;

    /**
     * @var AttestationStatementSupportManager
     */
    private $attestationStatementSupportManager;

    public function __construct(AttestationStatementSupportManager $attestationStatementSupportManager, ?Decoder $decoder = null)
    {
        if (null !== $decoder) {
            @trigger_error('The argument "$decoder" is deprecated since 2.1 and will be removed in v3.0. Set null instead', E_USER_DEPRECATED);
        }
        $this->decoder = $decoder ?? new Decoder(new TagObjectManager(), new OtherObjectManager());
        $this->attestationStatementSupportManager = $attestationStatementSupportManager;
    }

    public function load(string $data): AttestationObject
    {
        $decodedData = Base64Url::decode($data);
        $stream = new StringStream($decodedData);
        $parsed = $this->decoder->decode($stream);
        $attestationObject = $parsed->getNormalizedData();
        Assertion::true($stream->isEOF(), 'Invalid attestation object. Presence of extra bytes.');
        $stream->close();
        Assertion::isArray($attestationObject, 'Invalid attestation object');
        Assertion::keyExists($attestationObject, 'authData', 'Invalid attestation object');
        Assertion::keyExists($attestationObject, 'fmt', 'Invalid attestation object');
        Assertion::keyExists($attestationObject, 'attStmt', 'Invalid attestation object');
        $authData = $attestationObject['authData'];

        $attestationStatementSupport = $this->attestationStatementSupportManager->get($attestationObject['fmt']);
        $attestationStatement = $attestationStatementSupport->load($attestationObject);

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

        return new AttestationObject($data, $attestationStatement, $authenticatorData);
    }
}
