<?php

/**
 * Class ParagonIE_Sodium_Core32_Int64
 *
 * Encapsulates a 64-bit integer.
 *
 * These are immutable. It always returns a new instance.
 */
class ParagonIE_Sodium_Core32_Int64
{
    /**
     * @var array<int, int> - four 16-bit integers
     */
    public $limbs;

    /**
     * @var int
     */
    public $overflow = 0;

    /**
     * ParagonIE_Sodium_Core32_Int64 constructor.
     * @param array $array
     */
    public function __construct($array = array(0, 0, 0, 0))
    {
        $this->limbs = array(
            (int) $array[0],
            (int) $array[1],
            (int) $array[2],
            (int) $array[3]
        );
        $this->overflow = 0;
    }

    /**
     * Adds two int64 objects
     *
     * @param ParagonIE_Sodium_Core32_Int64 $addend
     * @return ParagonIE_Sodium_Core32_Int64
     */
    public function addInt64(ParagonIE_Sodium_Core32_Int64 $addend)
    {
        $return = new ParagonIE_Sodium_Core32_Int64();
        $carry = 0;
        for ($i = 3; $i >= 0; --$i) {
            $tmp = $this->limbs[$i] + $addend->limbs[$i] + $carry;
            $carry = $tmp >> 16;
            $return->limbs[$i] = (int) ($tmp & 0xffff);
        }
        $return->overflow = $carry;
        return $return;
    }

    /**
     * Adds a normal integer to an int64 object
     *
     * @param int $int
     * @return ParagonIE_Sodium_Core32_Int64
     */
    public function addInt($int)
    {
        ParagonIE_Sodium_Core32_Util::declareScalarType($int, 'int', 1);

        $return = new ParagonIE_Sodium_Core32_Int64();
        $carry = 0;
        for ($i = 3; $i >= 0; --$i) {
            $step = (3 - $i) << 4; // 0, 16, 32, 48
            if ($i < 2) {
                $toAdd = 0;
            } else {
                $toAdd = (($int >> $step) & 0xffff);
            }
            $tmp = $this->limbs[$i] + $toAdd + $carry;
            $carry = $tmp >> 16;
            $return->limbs[$i] = (int) ($tmp & 0xffff);
        }
        $return->overflow = $carry;
        return $return;
    }

    /**
     * @param int $b
     * @return int
     */
    public function compareInt($b = 0)
    {
        $gt = 0;
        $eq = 1;

        $i = 4;
        $j = 0;
        while ($i > 0) {
            --$i;
            $x1 = $this->limbs[$i];
            $x2 = ($b >> ($j << 4)) & 0xffff;
            $gt |= (($x2 - $x1) >> 8) & $eq;
            $eq &= (($x2 ^ $x1) - 1) >> 8;
        }
        return ($gt + $gt - $eq) + 1;
    }

    /**
     * @param int $b
     * @return bool
     */
    public function isGreaterThan($b = 0)
    {
        return $this->compareInt($b) > 0;
    }

    /**
     * @param int $b
     * @return bool
     */
    public function isLessThanInt($b = 0)
    {
        return $this->compareInt($b) < 0;
    }


    /**
     * @param int $hi
     * @param int $lo
     * @return ParagonIE_Sodium_Core32_Int64
     */
    public function mask64($hi = 0, $lo = 0)
    {
        $a = ($hi >> 16) & 0xffff;
        $b = ($hi) & 0xffff;
        $c = ($lo >> 16) & 0xffff;
        $d = ($lo & 0xffff);
        return new ParagonIE_Sodium_Core32_Int64(
            array(
                $this->limbs[0] & $a,
                $this->limbs[1] & $b,
                $this->limbs[2] & $c,
                $this->limbs[3] & $d
            )
        );
    }

    /**
     * @param int $int
     * @param int $size
     * @return ParagonIE_Sodium_Core32_Int64
     */
    public function mulInt($int = 0, $size = 0)
    {
        ParagonIE_Sodium_Core32_Util::declareScalarType($int, 'int', 1);
        ParagonIE_Sodium_Core32_Util::declareScalarType($size, 'int', 2);
        if (!$size) {
            $size = 63;
        }

        $a = clone $this;
        $return = new ParagonIE_Sodium_Core32_Int64();

        for ($i = $size; $i >= 0; --$i) {
            $return = $return->addInt64(
                $a->mask64(
                    (int) (-($int & 1)),
                    (int) (-($int & 1))
                )
            );
            $a = $a->shiftLeft(1);
            $int >>= 1;
        }
        return $return;
    }

    /**
     * @param ParagonIE_Sodium_Core32_Int64 $int
     * @param int $size
     * @return ParagonIE_Sodium_Core32_Int64
     */
    public function mulInt64(ParagonIE_Sodium_Core32_Int64 $int, $size = 0)
    {
        ParagonIE_Sodium_Core32_Util::declareScalarType($size, 'int', 2);
        if (!$size) {
            $size = 63;
        }

        $a = clone $this;
        $b = clone $int;
        $return = new ParagonIE_Sodium_Core32_Int64();

        for ($i = $size; $i >= 0; --$i) {
            /*
            $c += (int) ($a & -($b & 1));
            $a <<= 1;
            $b >>= 1;
             */
            $return = $return->addInt64(
                $a->mask64(
                    (int) (-($b->limbs[3] & 1)),
                    (int) (-($b->limbs[3] & 1))
                )
            );
            $a = $a->shiftLeft(1);
            $b = $b->shiftRight(1);
        }
        return $return;
    }

    /**
     * OR this 64-bit integer with another.
     *
     * @param ParagonIE_Sodium_Core32_Int64 $b
     * @return ParagonIE_Sodium_Core32_Int64
     */
    public function orInt64(ParagonIE_Sodium_Core32_Int64 $b)
    {
        $return = new ParagonIE_Sodium_Core32_Int64();
        $return->limbs = array(
            (int) ($this->limbs[0] | $b->limbs[0]),
            (int) ($this->limbs[1] | $b->limbs[1]),
            (int) ($this->limbs[2] | $b->limbs[2]),
            (int) ($this->limbs[3] | $b->limbs[3])
        );
        return $return;
    }

    /**
     * @param int $c
     * @return ParagonIE_Sodium_Core32_Int64
     */
    public function rotateLeft($c = 0)
    {
        ParagonIE_Sodium_Core32_Util::declareScalarType($c, 'int', 1);

        $return = new ParagonIE_Sodium_Core32_Int64();
        $c &= 63;
        if ($c === 0) {
            // NOP, but we want a copy.
            $return->limbs = $this->limbs;
        } else {
            $idx_shift = ($c >> 4) & 3;
            $sub_shift = $c & 15;

            for ($i = 3; $i >= 0; --$i) {
                $j = ($i + $idx_shift) & 3;
                $k = ($i + $idx_shift + 1) & 3;
                $return->limbs[$i] = (int) (
                    (
                        ($this->limbs[$j] << $sub_shift)
                            |
                        ($this->limbs[$k] >> (16 - $sub_shift))
                    ) & 0xffff
                );
            }
        }
        return $return;
    }

    /**
     * Rotate to the right
     *
     * @param int $c
     * @return ParagonIE_Sodium_Core32_Int64
     */
    public function rotateRight($c = 0)
    {
        ParagonIE_Sodium_Core32_Util::declareScalarType($c, 'int', 1);

        $return = new ParagonIE_Sodium_Core32_Int64();
        $c &= 63;
        if ($c === 0) {
            // NOP, but we want a copy.
            $return->limbs = $this->limbs;
        } else {
            $idx_shift = ($c >> 4) & 3;
            $sub_shift = $c & 15;

            for ($i = 3; $i >= 0; --$i) {
                $j = ($i - $idx_shift) & 3;
                $k = ($i - $idx_shift - 1) & 3;
                $return->limbs[$i] = (int) (
                    (
                        ($this->limbs[$j] >> ($sub_shift))
                            |
                        ($this->limbs[$k] << (16 - $sub_shift))
                    ) & 0xffff
                );
            }
        }
        return $return;
    }
    /**
     * @param int $c
     * @return ParagonIE_Sodium_Core32_Int64
     * @throws TypeError
     */
    public function shiftLeft($c = 0)
    {
        ParagonIE_Sodium_Core32_Util::declareScalarType($c, 'int', 1);
        $return = new ParagonIE_Sodium_Core32_Int64();
        $c &= 63;

        if ($c >= 16) {
            if ($c >= 48) {
                $return->limbs = array(
                    $this->limbs[3], 0, 0, 0
                );
            } elseif ($c >= 32) {
                $return->limbs = array(
                    $this->limbs[2], $this->limbs[3], 0, 0
                );
            } else {
                $return->limbs = array(
                    $this->limbs[1], $this->limbs[2], $this->limbs[3], 0
                );
            }
            return $return->shiftLeft($c & 15);
        }
        if ($c === 0) {
            $return->limbs = $this->limbs;
        } elseif ($c < 0) {
            return $this->shiftRight(-$c);
        } else {
            if (is_null($c)) {
                throw new TypeError();
            }
            $carry = 0;
            for ($i = 3; $i >= 0; --$i) {
                $tmp = ($this->limbs[$i] << $c) | ($carry & 0xffff);
                $return->limbs[$i] = (int) ($tmp & 0xffff);
                $carry = $tmp >> 16;
            }
        }
        return $return;
    }

    /**
     * @param int $c
     * @return ParagonIE_Sodium_Core32_Int64
     * @throws TypeError
     */
    public function shiftRight($c = 0)
    {
        ParagonIE_Sodium_Core32_Util::declareScalarType($c, 'int', 1);
        $return = new ParagonIE_Sodium_Core32_Int64();
        $c &= 63;

        $negative = -(($this->limbs[0] >> 15) & 1);
        if ($c >= 16) {
            if ($c >= 48) {
                $return->limbs = array(
                    (int) ($negative & 0xffff),
                    (int) ($negative & 0xffff),
                    (int) ($negative & 0xffff),
                    (int) $this->limbs[0]
                );
            } elseif ($c >= 32) {
                $return->limbs = array(
                    (int) ($negative & 0xffff),
                    (int) ($negative & 0xffff),
                    (int) $this->limbs[0],
                    (int) $this->limbs[1]
                );
            } else {
                $return->limbs = array(
                    (int) ($negative & 0xffff),
                    (int) $this->limbs[0],
                    (int) $this->limbs[1],
                    (int) $this->limbs[2]
                );
            }
            return $return->shiftRight($c & 15);
        }

        if ($c === 0) {
            $return->limbs = $this->limbs;
        } elseif ($c < 0) {
            return $this->shiftLeft(-$c);
        } else {
            if (is_null($c)) {
                throw new TypeError();
            }
            $carryRight = ($negative & 0xffff);
            $mask = (int) (((1 << ($c + 1)) - 1) & 0xffff);
            for ($i = 0; $i < 4; ++$i) {
                $return->limbs[$i] = (int) (
                    (($this->limbs[$i] >> $c) | ($carryRight << (16 - $c))) & 0xffff
                );
                $carryRight = (int) ($this->limbs[$i] & $mask);
            }
        }
        return $return;
    }


    /**
     * Subtract a normal integer from an int64 object.
     *
     * @param int $int
     * @return ParagonIE_Sodium_Core32_Int64
     */
    public function subInt($int)
    {
        ParagonIE_Sodium_Core32_Util::declareScalarType($int, 'int', 1);

        $return = new ParagonIE_Sodium_Core32_Int64();

        $carry = 0;
        for ($i = 3; $i >= 0; --$i) {
            $tmp = $this->limbs[$i] - (($int >> 16) & 0xffff) + $carry;
            $carry = $tmp >> 16;
            $return->limbs[$i] = (int) ($tmp & 0xffff);
        }
        return $return;
    }

    /**
     * The difference between two Int64 objects.
     *
     * @param ParagonIE_Sodium_Core32_Int64 $b
     * @return ParagonIE_Sodium_Core32_Int64
     */
    public function subInt64(ParagonIE_Sodium_Core32_Int64 $b)
    {
        $return = new ParagonIE_Sodium_Core32_Int64();
        $carry = 0;
        for ($i = 3; $i >= 0; --$i) {
            $tmp = $this->limbs[$i] - $b->limbs[$i] + $carry;
            $carry = ($tmp >> 16);
            $return->limbs[$i] = (int) ($tmp & 0xffff);

        }
        return $return;
    }

    /**
     * XOR this 64-bit integer with another.
     *
     * @param ParagonIE_Sodium_Core32_Int64 $b
     * @return ParagonIE_Sodium_Core32_Int64
     */
    public function xorInt64(ParagonIE_Sodium_Core32_Int64 $b)
    {
        $return = new ParagonIE_Sodium_Core32_Int64();
        $return->limbs = array(
            (int) ($this->limbs[0] ^ $b->limbs[0]),
            (int) ($this->limbs[1] ^ $b->limbs[1]),
            (int) ($this->limbs[2] ^ $b->limbs[2]),
            (int) ($this->limbs[3] ^ $b->limbs[3])
        );
        return $return;
    }

    /**
     * @param int $low
     * @param int $high
     * @return self
     */
    public static function fromInts($low, $high)
    {
        ParagonIE_Sodium_Core32_Util::declareScalarType($low, 'int', 1);
        ParagonIE_Sodium_Core32_Util::declareScalarType($high, 'int', 2);

        return new ParagonIE_Sodium_Core32_Int64(
            array(
                (int) (($high >> 16) & 0xffff),
                (int) ($high & 0xffff),
                (int) (($low >> 16) & 0xffff),
                (int) ($low & 0xffff)
            )
        );
    }

    /**
     * @param string $string
     * @return self
     */
    public static function fromString($string)
    {
        ParagonIE_Sodium_Core32_Util::declareScalarType($string, 'string', 1);
        $string = (string) $string;
        if (ParagonIE_Sodium_Core32_Util::strlen($string) !== 8) {
            throw new RangeException(
                'String must be 8 bytes; ' . ParagonIE_Sodium_Core32_Util::strlen($string) . ' given.'
            );
        }
        $return = new ParagonIE_Sodium_Core32_Int64();

        $return->limbs[0]  = (int) ((ParagonIE_Sodium_Core32_Util::chrToInt($string[0]) & 0xff) << 8);
        $return->limbs[0] |= (ParagonIE_Sodium_Core32_Util::chrToInt($string[1]) & 0xff);
        $return->limbs[1]  = (int) ((ParagonIE_Sodium_Core32_Util::chrToInt($string[2]) & 0xff) << 8);
        $return->limbs[1] |= (ParagonIE_Sodium_Core32_Util::chrToInt($string[3]) & 0xff);
        $return->limbs[2]  = (int) ((ParagonIE_Sodium_Core32_Util::chrToInt($string[4]) & 0xff) << 8);
        $return->limbs[2] |= (ParagonIE_Sodium_Core32_Util::chrToInt($string[5]) & 0xff);
        $return->limbs[3]  = (int) ((ParagonIE_Sodium_Core32_Util::chrToInt($string[6]) & 0xff) << 8);
        $return->limbs[3] |= (ParagonIE_Sodium_Core32_Util::chrToInt($string[7]) & 0xff);
        return $return;
    }

    /**
     * @param string $string
     * @return self
     */
    public static function fromReverseString($string)
    {
        ParagonIE_Sodium_Core32_Util::declareScalarType($string, 'string', 1);
        $string = (string) $string;
        if (ParagonIE_Sodium_Core32_Util::strlen($string) !== 8) {
            throw new RangeException(
                'String must be 8 bytes; ' . ParagonIE_Sodium_Core32_Util::strlen($string) . ' given.'
            );
        }
        $return = new ParagonIE_Sodium_Core32_Int64();

        $return->limbs[0]  = (int) ((ParagonIE_Sodium_Core32_Util::chrToInt($string[7]) & 0xff) << 8);
        $return->limbs[0] |= (ParagonIE_Sodium_Core32_Util::chrToInt($string[6]) & 0xff);
        $return->limbs[1]  = (int) ((ParagonIE_Sodium_Core32_Util::chrToInt($string[5]) & 0xff) << 8);
        $return->limbs[1] |= (ParagonIE_Sodium_Core32_Util::chrToInt($string[4]) & 0xff);
        $return->limbs[2]  = (int) ((ParagonIE_Sodium_Core32_Util::chrToInt($string[3]) & 0xff) << 8);
        $return->limbs[2] |= (ParagonIE_Sodium_Core32_Util::chrToInt($string[2]) & 0xff);
        $return->limbs[3]  = (int) ((ParagonIE_Sodium_Core32_Util::chrToInt($string[1]) & 0xff) << 8);
        $return->limbs[3] |= (ParagonIE_Sodium_Core32_Util::chrToInt($string[0]) & 0xff);
        return $return;
    }

    /**
     * @return array<int, int>
     */
    public function toArray()
    {
        return array(
            (int) ((($this->limbs[0] & 0xffff) << 16) | ($this->limbs[1] & 0xffff)),
            (int) ((($this->limbs[2] & 0xffff) << 16) | ($this->limbs[3] & 0xffff))
        );
    }

    /**
     * @return ParagonIE_Sodium_Core32_Int32
     */
    public function toInt32()
    {
        $return = new ParagonIE_Sodium_Core32_Int32();
        $return->limbs[0] = (int) ($this->limbs[2]);
        $return->limbs[1] = (int) ($this->limbs[3]);
        return $return;
    }

    /**
     * @return ParagonIE_Sodium_Core32_Int64
     */
    public function toInt64()
    {
        $return = new ParagonIE_Sodium_Core32_Int64();
        $return->limbs[0] = (int) ($this->limbs[0]);
        $return->limbs[1] = (int) ($this->limbs[1]);
        $return->limbs[2] = (int) ($this->limbs[2]);
        $return->limbs[3] = (int) ($this->limbs[3]);
        return $return;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return ParagonIE_Sodium_Core32_Util::intToChr(($this->limbs[0] >> 8) & 0xff) .
            ParagonIE_Sodium_Core32_Util::intToChr($this->limbs[0] & 0xff) .
            ParagonIE_Sodium_Core32_Util::intToChr(($this->limbs[1] >> 8) & 0xff) .
            ParagonIE_Sodium_Core32_Util::intToChr($this->limbs[1] & 0xff) .
            ParagonIE_Sodium_Core32_Util::intToChr(($this->limbs[2] >> 8) & 0xff) .
            ParagonIE_Sodium_Core32_Util::intToChr($this->limbs[2] & 0xff) .
            ParagonIE_Sodium_Core32_Util::intToChr(($this->limbs[3] >> 8) & 0xff) .
            ParagonIE_Sodium_Core32_Util::intToChr($this->limbs[3] & 0xff);
    }

    /**
     * @return string
     */
    public function toReverseString()
    {
        return ParagonIE_Sodium_Core32_Util::intToChr($this->limbs[3] & 0xff) .
            ParagonIE_Sodium_Core32_Util::intToChr(($this->limbs[3] >> 8) & 0xff) .
            ParagonIE_Sodium_Core32_Util::intToChr($this->limbs[2] & 0xff) .
            ParagonIE_Sodium_Core32_Util::intToChr(($this->limbs[2] >> 8) & 0xff) .
            ParagonIE_Sodium_Core32_Util::intToChr($this->limbs[1] & 0xff) .
            ParagonIE_Sodium_Core32_Util::intToChr(($this->limbs[1] >> 8) & 0xff) .
            ParagonIE_Sodium_Core32_Util::intToChr($this->limbs[0] & 0xff) .
            ParagonIE_Sodium_Core32_Util::intToChr(($this->limbs[0] >> 8) & 0xff);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
