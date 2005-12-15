<?php
/**
* Tools to help with ASCII in UTF-8
* @version $Id: utf8_ascii.php,v 1.2 2005/12/08 11:53:34 harryf Exp $
* @package utf8
* @subpackage ascii
*/

//--------------------------------------------------------------------
/**
* Tests whether a string contains only 7bit ASCII bytes.
* You might use this to conditionally check whether a string
* needs handling as UTF-8 or not, potentially offering performance
* benefits by using the native PHP equivalent if it's just ASCII e.g.;
*
* <code>
* <?php
* if ( utf8_is_ascii($someString) ) {
*     // It's just ASCII - use the native PHP version
*     $someString = strtolower($someString);
* } else {
*     $someString = utf8_strtolower($someString);
* }
* ?>
* </code>
* 
* @param string
* @return boolean TRUE if it's all ASCII
* @package utf8
* @subpackage ascii
* @see utf8_is_ascii_ctrl
*/
function utf8_is_ascii($str) {
    if ( strlen($str) > 0 ) {
        // Search for any bytes which are outside the ASCII range...
        return (preg_match('/[^\x00-\x7F]/',$str) !== 1);
    }
    return FALSE;
}

//--------------------------------------------------------------------
/**
* Tests whether a string contains only 7bit ASCII bytes with device
* control codes omitted. The device control codes can be found on the
* second table here: http://www.w3schools.com/tags/ref_ascii.asp
* 
* @param string
* @return boolean TRUE if it's all ASCII without device control codes
* @package utf8
* @subpackage ascii
* @see utf8_is_ascii
*/
function utf8_is_ascii_ctrl($str) {
    if ( strlen($str) > 0 ) {
        // Search for any bytes which are outside the ASCII range,
        // or are device control codes
        return (preg_match('/[^\x09\x0A\x0D\x20-\x7E]/',$str) !== 1);
    }
    return FALSE;
}

//--------------------------------------------------------------------
/**
* Strip out all non-7bit ASCII bytes
* If you need to transmit a string to system which you know can only
* support 7bit ASCII, you could use this function.
* @param string
* @return string with non ASCII bytes removed
* @package utf8
* @subpackage ascii
* @see utf8_strip_non_ascii_ctrl
*/
function utf8_strip_non_ascii($str) {
    ob_start();
    while ( preg_match(
        '/^([\x00-\x7F]+)|([^\x00-\x7F]+)/S',
            $str, $matches) ) {
        if ( !isset($matches[2]) ) {
            echo $matches[0];
        }
        $str = substr($str, strlen($matches[0]));
    }
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}

//--------------------------------------------------------------------
/**
* Strip out all non 7bit ASCII bytes and ASCII device control codes.
* For a list of ASCII device control codes see the 2nd table here:
* http://www.w3schools.com/tags/ref_ascii.asp
* 
* @param string
* @return boolean TRUE if it's all ASCII
* @package utf8
* @subpackage ascii
*/
function utf8_strip_non_ascii_ctrl($str) {
    ob_start();
    while ( preg_match(
        '/^([\x09\x0A\x0D\x20-\x7E]+)|([^\x09\x0A\x0D\x20-\x7E]+)/S',
            $str, $matches) ) {
        if ( !isset($matches[2]) ) {
            echo $matches[0];
        }
        $str = substr($str, strlen($matches[0]));
    }
    $result = ob_get_contents();
    ob_end_clean();
    return $result;
}

//---------------------------------------------------------------
/**
* Replace accented UTF-8 characters by unaccented ASCII-7 "equivalents".
* The purpose of this function is to replace characters commonly found in Latin
* alphabets with something more or less equivalent from the ASCII range. This can
* be useful for converting a UTF-8 to something ready for a filename, for example.
* Following the use of this function, you would probably also pass the string
* through utf8_strip_non_ascii to clean out any other non-ASCII chars
* Use the optional parameter to just deaccent lower ($case = -1) or upper ($case = 1)
* letters. Default is to deaccent both cases ($case = 0)
* @param string UTF-8 string
* @param int (optional) -1 lowercase only, +1 uppercase only, 1 both cases
* @param string UTF-8 with accented characters replaced by ASCII chars
* @return string accented chars replaced with ascii equivalents
* @author Andreas Gohr <andi@splitbrain.org>
* @package utf8
* @subpackage ascii
*/
function utf8_accents_to_ascii( $str, $case=0 ){
    
    if($case <= 0){
        global $UTF8_LOWER_ACCENTS;
        $string = str_replace(
                array_keys($UTF8_LOWER_ACCENTS),
                array_values($UTF8_LOWER_ACCENTS),
                $str
            );
    }
    
    if($case >= 0){
        global $UTF8_UPPER_ACCENTS;
        $string = str_replace(
                array_keys($UTF8_UPPER_ACCENTS),
                array_values($UTF8_UPPER_ACCENTS),
                $str
            );
    }
    
    return $str;
    
}

//---------------------------------------------------------------
/**
* UTF-8 lookup table for lower case accented letters
* This lookuptable defines replacements for accented characters from the ASCII-7
* range. This are lower case letters only.
* @author Andreas Gohr <andi@splitbrain.org>
* @see utf8_deaccent()
* @package utf8
* @subpackage ascii
*/
$UTF8_LOWER_ACCENTS = array(
    'Ã ' => 'a', 'Ã´' => 'o', 'Ä' => 'd', 'á¸Ÿ' => 'f', 'Ã«' => 'e', 'Å¡' => 's', 'Æ¡' => 'o', 
    'ÃŸ' => 'ss', 'Äƒ' => 'a', 'Å™' => 'r', 'È›' => 't', 'Åˆ' => 'n', 'Ä' => 'a', 'Ä·' => 'k', 
    'Å' => 's', 'á»³' => 'y', 'Å†' => 'n', 'Äº' => 'l', 'Ä§' => 'h', 'á¹—' => 'p', 'Ã³' => 'o', 
    'Ãº' => 'u', 'Ä›' => 'e', 'Ã©' => 'e', 'Ã§' => 'c', 'áº' => 'w', 'Ä‹' => 'c', 'Ãµ' => 'o', 
    'á¹¡' => 's', 'Ã¸' => 'o', 'Ä£' => 'g', 'Å§' => 't', 'È™' => 's', 'Ä—' => 'e', 'Ä‰' => 'c', 
    'Å›' => 's', 'Ã®' => 'i', 'Å±' => 'u', 'Ä‡' => 'c', 'Ä™' => 'e', 'Åµ' => 'w', 'á¹«' => 't', 
    'Å«' => 'u', 'Ä' => 'c', 'Ã¶' => 'oe', 'Ã¨' => 'e', 'Å·' => 'y', 'Ä…' => 'a', 'Å‚' => 'l', 
    'Å³' => 'u', 'Å¯' => 'u', 'ÅŸ' => 's', 'ÄŸ' => 'g', 'Ä¼' => 'l', 'Æ’' => 'f', 'Å¾' => 'z', 
    'áºƒ' => 'w', 'á¸ƒ' => 'b', 'Ã¥' => 'a', 'Ã¬' => 'i', 'Ã¯' => 'i', 'á¸‹' => 'd', 'Å¥' => 't', 
    'Å—' => 'r', 'Ã¤' => 'ae', 'Ã­' => 'i', 'Å•' => 'r', 'Ãª' => 'e', 'Ã¼' => 'ue', 'Ã²' => 'o', 
    'Ä“' => 'e', 'Ã±' => 'n', 'Å„' => 'n', 'Ä¥' => 'h', 'Ä' => 'g', 'Ä‘' => 'd', 'Äµ' => 'j', 
    'Ã¿' => 'y', 'Å©' => 'u', 'Å­' => 'u', 'Æ°' => 'u', 'Å£' => 't', 'Ã½' => 'y', 'Å‘' => 'o', 
    'Ã¢' => 'a', 'Ä¾' => 'l', 'áº…' => 'w', 'Å¼' => 'z', 'Ä«' => 'i', 'Ã£' => 'a', 'Ä¡' => 'g', 
    'á¹' => 'm', 'Å' => 'o', 'Ä©' => 'i', 'Ã¹' => 'u', 'Ä¯' => 'i', 'Åº' => 'z', 'Ã¡' => 'a', 
    'Ã»' => 'u', 'Ã¾' => 'th', 'Ã°' => 'dh', 'Ã¦' => 'ae', 'Âµ' => 'u',
);

//---------------------------------------------------------------
/**
* UTF-8 lookup table for upper case accented letters
* This lookuptable defines replacements for accented characters from the ASCII-7
* range. This are upper case letters only.
* @author Andreas Gohr <andi@splitbrain.org>
* @see utf8_deaccent()
* @package utf8
* @subpackage ascii
 */
$UTF8_UPPER_ACCENTS = array(
    'Ã ' => 'A', 'Ã´' => 'O', 'Ä' => 'D', 'á¸Ÿ' => 'F', 'Ã«' => 'E', 'Å¡' => 'S', 'Æ¡' => 'O', 
    'ÃŸ' => 'Ss', 'Äƒ' => 'A', 'Å™' => 'R', 'È›' => 'T', 'Åˆ' => 'N', 'Ä' => 'A', 'Ä·' => 'K', 
    'Å' => 'S', 'á»³' => 'Y', 'Å†' => 'N', 'Äº' => 'L', 'Ä§' => 'H', 'á¹—' => 'P', 'Ã³' => 'O', 
    'Ãº' => 'U', 'Ä›' => 'E', 'Ã©' => 'E', 'Ã§' => 'C', 'áº' => 'W', 'Ä‹' => 'C', 'Ãµ' => 'O', 
    'á¹¡' => 'S', 'Ã¸' => 'O', 'Ä£' => 'G', 'Å§' => 'T', 'È™' => 'S', 'Ä—' => 'E', 'Ä‰' => 'C', 
    'Å›' => 'S', 'Ã®' => 'I', 'Å±' => 'U', 'Ä‡' => 'C', 'Ä™' => 'E', 'Åµ' => 'W', 'á¹«' => 'T', 
    'Å«' => 'U', 'Ä' => 'C', 'Ã¶' => 'Oe', 'Ã¨' => 'E', 'Å·' => 'Y', 'Ä…' => 'A', 'Å‚' => 'L', 
    'Å³' => 'U', 'Å¯' => 'U', 'ÅŸ' => 'S', 'ÄŸ' => 'G', 'Ä¼' => 'L', 'Æ’' => 'F', 'Å¾' => 'Z', 
    'áºƒ' => 'W', 'á¸ƒ' => 'B', 'Ã¥' => 'A', 'Ã¬' => 'I', 'Ã¯' => 'I', 'á¸‹' => 'D', 'Å¥' => 'T', 
    'Å—' => 'R', 'Ã¤' => 'Ae', 'Ã­' => 'I', 'Å•' => 'R', 'Ãª' => 'E', 'Ã¼' => 'Ue', 'Ã²' => 'O', 
    'Ä“' => 'E', 'Ã±' => 'N', 'Å„' => 'N', 'Ä¥' => 'H', 'Ä' => 'G', 'Ä‘' => 'D', 'Äµ' => 'J', 
    'Ã¿' => 'Y', 'Å©' => 'U', 'Å­' => 'U', 'Æ°' => 'U', 'Å£' => 'T', 'Ã½' => 'Y', 'Å‘' => 'O', 
    'Ã¢' => 'A', 'Ä¾' => 'L', 'áº…' => 'W', 'Å¼' => 'Z', 'Ä«' => 'I', 'Ã£' => 'A', 'Ä¡' => 'G', 
    'á¹' => 'M', 'Å' => 'O', 'Ä©' => 'I', 'Ã¹' => 'U', 'Ä¯' => 'I', 'Åº' => 'Z', 'Ã¡' => 'A', 
    'Ã»' => 'U', 'Ãž' => 'Th', 'Ã' => 'Dh', 'Ã†' => 'Ae',
);
