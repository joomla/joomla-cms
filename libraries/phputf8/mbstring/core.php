<?php
/**
* @version $Id: core.php,v 1.4 2006/02/25 14:07:57 harryf Exp $
* @package utf8
* @subpackage strings
*/

/**
* Define UTF8_CORE as required
*/
if ( !defined('UTF8_CORE') ) {
    define('UTF8_CORE',TRUE);
}

//--------------------------------------------------------------------
/**
* Assumes mbstring internal encoding is set to UTF-8
* Wrapper around mb_strpos
* Find position of first occurrence of a string
* @param string haystack
* @param string needle (you should validate this with utf8_is_valid)
* @param integer offset in characters (from left)
* @return mixed integer position or FALSE on failure
* @package utf8
* @subpackage strings
*/
function utf8_strpos($str, $search, $offset = FALSE){
    if ( $offset === FALSE ) {
        return mb_strpos($str, $search);
    } else {
        return mb_strpos($str, $search, $offset);
    }
}

//--------------------------------------------------------------------
/**
* Assumes mbstring internal encoding is set to UTF-8
* Wrapper around mb_strrpos
* Find position of last occurrence of a char in a string
* @param string haystack
* @param string needle (you should validate this with utf8_is_valid)
* @param integer (optional) offset (from left)
* @return mixed integer position or FALSE on failure
* @package utf8
* @subpackage strings
*/
function utf8_strrpos($str, $search, $offset = FALSE){
    if ( $offset === FALSE ) {
        # Emulate behaviour of strrpos rather than raising warning
        if ( empty($str) ) {
            return FALSE;
        }
        return mb_strrpos($str, $search);
    } else {
        if ( !is_int($offset) ) {
            trigger_error('utf8_strrpos expects parameter 3 to be long',E_USER_WARNING);
            return FALSE;
        }

        $str = mb_substr($str, $offset);

        if ( FALSE !== ( $pos = mb_strrpos($str, $search) ) ) {
            return $pos + $offset;
        }

        return FALSE;
    }
}

//--------------------------------------------------------------------
/**
* Assumes mbstring internal encoding is set to UTF-8
* Wrapper around mb_substr
* Return part of a string given character offset (and optionally length)
* @param string
* @param integer number of UTF-8 characters offset (from left)
* @param integer (optional) length in UTF-8 characters from offset
* @return mixed string or FALSE if failure
* @package utf8
* @subpackage strings
*/
function utf8_substr($str, $offset, $length = FALSE){
    if ( $length === FALSE ) {
        return mb_substr($str, $offset);
    } else {
        return mb_substr($str, $offset, $length);
    }
}
