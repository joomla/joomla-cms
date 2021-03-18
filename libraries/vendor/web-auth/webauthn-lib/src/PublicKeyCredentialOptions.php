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

use JsonSerializable;
use Webauthn\AuthenticationExtensions\AuthenticationExtensionsClientInputs;

abstract class PublicKeyCredentialOptions implements JsonSerializable
{
    /**
     * @var string
     */
    protected $challenge;

    /**
     * @var int|null
     */
    protected $timeout;

    /**
     * @var AuthenticationExtensionsClientInputs
     */
    protected $extensions;

    public function __construct(string $challenge, ?int $timeout = null, ?AuthenticationExtensionsClientInputs $extensions = null)
    {
        $this->challenge = $challenge;
        $this->timeout = $timeout;
        $this->extensions = $extensions ?? new AuthenticationExtensionsClientInputs();
    }

    public function getChallenge(): string
    {
        return $this->challenge;
    }

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    public function getExtensions(): AuthenticationExtensionsClientInputs
    {
        return $this->extensions;
    }

    abstract public static function createFromString(string $data): self;

    abstract public static function createFromArray(array $json): self;
}
