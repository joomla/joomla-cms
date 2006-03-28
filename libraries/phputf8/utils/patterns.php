<?php
/**
* PCRE Regular expressions for UTF-8. Note this file is not actually used by
* the rest of the library but these regular expressions can be useful to have
* available.
* @version $Id: patterns.php,v 1.1 2006/02/25 14:20:02 harryf Exp $
* @see http://www.w3.org/International/questions/qa-forms-utf-8
* @package utf8
* @subpackage patterns
*/

//--------------------------------------------------------------------
/**
* PCRE Pattern to check a UTF-8 string is valid
* Comes from W3 FAQ: Multilingual Forms
* Note: modified to include full ASCII range including control chars
* @see http://www.w3.org/International/questions/qa-forms-utf-8
* @package utf8
* @subpackage patterns
*/
$UTF8_VALID = '^('.
    '[\x00-\x7F]'.                          # ASCII (including control chars)
    '|[\xC2-\xDF][\x80-\xBF]'.              # non-overlong 2-byte
    '|\xE0[\xA0-\xBF][\x80-\xBF]'.          # excluding overlongs
    '|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}'.   # straight 3-byte
    '|\xED[\x80-\x9F][\x80-\xBF]'.          # excluding surrogates
    '|\xF0[\x90-\xBF][\x80-\xBF]{2}'.       # planes 1-3
    '|[\xF1-\xF3][\x80-\xBF]{3}'.           # planes 4-15
    '|\xF4[\x80-\x8F][\x80-\xBF]{2}'.       # plane 16
    ')*$';

//--------------------------------------------------------------------
/**
* PCRE Pattern to match single UTF-8 characters
* Comes from W3 FAQ: Multilingual Forms
* Note: modified to include full ASCII range including control chars
* @see http://www.w3.org/International/questions/qa-forms-utf-8
* @package utf8
* @subpackage patterns
*/
$UTF8_MATCH =
    '([\x00-\x7F])'.                          # ASCII (including control chars)
    '|([\xC2-\xDF][\x80-\xBF])'.              # non-overlong 2-byte
    '|(\xE0[\xA0-\xBF][\x80-\xBF])'.          # excluding overlongs
    '|([\xE1-\xEC\xEE\xEF][\x80-\xBF]{2})'.   # straight 3-byte
    '|(\xED[\x80-\x9F][\x80-\xBF])'.          # excluding surrogates
    '|(\xF0[\x90-\xBF][\x80-\xBF]{2})'.       # planes 1-3
    '|([\xF1-\xF3][\x80-\xBF]{3})'.           # planes 4-15
    '|(\xF4[\x80-\x8F][\x80-\xBF]{2})';       # plane 16

//--------------------------------------------------------------------
/**
* PCRE Pattern to locate bad bytes in a UTF-8 string
* Comes from W3 FAQ: Multilingual Forms
* Note: modified to include full ASCII range including control chars
* @see http://www.w3.org/International/questions/qa-forms-utf-8
* @package utf8
* @subpackage patterns
*/
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
