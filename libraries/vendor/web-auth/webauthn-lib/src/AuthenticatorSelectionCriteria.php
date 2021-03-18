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

class AuthenticatorSelectionCriteria implements JsonSerializable
{
    public const AUTHENTICATOR_ATTACHMENT_NO_PREFERENCE = null;
    public const AUTHENTICATOR_ATTACHMENT_PLATFORM = 'platform';
    public const AUTHENTICATOR_ATTACHMENT_CROSS_PLATFORM = 'cross-platform';

    public const USER_VERIFICATION_REQUIREMENT_REQUIRED = 'required';
    public const USER_VERIFICATION_REQUIREMENT_PREFERRED = 'preferred';
    public const USER_VERIFICATION_REQUIREMENT_DISCOURAGED = 'discouraged';

    /**
     * @var string|null
     */
    private $authenticatorAttachment;

    /**
     * @var bool
     */
    private $requireResidentKey;

    /**
     * @var string
     */
    private $userVerification;

    public function __construct(?string $authenticatorAttachment = null, bool $requireResidentKey = false, string $userVerification = self::USER_VERIFICATION_REQUIREMENT_PREFERRED)
    {
        $this->authenticatorAttachment = $authenticatorAttachment;
        $this->requireResidentKey = $requireResidentKey;
        $this->userVerification = $userVerification;
    }

    public function getAuthenticatorAttachment(): ?string
    {
        return $this->authenticatorAttachment;
    }

    public function isRequireResidentKey(): bool
    {
        return $this->requireResidentKey;
    }

    public function getUserVerification(): string
    {
        return $this->userVerification;
    }

    public static function createFromString(string $data): self
    {
        $data = json_decode($data, true);
        Assertion::eq(JSON_ERROR_NONE, json_last_error(), 'Invalid data');
        Assertion::isArray($data, 'Invalid data');

        return self::createFromArray($data);
    }

    public static function createFromArray(array $json): self
    {
        return new self(
            $json['authenticatorAttachment'] ?? null,
            $json['requireResidentKey'] ?? false,
            $json['userVerification'] ?? self::USER_VERIFICATION_REQUIREMENT_PREFERRED
        );
    }

    public function jsonSerialize(): array
    {
        $json = [
            'requireResidentKey' => $this->requireResidentKey,
            'userVerification' => $this->userVerification,
        ];
        if (null !== $this->authenticatorAttachment) {
            $json['authenticatorAttachment'] = $this->authenticatorAttachment;
        }

        return $json;
    }
}
