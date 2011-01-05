<?php
/**
* @version $Id$
* @package utf8
* @subpackage strings
*/

//---------------------------------------------------------------
/**
* UTF-8 aware alternative to ord
* Returns the unicode ordinal for a character
* @param string UTF-8 encoded character
* @return int unicode ordinal for the character
* @see http://www.php.net/ord
* @see http://www.php.net/manual/en/function.ord.php#46267
*/
function utf8_ord($chr) {

    $ord0 = ord($chr);

    if ( $ord0 >= 0 && $ord0 <= 127 ) {
        return $ord0;
    }

    if ( !isset($chr{1}) ) {
        trigger_error('Short sequence - at least 2 bytes expected, only 1 seen');
        return FALSE;
    }

    $ord1 = ord($chr{1});
    if ( $ord0 >= 192 && $ord0 <= 223 ) {
        return ( $ord0 - 192 ) * 64
            + ( $ord1 - 128 );
    }

    if ( !isset($chr{2}) ) {
        trigger_error('Short sequence - at least 3 bytes expected, only 2 seen');
        return FALSE;
    }
    $ord2 = ord($chr{2});
    if ( $ord0 >= 224 && $ord0 <= 239 ) {
        return ($ord0-224)*4096
            + ($ord1-128)*64
                + ($ord2-128);
    }

    if ( !isset($chr{3}) ) {
        trigger_error('Short sequence - at least 4 bytes expected, only 3 seen');
        return FALSE;
    }
    $ord3 = ord($chr{3});
    if ($ord0>=240 && $ord0<=247) {
        return ($ord0-240)*262144
            + ($ord1-128)*4096
                + ($ord2-128)*64
                    + ($ord3-128);

    }

    if ( !isset($chr{4}) ) {
        trigger_error('Short sequence - at least 5 bytes expected, only 4 seen');
        return FALSE;
    }
    $ord4 = ord($chr{4});
    if ($ord0>=248 && $ord0<=251) {
        return ($ord0-248)*16777216
            + ($ord1-128)*262144
                + ($ord2-128)*4096
                    + ($ord3-128)*64
                        + ($ord4-128);
    }

    if ( !isset($chr{5}) ) {
        trigger_error('Short sequence - at least 6 bytes expected, only 5 seen');
        return FALSE;
    }
    if ($ord0>=252 && $ord0<=253) {
        return ($ord0-252) * 1073741824
            + ($ord1-128)*16777216
                + ($ord2-128)*262144
                    + ($ord3-128)*4096
                        + ($ord4-128)*64
                            + (ord($chr{5})-128);
    }

    if ( $ord0 >= 254 && $ord0 <= 255 ) {
        trigger_error('Invalid UTF-8 with surrogate ordinal '.$ord0);
        return FALSE;
    }

}

