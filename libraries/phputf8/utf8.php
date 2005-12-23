<?php
/**
* This is the dynamic loader for the library. It checks whether you have
* the iconv or mbstring extensions available and includes relevant files
* on that basis, falling back to the native (as in written in PHP) version
* if iconv / mbstring is unavailabe.
*
* It makes sure the the following functions are available;
* utf8_strlen, utf8_strpos, utf8_strrpos, utf8_substr,
* utf8_strtolower, utf8_strtoupper
* Other functions in the ./native directory depend on these
* six functions being available
* @package utf8
*/

/**
* Put the current directory in this variable
*/
$UTF8_DIR = dirname(__FILE__);

/**
* If string overloading is active, it will break many of the
* native implementations. mbstring.func_overload must be set
* to 0, 1 or 4 in php.ini (string overloading disabled).
* Also need to check we have the correct internal mbstring
* encoding
*/
if ( extension_loaded('mbstring')) {
    if ( ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING ) {
        trigger_error('String functions are overloaded by mbstring',E_USER_ERROR);
    }

    if ( mb_internal_encoding() != 'UTF-8' ) {
        trigger_error('mbstring internal encoding is not set to UTF-8',E_USER_ERROR);
    }
}

/**
* iconv_strlen only available in PHP 5+. Check the iconv encoding
* setting is correctly set as well.
* Note that there is no use of mb_strlen as it's slower
* than the native implementation
*/
if ( function_exists('iconv_strlen') ) {
     if ( iconv_get_encoding('internal_encoding') != 'UTF-8' ) {
        trigger_error('iconv internal encoding is not set to UTF-8',E_USER_ERROR);
    }
    require_once $UTF8_DIR . '/iconv/utf8_strlen.php';
} else {
    require_once $UTF8_DIR . '/native/utf8_strlen.php';
}

/**
* Load the smartest implementations of utf8_strpos, utf8_strrpos
* and utf8_substr
*/
if ( function_exists('iconv_substr') ) {
    require_once $UTF8_DIR . '/iconv/core.php';
} else if ( function_exists('mb_substr') ) {
    require_once $UTF8_DIR . '/mbstring/core.php';
} else {
    require_once $UTF8_DIR . '/native/utf8_strpos.php';
    require_once $UTF8_DIR . '/native/utf8_strrpos.php';
    require_once $UTF8_DIR . '/native/utf8_substr.php';
}

/**
* Load the smartest implementations of utf8_strtolower and
* utf8_strtoupper
*/
if ( function_exists('mb_strtolower') ) {
    require_once $UTF8_DIR . '/mbstring/case.php';
} else {
    require_once $UTF8_DIR . '/utf8_unicode.php';
    require_once $UTF8_DIR . '/native/utf8_strtolower.php';
    require_once $UTF8_DIR . '/native/utf8_strtoupper.php';
}

/**
* Load the native implementation of utf8_substr_replace
*/
require_once $UTF8_DIR . '/native/utf8_substr_replace.php';

/**
* You should now be able to use all the other functions
* in the native directory
*/
