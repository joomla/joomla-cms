<?php
/**
* @version $Id: strlen.php,v 1.1 2006/02/26 22:13:17 harryf Exp $
* @package utf8
* @subpackage strings
*/

/**
* Define UTF8_STRLEN as required
*/
if ( !defined('UTF8_STRLEN') ) {
    define('UTF8_STRLEN',TRUE);
}

//--------------------------------------------------------------------
/**
* Wrapper round mb_strlen
* Assumes you have mb_internal_encoding to UTF-8 already
* Note: this function does not count bad bytes in the string - these
* are simply ignored
* @param string UTF-8 string
* @return int number of UTF-8 characters in string
* @package utf8
* @subpackage strings
*/
function utf8_strlen($str){
    return mb_strlen($str);
}
