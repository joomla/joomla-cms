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

namespace Webauthn;

use Assert\Assertion;
use CBOR\Stream;

final class StringStream implements Stream
{
    /**
     * @var resource
     */
    private $data;

    /**
     * @var int
     */
    private $length;

    /**
     * @var int
     */
    private $totalRead = 0;

    public function __construct(string $data)
    {
        $this->length = mb_strlen($data, '8bit');
        $resource = fopen('php://memory', 'rb+');
        Assertion::isResource($resource, 'Unable to open memory');
        $result = fwrite($resource, $data);
        Assertion::integer($result, 'Unable to write memory');
        $result = rewind($resource);
        Assertion::true($result, 'Unable to read memory');
        $this->data = $resource;
    }

    public function read(int $length): string
    {
        if (0 === $length) {
            return '';
        }
        $read = fread($this->data, $length);
        Assertion::string($read, 'Unable to read memory');
        $bytesRead = mb_strlen($read, '8bit');
        Assertion::length($read, $length, sprintf('Out of range. Expected: %d, read: %d.', $length, $bytesRead), null, '8bit');
        $this->totalRead += $bytesRead;

        return $read;
    }

    public function close(): void
    {
        $result = fclose($this->data);
        Assertion::true($result, 'Unable to close the memory');
    }

    public function isEOF(): bool
    {
        return $this->totalRead === $this->length;
    }
}
