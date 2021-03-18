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

namespace Webauthn\AuthenticationExtensions;

use Assert\Assertion;
use CBOR\CBORObject;
use CBOR\MapObject;

class AuthenticationExtensionsClientOutputsLoader
{
    public static function load(CBORObject $object): AuthenticationExtensionsClientOutputs
    {
        Assertion::isInstanceOf($object, MapObject::class, 'Invalid extension object');
        $data = $object->getNormalizedData();
        $extensions = new AuthenticationExtensionsClientOutputs();
        foreach ($data as $key => $value) {
            Assertion::string($key, 'Invalid extension key');
            $extensions->add(new AuthenticationExtension($key, $value));
        }

        return $extensions;
    }
}
