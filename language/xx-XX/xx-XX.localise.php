<?php
/**
 * @version		$Id: language.php 15628 2010-03-27 05:20:29Z infograf768 $
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * xx-XX Localise class
 *
 * @package		Joomla.Site
 * @since		1.6
 */
abstract class xx_XXLocalise {
	/**
	 * Returns the potential suffixes for a specific number of items
	 *
	 * @param	int $count  The number of items.
	 * @return	array  An array of potential suffixes.
	 * @since	1.6
	 */
	public static function getPluralSuffices($count) {
		if ($count == 0) {
			$return =  array('0');
		}
		elseif($count == 1) {
			$return =  array('1');
		}
		elseif($count > 8 && $count < 12) {
			$return = array('TEN', 'MORE');
		}
		else {
			$return = array('MORE');
		}
		return $return;
	}
	/**
	 * Returns the ignored search words
	 *
	 * @return	array  An array of ignored search words.
	 * @since	1.6
	 */
	public static function getIgnoreSearchWords() {
		$search_ignore = array();
		$search_ignore[] = "and";
		$search_ignore[] = "in";
		$search_ignore[] = "on";
		return $search_ignore;
	}
	/**
	 * This method processes a string and replaces all accented UTF-8 characters by unaccented
	 * ASCII-7 "equivalents"
	 *
	 * @param	string	$string	The string to transliterate
	 * @return	string	The transliteration of the string
	 * @since	1.6
	 */
	public static function transliterate($string)
	{
		$str = JString::strtolower($string);

		//Specific language transliteration.
		//This one is for latin 1, latin supplement , extended A, Cyrillic, Greek

		$glyph_array = array(
		'a'		=>	'à,á,â,ã,ä,å,ā,ă,ą,ḁ,α,ά',
		'ae'	=>	'æ',
		'b'		=>	'β,б',
		'c'		=>	'ç,ć,ĉ,ċ,č,ч,ћ,ц',
		'ch'	=>	'ч',
		'd'		=>	'ď,đ,Ð,д,ђ,δ,ð',
		'dz'	=>	'џ',
		'e'		=>	'è,é,ê,ë,ē,ĕ,ė,ę,ě,э,ε,έ',
		'f'		=>	'ƒ,ф',
		'g'		=>	'ğ,ĝ,ğ,ġ,ģ,г,γ',
		'h'		=>	'ĥ,ħ,Ħ,х',
		'i'		=>	'ì,í,î,ï,ı,ĩ,ī,ĭ,į,и,й,ъ,ы,ь,η,ή',
		'ij'	=>	'ĳ',
		'j'		=>	'ĵ',
		'ja'	=>	'я',
		'ju'	=>	'яю',
		'k'		=>	'ķ,ĸ,κ',
		'l'		=>	'ĺ,ļ,ľ,ŀ,ł,л,λ',
		'lj'	=>	'љ',
		'm'		=>	'μ',
		'n'		=>	'ñ,ņ,ň,ŉ,ŋ,н,ν',
		'nj'	=>	'њ',
		'o'		=>	'ò,ó,ô,õ,ø,ō,ŏ,ő,ο,ό,ω,ώ',
		'oe'	=>	'œ,ö',
		'p'		=>	'п,π',
		'ph'	=>	'φ',
		'ps'	=>	'ψ',
		'r'		=>	'ŕ,ŗ,ř,р,ρ,σ,ς',
		's'		=>	'ş,ś,ŝ,ş,š,с',
		'ss'	=>	'ß,ſ',
		'sh'	=>	'ш',
		'shch'	=>	'щ',
		't'		=>	'ţ,ť,ŧ,τ',
		'th'	=>	'θ',
		'u'		=>	'ù,ú,û,ü,ũ,ū,ŭ,ů,ű,ų,у',
		'v'		=>	'в',
		'w'		=>	'ŵ',
		'x'		=>	'χ,ξ',
		'y'		=>	'ý,þ,ÿ,ŷ',
		'z'		=>	'ź,ż,ž,з,ж,ζ'
		);

		foreach( $glyph_array as $letter => $glyphs ) {
			$glyphs = explode( ',', $glyphs );
			$str = str_replace( $glyphs, $letter, $str );
		}

		return $str;
	}
}

