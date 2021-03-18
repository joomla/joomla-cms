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
use JsonSerializable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @see https://www.w3.org/TR/webauthn/#sec-attested-credential-data
 */
class AttestedCredentialData implements JsonSerializable
{
    /**
     * @var UuidInterface
     */
    private $aaguid;

    /**
     * @var string
     */
    private $credentialId;

    /**
     * @var string|null
     */
    private $credentialPublicKey;

    public function __construct(UuidInterface $aaguid, string $credentialId, ?string $credentialPublicKey)
    {
        $this->aaguid = $aaguid;
        $this->credentialId = $credentialId;
        $this->credentialPublicKey = $credentialPublicKey;
    }

    public function getAaguid(): UuidInterface
    {
        return $this->aaguid;
    }

    public function getCredentialId(): string
    {
        return $this->credentialId;
    }

    public function getCredentialPublicKey(): ?string
    {
        return $this->credentialPublicKey;
    }

    public static function createFromArray(array $json): self
    {
        Assertion::keyExists($json, 'aaguid', 'Invalid input. "aaguid" is missing.');
        Assertion::keyExists($json, 'credentialId', 'Invalid input. "credentialId" is missing.');
        switch (true) {
            case 36 === mb_strlen($json['aaguid'], '8bit'):
                $uuid = Uuid::fromString($json['aaguid']);
                break;
            default: // Kept for compatibility with old format
                $decoded = base64_decode($json['aaguid'], true);
                Assertion::string($decoded, 'Unable to decode the data');
                $uuid = Uuid::fromBytes($decoded);
        }
        $credentialId = base64_decode($json['credentialId'], true);
        Assertion::string($credentialId, 'Unable to decode the data');

        return new self(
            $uuid,
            $credentialId,
            isset($json['credentialPublicKey']) ? base64_decode($json['credentialPublicKey'], true) : null
        );
    }

    public function jsonSerialize(): array
    {
        $result = [
            'aaguid' => $this->aaguid->toString(),
            'credentialId' => base64_encode($this->credentialId),
        ];
        if (null !== $this->credentialPublicKey) {
            $result['credentialPublicKey'] = base64_encode($this->credentialPublicKey);
        }

        return $result;
    }
}
