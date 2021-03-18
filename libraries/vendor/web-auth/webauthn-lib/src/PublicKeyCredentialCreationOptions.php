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
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs;

class PublicKeyCredentialCreationOptions extends PublicKeyCredentialOptions
{
    public const ATTESTATION_CONVEYANCE_PREFERENCE_NONE = 'none';
    public const ATTESTATION_CONVEYANCE_PREFERENCE_INDIRECT = 'indirect';
    public const ATTESTATION_CONVEYANCE_PREFERENCE_DIRECT = 'direct';

    /**
     * @var PublicKeyCredentialRpEntity
     */
    private $rp;

    /**
     * @var PublicKeyCredentialUserEntity
     */
    private $user;

    /**
     * @var PublicKeyCredentialParameters[]
     */
    private $pubKeyCredParams;

    /**
     * @var PublicKeyCredentialDescriptor[]
     */
    private $excludeCredentials;

    /**
     * @var AuthenticatorSelectionCriteria
     */
    private $authenticatorSelection;

    /**
     * @var string
     */
    private $attestation;

    /**
     * PublicKeyCredentialCreationOptions constructor.
     *
     * @param PublicKeyCredentialParameters[] $pubKeyCredParams
     * @param PublicKeyCredentialDescriptor[] $excludeCredentials
     */
    public function __construct(PublicKeyCredentialRpEntity $rp, PublicKeyCredentialUserEntity $user, string $challenge, array $pubKeyCredParams, ?int $timeout, array $excludeCredentials, AuthenticatorSelectionCriteria $authenticatorSelection, string $attestation, ?AuthenticationExtensionsClientInputs $extensions)
    {
        parent::__construct($challenge, $timeout, $extensions);
        $this->rp = $rp;
        $this->user = $user;
        $this->pubKeyCredParams = array_values($pubKeyCredParams);
        $this->excludeCredentials = array_values($excludeCredentials);
        $this->authenticatorSelection = $authenticatorSelection;
        $this->attestation = $attestation;
    }

    public function getRp(): PublicKeyCredentialRpEntity
    {
        return $this->rp;
    }

    public function getUser(): PublicKeyCredentialUserEntity
    {
        return $this->user;
    }

    /**
     * @return PublicKeyCredentialParameters[]
     */
    public function getPubKeyCredParams(): array
    {
        return $this->pubKeyCredParams;
    }

    /**
     * @return PublicKeyCredentialDescriptor[]
     */
    public function getExcludeCredentials(): array
    {
        return $this->excludeCredentials;
    }

    public function getAuthenticatorSelection(): AuthenticatorSelectionCriteria
    {
        return $this->authenticatorSelection;
    }

    public function getAttestation(): string
    {
        return $this->attestation;
    }

    public static function createFromString(string $data): PublicKeyCredentialOptions
    {
        $data = json_decode($data, true);
        Assertion::eq(JSON_ERROR_NONE, json_last_error(), 'Invalid data');
        Assertion::isArray($data, 'Invalid data');

        return self::createFromArray($data);
    }

    public static function createFromArray(array $json): PublicKeyCredentialOptions
    {
        Assertion::keyExists($json, 'rp', 'Invalid input. "rp" is missing.');
        Assertion::keyExists($json, 'pubKeyCredParams', 'Invalid input. "pubKeyCredParams" is missing.');
        Assertion::isArray($json['pubKeyCredParams'], 'Invalid input. "pubKeyCredParams" is not an array.');
        Assertion::keyExists($json, 'challenge', 'Invalid input. "challenge" is missing.');
        Assertion::keyExists($json, 'attestation', 'Invalid input. "attestation" is missing.');
        Assertion::keyExists($json, 'user', 'Invalid input. "user" is missing.');
        Assertion::keyExists($json, 'authenticatorSelection', 'Invalid input. "authenticatorSelection" is missing.');

        $pubKeyCredParams = [];
        foreach ($json['pubKeyCredParams'] as $pubKeyCredParam) {
            $pubKeyCredParams[] = PublicKeyCredentialParameters::createFromArray($pubKeyCredParam);
        }
        $excludeCredentials = [];
        if (isset($json['excludeCredentials'])) {
            foreach ($json['excludeCredentials'] as $excludeCredential) {
                $excludeCredentials[] = PublicKeyCredentialDescriptor::createFromArray($excludeCredential);
            }
        }

        return new self(
            PublicKeyCredentialRpEntity::createFromArray($json['rp']),
            PublicKeyCredentialUserEntity::createFromArray($json['user']),
            Base64Url::decode($json['challenge']),
            $pubKeyCredParams,
            $json['timeout'] ?? null,
            $excludeCredentials,
            AuthenticatorSelectionCriteria::createFromArray($json['authenticatorSelection']),
            $json['attestation'],
            isset($json['extensions']) ? AuthenticationExtensionsClientInputs::createFromArray($json['extensions']) : new AuthenticationExtensionsClientInputs()
        );
    }

    public function jsonSerialize(): array
    {
        $json = [
            'rp' => $this->rp,
            'pubKeyCredParams' => $this->pubKeyCredParams,
            'challenge' => Base64Url::encode($this->challenge),
            'attestation' => $this->attestation,
            'user' => $this->user,
            'authenticatorSelection' => $this->authenticatorSelection,
        ];

        if (0 !== \count($this->excludeCredentials)) {
            $json['excludeCredentials'] = $this->excludeCredentials;
        }

        if (0 !== $this->extensions->count()) {
            $json['extensions'] = $this->extensions;
        }

        if (null !== $this->timeout) {
            $json['timeout'] = $this->timeout;
        }

        return $json;
    }
}
