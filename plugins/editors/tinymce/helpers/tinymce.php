<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * TinyMCE Helper Class
 *
 * @since  3.7
 */
abstract class TinymceHelper
{
	/**
	 * Gets all the available languages for WCAG 2
	 *
	 * @return  array  A list of all the languages
	 *
	 * @since   3.7
	 */
	public static function getAllLanguages()
	{
		return array(
			0 => array(
				'code'       => 'af-ZA',
				'name'       => 'Afrikaans',
				'nativeName' => 'Afrikaans',
				'dir'        => 'ltr',
			),
			1 => array(
				'code'       => 'ar',
				'name'       => 'Arabic',
				'nativeName' => 'عربي',
				'dir'        => 'rtl',
			),
			2 => array(
				'code'       => 'hy-AM',
				'name'       => 'Armenian',
				'nativeName' => 'Հայերեն',
				'dir'        => 'ltr',
			),
			3 => array(
				'code'       => 'eu-ES',
				'name'       => 'Basque',
				'nativeName' => 'euskara',
				'dir'        => 'ltr',
			),
			4 => array(
				'code'       => 'bn-BD',
				'name'       => 'Bengali (Bangladesh)',
				'nativeName' => 'বাংলা',
				'dir'        => 'ltr',
			),
			5 => array(
				'code'       =>  'bs-BA',
				'name'       =>  'Bosnian',
				'nativeName' =>  'bosanski',
				'dir'        =>  'ltr',
			),
			6 => array(
				'code'       =>  'bg',
				'name'       =>  'Bulgarian',
				'nativeName' =>  'български',
				'dir'        =>  'ltr',
			),
			7 => array(
				'code'       =>  'ca-ES',
				'name'       =>  'Catalan',
				'nativeName' =>  'Català',
				'dir'        =>  'ltr',
			),
			8 => array(
				'code'       =>  'zh-CN',
				'name'       =>  'Chinese (Simplified)',
				'nativeName' =>  '中文 (简体)',
				'dir'        =>  'ltr',
			),
			9 => array(
				'code'       =>  'zh-TW',
				'name'       =>  'Chinese (Traditional)',
				'nativeName' =>  '正體中文 (繁體)',
				'dir'        =>  'ltr',
			),
			10 => array(
				'code'       =>  'hr',
				'name'       =>  'Croatian',
				'nativeName' =>  'Hrvatski',
				'dir'        =>  'ltr'
			),
			11 => array(
				'code'       =>  'cs',
				'name'       =>  'Czech',
				'nativeName' =>  'Čeština',
				'dir'        =>  'ltr'
			),
			12 => array(
				'code'       =>  'da',
				'name'       =>  'Danish',
				'nativeName' =>  'dansk',
				'dir'        =>  'ltr',
			),
			13 => array(
				'code'       =>  'nl',
				'name'       =>  'Dutch',
				'nativeName' =>  'Nederlands',
				'dir'        =>  'ltr',
			),
			14 => array(
				'code'       =>  'nl-BE',
				'name'       =>  'Dutch (Belgium)',
				'nativeName' =>  'Flemish',
				'dir'        =>  'ltr',
			),
			15 => array(
				'code'       =>  'en-AU',
				'name'       =>  'English (Australian)',
				'nativeName' =>  'English (Australian)',
				'dir'        =>  'ltr',
			),
			16 => array(
				'code'       =>  'en-GB',
				'name'       =>  'English (British)',
				'nativeName' =>  'English (British)',
				'dir'        =>  'ltr',
			),
			17 => array(
				'code'       =>  'en-US',
				'name'       =>  'English (American)',
				'nativeName' =>  'English (American)',
				'dir'        =>  'ltr',
			),
			18 => array(
				'code'       =>  'eo',
				'name'       =>  'Esperanto',
				'nativeName' =>  'Esperanto',
				'dir'        =>  'ltr',
			),
			19 => array(
				'code'       =>  'et',
				'name'       =>  'Estonian',
				'nativeName' =>  'Eesti keel',
				'dir'        =>  'ltr',
			),
			20 => array(
				'code'       =>  'fi',
				'name'       =>  'Finnish',
				'nativeName' =>  'suomi',
				'dir'        =>  'ltr',
			),
			21 => array(
				'code'       =>  'fr',
				'name'       =>  'French',
				'nativeName' =>  'français',
				'dir'        =>  'ltr',
			),
			22 => array(
				'code'       =>  'fr-CA',
				'name'       =>  'French (Canadian)',
				'nativeName' =>  'français (canadien)',
				'dir'        =>  'ltr',
			),
			23 => array(
				'code'       =>  'de',
				'name'       =>  'German',
				'nativeName' =>  'Deutsch',
				'dir'        =>  'ltr',
			),
			24 => array(
				'code'       =>  'el',
				'name'       =>  'Greek',
				'nativeName' =>  'Ελληνικά',
				'dir'        =>  'ltr',
			),
			25 => array(
				'code'       =>  'he',
				'name'       =>  'Hebrew',
				'nativeName' =>  'עברית',
				'dir'        =>  'rtl',
			),
			26 => array(
				'code'       =>  'hu',
				'name'       =>  'Hungarian',
				'nativeName' =>  'Magyar',
				'dir'        =>  'ltr',
			),
			27 => array(
				'code'       =>  'id',
				'name'       =>  'Indonesian',
				'nativeName' =>  'Bahasa Indonesia',
				'dir'        =>  'ltr',
			),
			28 => array(
				'code'       =>  'ga-IE',
				'name'       =>  'Irish',
				'nativeName' =>  'Gaeilge',
				'dir'        =>  'ltr',
			),
			29 => array(
				'code'       =>  'it',
				'name'       =>  'Italian',
				'nativeName' =>  'Italiano',
				'dir'        =>  'ltr',
			),
			30 => array(
				'code'       =>  'ja-JP',
				'name'       =>  'Japanese',
				'nativeName' =>  '日本語',
				'dir'        =>  'ltr',
			),
			31 => array(
				'code'       =>  'km-KH',
				'name'       =>  'Khmer',
				'nativeName' =>  'ភាសាខ្មែរ',
				'dir'        =>  'ltr',
			),
			32 => array(
				'code'       =>  'ko-KR',
				'name'       =>  'Korean',
				'nativeName' =>  '한국어',
				'dir'        =>  'ltr',
			),
			33 => array(
				'code'       =>  'lv',
				'name'       =>  'Latvian',
				'nativeName' =>  'Latviešu',
				'dir'        =>  'ltr',
			),
			34 => array(
				'code'       =>  'lt',
				'name'       =>  'Lithuanian',
				'nativeName' =>  'lietuvių kalba',
				'dir'        =>  'ltr',
			),
			35 => array(
				'code'       =>  'mk',
				'name'       =>  'Macedonian',
				'nativeName' =>  'Македонски',
				'dir'        =>  'ltr',
			),
			36 => array(
				'code'       =>  'ms-MY',
				'name'       =>  'Malay',
				'nativeName' =>  'bahasa Melayu, بهاس ملايو‎',
				'dir'        =>  'ltr',
			),
			37 => array(
				'code'       =>  'nb-NO',
				'name'       =>  'Norwegian Bokmål',
				'nativeName' =>  'Norsk bokmål',
				'dir'        =>  'ltr',
			),
			38 => array(
				'code'       =>  'nn-NO',
				'name'       =>  'Norwegian Nynorsk',
				'nativeName' =>  'Norsk nynorsk',
				'dir'        =>  'ltr',
			),
			39 => array(
				'code'       =>  'fa',
				'name'       =>  'Persian',
				'nativeName' =>  'فارسی',
				'dir'        =>  'rtl',
			),
			40 => array(
				'code'       =>  'pl',
				'name'       =>  'Polish',
				'nativeName' =>  'polski',
				'dir'        =>  'ltr',
			),
			41 => array(
				'code'       =>  'pt',
				'name'       =>  'Portuguese',
				'nativeName' =>  'Português',
				'dir'        =>  'ltr',
			),
			42 => array(
				'code'       =>  'pt-BR',
				'name'       =>  'Portuguese (Brazilian)',
				'nativeName' =>  'Português do Brasil',
				'dir'        =>  'ltr',
			),
			43 => array(
				'code'       =>  'ro',
				'name'       =>  'Romanian',
				'nativeName' =>  'română',
				'dir'        =>  'ltr',
			),
			44 => array(
				'code'       =>  'ru',
				'name'       =>  'Russian',
				'nativeName' =>  'Русский',
				'dir'        =>  'ltr',
			),
			45 => array(
				'code'       =>  'sr-RS',
				'name'       =>  'Serbian',
				'nativeName' =>  'српски',
				'dir'        =>  'ltr',
			),
			46 => array(
				'code'       =>  'sr-YU',
				'name'       =>  'Serbian',
				'nativeName' =>  'Srpski',
				'dir'        =>  'ltr',
			),
			47 => array(
				'code'       =>  'sk',
				'name'       =>  'Slovak',
				'nativeName' =>  'slovenčina',
				'dir'        =>  'ltr',
			),
			48 => array(
				'code'       =>  'sl-SI',
				'name'       =>  'Slovenian',
				'nativeName' =>  'slovensko',
				'dir'        =>  'ltr',
			),
			49 => array(
				'code'       =>  'es',
				'name'       =>  'Spanish',
				'nativeName' =>  'Español',
				'dir'        =>  'ltr',
			),
			50 => array(
				'code'       =>  'es-CO',
				'name'       =>  'Spanish (Colombia)',
				'nativeName' =>  'Español de Colombia',
				'dir'        =>  'ltr',
			),
			51 => array(
				'code'       =>  'sw-KE',
				'name'       =>  'Swahili',
				'nativeName' =>  'Kiswahili',
				'dir'        =>  'ltr',
			),
			52 => array(
				'code'       =>  'sv-SE',
				'name'       =>  'Swedish',
				'nativeName' =>  'svenska',
				'dir'        =>  'ltr',
			),
			53 => array(
				'code'       =>  'ta-IN',
				'name'       =>  'Tamil',
				'nativeName' =>  'தமிழ்',
				'dir'        =>  'ltr',
			),
			54 => array(
				'code'       =>  'th',
				'name'       =>  'Thai',
				'nativeName' =>  'ไทย',
				'dir'        =>  'ltr',
			),
			55 => array(
				'code'       =>  'tr',
				'name'       =>  'Turkish',
				'nativeName' =>  'Türkçe',
				'dir'        =>  'ltr',
			),
			56 => array(
				'code'       =>  'uk-UA',
				'name'       =>  'Ukrainian',
				'nativeName' =>  'Українська',
				'dir'        =>  'ltr',
			)
		);
	}
}
