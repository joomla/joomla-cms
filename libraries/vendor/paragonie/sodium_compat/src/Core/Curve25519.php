<?php

if (class_exists('ParagonIE_Sodium_Core_Curve25519', false)) {
    return;
}

/**
 * Class ParagonIE_Sodium_Core_Curve25519
 *
 * Implements Curve25519 core functions
 *
 * Based on the ref10 curve25519 code provided by libsodium
 *
 * @ref https://github.com/jedisct1/libsodium/blob/master/src/libsodium/crypto_core/curve25519/ref10/curve25519_ref10.c
 */
abstract class ParagonIE_Sodium_Core_Curve25519 extends ParagonIE_Sodium_Core_Curve25519_H
{
    /**
     * Get a field element of size 10 with a value of 0
     *
     * @internal You should not use this directly from another application
     *
     * @return ParagonIE_Sodium_Core_Curve25519_Fe
     */
    public static function fe_0()
    {
        return ParagonIE_Sodium_Core_Curve25519_Fe::fromArray(
            array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0)
        );
    }

    /**
     * Get a field element of size 10 with a value of 1
     *
     * @internal You should not use this directly from another application
     *
     * @return ParagonIE_Sodium_Core_Curve25519_Fe
     */
    public static function fe_1()
    {
        return ParagonIE_Sodium_Core_Curve25519_Fe::fromArray(
            array(1, 0, 0, 0, 0, 0, 0, 0, 0, 0)
        );
    }

    /**
     * Add two field elements.
     *
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Fe $f
     * @param ParagonIE_Sodium_Core_Curve25519_Fe $g
     * @return ParagonIE_Sodium_Core_Curve25519_Fe
     */
    public static function fe_add(
        ParagonIE_Sodium_Core_Curve25519_Fe $f,
        ParagonIE_Sodium_Core_Curve25519_Fe $g
    ) {
        $arr = array();
        for ($i = 0; $i < 10; ++$i) {
            $arr[$i] = (int) ($f[$i] + $g[$i]);
        }
        return ParagonIE_Sodium_Core_Curve25519_Fe::fromArray($arr);
    }

    /**
     * Constant-time conditional move.
     *
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Fe $f
     * @param ParagonIE_Sodium_Core_Curve25519_Fe $g
     * @param int $b
     * @return ParagonIE_Sodium_Core_Curve25519_Fe
     */
    public static function fe_cmov(
        ParagonIE_Sodium_Core_Curve25519_Fe $f,
        ParagonIE_Sodium_Core_Curve25519_Fe $g,
        $b = 0
    ) {
        $h = array();
        $b *= -1;
        for ($i = 0; $i < 10; ++$i) {
            $x = (($f[$i] ^ $g[$i]) & $b);
            $h[$i] = $f[$i] ^ $x;
        }
        return ParagonIE_Sodium_Core_Curve25519_Fe::fromArray($h);
    }

    /**
     * Create a copy of a field element.
     *
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Fe $f
     * @return ParagonIE_Sodium_Core_Curve25519_Fe
     */
    public static function fe_copy(ParagonIE_Sodium_Core_Curve25519_Fe $f)
    {
        $h = clone $f;
        return $h;
    }

    /**
     * Give: 32-byte string.
     * Receive: A field element object to use for internal calculations.
     *
     * @internal You should not use this directly from another application
     *
     * @param string $s
     * @return ParagonIE_Sodium_Core_Curve25519_Fe
     * @throws RangeException
     */
    public static function fe_frombytes($s)
    {
        if (self::strlen($s) !== 32) {
            throw new RangeException('Expected a 32-byte string.');
        }
        $h0 = self::load_4($s);
        $h1 = self::load_3(self::substr($s, 4, 3)) << 6;
        $h2 = self::load_3(self::substr($s, 7, 3)) << 5;
        $h3 = self::load_3(self::substr($s, 10, 3)) << 3;
        $h4 = self::load_3(self::substr($s, 13, 3)) << 2;
        $h5 = self::load_4(self::substr($s, 16, 4));
        $h6 = self::load_3(self::substr($s, 20, 3)) << 7;
        $h7 = self::load_3(self::substr($s, 23, 3)) << 5;
        $h8 = self::load_3(self::substr($s, 26, 3)) << 4;
        $h9 = (self::load_3(self::substr($s, 29, 3)) & 8388607) << 2;

        $carry9 = ($h9 + (1 << 24)) >> 25;
        $h0 += self::mul($carry9, 19);
        $h9 -= self::mul($carry9,  1 << 25);
        $carry1 = ($h1 + (1 << 24)) >> 25;
        $h2 += $carry1;
        $h1 -= self::mul($carry1,  1 << 25);
        $carry3 = ($h3 + (1 << 24)) >> 25;
        $h4 += $carry3;
        $h3 -= self::mul($carry3,  1 << 25);
        $carry5 = ($h5 + (1 << 24)) >> 25;
        $h6 += $carry5;
        $h5 -= self::mul($carry5,  1 << 25);
        $carry7 = ($h7 + (1 << 24)) >> 25;
        $h8 += $carry7;
        $h7 -= self::mul($carry7,  1 << 25);

        $carry0 = ($h0 + (1 << 25)) >> 26;
        $h1 += $carry0;
        $h0 -= self::mul($carry0,  1 << 26);
        $carry2 = ($h2 + (1 << 25)) >> 26;
        $h3 += $carry2;
        $h2 -= self::mul($carry2,  1 << 26);
        $carry4 = ($h4 + (1 << 25)) >> 26;
        $h5 += $carry4;
        $h4 -= self::mul($carry4,  1 << 26);
        $carry6 = ($h6 + (1 << 25)) >> 26;
        $h7 += $carry6;
        $h6 -= self::mul($carry6,  1 << 26);
        $carry8 = ($h8 + (1 << 25)) >> 26;
        $h9 += $carry8;
        $h8 -= self::mul($carry8,  1 << 26);

        return ParagonIE_Sodium_Core_Curve25519_Fe::fromArray(
            array(
                (int) $h0,
                (int) $h1,
                (int) $h2,
                (int) $h3,
                (int) $h4,
                (int) $h5,
                (int) $h6,
                (int) $h7,
                (int) $h8,
                (int) $h9
            )
        );
    }

    /**
     * Convert a field element to a byte string.
     *
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Fe $h
     * @return string
     */
    public static function fe_tobytes(ParagonIE_Sodium_Core_Curve25519_Fe $h)
    {
        $h[0] = (int) $h[0];
        $h[1] = (int) $h[1];
        $h[2] = (int) $h[2];
        $h[3] = (int) $h[3];
        $h[4] = (int) $h[4];
        $h[5] = (int) $h[5];
        $h[6] = (int) $h[6];
        $h[7] = (int) $h[7];
        $h[8] = (int) $h[8];
        $h[9] = (int) $h[9];

        $q = (self::mul(19, $h[9]) + (1 << 24)) >> 25;
        $q = ($h[0] + $q) >> 26;
        $q = ($h[1] + $q) >> 25;
        $q = ($h[2] + $q) >> 26;
        $q = ($h[3] + $q) >> 25;
        $q = ($h[4] + $q) >> 26;
        $q = ($h[5] + $q) >> 25;
        $q = ($h[6] + $q) >> 26;
        $q = ($h[7] + $q) >> 25;
        $q = ($h[8] + $q) >> 26;
        $q = ($h[9] + $q) >> 25;

        $h[0] += self::mul(19, $q);

        $carry0 = $h[0] >> 26;
        $h[1] += $carry0;
        $h[0] -= $carry0 << 26;
        $carry1 = $h[1] >> 25;
        $h[2] += $carry1;
        $h[1] -= $carry1 << 25;
        $carry2 = $h[2] >> 26;
        $h[3] += $carry2;
        $h[2] -= $carry2 << 26;
        $carry3 = $h[3] >> 25;
        $h[4] += $carry3;
        $h[3] -= $carry3 << 25;
        $carry4 = $h[4] >> 26;
        $h[5] += $carry4;
        $h[4] -= $carry4 << 26;
        $carry5 = $h[5] >> 25;
        $h[6] += $carry5;
        $h[5] -= $carry5 << 25;
        $carry6 = $h[6] >> 26;
        $h[7] += $carry6;
        $h[6] -= $carry6 << 26;
        $carry7 = $h[7] >> 25;
        $h[8] += $carry7;
        $h[7] -= $carry7 << 25;
        $carry8 = $h[8] >> 26;
        $h[9] += $carry8;
        $h[8] -= $carry8 << 26;
        $carry9 = $h[9] >> 25;
        $h[9] -= $carry9 << 25;

        /**
         * @var array<int, int>
         */
        $s = array(
            (int) (($h[0] >> 0) & 0xff),
            (int) (($h[0] >> 8) & 0xff),
            (int) (($h[0] >> 16) & 0xff),
            (int) ((($h[0] >> 24) | ($h[1] << 2)) & 0xff),
            (int) (($h[1] >> 6) & 0xff),
            (int) (($h[1] >> 14) & 0xff),
            (int) ((($h[1] >> 22) | ($h[2] << 3)) & 0xff),
            (int) (($h[2] >> 5) & 0xff),
            (int) (($h[2] >> 13) & 0xff),
            (int) ((($h[2] >> 21) | ($h[3] << 5)) & 0xff),
            (int) (($h[3] >> 3) & 0xff),
            (int) (($h[3] >> 11) & 0xff),
            (int) ((($h[3] >> 19) | ($h[4] << 6)) & 0xff),
            (int) (($h[4] >> 2) & 0xff),
            (int) (($h[4] >> 10) & 0xff),
            (int) (($h[4] >> 18) & 0xff),
            (int) (($h[5] >> 0) & 0xff),
            (int) (($h[5] >> 8) & 0xff),
            (int) (($h[5] >> 16) & 0xff),
            (int) ((($h[5] >> 24) | ($h[6] << 1)) & 0xff),
            (int) (($h[6] >> 7) & 0xff),
            (int) (($h[6] >> 15) & 0xff),
            (int) ((($h[6] >> 23) | ($h[7] << 3)) & 0xff),
            (int) (($h[7] >> 5) & 0xff),
            (int) (($h[7] >> 13) & 0xff),
            (int) ((($h[7] >> 21) | ($h[8] << 4)) & 0xff),
            (int) (($h[8] >> 4) & 0xff),
            (int) (($h[8] >> 12) & 0xff),
            (int) ((($h[8] >> 20) | ($h[9] << 6)) & 0xff),
            (int) (($h[9] >> 2) & 0xff),
            (int) (($h[9] >> 10) & 0xff),
            (int) (($h[9] >> 18) & 0xff)
        );
        return self::intArrayToString($s);
    }

    /**
     * Is a field element negative? (1 = yes, 0 = no. Used in calculations.)
     *
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Fe $f
     * @return int
     */
    public static function fe_isnegative(ParagonIE_Sodium_Core_Curve25519_Fe $f)
    {
        $str = self::fe_tobytes($f);
        return self::chrToInt($str[0]) & 1;
    }

    /**
     * Returns 0 if this field element results in all NUL bytes.
     *
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Fe $f
     * @return bool
     */
    public static function fe_isnonzero(ParagonIE_Sodium_Core_Curve25519_Fe $f)
    {
        static $zero;
        if ($zero === null) {
            $zero = str_repeat("\x00", 32);
        }
        $str = self::fe_tobytes($f);
        return !self::verify_32($str, $zero);
    }

    /**
     * Multiply two field elements
     *
     * h = f * g
     *
     * @internal You should not use this directly from another application
     *
     * @security Is multiplication a source of timing leaks? If so, can we do
     *           anything to prevent that from happening?
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Fe $f
     * @param ParagonIE_Sodium_Core_Curve25519_Fe $g
     * @return ParagonIE_Sodium_Core_Curve25519_Fe
     */
    public static function fe_mul(
        ParagonIE_Sodium_Core_Curve25519_Fe $f,
        ParagonIE_Sodium_Core_Curve25519_Fe $g
    ) {
        $f0 = $f[0];
        $f1 = $f[1];
        $f2 = $f[2];
        $f3 = $f[3];
        $f4 = $f[4];
        $f5 = $f[5];
        $f6 = $f[6];
        $f7 = $f[7];
        $f8 = $f[8];
        $f9 = $f[9];
        $g0 = $g[0];
        $g1 = $g[1];
        $g2 = $g[2];
        $g3 = $g[3];
        $g4 = $g[4];
        $g5 = $g[5];
        $g6 = $g[6];
        $g7 = $g[7];
        $g8 = $g[8];
        $g9 = $g[9];
        $g1_19 = self::mul(19, $g1);
        $g2_19 = self::mul(19, $g2);
        $g3_19 = self::mul(19, $g3);
        $g4_19 = self::mul(19, $g4);
        $g5_19 = self::mul(19, $g5);
        $g6_19 = self::mul(19, $g6);
        $g7_19 = self::mul(19, $g7);
        $g8_19 = self::mul(19, $g8);
        $g9_19 = self::mul(19, $g9);
        $f1_2 = $f1 << 1;
        $f3_2 = $f3 << 1;
        $f5_2 = $f5 << 1;
        $f7_2 = $f7 << 1;
        $f9_2 = $f9 << 1;
        $f0g0    = self::mul($f0,    $g0);
        $f0g1    = self::mul($f0,    $g1);
        $f0g2    = self::mul($f0,    $g2);
        $f0g3    = self::mul($f0,    $g3);
        $f0g4    = self::mul($f0,    $g4);
        $f0g5    = self::mul($f0,    $g5);
        $f0g6    = self::mul($f0,    $g6);
        $f0g7    = self::mul($f0,    $g7);
        $f0g8    = self::mul($f0,    $g8);
        $f0g9    = self::mul($f0,    $g9);
        $f1g0    = self::mul($f1,    $g0);
        $f1g1_2  = self::mul($f1_2,  $g1);
        $f1g2    = self::mul($f1,    $g2);
        $f1g3_2  = self::mul($f1_2,  $g3);
        $f1g4    = self::mul($f1,    $g4);
        $f1g5_2  = self::mul($f1_2,  $g5);
        $f1g6    = self::mul($f1,    $g6);
        $f1g7_2  = self::mul($f1_2,  $g7);
        $f1g8    = self::mul($f1,    $g8);
        $f1g9_38 = self::mul($f1_2,  $g9_19);
        $f2g0    = self::mul($f2,    $g0);
        $f2g1    = self::mul($f2,    $g1);
        $f2g2    = self::mul($f2,    $g2);
        $f2g3    = self::mul($f2,    $g3);
        $f2g4    = self::mul($f2,    $g4);
        $f2g5    = self::mul($f2,    $g5);
        $f2g6    = self::mul($f2,    $g6);
        $f2g7    = self::mul($f2,    $g7);
        $f2g8_19 = self::mul($f2,    $g8_19);
        $f2g9_19 = self::mul($f2,    $g9_19);
        $f3g0    = self::mul($f3,    $g0);
        $f3g1_2  = self::mul($f3_2,  $g1);
        $f3g2    = self::mul($f3,    $g2);
        $f3g3_2  = self::mul($f3_2,  $g3);
        $f3g4    = self::mul($f3,    $g4);
        $f3g5_2  = self::mul($f3_2,  $g5);
        $f3g6    = self::mul($f3,    $g6);
        $f3g7_38 = self::mul($f3_2,  $g7_19);
        $f3g8_19 = self::mul($f3,    $g8_19);
        $f3g9_38 = self::mul($f3_2,  $g9_19);
        $f4g0    = self::mul($f4,    $g0);
        $f4g1    = self::mul($f4,    $g1);
        $f4g2    = self::mul($f4,    $g2);
        $f4g3    = self::mul($f4,    $g3);
        $f4g4    = self::mul($f4,    $g4);
        $f4g5    = self::mul($f4,    $g5);
        $f4g6_19 = self::mul($f4,    $g6_19);
        $f4g7_19 = self::mul($f4,    $g7_19);
        $f4g8_19 = self::mul($f4,    $g8_19);
        $f4g9_19 = self::mul($f4,    $g9_19);
        $f5g0    = self::mul($f5,    $g0);
        $f5g1_2  = self::mul($f5_2,  $g1);
        $f5g2    = self::mul($f5,    $g2);
        $f5g3_2  = self::mul($f5_2,  $g3);
        $f5g4    = self::mul($f5,    $g4);
        $f5g5_38 = self::mul($f5_2,  $g5_19);
        $f5g6_19 = self::mul($f5,    $g6_19);
        $f5g7_38 = self::mul($f5_2,  $g7_19);
        $f5g8_19 = self::mul($f5,    $g8_19);
        $f5g9_38 = self::mul($f5_2,  $g9_19);
        $f6g0    = self::mul($f6,    $g0);
        $f6g1    = self::mul($f6,    $g1);
        $f6g2    = self::mul($f6,    $g2);
        $f6g3    = self::mul($f6,    $g3);
        $f6g4_19 = self::mul($f6,    $g4_19);
        $f6g5_19 = self::mul($f6,    $g5_19);
        $f6g6_19 = self::mul($f6,    $g6_19);
        $f6g7_19 = self::mul($f6,    $g7_19);
        $f6g8_19 = self::mul($f6,    $g8_19);
        $f6g9_19 = self::mul($f6,    $g9_19);
        $f7g0    = self::mul($f7,    $g0);
        $f7g1_2  = self::mul($f7_2,  $g1);
        $f7g2    = self::mul($f7,    $g2);
        $f7g3_38 = self::mul($f7_2,  $g3_19);
        $f7g4_19 = self::mul($f7,    $g4_19);
        $f7g5_38 = self::mul($f7_2,  $g5_19);
        $f7g6_19 = self::mul($f7,    $g6_19);
        $f7g7_38 = self::mul($f7_2,  $g7_19);
        $f7g8_19 = self::mul($f7,    $g8_19);
        $f7g9_38 = self::mul($f7_2,  $g9_19);
        $f8g0    = self::mul($f8,    $g0);
        $f8g1    = self::mul($f8,    $g1);
        $f8g2_19 = self::mul($f8,    $g2_19);
        $f8g3_19 = self::mul($f8,    $g3_19);
        $f8g4_19 = self::mul($f8,    $g4_19);
        $f8g5_19 = self::mul($f8,    $g5_19);
        $f8g6_19 = self::mul($f8,    $g6_19);
        $f8g7_19 = self::mul($f8,    $g7_19);
        $f8g8_19 = self::mul($f8,    $g8_19);
        $f8g9_19 = self::mul($f8,    $g9_19);
        $f9g0    = self::mul($f9,    $g0);
        $f9g1_38 = self::mul($f9_2,  $g1_19);
        $f9g2_19 = self::mul($f9,    $g2_19);
        $f9g3_38 = self::mul($f9_2,  $g3_19);
        $f9g4_19 = self::mul($f9,    $g4_19);
        $f9g5_38 = self::mul($f9_2,  $g5_19);
        $f9g6_19 = self::mul($f9,    $g6_19);
        $f9g7_38 = self::mul($f9_2,  $g7_19);
        $f9g8_19 = self::mul($f9,    $g8_19);
        $f9g9_38 = self::mul($f9_2,  $g9_19);
        $h0 = $f0g0 + $f1g9_38 + $f2g8_19 + $f3g7_38 + $f4g6_19 + $f5g5_38 + $f6g4_19 + $f7g3_38 + $f8g2_19 + $f9g1_38;
        $h1 = $f0g1 + $f1g0    + $f2g9_19 + $f3g8_19 + $f4g7_19 + $f5g6_19 + $f6g5_19 + $f7g4_19 + $f8g3_19 + $f9g2_19;
        $h2 = $f0g2 + $f1g1_2  + $f2g0    + $f3g9_38 + $f4g8_19 + $f5g7_38 + $f6g6_19 + $f7g5_38 + $f8g4_19 + $f9g3_38;
        $h3 = $f0g3 + $f1g2    + $f2g1    + $f3g0    + $f4g9_19 + $f5g8_19 + $f6g7_19 + $f7g6_19 + $f8g5_19 + $f9g4_19;
        $h4 = $f0g4 + $f1g3_2  + $f2g2    + $f3g1_2  + $f4g0    + $f5g9_38 + $f6g8_19 + $f7g7_38 + $f8g6_19 + $f9g5_38;
        $h5 = $f0g5 + $f1g4    + $f2g3    + $f3g2    + $f4g1    + $f5g0    + $f6g9_19 + $f7g8_19 + $f8g7_19 + $f9g6_19;
        $h6 = $f0g6 + $f1g5_2  + $f2g4    + $f3g3_2  + $f4g2    + $f5g1_2  + $f6g0    + $f7g9_38 + $f8g8_19 + $f9g7_38;
        $h7 = $f0g7 + $f1g6    + $f2g5    + $f3g4    + $f4g3    + $f5g2    + $f6g1    + $f7g0    + $f8g9_19 + $f9g8_19;
        $h8 = $f0g8 + $f1g7_2  + $f2g6    + $f3g5_2  + $f4g4    + $f5g3_2  + $f6g2    + $f7g1_2  + $f8g0    + $f9g9_38;
        $h9 = $f0g9 + $f1g8    + $f2g7    + $f3g6    + $f4g5    + $f5g4    + $f6g3    + $f7g2    + $f8g1    + $f9g0   ;

        $carry0 = ($h0 + (1 << 25)) >> 26;
        $h1 += $carry0;
        $h0 -= $carry0 << 26;
        $carry4 = ($h4 + (1 << 25)) >> 26;
        $h5 += $carry4;
        $h4 -= $carry4 << 26;

        $carry1 = ($h1 + (1 << 24)) >> 25;
        $h2 += $carry1;
        $h1 -= $carry1 << 25;
        $carry5 = ($h5 + (1 << 24)) >> 25;
        $h6 += $carry5;
        $h5 -= $carry5 << 25;

        $carry2 = ($h2 + (1 << 25)) >> 26;
        $h3 += $carry2;
        $h2 -= $carry2 << 26;
        $carry6 = ($h6 + (1 << 25)) >> 26;
        $h7 += $carry6;
        $h6 -= $carry6 << 26;

        $carry3 = ($h3 + (1 << 24)) >> 25;
        $h4 += $carry3;
        $h3 -= $carry3 << 25;
        $carry7 = ($h7 + (1 << 24)) >> 25;
        $h8 += $carry7;
        $h7 -= $carry7 << 25;

        $carry4 = ($h4 + (1 << 25)) >> 26;
        $h5 += $carry4;
        $h4 -= $carry4 << 26;
        $carry8 = ($h8 + (1 << 25)) >> 26;
        $h9 += $carry8;
        $h8 -= $carry8 << 26;

        $carry9 = ($h9 + (1 << 24)) >> 25;
        $h0 += self::mul($carry9, 19);
        $h9 -= $carry9 << 25;

        $carry0 = ($h0 + (1 << 25)) >> 26;
        $h1 += $carry0;
        $h0 -= $carry0 << 26;

        return ParagonIE_Sodium_Core_Curve25519_Fe::fromArray(
            array(
                (int) $h0,
                (int) $h1,
                (int) $h2,
                (int) $h3,
                (int) $h4,
                (int) $h5,
                (int) $h6,
                (int) $h7,
                (int) $h8,
                (int) $h9
            )
        );
    }

    /**
     * Get the negative values for each piece of the field element.
     *
     * h = -f
     *
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Fe $f
     * @return ParagonIE_Sodium_Core_Curve25519_Fe
     */
    public static function fe_neg(ParagonIE_Sodium_Core_Curve25519_Fe $f)
    {
        $h = new ParagonIE_Sodium_Core_Curve25519_Fe();
        for ($i = 0; $i < 10; ++$i) {
            $h[$i] = -$f[$i];
        }
        return $h;
    }

    /**
     * Square a field element
     *
     * h = f * f
     *
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Fe $f
     * @return ParagonIE_Sodium_Core_Curve25519_Fe
     */
    public static function fe_sq(ParagonIE_Sodium_Core_Curve25519_Fe $f)
    {
        $f0 = (int) $f[0];
        $f1 = (int) $f[1];
        $f2 = (int) $f[2];
        $f3 = (int) $f[3];
        $f4 = (int) $f[4];
        $f5 = (int) $f[5];
        $f6 = (int) $f[6];
        $f7 = (int) $f[7];
        $f8 = (int) $f[8];
        $f9 = (int) $f[9];

        $f0_2 = $f0 << 1;
        $f1_2 = $f1 << 1;
        $f2_2 = $f2 << 1;
        $f3_2 = $f3 << 1;
        $f4_2 = $f4 << 1;
        $f5_2 = $f5 << 1;
        $f6_2 = $f6 << 1;
        $f7_2 = $f7 << 1;
        $f5_38 = self::mul(38, $f5);
        $f6_19 = self::mul(19, $f6);
        $f7_38 = self::mul(38, $f7);
        $f8_19 = self::mul(19, $f8);
        $f9_38 = self::mul(38, $f9);
        $f0f0    = self::mul($f0,    $f0);
        $f0f1_2  = self::mul($f0_2,  $f1);
        $f0f2_2  = self::mul($f0_2,  $f2);
        $f0f3_2  = self::mul($f0_2,  $f3);
        $f0f4_2  = self::mul($f0_2,  $f4);
        $f0f5_2  = self::mul($f0_2,  $f5);
        $f0f6_2  = self::mul($f0_2,  $f6);
        $f0f7_2  = self::mul($f0_2,  $f7);
        $f0f8_2  = self::mul($f0_2,  $f8);
        $f0f9_2  = self::mul($f0_2,  $f9);
        $f1f1_2  = self::mul($f1_2,  $f1);
        $f1f2_2  = self::mul($f1_2,  $f2);
        $f1f3_4  = self::mul($f1_2,  $f3_2);
        $f1f4_2  = self::mul($f1_2,  $f4);
        $f1f5_4  = self::mul($f1_2,  $f5_2);
        $f1f6_2  = self::mul($f1_2,  $f6);
        $f1f7_4  = self::mul($f1_2,  $f7_2);
        $f1f8_2  = self::mul($f1_2,  $f8);
        $f1f9_76 = self::mul($f1_2,  $f9_38);
        $f2f2    = self::mul($f2,    $f2);
        $f2f3_2  = self::mul($f2_2,  $f3);
        $f2f4_2  = self::mul($f2_2,  $f4);
        $f2f5_2  = self::mul($f2_2,  $f5);
        $f2f6_2  = self::mul($f2_2,  $f6);
        $f2f7_2  = self::mul($f2_2,  $f7);
        $f2f8_38 = self::mul($f2_2,  $f8_19);
        $f2f9_38 = self::mul($f2,    $f9_38);
        $f3f3_2  = self::mul($f3_2,  $f3);
        $f3f4_2  = self::mul($f3_2,  $f4);
        $f3f5_4  = self::mul($f3_2,  $f5_2);
        $f3f6_2  = self::mul($f3_2,  $f6);
        $f3f7_76 = self::mul($f3_2,  $f7_38);
        $f3f8_38 = self::mul($f3_2,  $f8_19);
        $f3f9_76 = self::mul($f3_2,  $f9_38);
        $f4f4    = self::mul($f4,    $f4);
        $f4f5_2  = self::mul($f4_2,  $f5);
        $f4f6_38 = self::mul($f4_2,  $f6_19);
        $f4f7_38 = self::mul($f4,    $f7_38);
        $f4f8_38 = self::mul($f4_2,  $f8_19);
        $f4f9_38 = self::mul($f4,    $f9_38);
        $f5f5_38 = self::mul($f5,    $f5_38);
        $f5f6_38 = self::mul($f5_2,  $f6_19);
        $f5f7_76 = self::mul($f5_2,  $f7_38);
        $f5f8_38 = self::mul($f5_2,  $f8_19);
        $f5f9_76 = self::mul($f5_2,  $f9_38);
        $f6f6_19 = self::mul($f6,    $f6_19);
        $f6f7_38 = self::mul($f6,    $f7_38);
        $f6f8_38 = self::mul($f6_2,  $f8_19);
        $f6f9_38 = self::mul($f6,    $f9_38);
        $f7f7_38 = self::mul($f7,    $f7_38);
        $f7f8_38 = self::mul($f7_2,  $f8_19);
        $f7f9_76 = self::mul($f7_2,  $f9_38);
        $f8f8_19 = self::mul($f8,    $f8_19);
        $f8f9_38 = self::mul($f8,    $f9_38);
        $f9f9_38 = self::mul($f9,    $f9_38);
        $h0 = $f0f0   + $f1f9_76 + $f2f8_38 + $f3f7_76 + $f4f6_38 + $f5f5_38;
        $h1 = $f0f1_2 + $f2f9_38 + $f3f8_38 + $f4f7_38 + $f5f6_38;
        $h2 = $f0f2_2 + $f1f1_2  + $f3f9_76 + $f4f8_38 + $f5f7_76 + $f6f6_19;
        $h3 = $f0f3_2 + $f1f2_2  + $f4f9_38 + $f5f8_38 + $f6f7_38;
        $h4 = $f0f4_2 + $f1f3_4  + $f2f2    + $f5f9_76 + $f6f8_38 + $f7f7_38;
        $h5 = $f0f5_2 + $f1f4_2  + $f2f3_2  + $f6f9_38 + $f7f8_38;
        $h6 = $f0f6_2 + $f1f5_4  + $f2f4_2  + $f3f3_2  + $f7f9_76 + $f8f8_19;
        $h7 = $f0f7_2 + $f1f6_2  + $f2f5_2  + $f3f4_2  + $f8f9_38;
        $h8 = $f0f8_2 + $f1f7_4  + $f2f6_2  + $f3f5_4  + $f4f4    + $f9f9_38;
        $h9 = $f0f9_2 + $f1f8_2  + $f2f7_2  + $f3f6_2  + $f4f5_2;

        $carry0 = ($h0 + (1 << 25)) >> 26;
        $h1 += $carry0;
        $h0 -= $carry0 << 26;
        $carry4 = ($h4 + (1 << 25)) >> 26;
        $h5 += $carry4;
        $h4 -= $carry4 << 26;

        $carry1 = ($h1 + (1 << 24)) >> 25;
        $h2 += $carry1;
        $h1 -= $carry1 << 25;
        $carry5 = ($h5 + (1 << 24)) >> 25;
        $h6 += $carry5;
        $h5 -= $carry5 << 25;

        $carry2 = ($h2 + (1 << 25)) >> 26;
        $h3 += $carry2;
        $h2 -= $carry2 << 26;
        $carry6 = ($h6 + (1 << 25)) >> 26;
        $h7 += $carry6;
        $h6 -= $carry6 << 26;

        $carry3 = ($h3 + (1 << 24)) >> 25;
        $h4 += $carry3;
        $h3 -= $carry3 << 25;
        $carry7 = ($h7 + (1 << 24)) >> 25;
        $h8 += $carry7;
        $h7 -= $carry7 << 25;

        $carry4 = ($h4 + (1 << 25)) >> 26;
        $h5 += $carry4;
        $h4 -= $carry4 << 26;
        $carry8 = ($h8 + (1 << 25)) >> 26;
        $h9 += $carry8;
        $h8 -= $carry8 << 26;

        $carry9 = ($h9 + (1 << 24)) >> 25;
        $h0 += self::mul($carry9, 19);
        $h9 -= $carry9 << 25;

        $carry0 = ($h0 + (1 << 25)) >> 26;
        $h1 += $carry0;
        $h0 -= $carry0 << 26;

        return ParagonIE_Sodium_Core_Curve25519_Fe::fromArray(
            array(
                (int) $h0,
                (int) $h1,
                (int) $h2,
                (int) $h3,
                (int) $h4,
                (int) $h5,
                (int) $h6,
                (int) $h7,
                (int) $h8,
                (int) $h9
            )
        );
    }


    /**
     * Square and double a field element
     *
     * h = 2 * f * f
     *
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Fe $f
     * @return ParagonIE_Sodium_Core_Curve25519_Fe
     */
    public static function fe_sq2(ParagonIE_Sodium_Core_Curve25519_Fe $f)
    {
        $f0 = (int) $f[0];
        $f1 = (int) $f[1];
        $f2 = (int) $f[2];
        $f3 = (int) $f[3];
        $f4 = (int) $f[4];
        $f5 = (int) $f[5];
        $f6 = (int) $f[6];
        $f7 = (int) $f[7];
        $f8 = (int) $f[8];
        $f9 = (int) $f[9];

        $f0_2 = $f0 << 1;
        $f1_2 = $f1 << 1;
        $f2_2 = $f2 << 1;
        $f3_2 = $f3 << 1;
        $f4_2 = $f4 << 1;
        $f5_2 = $f5 << 1;
        $f6_2 = $f6 << 1;
        $f7_2 = $f7 << 1;
        $f5_38 = self::mul(38, $f5); /* 1.959375*2^30 */
        $f6_19 = self::mul(19, $f6); /* 1.959375*2^30 */
        $f7_38 = self::mul(38, $f7); /* 1.959375*2^30 */
        $f8_19 = self::mul(19, $f8); /* 1.959375*2^30 */
        $f9_38 = self::mul(38, $f9); /* 1.959375*2^30 */
        $f0f0 = self::mul($f0, (int) $f0);
        $f0f1_2 = self::mul($f0_2, (int) $f1);
        $f0f2_2 = self::mul($f0_2, (int) $f2);
        $f0f3_2 = self::mul($f0_2, (int) $f3);
        $f0f4_2 = self::mul($f0_2, (int) $f4);
        $f0f5_2 = self::mul($f0_2, (int) $f5);
        $f0f6_2 = self::mul($f0_2, (int) $f6);
        $f0f7_2 = self::mul($f0_2, (int) $f7);
        $f0f8_2 = self::mul($f0_2, (int) $f8);
        $f0f9_2 = self::mul($f0_2, (int) $f9);
        $f1f1_2 = self::mul($f1_2,  (int) $f1);
        $f1f2_2 = self::mul($f1_2,  (int) $f2);
        $f1f3_4 = self::mul($f1_2,  (int) $f3_2);
        $f1f4_2 = self::mul($f1_2,  (int) $f4);
        $f1f5_4 = self::mul($f1_2,  (int) $f5_2);
        $f1f6_2 = self::mul($f1_2,  (int) $f6);
        $f1f7_4 = self::mul($f1_2,  (int) $f7_2);
        $f1f8_2 = self::mul($f1_2,  (int) $f8);
        $f1f9_76 = self::mul($f1_2,  (int) $f9_38);
        $f2f2 = self::mul($f2,  (int) $f2);
        $f2f3_2 = self::mul($f2_2,  (int) $f3);
        $f2f4_2 = self::mul($f2_2,  (int) $f4);
        $f2f5_2 = self::mul($f2_2,  (int) $f5);
        $f2f6_2 = self::mul($f2_2,  (int) $f6);
        $f2f7_2 = self::mul($f2_2,  (int) $f7);
        $f2f8_38 = self::mul($f2_2,  (int) $f8_19);
        $f2f9_38 = self::mul($f2,  (int) $f9_38);
        $f3f3_2 = self::mul($f3_2,  (int) $f3);
        $f3f4_2 = self::mul($f3_2,  (int) $f4);
        $f3f5_4 = self::mul($f3_2,  (int) $f5_2);
        $f3f6_2 = self::mul($f3_2,  (int) $f6);
        $f3f7_76 = self::mul($f3_2,  (int) $f7_38);
        $f3f8_38 = self::mul($f3_2,  (int) $f8_19);
        $f3f9_76 = self::mul($f3_2,  (int) $f9_38);
        $f4f4 = self::mul($f4,  (int) $f4);
        $f4f5_2 = self::mul($f4_2,  (int) $f5);
        $f4f6_38 = self::mul($f4_2,  (int) $f6_19);
        $f4f7_38 = self::mul($f4,  (int) $f7_38);
        $f4f8_38 = self::mul($f4_2,  (int) $f8_19);
        $f4f9_38 = self::mul($f4,  (int) $f9_38);
        $f5f5_38 = self::mul($f5,  (int) $f5_38);
        $f5f6_38 = self::mul($f5_2,  (int) $f6_19);
        $f5f7_76 = self::mul($f5_2,  (int) $f7_38);
        $f5f8_38 = self::mul($f5_2,  (int) $f8_19);
        $f5f9_76 = self::mul($f5_2,  (int) $f9_38);
        $f6f6_19 = self::mul($f6,  (int) $f6_19);
        $f6f7_38 = self::mul($f6,  (int) $f7_38);
        $f6f8_38 = self::mul($f6_2,  (int) $f8_19);
        $f6f9_38 = self::mul($f6,  (int) $f9_38);
        $f7f7_38 = self::mul($f7,  (int) $f7_38);
        $f7f8_38 = self::mul($f7_2,  (int) $f8_19);
        $f7f9_76 = self::mul($f7_2,  (int) $f9_38);
        $f8f8_19 = self::mul($f8,  (int) $f8_19);
        $f8f9_38 = self::mul($f8,  (int) $f9_38);
        $f9f9_38 = self::mul($f9,  (int) $f9_38);

        $h0 = (int) ($f0f0 + $f1f9_76 + $f2f8_38 + $f3f7_76 + $f4f6_38 + $f5f5_38);
        $h1 = (int) ($f0f1_2 + $f2f9_38 + $f3f8_38 + $f4f7_38 + $f5f6_38);
        $h2 = (int) ($f0f2_2 + $f1f1_2  + $f3f9_76 + $f4f8_38 + $f5f7_76 + $f6f6_19);
        $h3 = (int) ($f0f3_2 + $f1f2_2  + $f4f9_38 + $f5f8_38 + $f6f7_38);
        $h4 = (int) ($f0f4_2 + $f1f3_4  + $f2f2    + $f5f9_76 + $f6f8_38 + $f7f7_38);
        $h5 = (int) ($f0f5_2 + $f1f4_2  + $f2f3_2  + $f6f9_38 + $f7f8_38);
        $h6 = (int) ($f0f6_2 + $f1f5_4  + $f2f4_2  + $f3f3_2  + $f7f9_76 + $f8f8_19);
        $h7 = (int) ($f0f7_2 + $f1f6_2  + $f2f5_2  + $f3f4_2  + $f8f9_38);
        $h8 = (int) ($f0f8_2 + $f1f7_4  + $f2f6_2  + $f3f5_4  + $f4f4    + $f9f9_38);
        $h9 = (int) ($f0f9_2 + $f1f8_2  + $f2f7_2  + $f3f6_2  + $f4f5_2);

        $h0 = (int) ($h0 + $h0);
        $h1 = (int) ($h1 + $h1);
        $h2 = (int) ($h2 + $h2);
        $h3 = (int) ($h3 + $h3);
        $h4 = (int) ($h4 + $h4);
        $h5 = (int) ($h5 + $h5);
        $h6 = (int) ($h6 + $h6);
        $h7 = (int) ($h7 + $h7);
        $h8 = (int) ($h8 + $h8);
        $h9 = (int) ($h9 + $h9);

        $carry0 = ($h0 + (1 << 25)) >> 26;
        $h1 += $carry0;
        $h0 -= $carry0 << 26;
        $carry4 = ($h4 + (1 << 25)) >> 26;
        $h5 += $carry4;
        $h4 -= $carry4 << 26;

        $carry1 = ($h1 + (1 << 24)) >> 25;
        $h2 += $carry1;
        $h1 -= $carry1 << 25;
        $carry5 = ($h5 + (1 << 24)) >> 25;
        $h6 += $carry5;
        $h5 -= $carry5 << 25;

        $carry2 = ($h2 + (1 << 25)) >> 26;
        $h3 += $carry2;
        $h2 -= $carry2 << 26;
        $carry6 = ($h6 + (1 << 25)) >> 26;
        $h7 += $carry6;
        $h6 -= $carry6 << 26;

        $carry3 = ($h3 + (1 << 24)) >> 25;
        $h4 += $carry3;
        $h3 -= $carry3 << 25;
        $carry7 = ($h7 + (1 << 24)) >> 25;
        $h8 += $carry7;
        $h7 -= $carry7 << 25;

        $carry4 = ($h4 + (1 << 25)) >> 26;
        $h5 += $carry4;
        $h4 -= $carry4 << 26;
        $carry8 = ($h8 + (1 << 25)) >> 26;
        $h9 += $carry8;
        $h8 -= $carry8 << 26;

        $carry9 = ($h9 + (1 << 24)) >> 25;
        $h0 += self::mul($carry9, 19);
        $h9 -= $carry9 << 25;

        $carry0 = ($h0 + (1 << 25)) >> 26;
        $h1 += $carry0;
        $h0 -= $carry0 << 26;

        return ParagonIE_Sodium_Core_Curve25519_Fe::fromArray(
            array(
                (int) $h0,
                (int) $h1,
                (int) $h2,
                (int) $h3,
                (int) $h4,
                (int) $h5,
                (int) $h6,
                (int) $h7,
                (int) $h8,
                (int) $h9
            )
        );
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Fe $Z
     * @return ParagonIE_Sodium_Core_Curve25519_Fe
     */
    public static function fe_invert(ParagonIE_Sodium_Core_Curve25519_Fe $Z)
    {
        $z = clone $Z;
        $t0 = self::fe_sq($z);
        $t1 = self::fe_sq($t0);
        $t1 = self::fe_sq($t1);
        $t1 = self::fe_mul($z, $t1);
        $t0 = self::fe_mul($t0, $t1);
        $t2 = self::fe_sq($t0);
        $t1 = self::fe_mul($t1, $t2);
        $t2 = self::fe_sq($t1);
        for ($i = 1; $i < 5; ++$i) {
            $t2 = self::fe_sq($t2);
        }
        $t1 = self::fe_mul($t2, $t1);
        $t2 = self::fe_sq($t1);
        for ($i = 1; $i < 10; ++$i) {
            $t2 = self::fe_sq($t2);
        }
        $t2 = self::fe_mul($t2, $t1);
        $t3 = self::fe_sq($t2);
        for ($i = 1; $i < 20; ++$i) {
            $t3 = self::fe_sq($t3);
        }
        $t2 = self::fe_mul($t3, $t2);
        $t2 = self::fe_sq($t2);
        for ($i = 1; $i < 10; ++$i) {
            $t2 = self::fe_sq($t2);
        }
        $t1 = self::fe_mul($t2, $t1);
        $t2 = self::fe_sq($t1);
        for ($i = 1; $i < 50; ++$i) {
            $t2 = self::fe_sq($t2);
        }
        $t2 = self::fe_mul($t2, $t1);
        $t3 = self::fe_sq($t2);
        for ($i = 1; $i < 100; ++$i) {
            $t3 = self::fe_sq($t3);
        }
        $t2 = self::fe_mul($t3, $t2);
        $t2 = self::fe_sq($t2);
        for ($i = 1; $i < 50; ++$i) {
            $t2 = self::fe_sq($t2);
        }
        $t1 = self::fe_mul($t2, $t1);
        $t1 = self::fe_sq($t1);
        for ($i = 1; $i < 5; ++$i) {
            $t1 = self::fe_sq($t1);
        }
        return self::fe_mul($t1, $t0);
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @ref https://github.com/jedisct1/libsodium/blob/68564326e1e9dc57ef03746f85734232d20ca6fb/src/libsodium/crypto_core/curve25519/ref10/curve25519_ref10.c#L1054-L1106
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Fe $z
     * @return ParagonIE_Sodium_Core_Curve25519_Fe
     */
    public static function fe_pow22523(ParagonIE_Sodium_Core_Curve25519_Fe $z)
    {
        # fe_sq(t0, z);
        # fe_sq(t1, t0);
        # fe_sq(t1, t1);
        # fe_mul(t1, z, t1);
        # fe_mul(t0, t0, t1);
        # fe_sq(t0, t0);
        # fe_mul(t0, t1, t0);
        # fe_sq(t1, t0);
        $t0 = self::fe_sq($z);
        $t1 = self::fe_sq($t0);
        $t1 = self::fe_sq($t1);
        $t1 = self::fe_mul($z, $t1);
        $t0 = self::fe_mul($t0, $t1);
        $t0 = self::fe_sq($t0);
        $t0 = self::fe_mul($t1, $t0);
        $t1 = self::fe_sq($t0);

        # for (i = 1; i < 5; ++i) {
        #     fe_sq(t1, t1);
        # }
        for ($i = 1; $i < 5; ++$i) {
            $t1 = self::fe_sq($t1);
        }

        # fe_mul(t0, t1, t0);
        # fe_sq(t1, t0);
        $t0 = self::fe_mul($t1, $t0);
        $t1 = self::fe_sq($t0);

        # for (i = 1; i < 10; ++i) {
        #     fe_sq(t1, t1);
        # }
        for ($i = 1; $i < 10; ++$i) {
            $t1 = self::fe_sq($t1);
        }

        # fe_mul(t1, t1, t0);
        # fe_sq(t2, t1);
        $t1 = self::fe_mul($t1, $t0);
        $t2 = self::fe_sq($t1);

        # for (i = 1; i < 20; ++i) {
        #     fe_sq(t2, t2);
        # }
        for ($i = 1; $i < 20; ++$i) {
            $t2 = self::fe_sq($t2);
        }

        # fe_mul(t1, t2, t1);
        # fe_sq(t1, t1);
        $t1 = self::fe_mul($t2, $t1);
        $t1 = self::fe_sq($t1);

        # for (i = 1; i < 10; ++i) {
        #     fe_sq(t1, t1);
        # }
        for ($i = 1; $i < 10; ++$i) {
            $t1 = self::fe_sq($t1);
        }

        # fe_mul(t0, t1, t0);
        # fe_sq(t1, t0);
        $t0 = self::fe_mul($t1, $t0);
        $t1 = self::fe_sq($t0);

        # for (i = 1; i < 50; ++i) {
        #     fe_sq(t1, t1);
        # }
        for ($i = 1; $i < 50; ++$i) {
            $t1 = self::fe_sq($t1);
        }

        # fe_mul(t1, t1, t0);
        # fe_sq(t2, t1);
        $t1 = self::fe_mul($t1, $t0);
        $t2 = self::fe_sq($t1);

        # for (i = 1; i < 100; ++i) {
        #     fe_sq(t2, t2);
        # }
        for ($i = 1; $i < 100; ++$i) {
            $t2 = self::fe_sq($t2);
        }

        # fe_mul(t1, t2, t1);
        # fe_sq(t1, t1);
        $t1 = self::fe_mul($t2, $t1);
        $t1 = self::fe_sq($t1);

        # for (i = 1; i < 50; ++i) {
        #     fe_sq(t1, t1);
        # }
        for ($i = 1; $i < 50; ++$i) {
            $t1 = self::fe_sq($t1);
        }

        # fe_mul(t0, t1, t0);
        # fe_sq(t0, t0);
        # fe_sq(t0, t0);
        # fe_mul(out, t0, z);
        $t0 = self::fe_mul($t1, $t0);
        $t0 = self::fe_sq($t0);
        $t0 = self::fe_sq($t0);
        return self::fe_mul($t0, $z);
    }

    /**
     * Subtract two field elements.
     *
     * h = f - g
     *
     * Preconditions:
     * |f| bounded by 1.1*2^25,1.1*2^24,1.1*2^25,1.1*2^24,etc.
     * |g| bounded by 1.1*2^25,1.1*2^24,1.1*2^25,1.1*2^24,etc.
     *
     * Postconditions:
     * |h| bounded by 1.1*2^26,1.1*2^25,1.1*2^26,1.1*2^25,etc.
     *
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Fe $f
     * @param ParagonIE_Sodium_Core_Curve25519_Fe $g
     * @return ParagonIE_Sodium_Core_Curve25519_Fe
     */
    public static function fe_sub(ParagonIE_Sodium_Core_Curve25519_Fe $f, ParagonIE_Sodium_Core_Curve25519_Fe $g)
    {
        return ParagonIE_Sodium_Core_Curve25519_Fe::fromArray(
            array(
                (int) ($f[0] - $g[0]),
                (int) ($f[1] - $g[1]),
                (int) ($f[2] - $g[2]),
                (int) ($f[3] - $g[3]),
                (int) ($f[4] - $g[4]),
                (int) ($f[5] - $g[5]),
                (int) ($f[6] - $g[6]),
                (int) ($f[7] - $g[7]),
                (int) ($f[8] - $g[8]),
                (int) ($f[9] - $g[9])
            )
        );
    }

    /**
     * Add two group elements.
     *
     * r = p + q
     *
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_P3 $p
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_Cached $q
     * @return ParagonIE_Sodium_Core_Curve25519_Ge_P1p1
     */
    public static function ge_add(
        ParagonIE_Sodium_Core_Curve25519_Ge_P3 $p,
        ParagonIE_Sodium_Core_Curve25519_Ge_Cached $q
    ) {
        $r = new ParagonIE_Sodium_Core_Curve25519_Ge_P1p1();
        $r->X = self::fe_add($p->Y, $p->X);
        $r->Y = self::fe_sub($p->Y, $p->X);
        $r->Z = self::fe_mul($r->X, $q->YplusX);
        $r->Y = self::fe_mul($r->Y, $q->YminusX);
        $r->T = self::fe_mul($q->T2d, $p->T);
        $r->X = self::fe_mul($p->Z, $q->Z);
        $t0   = self::fe_add($r->X, $r->X);
        $r->X = self::fe_sub($r->Z, $r->Y);
        $r->Y = self::fe_add($r->Z, $r->Y);
        $r->Z = self::fe_add($t0, $r->T);
        $r->T = self::fe_sub($t0, $r->T);
        return $r;
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @ref https://github.com/jedisct1/libsodium/blob/157c4a80c13b117608aeae12178b2d38825f9f8f/src/libsodium/crypto_core/curve25519/ref10/curve25519_ref10.c#L1185-L1215
     * @param string $a
     * @return array<int, mixed>
     */
    public static function slide($a)
    {
        if (self::strlen($a) < 256) {
            if (self::strlen($a) < 16) {
                $a = str_pad($a, 256, '0', STR_PAD_RIGHT);
            }
        }
        $r = array();
        for ($i = 0; $i < 256; ++$i) {
            $r[$i] = 1 & (
                    self::chrToInt($a[$i >> 3])
                        >>
                    ($i & 7)
                );
        }

        for ($i = 0;$i < 256;++$i) {
            if ($r[$i]) {
                for ($b = 1;$b <= 6 && $i + $b < 256;++$b) {
                    if ($r[$i + $b]) {
                        if ($r[$i] + ($r[$i + $b] << $b) <= 15) {
                            $r[$i] += $r[$i + $b] << $b;
                            $r[$i + $b] = 0;
                        } elseif ($r[$i] - ($r[$i + $b] << $b) >= -15) {
                            $r[$i] -= $r[$i + $b] << $b;
                            for ($k = $i + $b; $k < 256; ++$k) {
                                if (!$r[$k]) {
                                    $r[$k] = 1;
                                    break;
                                }
                                $r[$k] = 0;
                            }
                        } else {
                            break;
                        }
                    }
                }
            }
        }
        return $r;
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param string $s
     * @return ParagonIE_Sodium_Core_Curve25519_Ge_P3
     */
    public static function ge_frombytes_negate_vartime($s)
    {
        static $d = null;
        if (!$d) {
            $d = ParagonIE_Sodium_Core_Curve25519_Fe::fromArray(self::$d);
        }

        # fe_frombytes(h->Y,s);
        # fe_1(h->Z);
        $h = new ParagonIE_Sodium_Core_Curve25519_Ge_P3(
            self::fe_0(),
            self::fe_frombytes($s),
            self::fe_1()
        );

        # fe_sq(u,h->Y);
        # fe_mul(v,u,d);
        # fe_sub(u,u,h->Z);       /* u = y^2-1 */
        # fe_add(v,v,h->Z);       /* v = dy^2+1 */
        $u = self::fe_sq($h->Y);
        $v = self::fe_mul($u, $d);
        $u = self::fe_sub($u, $h->Z); /* u =  y^2 - 1 */
        $v = self::fe_add($v, $h->Z); /* v = dy^2 + 1 */

        # fe_sq(v3,v);
        # fe_mul(v3,v3,v);        /* v3 = v^3 */
        # fe_sq(h->X,v3);
        # fe_mul(h->X,h->X,v);
        # fe_mul(h->X,h->X,u);    /* x = uv^7 */
        $v3 = self::fe_sq($v);
        $v3 = self::fe_mul($v3, $v); /* v3 = v^3 */
        $h->X = self::fe_sq($v3);
        $h->X = self::fe_mul($h->X, $v);
        $h->X = self::fe_mul($h->X, $u); /* x = uv^7 */

        # fe_pow22523(h->X,h->X); /* x = (uv^7)^((q-5)/8) */
        # fe_mul(h->X,h->X,v3);
        # fe_mul(h->X,h->X,u);    /* x = uv^3(uv^7)^((q-5)/8) */
        $h->X = self::fe_pow22523($h->X); /* x = (uv^7)^((q-5)/8) */
        $h->X = self::fe_mul($h->X, $v3);
        $h->X = self::fe_mul($h->X, $u); /* x = uv^3(uv^7)^((q-5)/8) */

        # fe_sq(vxx,h->X);
        # fe_mul(vxx,vxx,v);
        # fe_sub(check,vxx,u);    /* vx^2-u */
        $vxx = self::fe_sq($h->X);
        $vxx = self::fe_mul($vxx, $v);
        $check = self::fe_sub($vxx, $u); /* vx^2 - u */

        # if (fe_isnonzero(check)) {
        #     fe_add(check,vxx,u);  /* vx^2+u */
        #     if (fe_isnonzero(check)) {
        #         return -1;
        #     }
        #     fe_mul(h->X,h->X,sqrtm1);
        # }
        if (self::fe_isnonzero($check)) {
            $check = self::fe_add($vxx, $u); /* vx^2 + u */
            if (self::fe_isnonzero($check)) {
                throw new RangeException('Internal check failed.');
            }
            $h->X = self::fe_mul(
                $h->X,
                ParagonIE_Sodium_Core_Curve25519_Fe::fromArray(self::$sqrtm1)
            );
        }

        # if (fe_isnegative(h->X) == (s[31] >> 7)) {
        #     fe_neg(h->X,h->X);
        # }
        $i = self::chrToInt($s[31]);
        if (self::fe_isnegative($h->X) === ($i >> 7)) {
            $h->X = self::fe_neg($h->X);
        }

        # fe_mul(h->T,h->X,h->Y);
        $h->T = self::fe_mul($h->X, $h->Y);
        return $h;
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_P1p1 $R
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_P3 $p
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_Precomp $q
     * @return ParagonIE_Sodium_Core_Curve25519_Ge_P1p1
     */
    public static function ge_madd(
        ParagonIE_Sodium_Core_Curve25519_Ge_P1p1 $R,
        ParagonIE_Sodium_Core_Curve25519_Ge_P3 $p,
        ParagonIE_Sodium_Core_Curve25519_Ge_Precomp $q
    ) {
        $r = clone $R;
        $r->X = self::fe_add($p->Y, $p->X);
        $r->Y = self::fe_sub($p->Y, $p->X);
        $r->Z = self::fe_mul($r->X, $q->yplusx);
        $r->Y = self::fe_mul($r->Y, $q->yminusx);
        $r->T = self::fe_mul($q->xy2d, $p->T);
        $t0 = self::fe_add(clone $p->Z, clone $p->Z);
        $r->X = self::fe_sub($r->Z, $r->Y);
        $r->Y = self::fe_add($r->Z, $r->Y);
        $r->Z = self::fe_add($t0, $r->T);
        $r->T = self::fe_sub($t0, $r->T);

        return $r;
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_P1p1 $R
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_P3 $p
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_Precomp $q
     * @return ParagonIE_Sodium_Core_Curve25519_Ge_P1p1
     */
    public static function ge_msub(
        ParagonIE_Sodium_Core_Curve25519_Ge_P1p1 $R,
        ParagonIE_Sodium_Core_Curve25519_Ge_P3 $p,
        ParagonIE_Sodium_Core_Curve25519_Ge_Precomp $q
    ) {
        $r = clone $R;

        $r->X = self::fe_add($p->Y, $p->X);
        $r->Y = self::fe_sub($p->Y, $p->X);
        $r->Z = self::fe_mul($r->X, $q->yminusx);
        $r->Y = self::fe_mul($r->Y, $q->yplusx);
        $r->T = self::fe_mul($q->xy2d, $p->T);
        $t0 = self::fe_add($p->Z, $p->Z);
        $r->X = self::fe_sub($r->Z, $r->Y);
        $r->Y = self::fe_add($r->Z, $r->Y);
        $r->Z = self::fe_sub($t0, $r->T);
        $r->T = self::fe_add($t0, $r->T);

        return $r;
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_P1p1 $p
     * @return ParagonIE_Sodium_Core_Curve25519_Ge_P2
     */
    public static function ge_p1p1_to_p2(ParagonIE_Sodium_Core_Curve25519_Ge_P1p1 $p)
    {
        $r = new ParagonIE_Sodium_Core_Curve25519_Ge_P2();
        $r->X = self::fe_mul($p->X, $p->T);
        $r->Y = self::fe_mul($p->Y, $p->Z);
        $r->Z = self::fe_mul($p->Z, $p->T);
        return $r;
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_P1p1 $p
     * @return ParagonIE_Sodium_Core_Curve25519_Ge_P3
     */
    public static function ge_p1p1_to_p3(ParagonIE_Sodium_Core_Curve25519_Ge_P1p1 $p)
    {
        $r = new ParagonIE_Sodium_Core_Curve25519_Ge_P3();
        $r->X = self::fe_mul($p->X, $p->T);
        $r->Y = self::fe_mul($p->Y, $p->Z);
        $r->Z = self::fe_mul($p->Z, $p->T);
        $r->T = self::fe_mul($p->X, $p->Y);
        return $r;
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @return ParagonIE_Sodium_Core_Curve25519_Ge_P2
     */
    public static function ge_p2_0()
    {
        return new ParagonIE_Sodium_Core_Curve25519_Ge_P2(
            self::fe_0(),
            self::fe_1(),
            self::fe_1()
        );
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_P2 $p
     * @return ParagonIE_Sodium_Core_Curve25519_Ge_P1p1
     */
    public static function ge_p2_dbl(ParagonIE_Sodium_Core_Curve25519_Ge_P2 $p)
    {
        $r = new ParagonIE_Sodium_Core_Curve25519_Ge_P1p1();

        $r->X = self::fe_sq($p->X);
        $r->Z = self::fe_sq($p->Y);
        $r->T = self::fe_sq2($p->Z);
        $r->Y = self::fe_add($p->X, $p->Y);
        $t0   = self::fe_sq($r->Y);
        $r->Y = self::fe_add($r->Z, $r->X);
        $r->Z = self::fe_sub($r->Z, $r->X);
        $r->X = self::fe_sub($t0, $r->Y);
        $r->T = self::fe_sub($r->T, $r->Z);

        return $r;
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @return ParagonIE_Sodium_Core_Curve25519_Ge_P3
     */
    public static function ge_p3_0()
    {
        return new ParagonIE_Sodium_Core_Curve25519_Ge_P3(
            self::fe_0(),
            self::fe_1(),
            self::fe_1(),
            self::fe_0()
        );
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_P3 $p
     * @return ParagonIE_Sodium_Core_Curve25519_Ge_Cached
     */
    public static function ge_p3_to_cached(ParagonIE_Sodium_Core_Curve25519_Ge_P3 $p)
    {
        static $d2 = null;
        if ($d2 === null) {
            $d2 = ParagonIE_Sodium_Core_Curve25519_Fe::fromArray(self::$d2);
        }
        $r = new ParagonIE_Sodium_Core_Curve25519_Ge_Cached();
        $r->YplusX = self::fe_add($p->Y, $p->X);
        $r->YminusX = self::fe_sub($p->Y, $p->X);
        $r->Z = self::fe_copy($p->Z);
        $r->T2d = self::fe_mul($p->T, $d2);
        return $r;
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_P3 $p
     * @return ParagonIE_Sodium_Core_Curve25519_Ge_P2
     */
    public static function ge_p3_to_p2(ParagonIE_Sodium_Core_Curve25519_Ge_P3 $p)
    {
        return new ParagonIE_Sodium_Core_Curve25519_Ge_P2(
            $p->X,
            $p->Y,
            $p->Z
        );
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_P3 $h
     * @return string
     */
    public static function ge_p3_tobytes(ParagonIE_Sodium_Core_Curve25519_Ge_P3 $h)
    {
        $recip = self::fe_invert($h->Z);
        $x = self::fe_mul($h->X, $recip);
        $y = self::fe_mul($h->Y, $recip);
        $s = self::fe_tobytes($y);
        $s[31] = self::intToChr(
            self::chrToInt($s[31]) ^ (self::fe_isnegative($x) << 7)
        );
        return $s;
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_P3 $p
     * @return ParagonIE_Sodium_Core_Curve25519_Ge_P1p1
     */
    public static function ge_p3_dbl(ParagonIE_Sodium_Core_Curve25519_Ge_P3 $p)
    {
        $q = self::ge_p3_to_p2($p);
        return self::ge_p2_dbl($q);
    }

    /**
     * @return ParagonIE_Sodium_Core_Curve25519_Ge_Precomp
     */
    public static function ge_precomp_0()
    {
        return new ParagonIE_Sodium_Core_Curve25519_Ge_Precomp(
            self::fe_1(),
            self::fe_1(),
            self::fe_0()
        );
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param int $b
     * @param int $c
     * @return int
     */
    public static function equal($b, $c)
    {
        return (($b ^ $c) - 1 & 0xffffffff) >> 31;
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param int $char
     * @return int (1 = yes, 0 = no)
     */
    public static function negative($char)
    {
        if (is_int($char)) {
            return $char < 0 ? 1 : 0;
        }
        $x = self::chrToInt(self::substr($char, 0, 1));
        if (PHP_INT_SIZE === 8) {
            return $x >> 63;
        }
        return $x >> 31;
    }

    /**
     * Conditional move
     *
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_Precomp $t
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_Precomp $u
     * @param int $b
     * @return ParagonIE_Sodium_Core_Curve25519_Ge_Precomp
     */
    public static function cmov(
        ParagonIE_Sodium_Core_Curve25519_Ge_Precomp $t,
        ParagonIE_Sodium_Core_Curve25519_Ge_Precomp $u,
        $b
    ) {
        if (!is_int($b)) {
            throw new InvalidArgumentException('Expected an integer.');
        }
        return new ParagonIE_Sodium_Core_Curve25519_Ge_Precomp(
            self::fe_cmov($t->yplusx, $u->yplusx, $b),
            self::fe_cmov($t->yminusx, $u->yminusx, $b),
            self::fe_cmov($t->xy2d, $u->xy2d, $b)
        );
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param int $pos
     * @param int $b
     * @return ParagonIE_Sodium_Core_Curve25519_Ge_Precomp
     */
    public static function ge_select($pos = 0, $b = 0)
    {
        static $base = null;
        if ($base === null) {
            $base = array();
            foreach (self::$base as $i => $bas) {
                for ($j = 0; $j < 8; ++$j) {
                    $base[$i][$j] = new ParagonIE_Sodium_Core_Curve25519_Ge_Precomp(
                        ParagonIE_Sodium_Core_Curve25519_Fe::fromArray($bas[$j][0]),
                        ParagonIE_Sodium_Core_Curve25519_Fe::fromArray($bas[$j][1]),
                        ParagonIE_Sodium_Core_Curve25519_Fe::fromArray($bas[$j][2])
                    );
                }
            }
        }
        if (!is_int($pos)) {
            throw new InvalidArgumentException('Position must be an integer');
        }
        if ($pos < 0 || $pos > 31) {
            throw new RangeException('Position is out of range [0, 31]');
        }

        $bnegative = self::negative($b);
        $babs = $b - (((-$bnegative) & $b) << 1);

        $t = self::ge_precomp_0();
        for ($i = 0; $i < 8; ++$i) {
            $t = self::cmov(
                $t,
                $base[$pos][$i],
                self::equal($babs, $i + 1)
            );
        }
        $minusT = new ParagonIE_Sodium_Core_Curve25519_Ge_Precomp(
            self::fe_copy($t->yminusx),
            self::fe_copy($t->yplusx),
            self::fe_neg($t->xy2d)
        );
        return self::cmov($t, $minusT, $bnegative);
    }

    /**
     * Subtract two group elements.
     *
     * r = p - q
     *
     * @internal You should not use this directly from another application
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_P3 $p
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_Cached $q
     * @return ParagonIE_Sodium_Core_Curve25519_Ge_P1p1
     */
    public static function ge_sub(
        ParagonIE_Sodium_Core_Curve25519_Ge_P3 $p,
        ParagonIE_Sodium_Core_Curve25519_Ge_Cached $q
    ) {
        $r = new ParagonIE_Sodium_Core_Curve25519_Ge_P1p1();

        $r->X = self::fe_add($p->Y, $p->X);
        $r->Y = self::fe_sub($p->Y, $p->X);
        $r->Z = self::fe_mul($r->X, $q->YminusX);
        $r->Y = self::fe_mul($r->Y, $q->YplusX);
        $r->T = self::fe_mul($q->T2d, $p->T);
        $r->X = self::fe_mul($p->Z, $q->Z);
        $t0 = self::fe_add($r->X, $r->X);
        $r->X = self::fe_sub($r->Z, $r->Y);
        $r->Y = self::fe_add($r->Z, $r->Y);
        $r->Z = self::fe_sub($t0, $r->T);
        $r->T = self::fe_add($t0, $r->T);

        return $r;
    }

    /**
     * Convert a group element to a byte string.
     *
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_P2 $h
     * @return string
     */
    public static function ge_tobytes(ParagonIE_Sodium_Core_Curve25519_Ge_P2 $h)
    {
        $recip = self::fe_invert($h->Z);
        $x = self::fe_mul($h->X, $recip);
        $y = self::fe_mul($h->Y, $recip);
        $s = self::fe_tobytes($y);
        $s[31] = self::intToChr(
            self::chrToInt($s[31]) ^ (self::fe_isnegative($x) << 7)
        );
        return $s;
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param string $a
     * @param ParagonIE_Sodium_Core_Curve25519_Ge_P3 $A
     * @param string $b
     * @return ParagonIE_Sodium_Core_Curve25519_Ge_P2
     */
    public static function ge_double_scalarmult_vartime(
        $a,
        ParagonIE_Sodium_Core_Curve25519_Ge_P3 $A,
        $b
    ) {
        /**
         * @var ParagonIE_Sodium_Core_Curve25519_Ge_Cached[]
         */
        $Ai = array();

        /**
         * @var ParagonIE_Sodium_Core_Curve25519_Ge_Precomp[]
         */
        static $Bi = array();
        if (!$Bi) {
            for ($i = 0; $i < 8; ++$i) {
                $Bi[$i] = new ParagonIE_Sodium_Core_Curve25519_Ge_Precomp(
                    ParagonIE_Sodium_Core_Curve25519_Fe::fromArray(self::$base2[$i][0]),
                    ParagonIE_Sodium_Core_Curve25519_Fe::fromArray(self::$base2[$i][1]),
                    ParagonIE_Sodium_Core_Curve25519_Fe::fromArray(self::$base2[$i][2])
                );
            }
        }
        for ($i = 0; $i < 8; ++$i) {
            $Ai[$i] = new ParagonIE_Sodium_Core_Curve25519_Ge_Cached(
                self::fe_0(),
                self::fe_0(),
                self::fe_0(),
                self::fe_0()
            );
        }

        # slide(aslide,a);
        # slide(bslide,b);
        $aslide = self::slide($a);
        $bslide = self::slide($b);

        # ge_p3_to_cached(&Ai[0],A);
        # ge_p3_dbl(&t,A); ge_p1p1_to_p3(&A2,&t);
        $Ai[0] = self::ge_p3_to_cached($A);
        $t = self::ge_p3_dbl($A);
        $A2 = self::ge_p1p1_to_p3($t);

        # ge_add(&t,&A2,&Ai[0]); ge_p1p1_to_p3(&u,&t); ge_p3_to_cached(&Ai[1],&u);
        # ge_add(&t,&A2,&Ai[1]); ge_p1p1_to_p3(&u,&t); ge_p3_to_cached(&Ai[2],&u);
        # ge_add(&t,&A2,&Ai[2]); ge_p1p1_to_p3(&u,&t); ge_p3_to_cached(&Ai[3],&u);
        # ge_add(&t,&A2,&Ai[3]); ge_p1p1_to_p3(&u,&t); ge_p3_to_cached(&Ai[4],&u);
        # ge_add(&t,&A2,&Ai[4]); ge_p1p1_to_p3(&u,&t); ge_p3_to_cached(&Ai[5],&u);
        # ge_add(&t,&A2,&Ai[5]); ge_p1p1_to_p3(&u,&t); ge_p3_to_cached(&Ai[6],&u);
        # ge_add(&t,&A2,&Ai[6]); ge_p1p1_to_p3(&u,&t); ge_p3_to_cached(&Ai[7],&u);
        for ($i = 0; $i < 7; ++$i) {
            $t = self::ge_add($A2, $Ai[$i]);
            $u = self::ge_p1p1_to_p3($t);
            $Ai[$i + 1] = self::ge_p3_to_cached($u);
        }

        # ge_p2_0(r);
        $r = self::ge_p2_0();

        # for (i = 255;i >= 0;--i) {
        #     if (aslide[i] || bslide[i]) break;
        # }
        $i = 255;
        for (; $i >= 0; --$i) {
            if ($aslide[$i] || $bslide[$i]) {
                break;
            }
        }

        # for (;i >= 0;--i) {
        for (; $i >= 0; --$i) {
            # ge_p2_dbl(&t,r);
            $t = self::ge_p2_dbl($r);

            # if (aslide[i] > 0) {
            if ($aslide[$i] > 0) {
                # ge_p1p1_to_p3(&u,&t);
                # ge_add(&t,&u,&Ai[aslide[i]/2]);
                $u = self::ge_p1p1_to_p3($t);
                $t = self::ge_add(
                    $u,
                    $Ai[(int) floor($aslide[$i] / 2)]
                );
            # } else if (aslide[i] < 0) {
            } elseif ($aslide[$i] < 0) {
                # ge_p1p1_to_p3(&u,&t);
                # ge_sub(&t,&u,&Ai[(-aslide[i])/2]);
                $u = self::ge_p1p1_to_p3($t);
                $t = self::ge_sub(
                    $u,
                    $Ai[(int) floor(-$aslide[$i] / 2)]
                );
            }

            # if (bslide[i] > 0) {
            if ($bslide[$i] > 0) {
                # ge_p1p1_to_p3(&u,&t);
                # ge_madd(&t,&u,&Bi[bslide[i]/2]);
                $u = self::ge_p1p1_to_p3($t);
                $t = self::ge_madd(
                    $t,
                    $u,
                    $Bi[(int) floor($bslide[$i] / 2)]
                );
            # } else if (bslide[i] < 0) {
            } elseif ($bslide[$i] < 0) {
                # ge_p1p1_to_p3(&u,&t);
                # ge_msub(&t,&u,&Bi[(-bslide[i])/2]);
                $u = self::ge_p1p1_to_p3($t);
                $t = self::ge_msub(
                    $t,
                    $u,
                    $Bi[(int) floor(-$bslide[$i] / 2)]
                );
            }
            # ge_p1p1_to_p2(r,&t);
            $r = self::ge_p1p1_to_p2($t);
        }
        return $r;
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param string $a
     * @return ParagonIE_Sodium_Core_Curve25519_Ge_P3
     */
    public static function ge_scalarmult_base($a)
    {
        $e = array();
        $r = new ParagonIE_Sodium_Core_Curve25519_Ge_P1p1();

        for ($i = 0; $i < 32; ++$i) {
            $e[$i << 1] = self::chrToInt($a[$i]) & 15;
            $e[($i << 1) + 1] = (self::chrToInt($a[$i]) >> 4) & 15;
        }

        $carry = 0;
        for ($i = 0; $i < 63; ++$i) {
            $e[$i] += $carry;
            $carry = $e[$i] + 8;
            $carry >>= 4;
            $e[$i] -= $carry << 4;
        }
        $e[63] += $carry;

        $h = self::ge_p3_0();

        for ($i = 1; $i < 64; $i += 2) {
            $t = self::ge_select((int) floor($i / 2), $e[$i]);
            $r = self::ge_madd($r, $h, $t);
            $h = self::ge_p1p1_to_p3($r);
        }

        $r = self::ge_p3_dbl($h);

        $s = self::ge_p1p1_to_p2($r);
        $r = self::ge_p2_dbl($s);
        $s = self::ge_p1p1_to_p2($r);
        $r = self::ge_p2_dbl($s);
        $s = self::ge_p1p1_to_p2($r);
        $r = self::ge_p2_dbl($s);

        $h = self::ge_p1p1_to_p3($r);

        for ($i = 0; $i < 64; $i += 2) {
            $t = self::ge_select($i >> 1, $e[$i]);
            $r = self::ge_madd($r, $h, $t);
            $h = self::ge_p1p1_to_p3($r);
        }
        return $h;
    }

    /**
     * Calculates (ab + c) mod l
     * where l = 2^252 + 27742317777372353535851937790883648493
     *
     * @internal You should not use this directly from another application
     *
     * @param string $a
     * @param string $b
     * @param string $c
     * @return string
     */
    public static function sc_muladd($a, $b, $c)
    {
        $a0 = 2097151 & self::load_3(self::substr($a, 0, 3));
        $a1 = 2097151 & (self::load_4(self::substr($a, 2, 4)) >> 5);
        $a2 = 2097151 & (self::load_3(self::substr($a, 5, 3)) >> 2);
        $a3 = 2097151 & (self::load_4(self::substr($a, 7, 4)) >> 7);
        $a4 = 2097151 & (self::load_4(self::substr($a, 10, 4)) >> 4);
        $a5 = 2097151 & (self::load_3(self::substr($a, 13, 3)) >> 1);
        $a6 = 2097151 & (self::load_4(self::substr($a, 15, 4)) >> 6);
        $a7 = 2097151 & (self::load_3(self::substr($a, 18, 3)) >> 3);
        $a8 = 2097151 & self::load_3(self::substr($a, 21, 3));
        $a9 = 2097151 & (self::load_4(self::substr($a, 23, 4)) >> 5);
        $a10 = 2097151 & (self::load_3(self::substr($a, 26, 3)) >> 2);
        $a11 = (self::load_4(self::substr($a, 28, 4)) >> 7);
        $b0 = 2097151 & self::load_3(self::substr($b, 0, 3));
        $b1 = 2097151 & (self::load_4(self::substr($b, 2, 4)) >> 5);
        $b2 = 2097151 & (self::load_3(self::substr($b, 5, 3)) >> 2);
        $b3 = 2097151 & (self::load_4(self::substr($b, 7, 4)) >> 7);
        $b4 = 2097151 & (self::load_4(self::substr($b, 10, 4)) >> 4);
        $b5 = 2097151 & (self::load_3(self::substr($b, 13, 3)) >> 1);
        $b6 = 2097151 & (self::load_4(self::substr($b, 15, 4)) >> 6);
        $b7 = 2097151 & (self::load_3(self::substr($b, 18, 3)) >> 3);
        $b8 = 2097151 & self::load_3(self::substr($b, 21, 3));
        $b9 = 2097151 & (self::load_4(self::substr($b, 23, 4)) >> 5);
        $b10 = 2097151 & (self::load_3(self::substr($b, 26, 3)) >> 2);
        $b11 = (self::load_4(self::substr($b, 28, 4)) >> 7);
        $c0 = 2097151 & self::load_3(self::substr($c, 0, 3));
        $c1 = 2097151 & (self::load_4(self::substr($c, 2, 4)) >> 5);
        $c2 = 2097151 & (self::load_3(self::substr($c, 5, 3)) >> 2);
        $c3 = 2097151 & (self::load_4(self::substr($c, 7, 4)) >> 7);
        $c4 = 2097151 & (self::load_4(self::substr($c, 10, 4)) >> 4);
        $c5 = 2097151 & (self::load_3(self::substr($c, 13, 3)) >> 1);
        $c6 = 2097151 & (self::load_4(self::substr($c, 15, 4)) >> 6);
        $c7 = 2097151 & (self::load_3(self::substr($c, 18, 3)) >> 3);
        $c8 = 2097151 & self::load_3(self::substr($c, 21, 3));
        $c9 = 2097151 & (self::load_4(self::substr($c, 23, 4)) >> 5);
        $c10 = 2097151 & (self::load_3(self::substr($c, 26, 3)) >> 2);
        $c11 = (self::load_4(self::substr($c, 28, 4)) >> 7);

        /* Can't really avoid the pyramid here: */
        $s0 = $c0 + self::mul($a0, $b0);
        $s1 = $c1 + self::mul($a0, $b1) + self::mul($a1, $b0);
        $s2 = $c2 + self::mul($a0, $b2) + self::mul($a1, $b1) + self::mul($a2, $b0);
        $s3 = $c3 + self::mul($a0, $b3) + self::mul($a1, $b2) + self::mul($a2, $b1) + self::mul($a3, $b0);
        $s4 = $c4 + self::mul($a0, $b4) + self::mul($a1, $b3) + self::mul($a2, $b2) + self::mul($a3, $b1) + self::mul($a4, $b0);
        $s5 = $c5 + self::mul($a0, $b5) + self::mul($a1, $b4) + self::mul($a2, $b3) + self::mul($a3, $b2) + self::mul($a4, $b1) + self::mul($a5, $b0);
        $s6 = $c6 + self::mul($a0, $b6) + self::mul($a1, $b5) + self::mul($a2, $b4) + self::mul($a3, $b3) + self::mul($a4, $b2) + self::mul($a5, $b1) + self::mul($a6, $b0);
        $s7 = $c7 + self::mul($a0, $b7) + self::mul($a1, $b6) + self::mul($a2, $b5) + self::mul($a3, $b4) + self::mul($a4, $b3) + self::mul($a5, $b2) + self::mul($a6, $b1) + self::mul($a7, $b0);
        $s8 = $c8 + self::mul($a0, $b8) + self::mul($a1, $b7) + self::mul($a2, $b6) + self::mul($a3, $b5) + self::mul($a4, $b4) + self::mul($a5, $b3) + self::mul($a6, $b2) + self::mul($a7, $b1) + self::mul($a8, $b0);
        $s9 = $c9 + self::mul($a0, $b9) + self::mul($a1, $b8) + self::mul($a2, $b7) + self::mul($a3, $b6) + self::mul($a4, $b5) + self::mul($a5, $b4) + self::mul($a6, $b3) + self::mul($a7, $b2) + self::mul($a8, $b1) + self::mul($a9, $b0);
        $s10 = $c10 + self::mul($a0, $b10) + self::mul($a1, $b9) + self::mul($a2, $b8) + self::mul($a3, $b7) + self::mul($a4, $b6) + self::mul($a5, $b5) + self::mul($a6, $b4) + self::mul($a7, $b3) + self::mul($a8, $b2) + self::mul($a9, $b1) + self::mul($a10, $b0);
        $s11 = $c11 + self::mul($a0, $b11) + self::mul($a1, $b10) + self::mul($a2, $b9) + self::mul($a3, $b8) + self::mul($a4, $b7) + self::mul($a5, $b6) + self::mul($a6, $b5) + self::mul($a7, $b4) + self::mul($a8, $b3) + self::mul($a9, $b2) + self::mul($a10, $b1) + self::mul($a11, $b0);
        $s12 = self::mul($a1, $b11) + self::mul($a2, $b10) + self::mul($a3, $b9) + self::mul($a4, $b8) + self::mul($a5, $b7) + self::mul($a6, $b6) + self::mul($a7, $b5) + self::mul($a8, $b4) + self::mul($a9, $b3) + self::mul($a10, $b2) + self::mul($a11, $b1);
        $s13 = self::mul($a2, $b11) + self::mul($a3, $b10) + self::mul($a4, $b9) + self::mul($a5, $b8) + self::mul($a6, $b7) + self::mul($a7, $b6) + self::mul($a8, $b5) + self::mul($a9, $b4) + self::mul($a10, $b3) + self::mul($a11, $b2);
        $s14 = self::mul($a3, $b11) + self::mul($a4, $b10) + self::mul($a5, $b9) + self::mul($a6, $b8) + self::mul($a7, $b7) + self::mul($a8, $b6) + self::mul($a9, $b5) + self::mul($a10, $b4) + self::mul($a11, $b3);
        $s15 = self::mul($a4, $b11) + self::mul($a5, $b10) + self::mul($a6, $b9) + self::mul($a7, $b8) + self::mul($a8, $b7) + self::mul($a9, $b6) + self::mul($a10, $b5) + self::mul($a11, $b4);
        $s16 = self::mul($a5, $b11) + self::mul($a6, $b10) + self::mul($a7, $b9) + self::mul($a8, $b8) + self::mul($a9, $b7) + self::mul($a10, $b6) + self::mul($a11, $b5);
        $s17 = self::mul($a6, $b11) + self::mul($a7, $b10) + self::mul($a8, $b9) + self::mul($a9, $b8) + self::mul($a10, $b7) + self::mul($a11, $b6);
        $s18 = self::mul($a7, $b11) + self::mul($a8, $b10) + self::mul($a9, $b9) + self::mul($a10, $b8) + self::mul($a11, $b7);
        $s19 = self::mul($a8, $b11) + self::mul($a9, $b10) + self::mul($a10, $b9) + self::mul($a11, $b8);
        $s20 = self::mul($a9, $b11) + self::mul($a10, $b10) + self::mul($a11, $b9);
        $s21 = self::mul($a10, $b11) + self::mul($a11, $b10);
        $s22 = self::mul($a11, $b11);
        $s23 = 0;

        $carry0 = ($s0 + (1 << 20)) >> 21;
        $s1 += $carry0;
        $s0 -= self::mul($carry0, 1 << 21);
        $carry2 = ($s2 + (1 << 20)) >> 21;
        $s3 += $carry2;
        $s2 -= self::mul($carry2, 1 << 21);
        $carry4 = ($s4 + (1 << 20)) >> 21;
        $s5 += $carry4;
        $s4 -= self::mul($carry4, 1 << 21);
        $carry6 = ($s6 + (1 << 20)) >> 21;
        $s7 += $carry6;
        $s6 -= self::mul($carry6, 1 << 21);
        $carry8 = ($s8 + (1 << 20)) >> 21;
        $s9 += $carry8;
        $s8 -= self::mul($carry8, 1 << 21);
        $carry10 = ($s10 + (1 << 20)) >> 21;
        $s11 += $carry10;
        $s10 -= self::mul($carry10, 1 << 21);
        $carry12 = ($s12 + (1 << 20)) >> 21;
        $s13 += $carry12;
        $s12 -= self::mul($carry12, 1 << 21);
        $carry14 = ($s14 + (1 << 20)) >> 21;
        $s15 += $carry14;
        $s14 -= self::mul($carry14, 1 << 21);
        $carry16 = ($s16 + (1 << 20)) >> 21;
        $s17 += $carry16;
        $s16 -= self::mul($carry16, 1 << 21);
        $carry18 = ($s18 + (1 << 20)) >> 21;
        $s19 += $carry18;
        $s18 -= self::mul($carry18, 1 << 21);
        $carry20 = ($s20 + (1 << 20)) >> 21;
        $s21 += $carry20;
        $s20 -= self::mul($carry20, 1 << 21);
        $carry22 = ($s22 + (1 << 20)) >> 21;
        $s23 += $carry22;
        $s22 -= self::mul($carry22, 1 << 21);

        $carry1 = ($s1 + (1 << 20)) >> 21;
        $s2 += $carry1;
        $s1 -= self::mul($carry1, 1 << 21);
        $carry3 = ($s3 + (1 << 20)) >> 21;
        $s4 += $carry3;
        $s3 -= self::mul($carry3, 1 << 21);
        $carry5 = ($s5 + (1 << 20)) >> 21;
        $s6 += $carry5;
        $s5 -= self::mul($carry5, 1 << 21);
        $carry7 = ($s7 + (1 << 20)) >> 21;
        $s8 += $carry7;
        $s7 -= self::mul($carry7, 1 << 21);
        $carry9 = ($s9 + (1 << 20)) >> 21;
        $s10 += $carry9;
        $s9 -= self::mul($carry9, 1 << 21);
        $carry11 = ($s11 + (1 << 20)) >> 21;
        $s12 += $carry11;
        $s11 -= self::mul($carry11, 1 << 21);
        $carry13 = ($s13 + (1 << 20)) >> 21;
        $s14 += $carry13;
        $s13 -= self::mul($carry13, 1 << 21);
        $carry15 = ($s15 + (1 << 20)) >> 21;
        $s16 += $carry15;
        $s15 -= self::mul($carry15, 1 << 21);
        $carry17 = ($s17 + (1 << 20)) >> 21;
        $s18 += $carry17;
        $s17 -= self::mul($carry17, 1 << 21);
        $carry19 = ($s19 + (1 << 20)) >> 21;
        $s20 += $carry19;
        $s19 -= self::mul($carry19, 1 << 21);
        $carry21 = ($s21 + (1 << 20)) >> 21;
        $s22 += $carry21;
        $s21 -= self::mul($carry21, 1 << 21);

        $s11 += self::mul($s23, 666643);
        $s12 += self::mul($s23, 470296);
        $s13 += self::mul($s23, 654183);
        $s14 -= self::mul($s23, 997805);
        $s15 += self::mul($s23, 136657);
        $s16 -= self::mul($s23, 683901);

        $s10 += self::mul($s22, 666643);
        $s11 += self::mul($s22, 470296);
        $s12 += self::mul($s22, 654183);
        $s13 -= self::mul($s22, 997805);
        $s14 += self::mul($s22, 136657);
        $s15 -= self::mul($s22, 683901);

        $s9 += self::mul($s21,  666643);
        $s10 += self::mul($s21,  470296);
        $s11 += self::mul($s21,  654183);
        $s12 -= self::mul($s21,  997805);
        $s13 += self::mul($s21,  136657);
        $s14 -= self::mul($s21,  683901);

        $s8 += self::mul($s20,  666643);
        $s9 += self::mul($s20,  470296);
        $s10 += self::mul($s20,  654183);
        $s11 -= self::mul($s20,  997805);
        $s12 += self::mul($s20,  136657);
        $s13 -= self::mul($s20,  683901);

        $s7 += self::mul($s19,  666643);
        $s8 += self::mul($s19,  470296);
        $s9 += self::mul($s19,  654183);
        $s10 -= self::mul($s19,  997805);
        $s11 += self::mul($s19,  136657);
        $s12 -= self::mul($s19,  683901);

        $s6 += self::mul($s18,  666643);
        $s7 += self::mul($s18,  470296);
        $s8 += self::mul($s18,  654183);
        $s9 -= self::mul($s18,  997805);
        $s10 += self::mul($s18,  136657);
        $s11 -= self::mul($s18,  683901);

        $carry6 = ($s6 + (1 << 20)) >> 21;
        $s7 += $carry6;
        $s6 -= self::mul($carry6, 1 << 21);
        $carry8 = ($s8 + (1 << 20)) >> 21;
        $s9 += $carry8;
        $s8 -= self::mul($carry8, 1 << 21);
        $carry10 = ($s10 + (1 << 20)) >> 21;
        $s11 += $carry10;
        $s10 -= self::mul($carry10, 1 << 21);
        $carry12 = ($s12 + (1 << 20)) >> 21;
        $s13 += $carry12;
        $s12 -= self::mul($carry12, 1 << 21);
        $carry14 = ($s14 + (1 << 20)) >> 21;
        $s15 += $carry14;
        $s14 -= self::mul($carry14, 1 << 21);
        $carry16 = ($s16 + (1 << 20)) >> 21;
        $s17 += $carry16;
        $s16 -= self::mul($carry16, 1 << 21);

        $carry7 = ($s7 + (1 << 20)) >> 21;
        $s8 += $carry7;
        $s7 -= self::mul($carry7, 1 << 21);
        $carry9 = ($s9 + (1 << 20)) >> 21;
        $s10 += $carry9;
        $s9 -= self::mul($carry9, 1 << 21);
        $carry11 = ($s11 + (1 << 20)) >> 21;
        $s12 += $carry11;
        $s11 -= self::mul($carry11, 1 << 21);
        $carry13 = ($s13 + (1 << 20)) >> 21;
        $s14 += $carry13;
        $s13 -= self::mul($carry13, 1 << 21);
        $carry15 = ($s15 + (1 << 20)) >> 21;
        $s16 += $carry15;
        $s15 -= self::mul($carry15, 1 << 21);

        $s5 += self::mul($s17,  666643);
        $s6 += self::mul($s17,  470296);
        $s7 += self::mul($s17,  654183);
        $s8 -= self::mul($s17,  997805);
        $s9 += self::mul($s17,  136657);
        $s10 -= self::mul($s17,  683901);

        $s4 += self::mul($s16,  666643);
        $s5 += self::mul($s16,  470296);
        $s6 += self::mul($s16,  654183);
        $s7 -= self::mul($s16,  997805);
        $s8 += self::mul($s16,  136657);
        $s9 -= self::mul($s16,  683901);

        $s3 += self::mul($s15,  666643);
        $s4 += self::mul($s15,  470296);
        $s5 += self::mul($s15,  654183);
        $s6 -= self::mul($s15,  997805);
        $s7 += self::mul($s15,  136657);
        $s8 -= self::mul($s15,  683901);

        $s2 += self::mul($s14,  666643);
        $s3 += self::mul($s14,  470296);
        $s4 += self::mul($s14,  654183);
        $s5 -= self::mul($s14,  997805);
        $s6 += self::mul($s14,  136657);
        $s7 -= self::mul($s14,  683901);

        $s1 += self::mul($s13,  666643);
        $s2 += self::mul($s13,  470296);
        $s3 += self::mul($s13,  654183);
        $s4 -= self::mul($s13,  997805);
        $s5 += self::mul($s13,  136657);
        $s6 -= self::mul($s13,  683901);

        $s0 += self::mul($s12,  666643);
        $s1 += self::mul($s12,  470296);
        $s2 += self::mul($s12,  654183);
        $s3 -= self::mul($s12,  997805);
        $s4 += self::mul($s12,  136657);
        $s5 -= self::mul($s12,  683901);
        $s12 = 0;

        $carry0 = ($s0 + (1 << 20)) >> 21;
        $s1 += $carry0;
        $s0 -= self::mul($carry0,  1 << 21);
        $carry2 = ($s2 + (1 << 20)) >> 21;
        $s3 += $carry2;
        $s2 -= self::mul($carry2,  1 << 21);
        $carry4 = ($s4 + (1 << 20)) >> 21;
        $s5 += $carry4;
        $s4 -= self::mul($carry4,  1 << 21);
        $carry6 = ($s6 + (1 << 20)) >> 21;
        $s7 += $carry6;
        $s6 -= self::mul($carry6,  1 << 21);
        $carry8 = ($s8 + (1 << 20)) >> 21;
        $s9 += $carry8;
        $s8 -= self::mul($carry8,  1 << 21);
        $carry10 = ($s10 + (1 << 20)) >> 21;
        $s11 += $carry10;
        $s10 -= self::mul($carry10,  1 << 21);

        $carry1 = ($s1 + (1 << 20)) >> 21;
        $s2 += $carry1;
        $s1 -= self::mul($carry1,  1 << 21);
        $carry3 = ($s3 + (1 << 20)) >> 21;
        $s4 += $carry3;
        $s3 -= self::mul($carry3,  1 << 21);
        $carry5 = ($s5 + (1 << 20)) >> 21;
        $s6 += $carry5;
        $s5 -= self::mul($carry5,  1 << 21);
        $carry7 = ($s7 + (1 << 20)) >> 21;
        $s8 += $carry7;
        $s7 -= self::mul($carry7,  1 << 21);
        $carry9 = ($s9 + (1 << 20)) >> 21;
        $s10 += $carry9;
        $s9 -= self::mul($carry9,  1 << 21);
        $carry11 = ($s11 + (1 << 20)) >> 21;
        $s12 += $carry11;
        $s11 -= self::mul($carry11,  1 << 21);

        $s0 += self::mul($s12,  666643);
        $s1 += self::mul($s12,  470296);
        $s2 += self::mul($s12,  654183);
        $s3 -= self::mul($s12,  997805);
        $s4 += self::mul($s12,  136657);
        $s5 -= self::mul($s12,  683901);
        $s12 = 0;

        $carry0 = $s0 >> 21;
        $s1 += $carry0;
        $s0 -= self::mul($carry0,  1 << 21);
        $carry1 = $s1 >> 21;
        $s2 += $carry1;
        $s1 -= self::mul($carry1,  1 << 21);
        $carry2 = $s2 >> 21;
        $s3 += $carry2;
        $s2 -= self::mul($carry2,  1 << 21);
        $carry3 = $s3 >> 21;
        $s4 += $carry3;
        $s3 -= self::mul($carry3,  1 << 21);
        $carry4 = $s4 >> 21;
        $s5 += $carry4;
        $s4 -= self::mul($carry4,  1 << 21);
        $carry5 = $s5 >> 21;
        $s6 += $carry5;
        $s5 -= self::mul($carry5,  1 << 21);
        $carry6 = $s6 >> 21;
        $s7 += $carry6;
        $s6 -= self::mul($carry6,  1 << 21);
        $carry7 = $s7 >> 21;
        $s8 += $carry7;
        $s7 -= self::mul($carry7,  1 << 21);
        $carry8 = $s8 >> 21;
        $s9 += $carry8;
        $s8 -= self::mul($carry8,  1 << 21);
        $carry9 = $s9 >> 21;
        $s10 += $carry9;
        $s9 -= self::mul($carry9,  1 << 21);
        $carry10 = $s10 >> 21;
        $s11 += $carry10;
        $s10 -= self::mul($carry10,  1 << 21);
        $carry11 = $s11 >> 21;
        $s12 += $carry11;
        $s11 -= self::mul($carry11,  1 << 21);


        $s0 += self::mul($s12,  666643);
        $s1 += self::mul($s12,  470296);
        $s2 += self::mul($s12,  654183);
        $s3 -= self::mul($s12,  997805);
        $s4 += self::mul($s12,  136657);
        $s5 -= self::mul($s12,  683901);

        $carry0 = $s0 >> 21;
        $s1 += $carry0;
        $s0 -= self::mul($carry0,  1 << 21);
        $carry1 = $s1 >> 21;
        $s2 += $carry1;
        $s1 -= self::mul($carry1,  1 << 21);
        $carry2 = $s2 >> 21;
        $s3 += $carry2;
        $s2 -= self::mul($carry2,  1 << 21);
        $carry3 = $s3 >> 21;
        $s4 += $carry3;
        $s3 -= self::mul($carry3,  1 << 21);
        $carry4 = $s4 >> 21;
        $s5 += $carry4;
        $s4 -= self::mul($carry4,  1 << 21);
        $carry5 = $s5 >> 21;
        $s6 += $carry5;
        $s5 -= self::mul($carry5,  1 << 21);
        $carry6 = $s6 >> 21;
        $s7 += $carry6;
        $s6 -= self::mul($carry6,  1 << 21);
        $carry7 = $s7 >> 21;
        $s8 += $carry7;
        $s7 -= self::mul($carry7,  1 << 21);
        $carry8 = $s8 >> 21;
        $s9 += $carry8;
        $s8 -= self::mul($carry8,  1 << 21);
        $carry9 = $s9 >> 21;
        $s10 += $carry9;
        $s9 -= self::mul($carry9,  1 << 21);
        $carry10 = $s10 >> 21;
        $s11 += $carry10;
        $s10 -= self::mul($carry10,  1 << 21);


        /**
         * @var array<int, int>
         */
        $arr = array(
            (int) (0xff & ($s0 >> 0)),
            (int) (0xff & ($s0 >> 8)),
            (int) (0xff & (($s0 >> 16) | self::mul($s1, 1 << 5))),
            (int) (0xff & ($s1 >> 3)),
            (int) (0xff & ($s1 >> 11)),
            (int) (0xff & (($s1 >> 19) | self::mul($s2, 1 << 2))),
            (int) (0xff & ($s2 >> 6)),
            (int) (0xff & (($s2 >> 14) | self::mul($s3, 1 << 7))),
            (int) (0xff & ($s3 >> 1)),
            (int) (0xff & ($s3 >> 9)),
            (int) (0xff & (($s3 >> 17) | self::mul($s4, 1 << 4))),
            (int) (0xff & ($s4 >> 4)),
            (int) (0xff & ($s4 >> 12)),
            (int) (0xff & (($s4 >> 20) | self::mul($s5, 1 << 1))),
            (int) (0xff & ($s5 >> 7)),
            (int) (0xff & (($s5 >> 15) | self::mul($s6, 1 << 6))),
            (int) (0xff & ($s6 >> 2)),
            (int) (0xff & ($s6 >> 10)),
            (int) (0xff & (($s6 >> 18) | self::mul($s7, 1 << 3))),
            (int) (0xff & ($s7 >> 5)),
            (int) (0xff & ($s7 >> 13)),
            (int) (0xff & ($s8 >> 0)),
            (int) (0xff & ($s8 >> 8)),
            (int) (0xff & (($s8 >> 16) | self::mul($s9, 1 << 5))),
            (int) (0xff & ($s9 >> 3)),
            (int) (0xff & ($s9 >> 11)),
            (int) (0xff & (($s9 >> 19) | self::mul($s10, 1 << 2))),
            (int) (0xff & ($s10 >> 6)),
            (int) (0xff & (($s10 >> 14) | self::mul($s11, 1 << 7))),
            (int) (0xff & ($s11 >> 1)),
            (int) (0xff & ($s11 >> 9)),
            0xff & ($s11 >> 17)
        );
        return self::intArrayToString($arr);
    }

    /**
     * @internal You should not use this directly from another application
     *
     * @param string $s
     * @return string
     */
    public static function sc_reduce($s)
    {
        $s0 = 2097151 & self::load_3(self::substr($s, 0, 3));
        $s1 = 2097151 & (self::load_4(self::substr($s, 2, 4)) >> 5);
        $s2 = 2097151 & (self::load_3(self::substr($s, 5, 3)) >> 2);
        $s3 = 2097151 & (self::load_4(self::substr($s, 7, 4)) >> 7);
        $s4 = 2097151 & (self::load_4(self::substr($s, 10, 4)) >> 4);
        $s5 = 2097151 & (self::load_3(self::substr($s, 13, 3)) >> 1);
        $s6 = 2097151 & (self::load_4(self::substr($s, 15, 4)) >> 6);
        $s7 = 2097151 & (self::load_3(self::substr($s, 18, 4)) >> 3);
        $s8 = 2097151 & self::load_3(self::substr($s, 21, 3));
        $s9 = 2097151 & (self::load_4(self::substr($s, 23, 4)) >> 5);
        $s10 = 2097151 & (self::load_3(self::substr($s, 26, 3)) >> 2);
        $s11 = 2097151 & (self::load_4(self::substr($s, 28, 4)) >> 7);
        $s12 = 2097151 & (self::load_4(self::substr($s, 31, 4)) >> 4);
        $s13 = 2097151 & (self::load_3(self::substr($s, 34, 3)) >> 1);
        $s14 = 2097151 & (self::load_4(self::substr($s, 36, 4)) >> 6);
        $s15 = 2097151 & (self::load_3(self::substr($s, 39, 4)) >> 3);
        $s16 = 2097151 & self::load_3(self::substr($s, 42, 3));
        $s17 = 2097151 & (self::load_4(self::substr($s, 44, 4)) >> 5);
        $s18 = 2097151 & (self::load_3(self::substr($s, 47, 3)) >> 2);
        $s19 = 2097151 & (self::load_4(self::substr($s, 49, 4)) >> 7);
        $s20 = 2097151 & (self::load_4(self::substr($s, 52, 4)) >> 4);
        $s21 = 2097151 & (self::load_3(self::substr($s, 55, 3)) >> 1);
        $s22 = 2097151 & (self::load_4(self::substr($s, 57, 4)) >> 6);
        $s23 = (self::load_4(self::substr($s, 60, 4)) >> 3);

        $s11 += self::mul($s23,  666643);
        $s12 += self::mul($s23,  470296);
        $s13 += self::mul($s23,  654183);
        $s14 -= self::mul($s23,  997805);
        $s15 += self::mul($s23,  136657);
        $s16 -= self::mul($s23,  683901);

        $s10 += self::mul($s22,  666643);
        $s11 += self::mul($s22,  470296);
        $s12 += self::mul($s22,  654183);
        $s13 -= self::mul($s22,  997805);
        $s14 += self::mul($s22,  136657);
        $s15 -= self::mul($s22,  683901);

        $s9 += self::mul($s21,  666643);
        $s10 += self::mul($s21,  470296);
        $s11 += self::mul($s21,  654183);
        $s12 -= self::mul($s21,  997805);
        $s13 += self::mul($s21,  136657);
        $s14 -= self::mul($s21,  683901);

        $s8 += self::mul($s20,  666643);
        $s9 += self::mul($s20,  470296);
        $s10 += self::mul($s20,  654183);
        $s11 -= self::mul($s20,  997805);
        $s12 += self::mul($s20,  136657);
        $s13 -= self::mul($s20,  683901);

        $s7 += self::mul($s19,  666643);
        $s8 += self::mul($s19,  470296);
        $s9 += self::mul($s19,  654183);
        $s10 -= self::mul($s19,  997805);
        $s11 += self::mul($s19,  136657);
        $s12 -= self::mul($s19,  683901);

        $s6 += self::mul($s18,  666643);
        $s7 += self::mul($s18,  470296);
        $s8 += self::mul($s18,  654183);
        $s9 -= self::mul($s18,  997805);
        $s10 += self::mul($s18,  136657);
        $s11 -= self::mul($s18,  683901);

        $carry6 = ($s6 + (1 << 20)) >> 21;
        $s7 += $carry6;
        $s6 -= self::mul($carry6,  1 << 21);
        $carry8 = ($s8 + (1 << 20)) >> 21;
        $s9 += $carry8;
        $s8 -= self::mul($carry8,  1 << 21);
        $carry10 = ($s10 + (1 << 20)) >> 21;
        $s11 += $carry10;
        $s10 -= self::mul($carry10,  1 << 21);
        $carry12 = ($s12 + (1 << 20)) >> 21;
        $s13 += $carry12;
        $s12 -= self::mul($carry12,  1 << 21);
        $carry14 = ($s14 + (1 << 20)) >> 21;
        $s15 += $carry14;
        $s14 -= self::mul($carry14,  1 << 21);
        $carry16 = ($s16 + (1 << 20)) >> 21;
        $s17 += $carry16;
        $s16 -= self::mul($carry16,  1 << 21);

        $carry7 = ($s7 + (1 << 20)) >> 21;
        $s8 += $carry7;
        $s7 -= self::mul($carry7,  1 << 21);
        $carry9 = ($s9 + (1 << 20)) >> 21;
        $s10 += $carry9;
        $s9 -= self::mul($carry9,  1 << 21);
        $carry11 = ($s11 + (1 << 20)) >> 21;
        $s12 += $carry11;
        $s11 -= self::mul($carry11,  1 << 21);
        $carry13 = ($s13 + (1 << 20)) >> 21;
        $s14 += $carry13;
        $s13 -= self::mul($carry13,  1 << 21);
        $carry15 = ($s15 + (1 << 20)) >> 21;
        $s16 += $carry15;
        $s15 -= self::mul($carry15,  1 << 21);

        $s5 += self::mul($s17,  666643);
        $s6 += self::mul($s17,  470296);
        $s7 += self::mul($s17,  654183);
        $s8 -= self::mul($s17,  997805);
        $s9 += self::mul($s17,  136657);
        $s10 -= self::mul($s17,  683901);

        $s4 += self::mul($s16,  666643);
        $s5 += self::mul($s16,  470296);
        $s6 += self::mul($s16,  654183);
        $s7 -= self::mul($s16,  997805);
        $s8 += self::mul($s16,  136657);
        $s9 -= self::mul($s16,  683901);

        $s3 += self::mul($s15,  666643);
        $s4 += self::mul($s15,  470296);
        $s5 += self::mul($s15,  654183);
        $s6 -= self::mul($s15,  997805);
        $s7 += self::mul($s15,  136657);
        $s8 -= self::mul($s15,  683901);

        $s2 += self::mul($s14,  666643);
        $s3 += self::mul($s14,  470296);
        $s4 += self::mul($s14,  654183);
        $s5 -= self::mul($s14,  997805);
        $s6 += self::mul($s14,  136657);
        $s7 -= self::mul($s14,  683901);

        $s1 += self::mul($s13,  666643);
        $s2 += self::mul($s13,  470296);
        $s3 += self::mul($s13,  654183);
        $s4 -= self::mul($s13,  997805);
        $s5 += self::mul($s13,  136657);
        $s6 -= self::mul($s13,  683901);

        $s0 += self::mul($s12,  666643);
        $s1 += self::mul($s12,  470296);
        $s2 += self::mul($s12,  654183);
        $s3 -= self::mul($s12,  997805);
        $s4 += self::mul($s12,  136657);
        $s5 -= self::mul($s12,  683901);
        $s12 = 0;

        $carry0 = ($s0 + (1 << 20)) >> 21;
        $s1 += $carry0;
        $s0 -= self::mul($carry0,  1 << 21);
        $carry2 = ($s2 + (1 << 20)) >> 21;
        $s3 += $carry2;
        $s2 -= self::mul($carry2,  1 << 21);
        $carry4 = ($s4 + (1 << 20)) >> 21;
        $s5 += $carry4;
        $s4 -= self::mul($carry4,  1 << 21);
        $carry6 = ($s6 + (1 << 20)) >> 21;
        $s7 += $carry6;
        $s6 -= self::mul($carry6,  1 << 21);
        $carry8 = ($s8 + (1 << 20)) >> 21;
        $s9 += $carry8;
        $s8 -= self::mul($carry8,  1 << 21);
        $carry10 = ($s10 + (1 << 20)) >> 21;
        $s11 += $carry10;
        $s10 -= self::mul($carry10,  1 << 21);

        $carry1 = ($s1 + (1 << 20)) >> 21;
        $s2 += $carry1;
        $s1 -= self::mul($carry1,  1 << 21);
        $carry3 = ($s3 + (1 << 20)) >> 21;
        $s4 += $carry3;
        $s3 -= self::mul($carry3,  1 << 21);
        $carry5 = ($s5 + (1 << 20)) >> 21;
        $s6 += $carry5;
        $s5 -= self::mul($carry5,  1 << 21);
        $carry7 = ($s7 + (1 << 20)) >> 21;
        $s8 += $carry7;
        $s7 -= self::mul($carry7,  1 << 21);
        $carry9 = ($s9 + (1 << 20)) >> 21;
        $s10 += $carry9;
        $s9 -= self::mul($carry9,  1 << 21);
        $carry11 = ($s11 + (1 << 20)) >> 21;
        $s12 += $carry11;
        $s11 -= self::mul($carry11,  1 << 21);

        $s0 += self::mul($s12,  666643);
        $s1 += self::mul($s12,  470296);
        $s2 += self::mul($s12,  654183);
        $s3 -= self::mul($s12,  997805);
        $s4 += self::mul($s12,  136657);
        $s5 -= self::mul($s12,  683901);
        $s12 = 0;

        $carry0 = $s0 >> 21;
        $s1 += $carry0;
        $s0 -= $carry0 << 21;
        $carry1 = $s1 >> 21;
        $s2 += $carry1;
        $s1 -= $carry1 << 21;
        $carry2 = $s2 >> 21;
        $s3 += $carry2;
        $s2 -= $carry2 << 21;
        $carry3 = $s3 >> 21;
        $s4 += $carry3;
        $s3 -= $carry3 << 21;
        $carry4 = $s4 >> 21;
        $s5 += $carry4;
        $s4 -= $carry4 << 21;
        $carry5 = $s5 >> 21;
        $s6 += $carry5;
        $s5 -= $carry5 << 21;
        $carry6 = $s6 >> 21;
        $s7 += $carry6;
        $s6 -= $carry6 << 21;
        $carry7 = $s7 >> 21;
        $s8 += $carry7;
        $s7 -= $carry7 << 21;
        $carry8 = $s8 >> 21;
        $s9 += $carry8;
        $s8 -= $carry8 << 21;
        $carry9 = $s9 >> 21;
        $s10 += $carry9;
        $s9 -= $carry9 << 21;
        $carry10 = $s10 >> 21;
        $s11 += $carry10;
        $s10 -= $carry10 << 21;
        $carry11 = $s11 >> 21;
        $s12 += $carry11;
        $s11 -= $carry11 << 21;

        $s0 += self::mul($s12,  666643);
        $s1 += self::mul($s12,  470296);
        $s2 += self::mul($s12,  654183);
        $s3 -= self::mul($s12,  997805);
        $s4 += self::mul($s12,  136657);
        $s5 -= self::mul($s12,  683901);

        $carry0 = $s0 >> 21;
        $s1 += $carry0;
        $s0 -= $carry0 << 21;
        $carry1 = $s1 >> 21;
        $s2 += $carry1;
        $s1 -= $carry1 << 21;
        $carry2 = $s2 >> 21;
        $s3 += $carry2;
        $s2 -= $carry2 << 21;
        $carry3 = $s3 >> 21;
        $s4 += $carry3;
        $s3 -= $carry3 << 21;
        $carry4 = $s4 >> 21;
        $s5 += $carry4;
        $s4 -= $carry4 << 21;
        $carry5 = $s5 >> 21;
        $s6 += $carry5;
        $s5 -= $carry5 << 21;
        $carry6 = $s6 >> 21;
        $s7 += $carry6;
        $s6 -= $carry6 << 21;
        $carry7 = $s7 >> 21;
        $s8 += $carry7;
        $s7 -= $carry7 << 21;
        $carry8 = $s8 >> 21;
        $s9 += $carry8;
        $s8 -= $carry8 << 21;
        $carry9 = $s9 >> 21;
        $s10 += $carry9;
        $s9 -= $carry9 << 21;
        $carry10 = $s10 >> 21;
        $s11 += $carry10;
        $s10 -= $carry10 << 21;

        /**
         * @var array<int, int>
         */
        $arr = array(
            (int) ($s0 >> 0),
            (int) ($s0 >> 8),
            (int) (($s0 >> 16) | self::mul($s1, 1 << 5)),
            (int) ($s1 >> 3),
            (int) ($s1 >> 11),
            (int) (($s1 >> 19) | self::mul($s2, 1 << 2)),
            (int) ($s2 >> 6),
            (int) (($s2 >> 14) | self::mul($s3, 1 << 7)),
            (int) ($s3 >> 1),
            (int) ($s3 >> 9),
            (int) (($s3 >> 17) | self::mul($s4, 1 << 4)),
            (int) ($s4 >> 4),
            (int) ($s4 >> 12),
            (int) (($s4 >> 20) | self::mul($s5, 1 << 1)),
            (int) ($s5 >> 7),
            (int) (($s5 >> 15) | self::mul($s6, 1 << 6)),
            (int) ($s6 >> 2),
            (int) ($s6 >> 10),
            (int) (($s6 >> 18) | self::mul($s7, 1 << 3)),
            (int) ($s7 >> 5),
            (int) ($s7 >> 13),
            (int) ($s8 >> 0),
            (int) ($s8 >> 8),
            (int) (($s8 >> 16) | self::mul($s9, 1 << 5)),
            (int) ($s9 >> 3),
            (int) ($s9 >> 11),
            (int) (($s9 >> 19) | self::mul($s10, 1 << 2)),
            (int) ($s10 >> 6),
            (int) (($s10 >> 14) | self::mul($s11, 1 << 7)),
            (int) ($s11 >> 1),
            (int) ($s11 >> 9),
            (int) $s11 >> 17
        );
        return self::intArrayToString($arr);
    }
}
