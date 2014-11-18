<?php
/**
* @package utf8
*/

//---------------------------------------------------------------
/**
* UTF-8 aware alternative to stristr
* Find first occurrence of a string using case insensitive comparison
* Note: requires utf8_strtolower
* @param string
* @param string
* @return int
* @see http://www.php.net/strcasecmp
* @see utf8_strtolower
* @package utf8
*/
function utf8_stristr($str, $search) {

    if ( strlen($search) == 0 ) {
        return $str;
    }

    $lstr = utf8_strtolower($str);
    $lsearch = utf8_strtolower($search);
    //JOOMLA SPECIFIC FIX - BEGIN
    preg_match('/^(.*)'.preg_quote($lsearch, '/').'/Us',$lstr, $matches);
    //JOOMLA SPECIFIC FIX - END

    if ( count($matches) == 2 ) {
        return substr($str, strlen($matches[1]));
    }

    return FALSE;
}
