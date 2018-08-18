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
 * @package IdnaConvert
 * @author Matthias Sommerfeld  <mso@phlyLabs.de>
 * @copyright 2003-2016 phlyLabs Berlin, http://phlylabs.de
 * @version 0.1.0 2016-01-08
 */

namespace Mso\IdnaConvert;

interface UnicodeTranscoderInterface
{
    public static function convert($data, $from, $to, $safe_mode = false, $safe_char = 0xFFFC);

    public static function utf8_ucs4array($input);

    public static function ucs4array_utf8($input);

    public static function utf7imap_ucs4array($input);

    public static function utf7_ucs4array($input, $sc = '+');

    public static function ucs4array_utf7imap($input);

    public static function ucs4array_utf7($input, $sc = '+');

    public static function ucs4array_ucs4($input);

    public static function ucs4_ucs4array($input);
}
