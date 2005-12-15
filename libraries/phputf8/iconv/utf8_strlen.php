<?php
// Assumes iconv internal encoding is set to UTF-8
/**
* Returns the number of characters in a string using
* iconv extension
*/
function utf8_strlen($str){
    return iconv_strlen($str);
}
