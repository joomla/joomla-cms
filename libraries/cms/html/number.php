<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * HTML helper class for rendering numbers.
 *
 * @since  1.6
 */
abstract class JHtmlNumber
{
	/**
	 * Converts bytes to more distinguishable formats such as:
	 * kilobytes, megabytes, etc.
	 *
	 * By default, the proper format will automatically be chosen.
	 * However, one of the allowed unit types (viz. 'b', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB') may also be used instead.
	 * IEC standard unit types ('KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB') can be used as well.
	 *
	 * @param   string   $bytes      The number of bytes. Can be either numeric or suffixed format: 32M, 60K, 12G or 812b
	 * @param   string   $unit       The type of unit to return, few special values are:
	 *                               Blank string '' for no unit,
	 *                               'auto' to choose automatically (default)
	 *                               'binary' to choose automatically but use binary unit prefix
	 * @param   integer  $precision  The number of digits to be used after the decimal place.
	 * @param   bool     $iec        Whether to be aware of IEC standards. IEC prefixes are always acceptable in input.
	 *                               When IEC is ON:  KiB = 1024 B, KB = 1000 B
	 *                               When IEC is OFF: KiB = 1024 B, KB = 1024 B
	 *
	 * @return  string   The number of bytes in the proper units.
	 *
	 * @since   1.6
	 * @see     https://en.wikipedia.org/wiki/Binary_prefix
	 */
	public static function bytes($bytes, $unit = 'auto', $precision = 2, $iec = false)
	{
		/*
		 * Allowed 123.45, 123.45 M, 123.45 Mi, 123.45 MB, 123.45 MiB, 1.2345E+12MB, 1.2345E+12 MB , 1.2345E+12 MiB etc.
		 * i.e. – Any number in decimal digits or in sci. notation, optional space, optional 1-3 letter unit suffix
		 */
		if (is_numeric($bytes))
		{
			$oBytes = $bytes;
		}
		else
		{
			preg_match('/(.*?)\s?((?:[KMGTPEZY]i?)?B?)$/i', trim($bytes), $matches);
			list(, $oBytes, $oUnit) = $matches;

			if ($oUnit && is_numeric($oBytes))
			{
				$oBase  = $iec && strpos($oUnit, 'i') === false ? 1000 : 1024;
				$factor = pow($oBase, stripos('BKMGTPEZY', $oUnit[0]));
				$oBytes = $oBytes * $factor;
			}
		}

		if (empty($oBytes) || !is_numeric($oBytes))
		{
			return 0;
		}

		$oBytes = round($oBytes);

		// If no unit is requested return early
		if ($unit === '')
		{
			return (string) $oBytes;
		}

		$stdSuffixes = array('b', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		$iecSuffixes = array('b', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');

		// User supplied method
		if (in_array($unit, $iecSuffixes))
		{
			$base   = 1024;
			$i      = array_search($unit, $iecSuffixes, true);
			$suffix = $unit;
		}
		elseif (in_array($unit, $stdSuffixes))
		{
			$base   = $iec ? 1000 : 1024;
			$i      = array_search($unit, $stdSuffixes, true);
			$suffix = $unit;
		}
		elseif ($unit == 'binary')
		{
			$base   = 1024;
			$i      = (int) floor(log($oBytes, $base));
			$suffix = $iecSuffixes[$i];
		}
		else
		{
			// Default method
			$base   = $iec ? 1000 : 1024;
			$i      = (int) floor(log($oBytes, $base));
			$suffix = $stdSuffixes[$i];
		}

		return round($oBytes / pow($base, $i), (int) $precision) . ' ' . $suffix;
	}
}
