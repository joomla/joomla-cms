<?php
/**
* @version $Id: substr_replace.php,v 1.1 2006/02/25 13:50:17 harryf Exp $
* @package utf8
* @subpackage strings
*/

//---------------------------------------------------------------
/**
* UTF-8 aware substr_replace.
* Note: requires utf8_substr to be loaded
* @see http://www.php.net/substr_replace
* @see utf8_strlen
* @see utf8_substr
*/
function utf8_substr_replace($str, $repl, $start , $length = NULL ) {
    preg_match_all('/./us', $str, $ar);
    preg_match_all('/./us', $repl, $rar);
    if( $length === NULL ) {
        $length = utf8_strlen($str);
    }
    array_splice( $ar[0], $start, $length, $rar[0] );
    return join('',$ar[0]);
}
