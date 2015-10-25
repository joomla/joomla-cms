<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 4/15/14 6:39 PM $
* @package CBLib\Language
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Language;

use CBLib\Math\Interval;

defined('CBLIB') or die();

/**
 * CBLib\Language\Pluralization Class implementation
 *
 * Partly inspired with thanks from Laravel and Symfony
 */
class Pluralization
{
	/**
	 * @param  string  $message  The translated message string to pluralizations
	 * @param  string  $args     The arguments for the strtr
	 * @param  string  $locale   The language in ISO format
	 * @return string
	 */
	public static function pluralize( $message, $args, $locale )
	{
		// Gets the value of the first array element, or FALSE if the array is empty:
		$firstArg	=	reset( $args );

		if ( count( $args ) <= 1 ) {

			if ( is_int( $firstArg ) || is_numeric( $firstArg ) ) {
				// Simple case with one value:
				if ( strpos( $message, '|' ) ) {
					// Has plural choices ('|' exists and not at first position):
					return static::choose( $message, $firstArg, $locale );
				}
			}

			return $message;
		}

		// Case with multiple $args and possibly multiple parts of sentence to pluralize, each separated by '||':
		return implode(
			preg_replace_callback(
				'/^.*(%%[^%]+%%).*$/',
				function ( &$matches ) use ( $args, $locale ) {
					if ( isset( $args[$matches[1]] ) ) {
						return Pluralization::choose( $matches[0], $args[$matches[1]], $locale );
					}
					// Variable needed to evaluate choice not available: log and return untouched message:
					//TODO here log translation problem:
					//sprintf('Unable to choose a translation for "%s" with locale "%s". Double check that this translation has the correct plural options including correct %%-delimited variable (e.g. "There is one apple|There are %%count%% apples"). Here "%s" is not found in the available variables (%s).', $matches[0], $locale, $matches[1], implode( ',', array_keys( $args ) ) );
					return $matches[0];
				},
				explode( '||', $message )
			)
		);
	}

	/**
	 * Given a message with different plural translations separated by a
	 * pipe (|), this method returns the correct portion of the message based
	 * on the given number, locale and the pluralization rules in the message
	 * itself.
	 *
	 * The message supports two different types of pluralization rules:
	 *
	 * interval: {0} There are no apples|{1} There is one apple|]1,Inf] There are %count% apples
	 * indexed:  There is one apple|There are %count% apples
	 *
	 * The indexed solution can also contain labels (e.g. one: There is one apple).
	 * This is purely for making the translations more clear - it does not
	 * affect the functionality.
	 *
	 * The two methods can also be mixed:
	 *     {0} There are no apples|one: There is one apple|more: There are %count% apples
	 *
	 * @param  string   $message  The message being translated
	 * @param  integer  $number   The number of items represented for the message
	 * @param  string   $locale   The locale to use for choosing
	 * @return string
	 */
	public static function choose( $message, $number, $locale )
	{
		$parts = explode('|', $message);
		$explicitRules = array();
		$standardRules = array();
		foreach ($parts as $part) {
			$part = trim($part);

			if (preg_match('/^(?P<interval>' . Interval::getIntervalRegexp() . ')\s*(?P<message>.*?)$/x', $part, $matches)) {
				$explicitRules[$matches['interval']] = $matches['message'];
			} elseif (preg_match('/^\w+\:\s*(.*?)$/', $part, $matches)) {
				$standardRules[] = $matches[1];
			} else {
				$standardRules[] = $part;
			}
		}

		// try to match an explicit rule, then fallback to the standard ones
		foreach ($explicitRules as $interval => $m) {
			if ( Interval::test($number, $interval)) {
				return $m;
			}
		}

		$position = static::getPluralIndex( $number, $locale );

		if (!isset($standardRules[$position])) {
			// when there's exactly one rule given, and that rule is a standard
			// rule, use this rule
			if ( count($parts) === 1 && isset( $standardRules[0] ) ) {
				return $standardRules[0];
			}

			//TODO here log translation problem:
			// sprintf('Unable to choose a translation for "%s" with locale "%s". Double check that this translation has the correct plural options (e.g. "There is one apple|There are %%count%% apples").', $message, $locale);

			if ( isset( $standardRules[1] ) ) {
				// As best approximation return the first plural:
				return $standardRules[1];
			}

			if ( isset( $standardRules[0] ) ) {
				// As last resort, return the singular:
				return $standardRules[0];
			}

			// No translation choice possible: return message as is:
			return $message;

		}

		return $standardRules[$position];
	}

	/**
	 * Returns the plural position to use for the given locale and number.
	 * @see    http://localization-guide.readthedocs.org/en/latest/l10n/pluralforms.html
	 *         http://www.gnu.org/savannah-checkouts/gnu/gettext/manual/html_node/Plural-forms.html
	 *         http://www.backtheweb.com/i18n/gettext-plural-forms/
	 *         https://developer.mozilla.org/en-US/docs/Localization_and_Plurals
	 *         https://github.com/translate/l10n-guide/blob/master/docs/l10n/pluralforms.rst
	 *
	 * The plural rules are derived from code of the Zend Framework (2010-09-25),
	 * which is subject to the new BSD license (http://framework.zend.com/license/new-bsd).
	 * Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
	 *
	 * @param  integer $number  The number
	 * @param  string  $locale  The locale
	 * @return integer          The plural position
	 */
	public static function getPluralIndex( $number, $locale )
	{
		if ( ( strlen( $locale ) > 3 ) && ( $locale !== 'pt_BR' ) ) {
			$locale		=	substr( $locale, 0, - strlen(strrchr( $locale, '-' ) ) );
		}

		switch ( $locale ) {
			// nplurals=2; plural=(n != 1);
			case 'en':
			case 'af':
			case 'az':
			case 'bn':
			case 'bg':
			case 'ca':
			case 'da':
			case 'de':
			case 'el':
			case 'eo':
			case 'es':
			case 'et':
			case 'eu':
			case 'fa':
			case 'fi':
			case 'fo':
			case 'fur':
			case 'fy':
			case 'gl':
			case 'gu':
			case 'ha':
			case 'he':
			case 'hu':
			case 'is':
			case 'it':
			case 'ku':
			case 'lb':
			case 'ml':
			case 'mn':
			case 'mr':
			case 'nah':
			case 'nb':
			case 'ne':
			case 'nl':
			case 'nn':
			case 'no':
			case 'om':
			case 'or':
			case 'pa':
			case 'pap':
			case 'ps':
			case 'pt':
			case 'so':
			case 'sq':
			case 'sv':
			case 'sw':
			case 'ta':
			case 'te':
			case 'tk':
			case 'ur':
			case 'zu':
				return ($number == 1) ? 0 : 1;

			// nplurals=2; plural=(n > 1);
			case 'am':
			case 'bh':
			case 'fil':
			case 'fr':
			case 'gun':
			case 'hi':
			case 'ln':
			case 'mg':
			case 'nso':
			case 'pt_BR':
			case 'ti':
			case 'wa':
				return (($number == 0) || ($number == 1)) ? 0 : 1;

			// nplurals=1; plural=0;
			case 'bo':
			case 'dz':
			case 'id':
			case 'ja':
			case 'jv':
			case 'ka':
			case 'km':
			case 'kn':
			case 'ko':
			case 'ms':
			case 'th':
			case 'tr':
			case 'vi':
			case 'zh':
				return 0;
				break;

			// nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);
			case 'be':
			case 'bs':
			case 'hr':
			case 'ru':
			case 'sr':
			case 'uk':
				return (($number % 10 == 1) && ($number % 100 != 11)) ? 0 : ((($number % 10 >= 2) && ($number % 10 <= 4) && (($number % 100 < 10) || ($number % 100 >= 20))) ? 1 : 2);

			// nplurals=3; plural=(n==1) ? 0 : (n>=2 && n<=4) ? 1 : 2;
			case 'cs':
			case 'sk':
				return ($number == 1) ? 0 : ((($number >= 2) && ($number <= 4)) ? 1 : 2);

			// nplurals=5; plural=n==1 ? 0 : n==2 ? 1 : n<7 ? 2 : n<11 ? 3 : 4;
			case 'ga':
				return ($number == 1) ? 0 : (($number == 2) ? 1 : 2);

			// nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && (n%100<10 or n%100>=20) ? 1 : 2);
			case 'lt':
				return (($number % 10 == 1) && ($number % 100 != 11)) ? 0 : ((($number % 10 >= 2) && (($number % 100 < 10) || ($number % 100 >= 20))) ? 1 : 2);

			// nplurals=4;plural=n%100==1 ? 0 : n%100==2 ? 1 : n%100==3 || n%100==4 ? 2 : 3;
			case 'sl':
				return ($number % 100 == 1) ? 0 : (($number % 100 == 2) ? 1 : ((($number % 100 == 3) || ($number % 100 == 4)) ? 2 : 3));

			// nplurals=2; plural= n==1 || n%10==1 ? 0 : 1; Can’t be correct needs a 2 somewhere
			case 'mk':
				return ($number % 10 == 1) ? 0 : 1;

			// nplurals=4; plural=(n==1 ? 0 : n==0 || ( n%100>1 && n%100<11) ? 1 : (n%100>10 && n%100<20 ) ? 2 : 3);
			case 'mt':
				return ($number == 1) ? 0 : ((($number == 0) || (($number % 100 > 1) && ($number % 100 < 11))) ? 1 : ((($number % 100 > 10) && ($number % 100 < 20)) ? 2 : 3));

			// nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n != 0 ? 1 : 2);
			case 'lv':
				return ($number == 0) ? 0 : ((($number % 10 == 1) && ($number % 100 != 11)) ? 1 : 2);

			// nplurals=3; plural=(n==1 ? 0 : n%10>=2 && n%10<=4 && (n%100<12 || n%100>=14) ? 1 : 2);
			case 'pl':
				return ($number == 1) ? 0 : ((($number % 10 >= 2) && ($number % 10 <= 4) && (($number % 100 < 12) || ($number % 100 > 14))) ? 1 : 2);

			// nplurals=4; plural= (n==1) ? 0 : (n==2) ? 1 : (n != 8 && n != 11) ? 2 : 3;
			case 'cy':
				return ($number == 1) ? 0 : (($number == 2) ? 1 : ((($number != 8) && ($number != 11)) ? 2 : 3));

			// nplurals=3; plural=(n==1 ? 0 : (n==0 || (n%100 > 0 && n%100 < 20)) ? 1 : 2);
			case 'ro':
				return ($number == 1) ? 0 : ((($number == 0) || (($number % 100 > 0) && ($number % 100 < 20))) ? 1 : 2);

			// nplurals=6; plural=(n==0 ? 0 : n==1 ? 1 : n==2 ? 2 : n%100>=3 && n%100<=10 ? 3 : n%100>=11 ? 4 : 5);
			case 'ar':
				return ($number == 0) ? 0 : (($number == 1) ? 1 : (($number == 2) ? 2 : ((($number % 100 >= 3) && ($number % 100 <= 10)) ? 3 : (($number % 100 >= 11) ? 4 : 5))));

			default:
				return 0;
		}
	}
}
