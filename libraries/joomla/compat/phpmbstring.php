<?php
/**
 * PHP mbstring Compatibility 
 */
 
// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

// check if mbstring extension is loaded and attempt to load it if not present
if (!extension_loaded('mbstring')) {
   	if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    	dl('php_mbstring.dll');
   	} else {
       	dl('mbstring.so');
   	}
   	// these are settings that can be set inside code
	ini_set('mbstring.language', 'Neutral');
	ini_set('mbstring.internal_encoding', 'UTF-8');
	ini_set('mbstring.http_input', 'UTF-8');
	ini_set('mbstring.http_output', 'UTF-8');
}
 
?>
