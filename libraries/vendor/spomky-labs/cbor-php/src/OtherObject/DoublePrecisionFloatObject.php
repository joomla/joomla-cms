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

namespace CBOR\OtherObject;

use Assert\Assertion;
use CBOR\OtherObject as Base;
use InvalidArgumentException;

final class DoublePrecisionFloatObject extends Base
{
    public static function supportedAdditionalInformation(): array
    {
        return [27];
    }

    public static function createFromLoadedData(int $additionalInformation, ?string $data): Base
    {
        return new self($additionalInformation, $data);
    }

    /**
     * @return DoublePrecisionFloatObject
     */
    public static function create(string $value): self
    {
        if (8 !== mb_strlen($value, '8bit')) {
            throw new InvalidArgumentException('The value is not a valid double precision floating point');
        }

        return new self(27, $value);
    }

    public function getNormalizedData(bool $ignoreTags = false)
    {
        $data = $this->data;
        Assertion::string($data, 'Invalid data');
        $single = gmp_init(bin2hex($data), 16);
        $exp = gmp_intval($this->bitwiseAnd($this->rightShift($single, 52), gmp_init('7ff', 16)));
        $mant = gmp_intval($this->bitwiseAnd($single, gmp_init('fffffffffffff', 16)));
        $sign = gmp_intval($this->rightShift($single, 63));

        if (0 === $exp) {
            $val = $mant * 2 ** (-(1022 + 52));
        } elseif (0b11111111111 !== $exp) {
            $val = ($mant + (1 << 52)) * 2 ** ($exp - (1023 + 52));
        } else {
            $val = 0 === $mant ? INF : NAN;
        }

        return 1 === $sign ? -$val : $val;
    }

    public function getExponent(): int
    {
        $data = $this->data;
        Assertion::string($data, 'Invalid data');
        $single = gmp_intval(gmp_init(bin2hex($data), 16));

        return ($single >> 52) & 0x7ff;
    }

    public function getMantissa(): int
    {
        $data = $this->data;
        Assertion::string($data, 'Invalid data');
        $single = gmp_intval(gmp_init(bin2hex($data), 16));

        return $single & 0x7fffff;
    }

    public function getSign(): int
    {
        $data = $this->data;
        Assertion::string($data, 'Invalid data');
        $single = gmp_intval(gmp_init(bin2hex($data), 16));

        return 1 === ($single >> 63) ? -1 : 1;
    }

    private function rightShift(\GMP $number, int $positions): \GMP
    {
        return gmp_div($number, gmp_pow(gmp_init(2, 10), $positions));
    }

    private function bitwiseAnd(\GMP $first, \GMP $other): \GMP
    {
        return gmp_and($first, $other);
    }
}
