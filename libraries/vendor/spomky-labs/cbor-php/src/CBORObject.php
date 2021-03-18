<?php

declare(strict_types=1);

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2018 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

namespace CBOR;

interface CBORObject
{
    public function getMajorType(): int;

    public function getAdditionalInformation(): int;

    /**
     * @return mixed|null
     */
    public function getNormalizedData(bool $ignoreTags = false);

    public function __toString(): string;
}
