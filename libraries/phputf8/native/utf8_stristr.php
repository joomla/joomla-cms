<?php
/**
* @version $Id: utf8_stristr.php,v 1.1 2005/07/11 12:17:16 harryf Exp $
* @package utf8
* @subpackage strings
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
* @package utf8
* @subpackage strings
*/
function utf8_stristr($str, $search) {

    if ( strlen($search) == 0 ) {
        return $str;
    }

    $lstr = utf8_strtolower($str);
    $lsearch = utf8_strtolower($search);
    preg_match('/^(.*)'.preg_quote($lsearch).'/Us',$lstr, $matches);

    if ( count($matches) == 2 ) {
        return substr($str, strlen($matches[1]));
    }

    return FALSE;
}
