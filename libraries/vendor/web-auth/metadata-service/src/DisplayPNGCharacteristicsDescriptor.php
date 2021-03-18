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

class DisplayPNGCharacteristicsDescriptor
{
    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var int
     */
    private $bitDepth;

    /**
     * @var int
     */
    private $colorType;

    /**
     * @var int
     */
    private $compression;

    /**
     * @var int
     */
    private $filter;

    /**
     * @var int
     */
    private $interlace;

    /**
     * @var RgbPaletteEntry[]
     */
    private $plte = [];

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getBitDepth(): int
    {
        return $this->bitDepth;
    }

    public function getColorType(): int
    {
        return $this->colorType;
    }

    public function getCompression(): int
    {
        return $this->compression;
    }

    public function getFilter(): int
    {
        return $this->filter;
    }

    public function getInterlace(): int
    {
        return $this->interlace;
    }

    /**
     * @return RgbPaletteEntry[]
     */
    public function getPlte(): array
    {
        return $this->plte;
    }

    public static function createFromArray(array $data): self
    {
        $object = new self();
        $object->width = $data['width'] ?? null;
        $object->compression = $data['compression'] ?? null;
        $object->height = $data['height'] ?? null;
        $object->bitDepth = $data['bitDepth'] ?? null;
        $object->colorType = $data['colorType'] ?? null;
        $object->compression = $data['compression'] ?? null;
        $object->filter = $data['filter'] ?? null;
        $object->interlace = $data['interlace'] ?? null;
        if (isset($data['plte'])) {
            $plte = $data['plte'];
            Assertion::isArray($plte, 'Invalid "plte" parameter');
            foreach ($plte as $item) {
                $object->plte[] = RgbPaletteEntry::createFromArray($item);
            }
        }

        return $object;
    }
}
