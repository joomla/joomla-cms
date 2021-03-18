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

class Version
{
    /**
     * @var int
     */
    private $major;

    /**
     * @var int
     */
    private $minor;

    public function getMajor(): int
    {
        return $this->major;
    }

    public function getMinor(): int
    {
        return $this->minor;
    }

    public static function createFromArray(array $data): self
    {
        $object = new self();
        $object->major = $data['major'] ?? null;
        $object->minor = $data['minor'] ?? null;

        return $object;
    }
}
