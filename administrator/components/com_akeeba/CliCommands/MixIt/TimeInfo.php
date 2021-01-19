<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands\MixIt;

defined('_JEXEC') || die;

/**
 * Utility methods to get time information
 *
 * @since   7.5.0
 */
trait TimeInfo
{
	/**
	 * Returns a fancy formatted time lapse code
	 *
	 * @param   integer   $referenceDateTime  Timestamp of the reference date/time
	 * @param   int|null  $currentDateTime    Timestamp of the current date/time
	 * @param   string    $measureBy          One of s, m, h, d, or y (time unit)
	 * @param   boolean   $autoText           Append text automatically?
	 *
	 * @return  string
	 *
	 * @since   7.5.0
	 */
	private function timeAgo(int $referenceDateTime = 0, ?int $currentDateTime = null, string $measureBy = '', bool $autoText = true): string
	{
		if (is_null($currentDateTime))
		{
			$currentDateTime = time();
		}

		// Raw time difference
		$raw   = $currentDateTime - $referenceDateTime;
		$clean = abs($raw);

		$calcNum = [
			['s', 60],
			['m', 60 * 60],
			['h', 60 * 60 * 60],
			['d', 60 * 60 * 60 * 24],
			['y', 60 * 60 * 60 * 24 * 365],
		];

		$calc = [
			's' => [1, 'second'],
			'm' => [60, 'minute'],
			'h' => [60 * 60, 'hour'],
			'd' => [60 * 60 * 24, 'day'],
			'y' => [60 * 60 * 24 * 365, 'year'],
		];

		if ($measureBy == '')
		{
			$usemeasure = 's';

			for ($i = 0; $i < count($calcNum); $i++)
			{
				if ($clean <= $calcNum[$i][1])
				{
					$usemeasure = $calcNum[$i][0];
					$i          = count($calcNum);
				}
			}
		}
		else
		{
			$usemeasure = $measureBy;
		}

		$datedifference = floor($clean / $calc[$usemeasure][0]);

		if ($autoText == true && ($currentDateTime == time()))
		{
			if ($raw < 0)
			{
				$prospect = ' from now';
			}
			else
			{
				$prospect = ' ago';
			}
		}
		else
		{
			$prospect = '';
		}

		if ($referenceDateTime != 0)
		{
			if ($datedifference == 1)
			{
				return $datedifference . ' ' . $calc[$usemeasure][1] . ' ' . $prospect;
			}
			else
			{
				return $datedifference . ' ' . $calc[$usemeasure][1] . 's ' . $prospect;
			}
		}
		else
		{
			return 'No input time referenced.';
		}
	}
}
