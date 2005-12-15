<?php
/**
* @version $Id: utf8_strpos.php,v 1.1.1.1 2005/07/04 22:30:10 harryf Exp $
* @package utf8
* @subpackage strings
*/

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