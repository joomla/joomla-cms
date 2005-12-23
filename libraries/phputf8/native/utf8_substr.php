<?php
/**
* @version $Id: utf8_substr.php,v 1.1.1.1 2005/07/04 22:30:10 harryf Exp $
* @package utf8
* @subpackage strings
*/

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
                trigger_error(E_USER_ERROR, 'utf8_substr: Length must be an integer');
                return FALSE;
            }
            $length = '{'.$length.'}';
        }

        if ( !preg_match('/^[0-9]+$/', $offset) ) {
            trigger_error('E_USER_ERROR', 'utf8_substr: Offset must be an integer');
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

