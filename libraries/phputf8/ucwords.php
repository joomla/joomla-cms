<?php
/**
* @version $Id$
* @package utf8
* @subpackage strings
*/

//---------------------------------------------------------------
/**
* UTF-8 aware alternative to ucwords
* Uppercase the first character of each word in a string
* Note: requires utf8_substr_replace and utf8_strtoupper
* @param string
* @return string with first char of each word uppercase
* @see http://www.php.net/ucwords
* @package utf8
* @subpackage strings
*/
function utf8_ucwords($str) {
    // Note: [\x0c\x09\x0b\x0a\x0d\x20] matches;
    // form feeds, horizontal tabs, vertical tabs, linefeeds and carriage returns
    // This corresponds to the definition of a "word" defined at http://www.php.net/ucwords
    $pattern = '/(^|([\x0c\x09\x0b\x0a\x0d\x20]+))([^\x0c\x09\x0b\x0a\x0d\x20]{1})[^\x0c\x09\x0b\x0a\x0d\x20]*/u';
    return preg_replace_callback($pattern, 'utf8_ucwords_callback',$str);
}

//---------------------------------------------------------------
/**
* Callback function for preg_replace_callback call in utf8_ucwords
* You don't need to call this yourself
* @param array of matches corresponding to a single word
* @return string with first char of the word in uppercase
* @see utf8_ucwords
* @see utf8_strtoupper
* @package utf8
* @subpackage strings
*/
function utf8_ucwords_callback($matches) {
    $leadingws = $matches[2];
    $ucfirst = utf8_strtoupper($matches[3]);
    $ucword = utf8_substr_replace(ltrim($matches[0]),$ucfirst,0,1);
    return $leadingws . $ucword;
}

