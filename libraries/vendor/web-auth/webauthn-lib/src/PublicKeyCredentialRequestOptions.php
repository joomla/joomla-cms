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

class PublicKeyCredentialRequestOptions extends PublicKeyCredentialOptions
{
    public const USER_VERIFICATION_REQUIREMENT_REQUIRED = 'required';
    public const USER_VERIFICATION_REQUIREMENT_PREFERRED = 'preferred';
    public const USER_VERIFICATION_REQUIREMENT_DISCOURAGED = 'discouraged';

    /**
     * @var string|null
     */
    private $rpId;

    /**
     * @var PublicKeyCredentialDescriptor[]
     */
    private $allowCredentials;

    /**
     * @var string|null
     */
    private $userVerification;

    /**
     * @param PublicKeyCredentialDescriptor[] $allowCredentials
     */
    public function __construct(string $challenge, ?int $timeout = null, ?string $rpId = null, array $allowCredentials = [], ?string $userVerification = null, ?AuthenticationExtensionsClientInputs $extensions = null)
    {
        parent::__construct($challenge, $timeout, $extensions);
        $this->rpId = $rpId;
        $this->allowCredentials = array_values($allowCredentials);
        $this->userVerification = $userVerification;
    }

    public function getRpId(): ?string
    {
        return $this->rpId;
    }

    /**
     * @return PublicKeyCredentialDescriptor[]
     */
    public function getAllowCredentials(): array
    {
        return $this->allowCredentials;
    }

    public function getUserVerification(): ?string
    {
        return $this->userVerification;
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
        Assertion::keyExists($json, 'challenge', 'Invalid input. "challenge" is missing.');

        $allowCredentials = [];
        $allowCredentialList = $json['allowCredentials'] ?? [];
        foreach ($allowCredentialList as $allowCredential) {
            $allowCredentials[] = PublicKeyCredentialDescriptor::createFromArray($allowCredential);
        }

        return new self(
            Base64Url::decode($json['challenge']),
            $json['timeout'] ?? null,
            $json['rpId'] ?? null,
            $allowCredentials,
            $json['userVerification'] ?? null,
            isset($json['extensions']) ? AuthenticationExtensionsClientInputs::createFromArray($json['extensions']) : new AuthenticationExtensionsClientInputs()
        );
    }

    public function jsonSerialize(): array
    {
        $json = [
            'challenge' => Base64Url::encode($this->challenge),
        ];

        if (null !== $this->rpId) {
            $json['rpId'] = $this->rpId;
        }

        if (null !== $this->userVerification) {
            $json['userVerification'] = $this->userVerification;
        }

        if (0 !== \count($this->allowCredentials)) {
            $json['allowCredentials'] = $this->allowCredentials;
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
