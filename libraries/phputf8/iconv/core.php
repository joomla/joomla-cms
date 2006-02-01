<?php
//--------------------------------------------------------------------
// Assumes iconv internal encoding is set to UTF-8
/**
* Wrapper around iconv_strpos
*/
function utf8_strpos($str, $search, $offset = FALSE){
    if ( $offset === FALSE ) {
        return iconv_strpos($str, $search);
    } else {
        return iconv_strpos($str, $search, $offset);
    }
}

//--------------------------------------------------------------------
/**
* Wrapper around iconv_strrpos
*/
function utf8_strrpos($str, $search, $offset = FALSE){
    if ( $offset === FALSE ) {
        return iconv_strrpos($str, $search);
    } else {
        return iconv_strrpos($str, $search, $offset);
    }
}

//--------------------------------------------------------------------
/**
* Wrapper around iconv_substr
*/
function utf8_substr($str, $offset, $length = FALSE){

	if ( $length === FALSE ) {
		return iconv_substr($str, $offset);
    } else {
    	// supressed for now due to the problems in PHP 5.0
		return @iconv_substr($str, $offset, $length);
    }
}
?>