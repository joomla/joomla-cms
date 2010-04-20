<?php
/**
* Tools to help with ASCII in UTF-8
* @version $Id$
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
* @subpackage ascii
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
* @subpackage ascii
*/
function utf8_accents_to_ascii( $str, $case=0 ){

    static $UTF8_LOWER_ACCENTS = NULL;
    static $UTF8_UPPER_ACCENTS = NULL;

    if($case <= 0){

        if ( is_null($UTF8_LOWER_ACCENTS) ) {
            $UTF8_LOWER_ACCENTS = array(
  'à' => 'a', 'ô' => 'o', 'ď' => 'd', 'ḟ' => 'f', 'ë' => 'e', 'š' => 's', 'ơ' => 'o',
  'ß' => 'ss', 'ă' => 'a', 'ř' => 'r', 'ț' => 't', 'ň' => 'n', 'ā' => 'a', 'ķ' => 'k',
  'ŝ' => 's', 'ỳ' => 'y', 'ņ' => 'n', 'ĺ' => 'l', 'ħ' => 'h', 'ṗ' => 'p', 'ó' => 'o',
  'ú' => 'u', 'ě' => 'e', 'é' => 'e', 'ç' => 'c', 'ẁ' => 'w', 'ċ' => 'c', 'õ' => 'o',
  'ṡ' => 's', 'ø' => 'o', 'ģ' => 'g', 'ŧ' => 't', 'ș' => 's', 'ė' => 'e', 'ĉ' => 'c',
  'ś' => 's', 'î' => 'i', 'ű' => 'u', 'ć' => 'c', 'ę' => 'e', 'ŵ' => 'w', 'ṫ' => 't',
  'ū' => 'u', 'č' => 'c', 'ö' => 'oe', 'è' => 'e', 'ŷ' => 'y', 'ą' => 'a', 'ł' => 'l',
  'ų' => 'u', 'ů' => 'u', 'ş' => 's', 'ğ' => 'g', 'ļ' => 'l', 'ƒ' => 'f', 'ž' => 'z',
  'ẃ' => 'w', 'ḃ' => 'b', 'å' => 'a', 'ì' => 'i', 'ï' => 'i', 'ḋ' => 'd', 'ť' => 't',
  'ŗ' => 'r', 'ä' => 'ae', 'í' => 'i', 'ŕ' => 'r', 'ê' => 'e', 'ü' => 'ue', 'ò' => 'o',
  'ē' => 'e', 'ñ' => 'n', 'ń' => 'n', 'ĥ' => 'h', 'ĝ' => 'g', 'đ' => 'd', 'ĵ' => 'j',
  'ÿ' => 'y', 'ũ' => 'u', 'ŭ' => 'u', 'ư' => 'u', 'ţ' => 't', 'ý' => 'y', 'ő' => 'o',
  'â' => 'a', 'ľ' => 'l', 'ẅ' => 'w', 'ż' => 'z', 'ī' => 'i', 'ã' => 'a', 'ġ' => 'g',
  'ṁ' => 'm', 'ō' => 'o', 'ĩ' => 'i', 'ù' => 'u', 'į' => 'i', 'ź' => 'z', 'á' => 'a',
  'û' => 'u', 'þ' => 'th', 'ð' => 'dh', 'æ' => 'ae', 'µ' => 'u', 'ĕ' => 'e',
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
  'À' => 'A', 'Ô' => 'O', 'Ď' => 'D', 'Ḟ' => 'F', 'Ë' => 'E', 'Š' => 'S', 'Ơ' => 'O',
  'Ă' => 'A', 'Ř' => 'R', 'Ț' => 'T', 'Ň' => 'N', 'Ā' => 'A', 'Ķ' => 'K',
  'Ŝ' => 'S', 'Ỳ' => 'Y', 'Ņ' => 'N', 'Ĺ' => 'L', 'Ħ' => 'H', 'Ṗ' => 'P', 'Ó' => 'O',
  'Ú' => 'U', 'Ě' => 'E', 'É' => 'E', 'Ç' => 'C', 'Ẁ' => 'W', 'Ċ' => 'C', 'Õ' => 'O',
  'Ṡ' => 'S', 'Ø' => 'O', 'Ģ' => 'G', 'Ŧ' => 'T', 'Ș' => 'S', 'Ė' => 'E', 'Ĉ' => 'C',
  'Ś' => 'S', 'Î' => 'I', 'Ű' => 'U', 'Ć' => 'C', 'Ę' => 'E', 'Ŵ' => 'W', 'Ṫ' => 'T',
  'Ū' => 'U', 'Č' => 'C', 'Ö' => 'Oe', 'È' => 'E', 'Ŷ' => 'Y', 'Ą' => 'A', 'Ł' => 'L',
  'Ų' => 'U', 'Ů' => 'U', 'Ş' => 'S', 'Ğ' => 'G', 'Ļ' => 'L', 'Ƒ' => 'F', 'Ž' => 'Z',
  'Ẃ' => 'W', 'Ḃ' => 'B', 'Å' => 'A', 'Ì' => 'I', 'Ï' => 'I', 'Ḋ' => 'D', 'Ť' => 'T',
  'Ŗ' => 'R', 'Ä' => 'Ae', 'Í' => 'I', 'Ŕ' => 'R', 'Ê' => 'E', 'Ü' => 'Ue', 'Ò' => 'O',
  'Ē' => 'E', 'Ñ' => 'N', 'Ń' => 'N', 'Ĥ' => 'H', 'Ĝ' => 'G', 'Đ' => 'D', 'Ĵ' => 'J',
  'Ÿ' => 'Y', 'Ũ' => 'U', 'Ŭ' => 'U', 'Ư' => 'U', 'Ţ' => 'T', 'Ý' => 'Y', 'Ő' => 'O',
  'Â' => 'A', 'Ľ' => 'L', 'Ẅ' => 'W', 'Ż' => 'Z', 'Ī' => 'I', 'Ã' => 'A', 'Ġ' => 'G',
  'Ṁ' => 'M', 'Ō' => 'O', 'Ĩ' => 'I', 'Ù' => 'U', 'Į' => 'I', 'Ź' => 'Z', 'Á' => 'A',
  'Û' => 'U', 'Þ' => 'Th', 'Ð' => 'Dh', 'Æ' => 'Ae', 'Ĕ' => 'E',
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
