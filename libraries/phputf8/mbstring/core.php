<?php
//--------------------------------------------------------------------
// Assumes mbstring internal encoding is set to UTF-8
/**
* Wrapper around mb_strpos
*/
function utf8_strpos($str, $search, $offset = FALSE){
    if ( $offset === FALSE ) {
        return mb_strpos($str, $search);
    } else {
        return mb_strpos($str, $search, $offset);
    }
}

//--------------------------------------------------------------------
/**
* Wrapper around mb_strrpos
*/
function utf8_strrpos($str, $search, $offset = FALSE){
    if ( $offset === FALSE ) {
        return mb_strrpos($str, $search);
    } else {
        return mb_strrpos($str, $search, $offset);
    }
}

//--------------------------------------------------------------------
/**
* Wrapper around mb_substr
*/
function utf8_substr($str, $offset, $length = FALSE){
    if ( $length === FALSE ) {
        return mb_substr($str, $offset);
    } else {
        return mb_substr($str, $offset, $length);
    }
}
?>