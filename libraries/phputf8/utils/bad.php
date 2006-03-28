<?php
/**
* @version $Id: bad.php,v 1.2 2006/02/26 13:20:44 harryf Exp $
* Tools for locating / replacing bad bytes in UTF-8 strings
* The Original Code is Mozilla Communicator client code.
* The Initial Developer of the Original Code is
* Netscape Communications Corporation.
* Portions created by the Initial Developer are Copyright (C) 1998
* the Initial Developer. All Rights Reserved.
* Ported to PHP by Henri Sivonen (http://hsivonen.iki.fi)
* Slight modifications to fit with phputf8 library by Harry Fuecks (hfuecks gmail com)
* @see http://lxr.mozilla.org/seamonkey/source/intl/uconv/src/nsUTF8ToUnicode.cpp
* @see http://lxr.mozilla.org/seamonkey/source/intl/uconv/src/nsUnicodeToUTF8.cpp
* @see http://hsivonen.iki.fi/php-utf8/
* @package utf8
* @subpackage bad
* @see utf8_is_valid
*/

//--------------------------------------------------------------------
/**
* Locates the first bad byte in a UTF-8 string returning it's
* byte index in the string
* PCRE Pattern to locate bad bytes in a UTF-8 string
* Comes from W3 FAQ: Multilingual Forms
* Note: modified to include full ASCII range including control chars
* @see http://www.w3.org/International/questions/qa-forms-utf-8
* @param string
* @return mixed integer byte index or FALSE if no bad found
* @package utf8
* @subpackage bad
*/
function utf8_bad_find($str) {
    $UTF8_BAD =
    '([\x00-\x7F]'.                          # ASCII (including control chars)
    '|[\xC2-\xDF][\x80-\xBF]'.               # non-overlong 2-byte
    '|\xE0[\xA0-\xBF][\x80-\xBF]'.           # excluding overlongs
    '|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}'.    # straight 3-byte
    '|\xED[\x80-\x9F][\x80-\xBF]'.           # excluding surrogates
    '|\xF0[\x90-\xBF][\x80-\xBF]{2}'.        # planes 1-3
    '|[\xF1-\xF3][\x80-\xBF]{3}'.            # planes 4-15
    '|\xF4[\x80-\x8F][\x80-\xBF]{2}'.        # plane 16
    '|(.{1}))';                              # invalid byte
    $pos = 0;
    $badList = array();
    while (preg_match('/'.$UTF8_BAD.'/S', $str, $matches)) {
        $bytes = strlen($matches[0]);
        if ( isset($matches[2])) {
            return $pos;
        }
        $pos += $bytes;
        $str = substr($str,$bytes);
    }
    return FALSE;
}

//--------------------------------------------------------------------
/**
* Locates all bad bytes in a UTF-8 string and returns a list of their
* byte index in the string
* PCRE Pattern to locate bad bytes in a UTF-8 string
* Comes from W3 FAQ: Multilingual Forms
* Note: modified to include full ASCII range including control chars
* @see http://www.w3.org/International/questions/qa-forms-utf-8
* @param string
* @return mixed array of integers or FALSE if no bad found
* @package utf8
* @subpackage bad
*/
function utf8_bad_findall($str) {
    $UTF8_BAD =
    '([\x00-\x7F]'.                          # ASCII (including control chars)
    '|[\xC2-\xDF][\x80-\xBF]'.               # non-overlong 2-byte
    '|\xE0[\xA0-\xBF][\x80-\xBF]'.           # excluding overlongs
    '|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}'.    # straight 3-byte
    '|\xED[\x80-\x9F][\x80-\xBF]'.           # excluding surrogates
    '|\xF0[\x90-\xBF][\x80-\xBF]{2}'.        # planes 1-3
    '|[\xF1-\xF3][\x80-\xBF]{3}'.            # planes 4-15
    '|\xF4[\x80-\x8F][\x80-\xBF]{2}'.        # plane 16
    '|(.{1}))';                              # invalid byte
    $pos = 0;
    $badList = array();
    while (preg_match('/'.$UTF8_BAD.'/S', $str, $matches)) {
        $bytes = strlen($matches[0]);
        if ( isset($matches[2])) {
            $badList[] = $pos;
        }
        $pos += $bytes;
        $str = substr($str,$bytes);
    }
    if ( count($badList) > 0 ) {
        return $badList;
    }
    return FALSE;
}

//--------------------------------------------------------------------
/**
* Strips out any bad bytes from a UTF-8 string and returns the rest
* PCRE Pattern to locate bad bytes in a UTF-8 string
* Comes from W3 FAQ: Multilingual Forms
* Note: modified to include full ASCII range including control chars
* @see http://www.w3.org/International/questions/qa-forms-utf-8
* @param string
* @return string
* @package utf8
* @subpackage bad
*/
function utf8_bad_strip($str) {
    $UTF8_BAD =
    '([\x00-\x7F]'.                          # ASCII (including control chars)
    '|[\xC2-\xDF][\x80-\xBF]'.               # non-overlong 2-byte
    '|\xE0[\xA0-\xBF][\x80-\xBF]'.           # excluding overlongs
    '|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}'.    # straight 3-byte
    '|\xED[\x80-\x9F][\x80-\xBF]'.           # excluding surrogates
    '|\xF0[\x90-\xBF][\x80-\xBF]{2}'.        # planes 1-3
    '|[\xF1-\xF3][\x80-\xBF]{3}'.            # planes 4-15
    '|\xF4[\x80-\x8F][\x80-\xBF]{2}'.        # plane 16
    '|(.{1}))';                              # invalid byte
    ob_start();
    while (preg_match('/'.$UTF8_BAD.'/S', $str, $matches)) {
        if ( !isset($matches[2])) {
            echo $matches[0];
        }
        $str = substr($str,strlen($matches[0]));
    }
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}

//--------------------------------------------------------------------
/**
* Replace bad bytes with an alternative character - ASCII character
* recommended is replacement char
* PCRE Pattern to locate bad bytes in a UTF-8 string
* Comes from W3 FAQ: Multilingual Forms
* Note: modified to include full ASCII range including control chars
* @see http://www.w3.org/International/questions/qa-forms-utf-8
* @param string to search
* @param string to replace bad bytes with (defaults to '?') - use ASCII
* @return string
* @package utf8
* @subpackage bad
*/
function utf8_bad_replace($str, $replace = '?') {
    $UTF8_BAD =
    '([\x00-\x7F]'.                          # ASCII (including control chars)
    '|[\xC2-\xDF][\x80-\xBF]'.               # non-overlong 2-byte
    '|\xE0[\xA0-\xBF][\x80-\xBF]'.           # excluding overlongs
    '|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}'.    # straight 3-byte
    '|\xED[\x80-\x9F][\x80-\xBF]'.           # excluding surrogates
    '|\xF0[\x90-\xBF][\x80-\xBF]{2}'.        # planes 1-3
    '|[\xF1-\xF3][\x80-\xBF]{3}'.            # planes 4-15
    '|\xF4[\x80-\x8F][\x80-\xBF]{2}'.        # plane 16
    '|(.{1}))';                              # invalid byte
    ob_start();
    while (preg_match('/'.$UTF8_BAD.'/S', $str, $matches)) {
        if ( !isset($matches[2])) {
            echo $matches[0];
        } else {
            echo $replace;
        }
        $str = substr($str,strlen($matches[0]));
    }
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}

//--------------------------------------------------------------------
/**
* Return code from utf8_bad_identify() when a five octet sequence is detected.
* Note: 5 octets sequences are valid UTF-8 but are not supported by Unicode so
* do not represent a useful character
* @see utf8_bad_identify
* @package utf8
* @subpackage bad
*/
define('UTF8_BAD_5OCTET',1);

/**
* Return code from utf8_bad_identify() when a six octet sequence is detected.
* Note: 6 octets sequences are valid UTF-8 but are not supported by Unicode so
* do not represent a useful character
* @see utf8_bad_identify
* @package utf8
* @subpackage bad
*/
define('UTF8_BAD_6OCTET',2);

/**
* Return code from utf8_bad_identify().
* Invalid octet for use as start of multi-byte UTF-8 sequence
* @see utf8_bad_identify
* @package utf8
* @subpackage bad
*/
define('UTF8_BAD_SEQID',3);

/**
* Return code from utf8_bad_identify().
* From Unicode 3.1, non-shortest form is illegal
* @see utf8_bad_identify
* @package utf8
* @subpackage bad
*/
define('UTF8_BAD_NONSHORT',4);

/**
* Return code from utf8_bad_identify().
* From Unicode 3.2, surrogate characters are illegal
* @see utf8_bad_identify
* @package utf8
* @subpackage bad
*/
define('UTF8_BAD_SURROGATE',5);

/**
* Return code from utf8_bad_identify().
* Codepoints outside the Unicode range are illegal
* @see utf8_bad_identify
* @package utf8
* @subpackage bad
*/
define('UTF8_BAD_UNIOUTRANGE',6);

/**
* Return code from utf8_bad_identify().
* Incomplete multi-octet sequence
* Note: this is kind of a "catch-all"
* @see utf8_bad_identify
* @package utf8
* @subpackage bad
*/
define('UTF8_BAD_SEQINCOMPLETE',7);

//--------------------------------------------------------------------
/**
* Reports on the type of bad byte found in a UTF-8 string. Returns a
* status code on the first bad byte found
* @author <hsivonen@iki.fi>
* @param string UTF-8 encoded string
* @return mixed integer constant describing problem or FALSE if valid UTF-8
* @see utf8_bad_explain
* @see http://hsivonen.iki.fi/php-utf8/
* @package utf8
* @subpackage bad
*/
function utf8_bad_identify($str, &$i) {
    
    $mState = 0;     // cached expected number of octets after the current octet
                     // until the beginning of the next UTF8 character sequence
    $mUcs4  = 0;     // cached Unicode character
    $mBytes = 1;     // cached expected number of octets in the current sequence
    
    $len = strlen($str);
    
    for($i = 0; $i < $len; $i++) {
        
        $in = ord($str{$i});
        
        if ( $mState == 0) {
            
            // When mState is zero we expect either a US-ASCII character or a
            // multi-octet sequence.
            if (0 == (0x80 & ($in))) {
                // US-ASCII, pass straight through.
                $mBytes = 1;
                
            } else if (0xC0 == (0xE0 & ($in))) {
                // First octet of 2 octet sequence
                $mUcs4 = ($in);
                $mUcs4 = ($mUcs4 & 0x1F) << 6;
                $mState = 1;
                $mBytes = 2;
                
            } else if (0xE0 == (0xF0 & ($in))) {
                // First octet of 3 octet sequence
                $mUcs4 = ($in);
                $mUcs4 = ($mUcs4 & 0x0F) << 12;
                $mState = 2;
                $mBytes = 3;
                
            } else if (0xF0 == (0xF8 & ($in))) {
                // First octet of 4 octet sequence
                $mUcs4 = ($in);
                $mUcs4 = ($mUcs4 & 0x07) << 18;
                $mState = 3;
                $mBytes = 4;
                
            } else if (0xF8 == (0xFC & ($in))) {
                
                /* First octet of 5 octet sequence.
                *
                * This is illegal because the encoded codepoint must be either
                * (a) not the shortest form or
                * (b) outside the Unicode range of 0-0x10FFFF.
                */
                
                return UTF8_BAD_5OCTET;
                
            } else if (0xFC == (0xFE & ($in))) {
                
                // First octet of 6 octet sequence, see comments for 5 octet sequence.
                return UTF8_BAD_6OCTET;
                
            } else {
                // Current octet is neither in the US-ASCII range nor a legal first
                // octet of a multi-octet sequence.
                return UTF8_BAD_SEQID;
                
            }
        
        } else {
            
            // When mState is non-zero, we expect a continuation of the multi-octet
            // sequence
            if (0x80 == (0xC0 & ($in))) {
                
                // Legal continuation.
                $shift = ($mState - 1) * 6;
                $tmp = $in;
                $tmp = ($tmp & 0x0000003F) << $shift;
                $mUcs4 |= $tmp;
            
                /**
                * End of the multi-octet sequence. mUcs4 now contains the final
                * Unicode codepoint to be output
                */
                if (0 == --$mState) {
                    
                    // From Unicode 3.1, non-shortest form is illegal
                    if (((2 == $mBytes) && ($mUcs4 < 0x0080)) ||
                        ((3 == $mBytes) && ($mUcs4 < 0x0800)) ||
                        ((4 == $mBytes) && ($mUcs4 < 0x10000)) ) {
                        return UTF8_BAD_NONSHORT;
                        
                    // From Unicode 3.2, surrogate characters are illegal
                    } else if (($mUcs4 & 0xFFFFF800) == 0xD800) {
                        return UTF8_BAD_SURROGATE;
                        
                    // Codepoints outside the Unicode range are illegal
                    } else if ($mUcs4 > 0x10FFFF) {
                        return UTF8_BAD_UNIOUTRANGE;
                    }
                    
                    //initialize UTF8 cache
                    $mState = 0;
                    $mUcs4  = 0;
                    $mBytes = 1;
                }
            
            } else {
                // ((0xC0 & (*in) != 0x80) && (mState != 0))
                // Incomplete multi-octet sequence.
                $i--;
                return UTF8_BAD_SEQINCOMPLETE;
            }
        }
    }
    
    if ( $mState != 0 ) {
        // Incomplete multi-octet sequence.
        $i--;
        return UTF8_BAD_SEQINCOMPLETE;
    }
    
    // No bad octets found
    $i = NULL;
    return FALSE;
}

//--------------------------------------------------------------------
/**
* Takes a return code from utf8_bad_identify() are returns a message
* (in English) explaining what the problem is.
* @param int return code from utf8_bad_identify
* @return mixed string message or FALSE if return code unknown
* @see utf8_bad_identify
* @package utf8
* @subpackage bad
*/
function utf8_bad_explain($code) {
    
    switch ($code) {
        
        case UTF8_BAD_5OCTET:
            return 'Five octet sequences are valid UTF-8 but are not supported by Unicode';
        break;
        
        case UTF8_BAD_6OCTET:
            return 'Six octet sequences are valid UTF-8 but are not supported by Unicode';
        break;
        
        case UTF8_BAD_SEQID:
            return 'Invalid octet for use as start of multi-byte UTF-8 sequence';
        break;
        
        case UTF8_BAD_NONSHORT:
            return 'From Unicode 3.1, non-shortest form is illegal';
        break;
        
        case UTF8_BAD_SURROGATE:
            return 'From Unicode 3.2, surrogate characters are illegal';
        break;
        
        case UTF8_BAD_UNIOUTRANGE:
            return 'Codepoints outside the Unicode range are illegal';
        break;
        
        case UTF8_BAD_SEQINCOMPLETE:
            return 'Incomplete multi-octet sequence';
        break;
        
    }
    
    trigger_error('Unknown error code: '.$code,E_USER_WARNING);
    return FALSE;
    
}
