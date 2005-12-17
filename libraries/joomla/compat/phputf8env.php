<?php
/**
 * PHP mbstring and iconv local configuration 
 */
 

// check if mbstring extension is loaded and attempt to load it if not present except for windows
if (extension_loaded('mbstring') || ((!strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && dl('mbstring.so')))) {
	ini_set('mbstring.language', 'Neutral');
	ini_set('mbstring.internal_encoding', 'UTF-8');
	ini_set('mbstring.http_input', 'UTF-8');
	ini_set('mbstring.http_output', 'UTF-8');
}

// same for iconv
if (function_exists('iconv') || ((!strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && dl('iconv.so')))) {
   	// these are settings that can be set inside code
	iconv_set_encoding("internal_encoding", "UTF-8");
	iconv_set_encoding("input_encoding", "UTF-8");
	iconv_set_encoding("output_encoding", "UTF-8");
}

?>
