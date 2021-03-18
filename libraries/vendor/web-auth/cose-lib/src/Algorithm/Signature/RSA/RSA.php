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

namespace Cose\Algorithm\Signature\RSA;

use Assert\Assertion;
use Cose\Algorithm\Signature\Signature;
use Cose\Key\Key;
use Cose\Key\RsaKey;

abstract class RSA implements Signature
{
    public function sign(string $data, Key $key): string
    {
        $key = $this->handleKey($key);
        Assertion::true($key->isPrivate(), 'The key is not private');

        $result = openssl_sign($data, $signature, $key->asPem(), $this->getHashAlgorithm());
        Assertion::true($result, 'Unable to sign the data');

        return $signature;
    }

    public function verify(string $data, Key $key, string $signature): bool
    {
        $key = $this->handleKey($key);

        return 1 === openssl_verify($data, $signature, $key->asPem(), $this->getHashAlgorithm());
    }

    private function handleKey(Key $key): RsaKey
    {
        return new RsaKey($key->getData());
    }

    abstract protected function getHashAlgorithm(): int;
}
