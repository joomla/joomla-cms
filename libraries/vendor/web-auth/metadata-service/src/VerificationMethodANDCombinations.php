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

namespace Webauthn\MetadataService;

use Assert\Assertion;

class VerificationMethodANDCombinations
{
    /**
     * @var VerificationMethodDescriptor[]
     */
    private $verificationMethods = [];

    /**
     * @return VerificationMethodDescriptor[]
     */
    public function getVerificationMethods(): array
    {
        return $this->verificationMethods;
    }

    public static function createFromArray(array $data): self
    {
        $object = new self();

        foreach ($data as $datum) {
            Assertion::isArray($datum, 'Invalid verificationMethod and combinations');
            $object->verificationMethods[] = VerificationMethodDescriptor::createFromArray($datum);
        }

        return $object;
    }
}
