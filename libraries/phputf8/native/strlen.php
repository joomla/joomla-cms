<?php
/**
* @version $Id: strlen.php,v 1.4 2006/02/26 23:59:50 harryf Exp $
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
* Unicode aware replacement for strlen(). Returns the number
* of characters in the string (not the number of bytes), replacing
* multibyte characters with a single byte equivalent
* utf8_decode() converts characters that are not in ISO-8859-1
* to '?', which, for the purpose of counting, is alright - It's
* much faster than iconv_strlen
* Note: this function does not count bad UTF-8 bytes in the string
* - these are simply ignored
* @author <chernyshevsky at hotmail dot com>
* @link   http://www.php.net/manual/en/function.strlen.php
* @link   http://www.php.net/manual/en/function.utf8-decode.php
* @param string UTF-8 string
* @return int number of UTF-8 characters in string
* @package utf8
* @subpackage strings
*/
function utf8_strlen($str){
    return strlen(utf8_decode($str));
}
