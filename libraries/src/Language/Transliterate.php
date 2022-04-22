<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Language;

defined('JPATH_PLATFORM') or die;

/**
 * Class to transliterate strings
 *
 * @since  1.7.0
 * @note   Port of phputf8's utf8_accents_to_ascii()
 */
class Transliterate
{
	/**
	 * Returns strings transliterated from UTF-8 to Latin
	 *
	 * @param   string   $string  String to transliterate
	 * @param   integer  $case    Optionally specify upper or lower case. Default to null.
	 *
	 * @return  string  Transliterated string
	 *
	 * @since   1.7.0
	 */
	public static function utf8_latin_to_ascii($string, $case = 0)
	{
		static $UTF8_LOWER_ACCENTS = null;
		static $UTF8_UPPER_ACCENTS = null;

		if ($case <= 0)
		{
			if (is_null($UTF8_LOWER_ACCENTS))
			{
				$UTF8_LOWER_ACCENTS = array(
					'à' => 'a',
					'ô' => 'o',
					'ď' => 'd',
					'ḟ' => 'f',
					'ë' => 'e',
					'š' => 's',
					'ơ' => 'o',
					'ß' => 'ss',
					'ă' => 'a',
					'ř' => 'r',
					'ț' => 't',
					'ň' => 'n',
					'ā' => 'a',
					'ķ' => 'k',
					'ŝ' => 's',
					'ỳ' => 'y',
					'ņ' => 'n',
					'ĺ' => 'l',
					'ħ' => 'h',
					'ṗ' => 'p',
					'ó' => 'o',
					'ú' => 'u',
					'ě' => 'e',
					'é' => 'e',
					'ç' => 'c',
					'ẁ' => 'w',
					'ċ' => 'c',
					'õ' => 'o',
					'ṡ' => 's',
					'ø' => 'o',
					'ģ' => 'g',
					'ŧ' => 't',
					'ș' => 's',
					'ė' => 'e',
					'ĉ' => 'c',
					'ś' => 's',
					'î' => 'i',
					'ű' => 'u',
					'ć' => 'c',
					'ę' => 'e',
					'ŵ' => 'w',
					'ṫ' => 't',
					'ū' => 'u',
					'č' => 'c',
					'ö' => 'oe',
					'è' => 'e',
					'ŷ' => 'y',
					'ą' => 'a',
					'ł' => 'l',
					'ų' => 'u',
					'ů' => 'u',
					'ş' => 's',
					'ğ' => 'g',
					'ļ' => 'l',
					'ƒ' => 'f',
					'ž' => 'z',
					'ẃ' => 'w',
					'ḃ' => 'b',
					'å' => 'a',
					'ì' => 'i',
					'ï' => 'i',
					'ḋ' => 'd',
					'ť' => 't',
					'ŗ' => 'r',
					'ä' => 'ae',
					'í' => 'i',
					'ŕ' => 'r',
					'ê' => 'e',
					'ü' => 'ue',
					'ò' => 'o',
					'ē' => 'e',
					'ñ' => 'n',
					'ń' => 'n',
					'ĥ' => 'h',
					'ĝ' => 'g',
					'đ' => 'd',
					'ĵ' => 'j',
					'ÿ' => 'y',
					'ũ' => 'u',
					'ŭ' => 'u',
					'ư' => 'u',
					'ţ' => 't',
					'ý' => 'y',
					'ő' => 'o',
					'â' => 'a',
					'ľ' => 'l',
					'ẅ' => 'w',
					'ż' => 'z',
					'ī' => 'i',
					'ã' => 'a',
					'ġ' => 'g',
					'ṁ' => 'm',
					'ō' => 'o',
					'ĩ' => 'i',
					'ù' => 'u',
					'į' => 'i',
					'ź' => 'z',
					'á' => 'a',
					'û' => 'u',
					'þ' => 'th',
					'ð' => 'dh',
					'æ' => 'ae',
					'µ' => 'u',
					'ĕ' => 'e',
					'œ' => 'oe',
				);
			}

			$string = str_replace(array_keys($UTF8_LOWER_ACCENTS), array_values($UTF8_LOWER_ACCENTS), $string);
		}

		if ($case >= 0)
		{
			if (is_null($UTF8_UPPER_ACCENTS))
			{
				$UTF8_UPPER_ACCENTS = array(
					'À' => 'A',
					'Ô' => 'O',
					'Ď' => 'D',
					'Ḟ' => 'F',
					'Ë' => 'E',
					'Š' => 'S',
					'Ơ' => 'O',
					'Ă' => 'A',
					'Ř' => 'R',
					'Ț' => 'T',
					'Ň' => 'N',
					'Ā' => 'A',
					'Ķ' => 'K',
					'Ŝ' => 'S',
					'Ỳ' => 'Y',
					'Ņ' => 'N',
					'Ĺ' => 'L',
					'Ħ' => 'H',
					'Ṗ' => 'P',
					'Ó' => 'O',
					'Ú' => 'U',
					'Ě' => 'E',
					'É' => 'E',
					'Ç' => 'C',
					'Ẁ' => 'W',
					'Ċ' => 'C',
					'Õ' => 'O',
					'Ṡ' => 'S',
					'Ø' => 'O',
					'Ģ' => 'G',
					'Ŧ' => 'T',
					'Ș' => 'S',
					'Ė' => 'E',
					'Ĉ' => 'C',
					'Ś' => 'S',
					'Î' => 'I',
					'Ű' => 'U',
					'Ć' => 'C',
					'Ę' => 'E',
					'Ŵ' => 'W',
					'Ṫ' => 'T',
					'Ū' => 'U',
					'Č' => 'C',
					'Ö' => 'Oe',
					'È' => 'E',
					'Ŷ' => 'Y',
					'Ą' => 'A',
					'Ł' => 'L',
					'Ų' => 'U',
					'Ů' => 'U',
					'Ş' => 'S',
					'Ğ' => 'G',
					'Ļ' => 'L',
					'Ƒ' => 'F',
					'Ž' => 'Z',
					'Ẃ' => 'W',
					'Ḃ' => 'B',
					'Å' => 'A',
					'Ì' => 'I',
					'Ï' => 'I',
					'Ḋ' => 'D',
					'Ť' => 'T',
					'Ŗ' => 'R',
					'Ä' => 'Ae',
					'Í' => 'I',
					'Ŕ' => 'R',
					'Ê' => 'E',
					'Ü' => 'Ue',
					'Ò' => 'O',
					'Ē' => 'E',
					'Ñ' => 'N',
					'Ń' => 'N',
					'Ĥ' => 'H',
					'Ĝ' => 'G',
					'Đ' => 'D',
					'Ĵ' => 'J',
					'Ÿ' => 'Y',
					'Ũ' => 'U',
					'Ŭ' => 'U',
					'Ư' => 'U',
					'Ţ' => 'T',
					'Ý' => 'Y',
					'Ő' => 'O',
					'Â' => 'A',
					'Ľ' => 'L',
					'Ẅ' => 'W',
					'Ż' => 'Z',
					'Ī' => 'I',
					'Ã' => 'A',
					'Ġ' => 'G',
					'Ṁ' => 'M',
					'Ō' => 'O',
					'Ĩ' => 'I',
					'Ù' => 'U',
					'Į' => 'I',
					'Ź' => 'Z',
					'Á' => 'A',
					'Û' => 'U',
					'Þ' => 'Th',
					'Ð' => 'Dh',
					'Æ' => 'Ae',
					'Ĕ' => 'E',
					'Œ' => 'Oe',
				);
			}

			$string = str_replace(array_keys($UTF8_UPPER_ACCENTS), array_values($UTF8_UPPER_ACCENTS), $string);
		}

		return $string;
	}
}
