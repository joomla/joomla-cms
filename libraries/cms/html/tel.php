<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * HTML helper class for rendering telephone numbers.
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       1.6
 */
abstract class JHtmlTel
{
	/**
	 * Converts strings of integers into more readable telephone format
	 *
	 * By default, the ITU-T format will automatically be used.
	 * However, one of the allowed unit types may also be used instead.
	 *
	 * @param   integer  $number       The integers in a phone number with dot separated country code
	 *                                 ccc.nnnnnnn where ccc represents country code and nnn represents the local number.
	 * @param   string   $displayplan  The numbering plan used to display the numbers.
	 *
	 * @return  string  The formatted telephone number.
	 *
	 * @see     JFormRuleTel
	 * @since   1.6
	 */
	public static function tel($number, $displayplan)
	{
		$number = explode('.', $number);
		$countrycode = $number[0];
		$number = $number[1];

		if ($displayplan == 'ITU-T' || $displayplan == 'International' || $displayplan == 'int' || $displayplan == 'missdn' || $displayplan == null)
		{
			$display[0] = '+';
			$display[1] = $countrycode;
			$display[2] = ' ';
			$display[3] = implode(str_split($number, 2), ' ');
		}
		elseif ($displayplan == 'NANP' || $displayplan == 'northamerica' || $displayplan == 'US')
		{
			$display[0] = '(';
			$display[1] = substr($number, 0, 3);
			$display[2] = ') ';
			$display[3] = substr($number, 3, 3);
			$display[4] = '-';
			$display[5] = substr($number, 6, 4);
		}
		elseif ($displayplan == 'EPP' || $displayplan == 'IETF')
		{
			$display[0] = '+';
			$display[1] = $countrycode;
			$display[2] = '.';
			$display[3] = $number;

		}
		elseif ($displayplan == 'ARPA' || $displayplan == 'ENUM')
		{
			$number = implode(str_split(strrev($number), 1), '.');
			$display[0] = '+';
			$display[1] = $number;
			$display[2] = '.';
			$display[3] = $countrycode;
			$display[4] = '.e164.arpa';
		}

		$display = implode($display, '');

		return $display;
	}
}
