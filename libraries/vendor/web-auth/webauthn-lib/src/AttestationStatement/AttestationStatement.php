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
use JsonSerializable;
use Webauthn\TrustPath\TrustPath;
use Webauthn\TrustPath\TrustPathLoader;

class AttestationStatement implements JsonSerializable
{
    public const TYPE_NONE = 'none';
    public const TYPE_BASIC = 'basic';
    public const TYPE_SELF = 'self';
    public const TYPE_ATTCA = 'attca';
    public const TYPE_ECDAA = 'ecdaa';

    /**
     * @var string
     */
    private $fmt;

    /**
     * @var array
     */
    private $attStmt;

    /**
     * @var TrustPath
     */
    private $trustPath;

    /**
     * @var string
     */
    private $type;

    public function __construct(string $fmt, array $attStmt, string $type, TrustPath $trustPath)
    {
        $this->fmt = $fmt;
        $this->attStmt = $attStmt;
        $this->type = $type;
        $this->trustPath = $trustPath;
    }

    public static function createNone(string $fmt, array $attStmt, TrustPath $trustPath): self
    {
        return new self($fmt, $attStmt, self::TYPE_NONE, $trustPath);
    }

    public static function createBasic(string $fmt, array $attStmt, TrustPath $trustPath): self
    {
        return new self($fmt, $attStmt, self::TYPE_BASIC, $trustPath);
    }

    public static function createSelf(string $fmt, array $attStmt, TrustPath $trustPath): self
    {
        return new self($fmt, $attStmt, self::TYPE_SELF, $trustPath);
    }

    public static function createAttCA(string $fmt, array $attStmt, TrustPath $trustPath): self
    {
        return new self($fmt, $attStmt, self::TYPE_ATTCA, $trustPath);
    }

    public static function createEcdaa(string $fmt, array $attStmt, TrustPath $trustPath): self
    {
        return new self($fmt, $attStmt, self::TYPE_ECDAA, $trustPath);
    }

    public function getFmt(): string
    {
        return $this->fmt;
    }

    public function getAttStmt(): array
    {
        return $this->attStmt;
    }

    public function has(string $key): bool
    {
        return \array_key_exists($key, $this->attStmt);
    }

    /**
     * @return mixed
     */
    public function get(string $key)
    {
        Assertion::true($this->has($key), sprintf('The attestation statement has no key "%s".', $key));

        return $this->attStmt[$key];
    }

    public function getTrustPath(): TrustPath
    {
        return $this->trustPath;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public static function createFromArray(array $data): self
    {
        foreach (['fmt', 'attStmt', 'trustPath', 'type'] as $key) {
            Assertion::keyExists($data, $key, sprintf('The key "%s" is missing', $key));
        }

        return new self(
            $data['fmt'],
            $data['attStmt'],
            $data['type'],
            TrustPathLoader::loadTrustPath($data['trustPath'])
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'fmt' => $this->fmt,
            'attStmt' => $this->attStmt,
            'trustPath' => $this->trustPath,
            'type' => $this->type,
        ];
    }
}
