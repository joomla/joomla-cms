<?php

/**
 * This is the HMACSHA1 implementation for the OpenID library.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @access private
 * @package OpenID
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005 Janrain, Inc.
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 */

/**
 * SHA1_BLOCKSIZE is this module's SHA1 blocksize used by the fallback
 * implementation.
 */
define('Auth_OpenID_SHA1_BLOCKSIZE', 64);

if (!function_exists('sha1')) {
    /**
     * Return a raw SHA1 hash of the given string
     *
     * XXX: include the SHA1 code from Dan Libby's OpenID library
     */
    function Auth_OpenID_SHA1($text)
    {
        trigger_error('No SHA1 function found', E_USER_ERROR);
    }
} else {
    /**
     * @ignore
     */
    function Auth_OpenID_SHA1($text)
        {
            $hex = sha1($text);
            $raw = '';
            for ($i = 0; $i < 40; $i += 2) {
                $hexcode = substr($hex, $i, 2);
                $charcode = (int)base_convert($hexcode, 16, 10);
                $raw .= chr($charcode);
            }
            return $raw;
        }
}

/**
 * Compute an HMAC/SHA1 hash.
 *
 * @access private
 * @param string $key The HMAC key
 * @param string $text The message text to hash
 * @return string $mac The MAC
 */
function Auth_OpenID_HMACSHA1($key, $text)
{
    if (strlen($key) > Auth_OpenID_SHA1_BLOCKSIZE) {
        $key = Auth_OpenID_SHA1($key, true);
    }

    $key = str_pad($key, Auth_OpenID_SHA1_BLOCKSIZE, chr(0x00));
    $ipad = str_repeat(chr(0x36), Auth_OpenID_SHA1_BLOCKSIZE);
    $opad = str_repeat(chr(0x5c), Auth_OpenID_SHA1_BLOCKSIZE);
    $hash1 = Auth_OpenID_SHA1(($key ^ $ipad) . $text, true);
    $hmac = Auth_OpenID_SHA1(($key ^ $opad) . $hash1, true);
    return $hmac;
}

?>