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

class ExtensionDescriptor
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var int|null
     */
    private $tag;

    /**
     * @var string|null
     */
    private $data;

    /**
     * @var bool
     */
    private $fail_if_unknown;

    public function getId(): string
    {
        return $this->id;
    }

    public function getTag(): ?int
    {
        return $this->tag;
    }

    public function getData(): ?string
    {
        return $this->data;
    }

    public function isFailIfUnknown(): bool
    {
        return $this->fail_if_unknown;
    }

    public static function createFromArray(array $data): self
    {
        $object = new self();
        $object->id = $data['id'] ?? null;
        $object->tag = $data['tag'] ?? null;
        $object->data = $data['data'] ?? null;
        $object->fail_if_unknown = $data['fail_if_unknown'] ?? null;

        return $object;
    }
}
