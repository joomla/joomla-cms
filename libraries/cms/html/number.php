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
	 *
	 * @param   string   $bytes      The number of bytes. Can be either numeric or suffixed format: 32M, 60K, 12G or 812b
	 * @param   string   $unit       The type of unit to return. Special: Blank string '' for no unit and 'auto' to choose automatically (default)
	 * @param   integer  $precision  The number of digits to be used after the decimal place.
	 *
	 * @return  string   The number of bytes in the proper units.
	 *
	 * @since   1.6
	 */
	public static function bytes($bytes, $unit = 'auto', $precision = 2)
	{
		/*
		 * Allowed 123.45, 123.45 M, 123.45 MB, 1.2345E+12MB, 1.2345E+12 MB etc.
		 * i.e. – Any number in decimal digits or in sci. notation, optional space, optional 1-2 letter unit suffix
		 */
		if (is_numeric($bytes))
		{
			$oBytes = $bytes;
			$oUnit  = null;
		}
		else
		{
			preg_match('/(.*?)\s?([KMGTPEZY]?B?)$/i', trim($bytes), $matches);
			list(, $oBytes, $oUnit) = $matches;
		}

		if (empty($oBytes) || !is_numeric($oBytes))
		{
			return 0;
		}

		$factor = $oUnit ? pow(1024, stripos('BKMGTPEZY', $oUnit[0])) : 1;
		$oBytes = round($oBytes * $factor);

		// If no unit is requested return early
		if ($unit === '')
		{
			return (string) $oBytes;
		}

		$suffixes = array('b', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

		// Default automatic method
		$i = floor(log($oBytes, 1024));

		// User supplied method
		if ($unit !== 'auto' && in_array($unit, $suffixes))
		{
			$i = array_search($unit, $suffixes, true);
		}

		return round($oBytes / pow(1024, $i), (int) $precision) . ' ' . $suffixes[$i];
	}
}
