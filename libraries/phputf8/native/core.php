<?php
/**
* @version $Id: core.php,v 1.4 2006/02/25 13:54:31 harryf Exp $
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
* UTF-8 aware alternative to strpos
* Find position of first occurrence of a string
* Note: This will get alot slower if offset is used
* Note: requires utf8_strlen amd utf8_substr to be loaded
* @param string haystack
* @param string needle (you should validate this with utf8_is_valid)
* @param integer offset in characters (from left)
* @return mixed integer position or FALSE on failure
* @see http://www.php.net/strpos
* @see utf8_strlen
* @see utf8_substr
* @package utf8
* @subpackage strings
*/
function utf8_strpos($str, $needle, $offset = NULL) {

    if ( is_null($offset) ) {

        $ar = explode($needle, $str);
        if ( count($ar) > 1 ) {
            return utf8_strlen($ar[0]);
        }
        return FALSE;

    } else {

        if ( !is_int($offset) ) {
            trigger_error('utf8_strpos: Offset must be an integer',E_USER_ERROR);
            return FALSE;
        }

        $str = utf8_substr($str, $offset);

        if ( FALSE !== ( $pos = utf8_strpos($str, $needle) ) ) {
            return $pos + $offset;
        }

        return FALSE;
    }

}

//--------------------------------------------------------------------
/**
* UTF-8 aware alternative to strrpos
* Find position of last occurrence of a char in a string
* Note: This will get alot slower if offset is used
* Note: requires utf8_substr and utf8_strlen to be loaded
* @param string haystack
* @param string needle (you should validate this with utf8_is_valid)
* @param integer (optional) offset (from left)
* @return mixed integer position or FALSE on failure
* @see http://www.php.net/strrpos
* @see utf8_substr
* @see utf8_strlen
* @package utf8
* @subpackage strings
*/
function utf8_strrpos($str, $needle, $offset = NULL) {

    if ( is_null($offset) ) {

        $ar = explode($needle, $str);

        if ( count($ar) > 1 ) {
            // Pop off the end of the string where the last match was made
            array_pop($ar);
            $str = join($needle,$ar);
            return utf8_strlen($str);
        }
        return FALSE;

    } else {

        if ( !is_int($offset) ) {
            trigger_error('utf8_strrpos expects parameter 3 to be long',E_USER_WARNING);
            return FALSE;
        }

        $str = utf8_substr($str, $offset);

        if ( FALSE !== ( $pos = utf8_strrpos($str, $needle) ) ) {
            return $pos + $offset;
        }

        return FALSE;
    }

}

//--------------------------------------------------------------------
/**
* UTF-8 aware alternative to substr
* Return part of a string given character offset (and optionally length)
* Note: supports use of negative offsets and lengths but will be slower
* when doing so
* @param string
* @param integer number of UTF-8 characters offset (from left)
* @param integer (optional) length in UTF-8 characters from offset
* @return mixed string or FALSE if failure
* @package utf8
* @subpackage strings
*/
function utf8_substr($str, $offset, $length = NULL) {

    if ( $offset >= 0 && $length >= 0 ) {

        if ( $length === NULL ) {
            $length = '*';
        } else {
            if ( !preg_match('/^[0-9]+$/', $length) ) {
                trigger_error('utf8_substr expects parameter 3 to be long', E_USER_WARNING);
                return FALSE;
            }

            $strlen = strlen(utf8_decode($str));
            if ( $offset > $strlen ) {
                return '';
            }

            if ( ( $offset + $length ) > $strlen ) {
               $length = '*';
            } else {
                $length = '{'.$length.'}';
            }
        }

        if ( !preg_match('/^[0-9]+$/', $offset) ) {
            trigger_error('utf8_substr expects parameter 2 to be long', E_USER_WARNING);
            return FALSE;
        }

        $pattern = '/^.{'.$offset.'}(.'.$length.')/us';

        preg_match($pattern, $str, $matches);

        if ( isset($matches[1]) ) {
            return $matches[1];
        }

        return FALSE;

    } else {

        // Handle negatives using different, slower technique
        // From: http://www.php.net/manual/en/function.substr.php#44838
        preg_match_all('/./u', $str, $ar);
        if( $length !== NULL ) {
            return join('',array_slice($ar[0],$offset,$length));
        } else {
            return join('',array_slice($ar[0],$offset));
        }
    }
}
