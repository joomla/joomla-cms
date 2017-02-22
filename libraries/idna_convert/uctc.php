<?php
/**
 * UCTC - The Unicode Transcoder
 *
 * Converts between various flavours of Unicode representations like UCS-4 or UTF-8
 * Supported schemes:
 * - UCS-4 Little Endian / Big Endian / Array (partially)
 * - UTF-16 Little Endian / Big Endian (not yet)
 * - UTF-8
 * - UTF-7
 * - UTF-7 IMAP (modified UTF-7)
 *
 * @package phlyMail Nahariya 4.0+ Default branch
 * @author Matthias Sommerfeld  <mso@phlyLabs.de>
 * @copyright 2003-2009 phlyLabs Berlin, http://phlylabs.de
 * @version 0.0.6 2009-05-10
 */
class uctc {
    private static $mechs = array('ucs4', /*'ucs4le', 'ucs4be', */'ucs4array', /*'utf16', 'utf16le', 'utf16be', */'utf8', 'utf7', 'utf7imap');
    private static $allow_overlong = false;
    private static $safe_mode;
    private static $safe_char;

    /**
     * The actual conversion routine
     *
     * @param mixed $data  The data to convert, usually a string, array when converting from UCS-4 array
     * @param string $from  Original encoding of the data
     * @param string $to  Target encoding of the data
     * @param bool $safe_mode  SafeMode tries to correct invalid codepoints
     * @return mixed  False on failure, String or array on success, depending on target encoding
     * @access public
     * @since 0.0.1
     */
    public static function convert($data, $from, $to, $safe_mode = false, $safe_char = 0xFFFC)
    {
        self::$safe_mode = ($safe_mode) ? true : false;
        self::$safe_char = ($safe_char) ? $safe_char : 0xFFFC;
        if (self::$safe_mode) self::$allow_overlong = true;
        if (!in_array($from, self::$mechs)) throw new Exception('Invalid input format specified');
        if (!in_array($to, self::$mechs)) throw new Exception('Invalid output format specified');
        if ($from != 'ucs4array') eval('$data = self::'.$from.'_ucs4array($data);');
        if ($to != 'ucs4array') eval('$data = self::ucs4array_'.$to.'($data);');
        return $data;
    }

    /**
     * This converts an UTF-8 encoded string to its UCS-4 representation
     *
     * @param string $input  The UTF-8 string to convert
     * @return array  Array of 32bit values representing each codepoint
     * @access private
     */
    private static function utf8_ucs4array($input)
    {
        $output = array();
        $out_len = 0;
        $inp_len = strlen($input);
        $mode = 'next';
        $test = 'none';
        for ($k = 0; $k < $inp_len; ++$k) {
            $v = ord($input{$k}); // Extract byte from input string

            if ($v < 128) { // We found an ASCII char - put into stirng as is
                $output[$out_len] = $v;
                ++$out_len;
                if ('add' == $mode) {
                    if (self::$safe_mode) {
                        $output[$out_len-2] = self::$safe_char;
                        $mode = 'next';
                    } else {
                        throw new Exception('Conversion from UTF-8 to UCS-4 failed: malformed input at byte '.$k);
                    }
                }
                continue;
            }
            if ('next' == $mode) { // Try to find the next start byte; determine the width of the Unicode char
                $start_byte = $v;
                $mode = 'add';
                $test = 'range';
                if ($v >> 5 == 6) { // &110xxxxx 10xxxxx
                    $next_byte = 0; // Tells, how many times subsequent bitmasks must rotate 6bits to the left
                    $v = ($v - 192) << 6;
                } elseif ($v >> 4 == 14) { // &1110xxxx 10xxxxxx 10xxxxxx
                    $next_byte = 1;
                    $v = ($v - 224) << 12;
                } elseif ($v >> 3 == 30) { // &11110xxx 10xxxxxx 10xxxxxx 10xxxxxx
                    $next_byte = 2;
                    $v = ($v - 240) << 18;
                } elseif (self::$safe_mode) {
                    $mode = 'next';
                    $output[$out_len] = self::$safe_char;
                    ++$out_len;
                    continue;
                } else {
                    throw new Exception('This might be UTF-8, but I don\'t understand it at byte '.$k);
                }
                if ($inp_len-$k-$next_byte < 2) {
                    $output[$out_len] = self::$safe_char;
                    $mode = 'no';
                    continue;
                }

                if ('add' == $mode) {
                    $output[$out_len] = (int) $v;
                    ++$out_len;
                    continue;
                }
            }
            if ('add' == $mode) {
                if (!self::$allow_overlong && $test == 'range') {
                    $test = 'none';
                    if (($v < 0xA0 && $start_byte == 0xE0) || ($v < 0x90 && $start_byte == 0xF0) || ($v > 0x8F && $start_byte == 0xF4)) {
                        throw new Exception('Bogus UTF-8 character detected (out of legal range) at byte '.$k);
                    }
                }
                if ($v >> 6 == 2) { // Bit mask must be 10xxxxxx
                    $v = ($v-128) << ($next_byte*6);
                    $output[($out_len-1)] += $v;
                    --$next_byte;
                } else {
                    if (self::$safe_mode) {
                        $output[$out_len-1] = ord(self::$safe_char);
                        $k--;
                        $mode = 'next';
                        continue;
                    } else {
                        throw new Exception('Conversion from UTF-8 to UCS-4 failed: malformed input at byte '.$k);
                    }
                }
                if ($next_byte < 0) {
                    $mode = 'next';
                }
            }
        } // for
        return $output;
    }

    /**
     * Convert UCS-4 string into UTF-8 string
     * See utf8_ucs4array() for details
     * @access   private
     */
    private static function ucs4array_utf8($input)
    {
        $output = '';
        foreach ($input as $v) {
            if ($v < 128) { // 7bit are transferred literally
                $output .= chr($v);
            } elseif ($v < (1 << 11)) { // 2 bytes
                $output .= chr(192+($v >> 6)).chr(128+($v & 63));
            } elseif ($v < (1 << 16)) { // 3 bytes
                $output .= chr(224+($v >> 12)).chr(128+(($v >> 6) & 63)).chr(128+($v & 63));
            } elseif ($v < (1 << 21)) { // 4 bytes
                $output .= chr(240+($v >> 18)).chr(128+(($v >> 12) & 63)).chr(128+(($v >> 6) & 63)).chr(128+($v & 63));
            } elseif (self::$safe_mode) {
                $output .= self::$safe_char;
            } else {
                throw new Exception('Conversion from UCS-4 to UTF-8 failed: malformed input at byte '.$k);
            }
        }
        return $output;
    }

    private static function utf7imap_ucs4array($input)
    {
        return self::utf7_ucs4array(str_replace(',', '/', $input), '&');
    }

    private static function utf7_ucs4array($input, $sc = '+')
    {
        $output  = array();
        $out_len = 0;
        $inp_len = strlen($input);
        $mode    = 'd';
        $b64     = '';

        for ($k = 0; $k < $inp_len; ++$k) {
            $c = $input{$k};
            if (0 == ord($c)) continue; // Ignore zero bytes
            if ('b' == $mode) {
                // Sequence got terminated
                if (!preg_match('![A-Za-z0-9/'.preg_quote($sc, '!').']!', $c)) {
                    if ('-' == $c) {
                        if ($b64 == '') {
                            $output[$out_len] = ord($sc);
                            $out_len++;
                            $mode = 'd';
                            continue;
                        }
                    }
                    $tmp = base64_decode($b64);
                    $tmp = substr($tmp, -1 * (strlen($tmp) % 2));
                    for ($i = 0; $i < strlen($tmp); $i++) {
                        if ($i % 2) {
                            $output[$out_len] += ord($tmp{$i});
                            $out_len++;
                        } else {
                            $output[$out_len] = ord($tmp{$i}) << 8;
                        }
                    }
                    $mode = 'd';
                    $b64 = '';
                    continue;
                } else {
                    $b64 .= $c;
                }
            }
            if ('d' == $mode) {
                if ($sc == $c) {
                    $mode = 'b';
                    continue;
                }
                $output[$out_len] = ord($c);
                $out_len++;
            }
        }
        return $output;
    }

    private static function ucs4array_utf7imap($input)
    {
        return str_replace('/', ',', self::ucs4array_utf7($input, '&'));
    }

    private static function ucs4array_utf7($input, $sc = '+')
    {
        $output = '';
        $mode = 'd';
        $b64 = '';
        while (true) {
            $v = (!empty($input)) ? array_shift($input) : false;
            $is_direct = (false !== $v) ? (0x20 <= $v && $v <= 0x7e && $v != ord($sc)) : true;
            if ($mode == 'b') {
                if ($is_direct) {
                    if ($b64 == chr(0).$sc) {
                        $output .= $sc.'-';
                        $b64 = '';
                    } elseif ($b64) {
                        $output .= $sc.str_replace('=', '', base64_encode($b64)).'-';
                        $b64 = '';
                    }
                    $mode = 'd';
                } elseif (false !== $v) {
                    $b64 .= chr(($v >> 8) & 255). chr($v & 255);
                }
            }
            if ($mode == 'd' && false !== $v) {
                if ($is_direct) {
                    $output .= chr($v);
                } else {
                    $b64 = chr(($v >> 8) & 255). chr($v & 255);
                    $mode = 'b';
                }
            }
            if (false === $v && $b64 == '') break;
        }
        return $output;
    }

    /**
     * Convert UCS-4 array into UCS-4 string (Little Endian at the moment)
     * @access   private
     */
    private static function ucs4array_ucs4($input)
    {
        $output = '';
        foreach ($input as $v) {
            $output .= chr(($v >> 24) & 255).chr(($v >> 16) & 255).chr(($v >> 8) & 255).chr($v & 255);
        }
        return $output;
    }

    /**
     * Convert UCS-4 string (LE in the moment) into UCS-4 garray
     * @access   private
     */
    private static function ucs4_ucs4array($input)
    {
        $output = array();

        $inp_len = strlen($input);
        // Input length must be dividable by 4
        if ($inp_len % 4) {
            throw new Exception('Input UCS4 string is broken');
        }
        // Empty input - return empty output
        if (!$inp_len) return $output;

        for ($i = 0, $out_len = -1; $i < $inp_len; ++$i) {
            if (!($i % 4)) { // Increment output position every 4 input bytes
                $out_len++;
                $output[$out_len] = 0;
            }
            $output[$out_len] += ord($input{$i}) << (8 * (3 - ($i % 4) ) );
        }
        return $output;
    }
}
?>