<?php
/**
* @version $Id: utf8_strlen.php,v 1.1.1.1 2005/07/04 22:30:10 harryf Exp $
* @package utf8
* @subpackage strings
*/

//--------------------------------------------------------------------
/**
* Unicode aware replacement for strlen(). Returns the number
* of characters in the string (not the number of bytes), replacing
* multibyte characters with a single byte equivalent
* utf8_decode() converts characters that are not in ISO-8859-1
* to '?', which, for the purpose of counting, is alright - It's
* even faster than mb_strlen.
* Note: this function does not count bad bytes in the string - these
* are simply ignored
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
?>