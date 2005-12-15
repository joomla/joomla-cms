<?php
//--------------------------------------------------------------------
// Assumes mbstring internal encoding is set to UTF-8
/**
* Wrapper around mb_strtolower
*/
function utf8_strtolower($str){
    return mb_strtolower($str);
}

//--------------------------------------------------------------------
/**
* Wrapper around mb_strtoupper
*/
function utf8_strtoupper($str){
    return mb_strtoupper($str);
}
