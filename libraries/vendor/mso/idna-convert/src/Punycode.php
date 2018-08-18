<?php

// {{{ license

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */
//
// +----------------------------------------------------------------------+
// | This library is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU Lesser General Public License as       |
// | published by the Free Software Foundation; either version 2.1 of the |
// | License, or (at your option) any later version.                      |
// |                                                                      |
// | This library is distributed in the hope that it will be useful, but  |
// | WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU    |
// | Lesser General Public License for more details.                      |
// |                                                                      |
// | You should have received a copy of the GNU Lesser General Public     |
// | License along with this library; if not, write to the Free Software  |
// | Foundation, Inc., 51 Franklin St, Boston, MA 02110, United States    |
// +----------------------------------------------------------------------+
//
// }}}

 /*
 * @author  Matthias Sommerfeld <mso@phlylabs.de>
 * @copyright 2004-2016 phlyLabs Berlin, http://phlylabs.de
 * @version 1.0.1 2016-01-24
 */

namespace Mso\IdnaConvert;

class Punycode implements PunycodeInterface
{
    // Internal settings, do not touch!
    const punycodePrefix = 'xn--';
    const invalidUcs = 0x80000000;
    const maxUcs = 0x10FFFF;
    const base = 36;
    const tMin = 1;
    const tMax = 26;
    const skew = 38;
    const damp = 700;
    const initialBias = 72;
    const initialN = 0x80;
    const sBase = 0xAC00;
    const lBase = 0x1100;
    const vBase = 0x1161;
    const tBase = 0x11A7;
    const lCount = 19;
    const vCount = 21;
    const tCount = 28;
    const nCount = 588;   // vCount * tCount
    const sCount = 11172; // lCount * tCount * vCount
    const sLast = self::sBase + self::lCount * self::vCount * self::tCount;

    protected static $isMbStringOverload = null;

    protected $NamePrepData;
    protected $UnicodeTranscoder;

    /**
     * the constructor
     *
     * @param $NamePrepData NamePrepDataInterface inject NamePrepData object
     * @param $UnicodeTranscoder UnicodeTranscoderInterface inject Unicode Transcoder
     * @since 0.5.2
     */
    public function __construct(NamePrepDataInterface $NamePrepData, UnicodeTranscoderInterface $UnicodeTranscoder)
    {
        // populate mbstring overloading cache if not set
        if (self::$isMbStringOverload === null) {
            self::$isMbStringOverload = (extension_loaded('mbstring') && (ini_get('mbstring.func_overload') & 0x02) === 0x02);
        }

        $this->NamePrepData = $NamePrepData;
        $this->UnicodeTranscoder = $UnicodeTranscoder;
    }

    /**
     * Returns the used prefix for punycode-encoded strings
     * @return string
     */
    public function getPunycodePrefix()
    {
        return self::punycodePrefix;
    }

    /**
     * Checks, whether or not the provided string is a valid punycode string
     * @param string $encoded
     * @return boolean
     */
    public function validate($encoded) {
        // Check for existence of the prefix
        if (strpos($encoded, self::punycodePrefix) !== 0) {
            return false;
        }
        // If nothing is left after the prefix, it is hopeless
        if (strlen(trim($encoded)) <= strlen(self::punycodePrefix)) {
            return false;
        }
        return true;
    }

    /**
     * The actual decoding algorithm
     * @param string
     * @return mixed
     */
    public function decode($encoded)
    {
        if (!$this->validate($encoded)) {
            return false;
        }

        $decoded = [];
        // Find last occurence of the delimiter
        $delim_pos = strrpos($encoded, '-');
        if ($delim_pos > self::byteLength(self::punycodePrefix)) {
            for ($k = self::byteLength(self::punycodePrefix); $k < $delim_pos; ++$k) {
                $decoded[] = ord($encoded{$k});
            }
        }
        $deco_len = count($decoded);
        $enco_len = self::byteLength($encoded);

        // Wandering through the strings; init
        $is_first = true;
        $bias = self::initialBias;
        $idx = 0;
        $char = self::initialN;

        for ($enco_idx = ($delim_pos) ? ($delim_pos + 1) : 0; $enco_idx < $enco_len; ++$deco_len) {
            for ($old_idx = $idx, $w = 1, $k = self::base; 1; $k += self::base) {
                $digit = $this->decodeDigit($encoded{$enco_idx++});
                $idx += $digit * $w;
                $t = ($k <= $bias) ? self::tMin :
                        (($k >= $bias + self::tMax) ? self::tMax : ($k - $bias));
                if ($digit < $t) {
                    break;
                }
                $w = (int) ($w * (self::base - $t));
            }
            $bias = $this->adapt($idx - $old_idx, $deco_len + 1, $is_first);
            $is_first = false;
            $char += (int) ($idx / ($deco_len + 1));
            $idx %= ($deco_len + 1);
            if ($deco_len > 0) {
                // Make room for the decoded char
                for ($i = $deco_len; $i > $idx; $i--) {
                    $decoded[$i] = $decoded[($i - 1)];
                }
            }
            $decoded[$idx++] = $char;
        }
        return $this->UnicodeTranscoder->ucs4array_utf8($decoded);
    }

    /**
     * The actual encoding algorithm
     * @param  array $decoded
     * @return mixed
     */
    public function encode($decoded)
    {
        // We cannot encode a domain name containing the Punycode prefix
        $extract = self::byteLength(self::punycodePrefix);
        $check_pref = $this->UnicodeTranscoder->utf8_ucs4array(self::punycodePrefix);
        $check_deco = array_slice($decoded, 0, $extract);

        if ($check_pref == $check_deco) {
            throw new \InvalidArgumentException('This is already a Punycode string');
        }
        // We will not try to encode strings consisting of basic code points only
        $encodable = false;
        foreach ($decoded as $k => $v) {
            if ($v > 0x7a) {
                $encodable = true;
                break;
            }
        }
        if (!$encodable) {
            return false;
        }
        // Do NAMEPREP
        $decoded = $this->namePrep($decoded);
        if (!$decoded || !is_array($decoded)) {
            return false; // NAMEPREP failed
        }
        $deco_len = count($decoded);
        if (!$deco_len) {
            return false; // Empty array
        }
        $codecount = 0; // How many chars have been consumed
        $encoded = '';
        // Copy all basic code points to output
        for ($i = 0; $i < $deco_len; ++$i) {
            $test = $decoded[$i];
            // Will match [-0-9a-zA-Z]
            if ((0x2F < $test && $test < 0x40)
                    || (0x40 < $test && $test < 0x5B)
                    || (0x60 < $test && $test <= 0x7B)
                    || (0x2D == $test)) {
                $encoded .= chr($decoded[$i]);
                $codecount++;
            }
        }
        if ($codecount == $deco_len) {
            return $encoded; // All codepoints were basic ones
        }
        // Start with the prefix; copy it to output
        $encoded = self::punycodePrefix . $encoded;
        // If we have basic code points in output, add an hyphen to the end
        if ($codecount) {
            $encoded .= '-';
        }
        // Now find and encode all non-basic code points
        $is_first = true;
        $cur_code = self::initialN;
        $bias = self::initialBias;
        $delta = 0;
        while ($codecount < $deco_len) {
            // Find the smallest code point >= the current code point and
            // remember the last ouccrence of it in the input
            for ($i = 0, $next_code = self::maxUcs; $i < $deco_len; $i++) {
                if ($decoded[$i] >= $cur_code && $decoded[$i] <= $next_code) {
                    $next_code = $decoded[$i];
                }
            }
            $delta += ($next_code - $cur_code) * ($codecount + 1);
            $cur_code = $next_code;

            // Scan input again and encode all characters whose code point is $cur_code
            for ($i = 0; $i < $deco_len; $i++) {
                if ($decoded[$i] < $cur_code) {
                    $delta++;
                } elseif ($decoded[$i] == $cur_code) {
                    for ($q = $delta, $k = self::base; 1; $k += self::base) {
                        $t = ($k <= $bias)
                                ? self::tMin
                                : (($k >= $bias + self::tMax) ? self::tMax : $k - $bias);
                        if ($q < $t) {
                            break;
                        }

                        $encoded .= $this->encodeDigit(intval($t + (($q - $t) % (self::base - $t))));
                        $q = (int) (($q - $t) / (self::base - $t));
                    }
                    $encoded .= $this->encodeDigit($q);
                    $bias = $this->adapt($delta, $codecount + 1, $is_first);
                    $codecount++;
                    $delta = 0;
                    $is_first = false;
                }
            }
            $delta++;
            $cur_code++;
        }
        return $encoded;
    }

    /**
     * Adapt the bias according to the current code point and position
     * @param int $delta
     * @param int $npoints
     * @param int $is_first
     * @return int
     */
    protected function adapt($delta, $npoints, $is_first)
    {
        $delta = intval($is_first ? ($delta / self::damp) : ($delta / 2));
        $delta += intval($delta / $npoints);
        for ($k = 0; $delta > ((self::base - self::tMin) * self::tMax) / 2; $k += self::base) {
            $delta = intval($delta / (self::base - self::tMin));
        }
        return intval($k + (self::base - self::tMin + 1) * $delta / ($delta + self::skew));
    }

    /**
     * Encoding a certain digit
     * @param    int $d
     * @return string
     */
    protected function encodeDigit($d)
    {
        return chr($d + 22 + 75 * ($d < 26));
    }

    /**
     * Decode a certain digit
     * @param    int $cp
     * @return int
     */
    protected function decodeDigit($cp)
    {
        $cp = ord($cp);
        if ($cp - 48 < 10) {

            return $cp - 22;
        }

        if ($cp - 65 < 26) {

            return $cp - 65;
        }
        if ($cp - 97 < 26) {

            return $cp - 97;
        }

        return self::base;
    }

    /**
     * Do Nameprep according to RFC3491 and RFC3454
     * @param array $input Unicode Characters
     * @return string  Unicode Characters, Nameprep'd
     */
    protected function namePrep($input)
    {
        $output = [];
        //
        // Mapping
        // Walking through the input array, performing the required steps on each of
        // the input chars and putting the result into the output array
        // While mapping required chars we apply the canonical ordering
        foreach ($input as $v) {
            // Map to nothing == skip that code point
            if (in_array($v, $this->NamePrepData->mapToNothing)) {
                continue;
            }
            // Try to find prohibited input
            if (in_array($v, $this->NamePrepData->prohibit) || in_array($v, $this->NamePrepData->generalProhibited)) {
                throw new \InvalidArgumentException(sprintf('NAMEPREP: Prohibited input U+%08X', $v));
            }
            foreach ($this->NamePrepData->prohibitRanges as $range) {
                if ($range[0] <= $v && $v <= $range[1]) {
                    throw new \InvalidArgumentException(sprintf('NAMEPREP: Prohibited input U+%08X', $v));
                }
            }

            if (0xAC00 <= $v && $v <= 0xD7AF) {
                // Hangul syllable decomposition
                foreach ($this->hangulDecompose($v) as $out) {
                    $output[] = (int) $out;
                }
            } elseif (isset($this->NamePrepData->replaceMaps[$v])) {
                foreach ($this->applyCanonicalOrdering($this->NamePrepData->replaceMaps[$v]) as $out) {
                    $output[] = (int) $out;
                }
            } else {
                $output[] = (int) $v;
            }
        }
        // Before applying any Combining, try to rearrange any Hangul syllables
        $output = $this->hangulCompose($output);
        //
        // Combine code points
        //
        $last_class = 0;
        $last_starter = 0;
        $out_len = count($output);
        for ($i = 0; $i < $out_len; ++$i) {
            $class = $this->getCombiningClass($output[$i]);
            if ((!$last_class || $last_class > $class) && $class) {
                // Try to match
                $seq_len = $i - $last_starter;
                $out = $this->combine(array_slice($output, $last_starter, $seq_len));
                // On match: Replace the last starter with the composed character and remove
                // the now redundant non-starter(s)
                if ($out) {
                    $output[$last_starter] = $out;
                    if (count($out) != $seq_len) {
                        for ($j = $i + 1; $j < $out_len; ++$j) {
                            $output[$j - 1] = $output[$j];
                        }
                        unset($output[$out_len]);
                    }
                    // Rewind the for loop by one, since there can be more possible compositions
                    $i--;
                    $out_len--;
                    $last_class = ($i == $last_starter) ? 0 : $this->getCombiningClass($output[$i - 1]);
                    continue;
                }
            }
            // The current class is 0
            if (!$class) {
                $last_starter = $i;
            }
            $last_class = $class;
        }
        return $output;
    }

    /**
     * Decomposes a Hangul syllable
     * (see http://www.unicode.org/unicode/reports/tr15/#Hangul
     * @param    integer  32bit UCS4 code point
     * @return   array    Either Hangul Syllable decomposed or original 32bit value as one value array
     */
    protected function hangulDecompose($char)
    {
        $sindex = (int) $char - self::sBase;
        if ($sindex < 0 || $sindex >= self::sCount) {
            return [$char];
        }
        $result = [];
        $result[] = (int) self::lBase + $sindex / self::nCount;
        $result[] = (int) self::vBase + ($sindex % self::nCount) / self::tCount;
        $T = intval(self::tBase + $sindex % self::tCount);
        if ($T != self::tBase) {
            $result[] = $T;
        }
        return $result;
    }

    /**
     * Ccomposes a Hangul syllable
     * (see http://www.unicode.org/unicode/reports/tr15/#Hangul
     * @param  array $input   Decomposed UCS4 sequence
     * @return array UCS4 sequence with syllables composed
     */
    protected function hangulCompose($input)
    {
        $inp_len = count($input);
        if (!$inp_len) {
            return [];
        }
        $result = [];
        $last = (int) $input[0];
        $result[] = $last; // copy first char from input to output

        for ($i = 1; $i < $inp_len; ++$i) {
            $char = (int) $input[$i];
            $sindex = $last - self::sBase;
            $lindex = $last - self::lBase;
            $vindex = $char - self::vBase;
            $tindex = $char - self::tBase;
            // Find out, whether two current characters are LV and T
            if (0 <= $sindex && $sindex < self::sCount && ($sindex % self::tCount == 0) && 0 <= $tindex && $tindex <= self::tCount) {
                // create syllable of form LVT
                $last += $tindex;
                $result[(count($result) - 1)] = $last; // reset last
                continue; // discard char
            }
            // Find out, whether two current characters form L and V
            if (0 <= $lindex && $lindex < self::lCount && 0 <= $vindex && $vindex < self::vCount) {
                // create syllable of form LV
                $last = (int) self::sBase + ($lindex * self::vCount + $vindex) * self::tCount;
                $result[(count($result) - 1)] = $last; // reset last
                continue; // discard char
            }
            // if neither case was true, just add the character
            $last = $char;
            $result[] = $char;
        }
        return $result;
    }

    /**
     * Returns the combining class of a certain wide char
     * @param integer  $char  Wide char to check (32bit integer)
     * @return integer Combining class if found, else 0
     */
    protected function getCombiningClass($char)
    {
        return isset($this->NamePrepData->normalizeCombiningClasses[$char])
                ? $this->NamePrepData->normalizeCombiningClasses[$char]
                : 0;
    }

    /**
     * Applies the canonical ordering of a decomposed UCS4 sequence
     * @param array  $input Decomposed UCS4 sequence
     * @return array Ordered USC4 sequence
     */
    protected function applyCanonicalOrdering($input)
    {
        $swap = true;
        $size = count($input);
        while ($swap) {
            $swap = false;
            $last = $this->getCombiningClass(intval($input[0]));
            for ($i = 0; $i < $size - 1; ++$i) {
                $next = $this->getCombiningClass(intval($input[$i + 1]));
                if ($next != 0 && $last > $next) {
                    // Move item leftward until it fits
                    for ($j = $i + 1; $j > 0; --$j) {
                        if ($this->getCombiningClass(intval($input[$j - 1])) <= $next) {
                            break;
                        }
                        $t = intval($input[$j]);
                        $input[$j] = intval($input[$j - 1]);
                        $input[$j - 1] = $t;
                        $swap = true;
                    }
                    // Reentering the loop looking at the old character again
                    $next = $last;
                }
                $last = $next;
            }
        }
        return $input;
    }

    /**
     * Do composition of a sequence of starter and non-starter
     * @param   array $input UCS4 Decomposed sequence
     * @return  array  Ordered USC4 sequence
     */
    protected function combine($input)
    {
        $inp_len = count($input);
        if (0 == $inp_len) {
            return false;
        }
        foreach ($this->NamePrepData->replaceMaps as $np_src => $np_target) {
            if ($np_target[0] != $input[0]) {
                continue;
            }
            if (count($np_target) != $inp_len) {
                continue;
            }
            $hit = false;
            foreach ($input as $k2 => $v2) {
                if ($v2 == $np_target[$k2]) {
                    $hit = true;
                } else {
                    $hit = false;
                    break;
                }
            }
            if ($hit) {
                return $np_src;
            }
        }
        return false;
    }

    /**
     * Gets the length of a string in bytes even if mbstring function
     * overloading is turned on
     *
     * @param string $string the string for which to get the length.
     * @return integer the length of the string in bytes.
     */
    protected static function byteLength($string)
    {
        if (self::$isMbStringOverload) {
            return mb_strlen($string, '8bit');
        }
        return strlen((binary) $string);
    }
}
