<?php

if (class_exists('ParagonIE_Sodium_Core_Poly1305_State', false)) {
    return;
}

/**
 * Class ParagonIE_Sodium_Core_Poly1305_State
 */
class ParagonIE_Sodium_Core_Poly1305_State extends ParagonIE_Sodium_Core_Util
{
    /**
     * @var array<int, int>
     */
    protected $buffer = array();

    /**
     * @var bool
     */
    protected $final = false;

    /**
     * @var array<int, int>
     */
    public $h;

    /**
     * @var int
     */
    protected $leftover = 0;

    /**
     * @var int[]
     */
    public $r;

    /**
     * @var int[]
     */
    public $pad;

    /**
     * ParagonIE_Sodium_Core_Poly1305_State constructor.
     *
     * @internal You should not use this directly from another application
     *
     * @param string $key
     * @throws InvalidArgumentException
     */
    public function __construct($key = '')
    {
        if (self::strlen($key) < 32) {
            throw new InvalidArgumentException(
                'Poly1305 requires a 32-byte key'
            );
        }
        /* r &= 0xffffffc0ffffffc0ffffffc0fffffff */
        $this->r = array(
            (int) ((self::load_4(self::substr($key, 0, 4))) & 0x3ffffff),
            (int) ((self::load_4(self::substr($key, 3, 4)) >> 2) & 0x3ffff03),
            (int) ((self::load_4(self::substr($key, 6, 4)) >> 4) & 0x3ffc0ff),
            (int) ((self::load_4(self::substr($key, 9, 4)) >> 6) & 0x3f03fff),
            (int) ((self::load_4(self::substr($key, 12, 4)) >> 8) & 0x00fffff)
        );

        /* h = 0 */
        $this->h = array(0, 0, 0, 0, 0);

        /* save pad for later */
        $this->pad = array(
            self::load_4(self::substr($key, 16, 4)),
            self::load_4(self::substr($key, 20, 4)),
            self::load_4(self::substr($key, 24, 4)),
            self::load_4(self::substr($key, 28, 4)),
        );

        $this->leftover = 0;
        $this->final = false;
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param string $message
     * @return self
     */
    public function update($message = '')
    {
        $bytes = self::strlen($message);

        /* handle leftover */
        if ($this->leftover) {
            $want = ParagonIE_Sodium_Core_Poly1305::BLOCK_SIZE - $this->leftover;
            if ($want > $bytes) {
                $want = $bytes;
            }
            for ($i = 0; $i < $want; ++$i) {
                $mi = self::chrToInt($message[$i]);
                $this->buffer[$this->leftover + $i] = $mi;
            }
            // We snip off the leftmost bytes.
            $message = self::substr($message, $want);
            $bytes = self::strlen($message);
            $this->leftover += $want;
            if ($this->leftover < ParagonIE_Sodium_Core_Poly1305::BLOCK_SIZE) {
                // We still don't have enough to run $this->blocks()
                return $this;
            }

            $this->blocks(
                static::intArrayToString($this->buffer),
                ParagonIE_Sodium_Core_Poly1305::BLOCK_SIZE
            );
            $this->leftover = 0;
        }

        /* process full blocks */
        if ($bytes >= ParagonIE_Sodium_Core_Poly1305::BLOCK_SIZE) {
            $want = $bytes & ~(ParagonIE_Sodium_Core_Poly1305::BLOCK_SIZE - 1);
            if ($want >= ParagonIE_Sodium_Core_Poly1305::BLOCK_SIZE) {
                $block = self::substr($message, 0, $want);
                if (self::strlen($block) >= ParagonIE_Sodium_Core_Poly1305::BLOCK_SIZE) {
                    $this->blocks($block, $want);
                    $message = self::substr($message, $want);
                    $bytes = self::strlen($message);
                }
            }
        }

        /* store leftover */
        if ($bytes) {
            for ($i = 0; $i < $bytes; ++$i) {
                $mi = self::chrToInt($message[$i]);
                $this->buffer[$this->leftover + $i] = $mi;
            }
            $this->leftover = (int) $this->leftover + $bytes;
        }
        return $this;
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param string $message
     * @param int $bytes
     * @return self
     */
    public function blocks($message, $bytes)
    {
        if (self::strlen($message) < 16) {
            $message = str_pad($message, 16, "\x00", STR_PAD_RIGHT);
        }
        $hibit = $this->final ? 0 : 1 << 24; /* 1 << 128 */
        $r0 = (int) $this->r[0];
        $r1 = (int) $this->r[1];
        $r2 = (int) $this->r[2];
        $r3 = (int) $this->r[3];
        $r4 = (int) $this->r[4];

        $s1 = self::mul($r1, 5, 3);
        $s2 = self::mul($r2, 5, 3);
        $s3 = self::mul($r3, 5, 3);
        $s4 = self::mul($r4, 5, 3);

        $h0 = $this->h[0];
        $h1 = $this->h[1];
        $h2 = $this->h[2];
        $h3 = $this->h[3];
        $h4 = $this->h[4];

        while ($bytes >= ParagonIE_Sodium_Core_Poly1305::BLOCK_SIZE) {
            /* h += m[i] */
            $h0 +=  self::load_4(self::substr($message, 0, 4))       & 0x3ffffff;
            $h1 += (self::load_4(self::substr($message, 3, 4)) >> 2) & 0x3ffffff;
            $h2 += (self::load_4(self::substr($message, 6, 4)) >> 4) & 0x3ffffff;
            $h3 += (self::load_4(self::substr($message, 9, 4)) >> 6) & 0x3ffffff;
            $h4 += (self::load_4(self::substr($message, 12, 4)) >> 8) | $hibit;

            /* h *= r */
            $d0 = (
                self::mul($h0, $r0) +
                self::mul($h1, $s4) +
                self::mul($h2, $s3) +
                self::mul($h3, $s2) +
                self::mul($h4, $s1)
            );

            $d1 = (
                self::mul($h0, $r1) +
                self::mul($h1, $r0) +
                self::mul($h2, $s4) +
                self::mul($h3, $s3) +
                self::mul($h4, $s2)
            );

            $d2 = (
                self::mul($h0, $r2) +
                self::mul($h1, $r1) +
                self::mul($h2, $r0) +
                self::mul($h3, $s4) +
                self::mul($h4, $s3)
            );

            $d3 = (
                self::mul($h0, $r3) +
                self::mul($h1, $r2) +
                self::mul($h2, $r1) +
                self::mul($h3, $r0) +
                self::mul($h4, $s4)
            );

            $d4 = (
                self::mul($h0, $r4) +
                self::mul($h1, $r3) +
                self::mul($h2, $r2) +
                self::mul($h3, $r1) +
                self::mul($h4, $r0)
            );

            /* (partial) h %= p */
            $c = $d0 >> 26;
            $h0 = $d0 & 0x3ffffff;
            $d1 += $c;
            $c = $d1 >> 26;
            $h1 = $d1 & 0x3ffffff;
            $d2 += $c;
            $c = $d2 >> 26;
            $h2 = $d2 & 0x3ffffff;
            $d3 += $c;
            $c = $d3 >> 26;
            $h3 = $d3 & 0x3ffffff;
            $d4 += $c;
            $c = $d4 >> 26;
            $h4 = $d4 & 0x3ffffff;
            $h0 += (int) self::mul($c, 5, 3);
            $c = $h0 >> 26;
            $h0 &= 0x3ffffff;
            $h1 += $c;

            // Chop off the left 32 bytes.
            $message = self::substr(
                $message,
                ParagonIE_Sodium_Core_Poly1305::BLOCK_SIZE
            );
            $bytes -= ParagonIE_Sodium_Core_Poly1305::BLOCK_SIZE;
        }

        $this->h = array(
            (int) ($h0 & 0xffffffff),
            (int) ($h1 & 0xffffffff),
            (int) ($h2 & 0xffffffff),
            (int) ($h3 & 0xffffffff),
            (int) ($h4 & 0xffffffff)
        );
        return $this;
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @return string
     */
    public function finish()
    {
        /* process the remaining block */
        if ($this->leftover) {
            $i = $this->leftover;
            $this->buffer[$i++] = 1;
            for (; $i < ParagonIE_Sodium_Core_Poly1305::BLOCK_SIZE; ++$i) {
                $this->buffer[$i] = 0;
            }
            $this->final = true;
            $this->blocks(
                self::substr(
                    static::intArrayToString($this->buffer),
                    0,
                    ParagonIE_Sodium_Core_Poly1305::BLOCK_SIZE
                ),
                ParagonIE_Sodium_Core_Poly1305::BLOCK_SIZE
            );
        }

        $h0 = (int) $this->h[0];
        $h1 = (int) $this->h[1];
        $h2 = (int) $this->h[2];
        $h3 = (int) $this->h[3];
        $h4 = (int) $this->h[4];

        $c = $h1 >> 26;
        $h1 &= 0x3ffffff;
        $h2 += $c;
        $c = $h2 >> 26;
        $h2 &= 0x3ffffff;
        $h3 += $c;
        $c = $h3 >> 26;
        $h3 &= 0x3ffffff;
        $h4 += $c;
        $c = $h4 >> 26;
        $h4 &= 0x3ffffff;
        $h0 += self::mul($c, 5, 3);
        $c = $h0 >> 26;
        $h0 &= 0x3ffffff;
        $h1 += $c;

        /* compute h + -p */
        $g0 = $h0 + 5;
        $c = $g0 >> 26;
        $g0 &= 0x3ffffff;
        $g1 = $h1 + $c;
        $c = $g1 >> 26;
        $g1 &= 0x3ffffff;
        $g2 = $h2 + $c;
        $c = $g2 >> 26;
        $g2 &= 0x3ffffff;
        $g3 = $h3 + $c;
        $c = $g3 >> 26;
        $g3 &= 0x3ffffff;
        $g4 = ($h4 + $c - (1 << 26)) & 0xffffffff;

        /* select h if h < p, or h + -p if h >= p */
        $mask = ($g4 >> 31) - 1;

        $g0 &= $mask;
        $g1 &= $mask;
        $g2 &= $mask;
        $g3 &= $mask;
        $g4 &= $mask;

        $mask = ~$mask & 0xffffffff;
        $h0 = ($h0 & $mask) | $g0;
        $h1 = ($h1 & $mask) | $g1;
        $h2 = ($h2 & $mask) | $g2;
        $h3 = ($h3 & $mask) | $g3;
        $h4 = ($h4 & $mask) | $g4;

        /* h = h % (2^128) */
        $h0 = (($h0) | ($h1 << 26)) & 0xffffffff;
        $h1 = (($h1 >>  6) | ($h2 << 20)) & 0xffffffff;
        $h2 = (($h2 >> 12) | ($h3 << 14)) & 0xffffffff;
        $h3 = (($h3 >> 18) | ($h4 <<  8)) & 0xffffffff;

        /* mac = (h + pad) % (2^128) */
        $f = ($h0 + $this->pad[0]);
        $h0 = (int) $f;
        $f = ($h1 + $this->pad[1] + ($f >> 32));
        $h1 = (int) $f;
        $f = ($h2 + $this->pad[2] + ($f >> 32));
        $h2 = (int) $f;
        $f = ($h3 + $this->pad[3] + ($f >> 32));
        $h3 = (int) $f;

        return self::store32_le($h0 & 0xffffffff) .
            self::store32_le($h1 & 0xffffffff) .
            self::store32_le($h2 & 0xffffffff) .
            self::store32_le($h3 & 0xffffffff);
    }
}
