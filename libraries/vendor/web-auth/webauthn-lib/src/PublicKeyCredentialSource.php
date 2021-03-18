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
use InvalidArgumentException;
use JsonSerializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Throwable;
use Webauthn\TrustPath\TrustPath;
use Webauthn\TrustPath\TrustPathLoader;

/**
 * @see https://www.w3.org/TR/webauthn/#iface-pkcredential
 */
class PublicKeyCredentialSource implements JsonSerializable
{
    /**
     * @var string
     */
    protected $publicKeyCredentialId;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string[]
     */
    protected $transports;

    /**
     * @var string
     */
    protected $attestationType;

    /**
     * @var TrustPath
     */
    protected $trustPath;

    /**
     * @var UuidInterface
     */
    protected $aaguid;

    /**
     * @var string
     */
    protected $credentialPublicKey;

    /**
     * @var string
     */
    protected $userHandle;

    /**
     * @var int
     */
    protected $counter;

    public function __construct(string $publicKeyCredentialId, string $type, array $transports, string $attestationType, TrustPath $trustPath, UuidInterface $aaguid, string $credentialPublicKey, string $userHandle, int $counter)
    {
        $this->publicKeyCredentialId = $publicKeyCredentialId;
        $this->type = $type;
        $this->transports = $transports;
        $this->aaguid = $aaguid;
        $this->credentialPublicKey = $credentialPublicKey;
        $this->userHandle = $userHandle;
        $this->counter = $counter;
        $this->attestationType = $attestationType;
        $this->trustPath = $trustPath;
    }

    /**
     * @deprecated Deprecated since v2.1. Will be removed in v3.0. Please use response from the credential source returned by the AuthenticatorAttestationResponseValidator after "check" method
     */
    public static function createFromPublicKeyCredential(PublicKeyCredential $publicKeyCredential, string $userHandle): self
    {
        $response = $publicKeyCredential->getResponse();
        Assertion::isInstanceOf($response, AuthenticatorAttestationResponse::class, 'This method is only available with public key credential containing an authenticator attestation response.');
        $publicKeyCredentialDescriptor = $publicKeyCredential->getPublicKeyCredentialDescriptor();
        $attestationStatement = $response->getAttestationObject()->getAttStmt();
        $authenticatorData = $response->getAttestationObject()->getAuthData();
        $attestedCredentialData = $authenticatorData->getAttestedCredentialData();
        Assertion::notNull($attestedCredentialData, 'No attested credential data available');

        return new self(
            $publicKeyCredentialDescriptor->getId(),
            $publicKeyCredentialDescriptor->getType(),
            $publicKeyCredentialDescriptor->getTransports(),
            $attestationStatement->getType(),
            $attestationStatement->getTrustPath(),
            $attestedCredentialData->getAaguid(),
            $attestedCredentialData->getCredentialPublicKey(),
            $userHandle,
            $authenticatorData->getSignCount()
        );
    }

    public function getPublicKeyCredentialId(): string
    {
        return $this->publicKeyCredentialId;
    }

    public function getPublicKeyCredentialDescriptor(): PublicKeyCredentialDescriptor
    {
        return new PublicKeyCredentialDescriptor(
            $this->type,
            $this->publicKeyCredentialId,
            $this->transports
        );
    }

    public function getAttestationType(): string
    {
        return $this->attestationType;
    }

    public function getTrustPath(): TrustPath
    {
        return $this->trustPath;
    }

    public function getAttestedCredentialData(): AttestedCredentialData
    {
        return new AttestedCredentialData(
            $this->aaguid,
            $this->publicKeyCredentialId,
            $this->credentialPublicKey
        );
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string[]
     */
    public function getTransports(): array
    {
        return $this->transports;
    }

    public function getAaguid(): UuidInterface
    {
        return $this->aaguid;
    }

    public function getCredentialPublicKey(): string
    {
        return $this->credentialPublicKey;
    }

    public function getUserHandle(): string
    {
        return $this->userHandle;
    }

    public function getCounter(): int
    {
        return $this->counter;
    }

    public function setCounter(int $counter): void
    {
        $this->counter = $counter;
    }

    public static function createFromArray(array $data): self
    {
        $keys = array_keys(get_class_vars(self::class));
        foreach ($keys as $key) {
            Assertion::keyExists($data, $key, sprintf('The parameter "%s" is missing', $key));
        }
        switch (true) {
            case 36 === mb_strlen($data['aaguid'], '8bit'):
                $uuid = Uuid::fromString($data['aaguid']);
                break;
            default: // Kept for compatibility with old format
                $decoded = base64_decode($data['aaguid'], true);
                Assertion::string($decoded, 'Invalid AAGUID');
                $uuid = Uuid::fromBytes($decoded);
        }

        try {
            return new self(
                Base64Url::decode($data['publicKeyCredentialId']),
                $data['type'],
                $data['transports'],
                $data['attestationType'],
                TrustPathLoader::loadTrustPath($data['trustPath']),
                $uuid,
                Base64Url::decode($data['credentialPublicKey']),
                Base64Url::decode($data['userHandle']),
                $data['counter']
            );
        } catch (Throwable $throwable) {
            throw new InvalidArgumentException('Unable to load the data', $throwable->getCode(), $throwable);
        }
    }

    public function jsonSerialize(): array
    {
        return [
            'publicKeyCredentialId' => Base64Url::encode($this->publicKeyCredentialId),
            'type' => $this->type,
            'transports' => $this->transports,
            'attestationType' => $this->attestationType,
            'trustPath' => $this->trustPath,
            'aaguid' => $this->aaguid->toString(),
            'credentialPublicKey' => Base64Url::encode($this->credentialPublicKey),
            'userHandle' => Base64Url::encode($this->userHandle),
            'counter' => $this->counter,
        ];
    }
}
