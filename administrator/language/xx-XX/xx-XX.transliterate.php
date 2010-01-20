<?php
/**
* @version		1.6
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
* This file has to be saved as UTF8 no BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

function xxXXTransliterate($string) //change xxXX to fit language prefix
{
	$str = JString::strtolower($string);
	
	//Specific language transliteration. 
	//This one is for latin 1, latin supplement , extended A, Cyrillic, Greek

	$glyph_array = array(
	'a' 	=>	'à,á,â,ã,ä,å,ā,ă,ą,ḁ,α,ά',
	'ae' 	=>	'æ',
	'b' 	=>	'β,б',
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
	'm' 	=>	'μ',
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