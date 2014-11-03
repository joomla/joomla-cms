<?php
/**
* @package utf8
*/

//---------------------------------------------------------------
/**
* UTF-8 aware alternative to strrev
* Reverse a string
* @param string UTF-8 encoded
* @return string characters in string reverses
* @see http://www.php.net/strrev
* @package utf8
*/
function utf8_strrev($str){
    preg_match_all('/./us', $str, $ar);
    return join('',array_reverse($ar[0]));
}

