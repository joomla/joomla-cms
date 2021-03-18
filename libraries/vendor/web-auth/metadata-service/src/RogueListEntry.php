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

class RogueListEntry
{
    /**
     * @var string
     */
    private $sk;
    /**
     * @var string
     */
    private $date;

    public function getSk(): string
    {
        return $this->sk;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public static function createFromArray(array $data): self
    {
        $object = new self();
        $object->sk = $data['sk'] ?? null;
        $object->date = $data['date'] ?? null;

        return $object;
    }
}
