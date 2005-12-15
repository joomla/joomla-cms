<?php
/**
* @version $Id: utf8_strrpos.php,v 1.2 2005/07/12 07:44:02 harryf Exp $
* @package utf8
* @subpackage strings
*/

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
            trigger_error('utf8_strrpos: Offset must be an integer',E_USER_ERROR);
            return FALSE;
        }
        
        $str = utf8_substr($str, $offset);
        
        if ( FALSE !== ( $pos = utf8_strrpos($str, $needle) ) ) {
            return $pos + $offset;
        }
        
        return FALSE;
    }
    
}