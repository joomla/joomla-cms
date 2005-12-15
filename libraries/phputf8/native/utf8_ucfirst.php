<?php
/**
* @version $Id: utf8_ucfirst.php,v 1.1.1.1 2005/07/04 22:30:10 harryf Exp $
* @package utf8
* @subpackage strings
*/

//---------------------------------------------------------------
/**
* UTF-8 aware alternative to ucfirst
* Make a string's first character uppercase
* Note: requires utf8_strtoupper
* @param string
* @return string with first character as upper case (if applicable)
* @see http://www.php.net/ucfirst
* @see utf8_strtoupper
* @package utf8
* @subpackage strings
*/
function utf8_ucfirst($str){
    
    preg_match('/^(\w{1})(.*)$/us', $str, $matches);
    
    if ( isset($matches[1]) && isset($matches[2]) ) {
        return utf8_strtoupper($matches[1]).$matches[2];
    } else {
        return $str;
    }
    
}

