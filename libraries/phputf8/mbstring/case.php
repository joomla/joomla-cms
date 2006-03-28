<?php
/**
* @version $Id: case.php,v 1.2 2006/02/25 14:10:37 harryf Exp $
* @package utf8
* @subpackage strings
*/

/**
* Define UTF8_CASE as required
*/
if ( !defined('UTF8_CASE') ) {
    define('UTF8_CASE',TRUE);
}

//--------------------------------------------------------------------
/**
* Assumes mbstring internal encoding is set to UTF-8
* Wrapper around mb_strtolower
* Make a string lowercase
* Note: The concept of a characters "case" only exists is some alphabets
* such as Latin, Greek, Cyrillic, Armenian and archaic Georgian - it does
* not exist in the Chinese alphabet, for example. See Unicode Standard
* Annex #21: Case Mappings
* @param string
* @return mixed either string in lowercase or FALSE is UTF-8 invalid
* @package utf8
* @subpackage strings
*/
function utf8_strtolower($str){
    return mb_strtolower($str);
}

//--------------------------------------------------------------------
/**
* Assumes mbstring internal encoding is set to UTF-8
* Wrapper around mb_strtoupper
* Make a string uppercase
* Note: The concept of a characters "case" only exists is some alphabets
* such as Latin, Greek, Cyrillic, Armenian and archaic Georgian - it does
* not exist in the Chinese alphabet, for example. See Unicode Standard
* Annex #21: Case Mappings
* @param string
* @return mixed either string in lowercase or FALSE is UTF-8 invalid
* @package utf8
* @subpackage strings
*/
function utf8_strtoupper($str){
    return mb_strtoupper($str);
}
