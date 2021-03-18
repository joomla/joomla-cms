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

class MetadataTOCPayload
{
    /**
     * @var string|null
     */
    private $legalHeader;

    /**
     * @var int
     */
    private $no;

    /**
     * @var string
     */
    private $nextUpdate;

    /**
     * @var MetadataTOCPayloadEntry[]
     */
    private $entries = [];

    public function getLegalHeader(): ?string
    {
        return $this->legalHeader;
    }

    public function getNo(): int
    {
        return $this->no;
    }

    public function getNextUpdate(): string
    {
        return $this->nextUpdate;
    }

    /**
     * @return MetadataTOCPayloadEntry[]
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    public static function createFromArray(array $data): self
    {
        $object = new self();
        $object->legalHeader = $data['legalHeader'] ?? null;
        $object->nextUpdate = $data['nextUpdate'] ?? null;
        $object->no = $data['no'] ?? null;
        $object->entries = [];
        if (isset($data['entries'])) {
            foreach ($data['entries'] as $k => $entry) {
                $object->entries[$k] = MetadataTOCPayloadEntry::createFromArray($entry);
            }
        }

        return $object;
    }
}
