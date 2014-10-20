<?php
/**
* Tools to help with ASCII in UTF-8
*
* @package utf8
*/

//--------------------------------------------------------------------
/**
* Tests whether a string contains only 7bit ASCII bytes.
* You might use this to conditionally check whether a string
* needs handling as UTF-8 or not, potentially offering performance
* benefits by using the native PHP equivalent if it's just ASCII e.g.;
*
* <code>
* if ( utf8_is_ascii($someString) ) {
*     // It's just ASCII - use the native PHP version
*     $someString = strtolower($someString);
* } else {
*     $someString = utf8_strtolower($someString);
* }
* </code>
*
* @param string
* @return boolean TRUE if it's all ASCII
* @package utf8
* @see utf8_is_ascii_ctrl
*/
function utf8_is_ascii($str) {
    // Search for any bytes which are outside the ASCII range...
    return (preg_match('/(?:[^\x00-\x7F])/',$str) !== 1);
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
* Strip out device control codes in the ASCII range
* which are not permitted in XML. Note that this leaves
* multi-byte characters untouched - it only removes device
* control codes
* @see http://hsivonen.iki.fi/producing-xml/#controlchar
* @param string
* @return string control codes removed
*/
function utf8_strip_ascii_ctrl($str) {
    ob_start();
    while ( preg_match(
        '/^([^\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+)|([\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+)/S',
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
*
* For a more complete implementation of transliteration, see the utf8_to_ascii package
* available from the phputf8 project downloads:
* http://prdownloads.sourceforge.net/phputf8
*
* @param string UTF-8 string
* @param int (optional) -1 lowercase only, +1 uppercase only, 1 both cases
* @param string UTF-8 with accented characters replaced by ASCII chars
* @return string accented chars replaced with ascii equivalents
* @author Andreas Gohr <andi@splitbrain.org>
* @package utf8
*/
function utf8_accents_to_ascii( $str, $case=0 ){

    static $UTF8_LOWER_ACCENTS = NULL;
    static $UTF8_UPPER_ACCENTS = NULL;

    if($case <= 0){

        if ( is_null($UTF8_LOWER_ACCENTS) ) {
            $UTF8_LOWER_ACCENTS = array(
  '�' => 'a', '�' => 'o', 'd' => 'd', '?' => 'f', '�' => 'e', '�' => 's', 'o' => 'o',
  '�' => 'ss', 'a' => 'a', 'r' => 'r', '?' => 't', 'n' => 'n', 'a' => 'a', 'k' => 'k',
  's' => 's', '?' => 'y', 'n' => 'n', 'l' => 'l', 'h' => 'h', '?' => 'p', '�' => 'o',
  '�' => 'u', 'e' => 'e', '�' => 'e', '�' => 'c', '?' => 'w', 'c' => 'c', '�' => 'o',
  '?' => 's', '�' => 'o', 'g' => 'g', 't' => 't', '?' => 's', 'e' => 'e', 'c' => 'c',
  's' => 's', '�' => 'i', 'u' => 'u', 'c' => 'c', 'e' => 'e', 'w' => 'w', '?' => 't',
  'u' => 'u', 'c' => 'c', '�' => 'oe', '�' => 'e', 'y' => 'y', 'a' => 'a', 'l' => 'l',
  'u' => 'u', 'u' => 'u', 's' => 's', 'g' => 'g', 'l' => 'l', '�' => 'f', '�' => 'z',
  '?' => 'w', '?' => 'b', '�' => 'a', '�' => 'i', '�' => 'i', '?' => 'd', 't' => 't',
  'r' => 'r', '�' => 'ae', '�' => 'i', 'r' => 'r', '�' => 'e', '�' => 'ue', '�' => 'o',
  'e' => 'e', '�' => 'n', 'n' => 'n', 'h' => 'h', 'g' => 'g', 'd' => 'd', 'j' => 'j',
  '�' => 'y', 'u' => 'u', 'u' => 'u', 'u' => 'u', 't' => 't', '�' => 'y', 'o' => 'o',
  '�' => 'a', 'l' => 'l', '?' => 'w', 'z' => 'z', 'i' => 'i', '�' => 'a', 'g' => 'g',
  '?' => 'm', 'o' => 'o', 'i' => 'i', '�' => 'u', 'i' => 'i', 'z' => 'z', '�' => 'a',
  '�' => 'u', '�' => 'th', '�' => 'dh', '�' => 'ae', '�' => 'u', 'e' => 'e',
            );
        }

        $str = str_replace(
                array_keys($UTF8_LOWER_ACCENTS),
                array_values($UTF8_LOWER_ACCENTS),
                $str
            );
    }

    if($case >= 0){
        if ( is_null($UTF8_UPPER_ACCENTS) ) {
            $UTF8_UPPER_ACCENTS = array(
  '�' => 'A', '�' => 'O', 'D' => 'D', '?' => 'F', '�' => 'E', '�' => 'S', 'O' => 'O',
  'A' => 'A', 'R' => 'R', '?' => 'T', 'N' => 'N', 'A' => 'A', 'K' => 'K',
  'S' => 'S', '?' => 'Y', 'N' => 'N', 'L' => 'L', 'H' => 'H', '?' => 'P', '�' => 'O',
  '�' => 'U', 'E' => 'E', '�' => 'E', '�' => 'C', '?' => 'W', 'C' => 'C', '�' => 'O',
  '?' => 'S', '�' => 'O', 'G' => 'G', 'T' => 'T', '?' => 'S', 'E' => 'E', 'C' => 'C',
  'S' => 'S', '�' => 'I', 'U' => 'U', 'C' => 'C', 'E' => 'E', 'W' => 'W', '?' => 'T',
  'U' => 'U', 'C' => 'C', '�' => 'Oe', '�' => 'E', 'Y' => 'Y', 'A' => 'A', 'L' => 'L',
  'U' => 'U', 'U' => 'U', 'S' => 'S', 'G' => 'G', 'L' => 'L', '�' => 'F', '�' => 'Z',
  '?' => 'W', '?' => 'B', '�' => 'A', '�' => 'I', '�' => 'I', '?' => 'D', 'T' => 'T',
  'R' => 'R', '�' => 'Ae', '�' => 'I', 'R' => 'R', '�' => 'E', '�' => 'Ue', '�' => 'O',
  'E' => 'E', '�' => 'N', 'N' => 'N', 'H' => 'H', 'G' => 'G', '�' => 'D', 'J' => 'J',
  '�' => 'Y', 'U' => 'U', 'U' => 'U', 'U' => 'U', 'T' => 'T', '�' => 'Y', 'O' => 'O',
  '�' => 'A', 'L' => 'L', '?' => 'W', 'Z' => 'Z', 'I' => 'I', '�' => 'A', 'G' => 'G',
  '?' => 'M', 'O' => 'O', 'I' => 'I', '�' => 'U', 'I' => 'I', 'Z' => 'Z', '�' => 'A',
  '�' => 'U', '�' => 'Th', '�' => 'Dh', '�' => 'Ae', 'E' => 'E',
            );
        }
        $str = str_replace(
                array_keys($UTF8_UPPER_ACCENTS),
                array_values($UTF8_UPPER_ACCENTS),
                $str
            );
    }

    return $str;

}
