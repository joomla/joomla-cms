<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Cli\Traits;

defined('_JEXEC') || die;

/**
 * Allows the developer to show the relative time difference between two timestamps.
 *
 * @package FOF30\Cli\Traits
 */
trait TimeAgoAware
{
	/**
	 * Returns the relative time difference between two timestamps in a human readable format
	 *
	 * @param   int       $referenceTimestamp  Timestamp of the reference date/time
	 * @param   int|null  $currentTimestamp    Timestamp of the current date/time. Null for time().
	 * @param   string    $timeUnit            Time unit. One of s, m, h, d, or y.
	 * @param   bool      $autoSuffix          Add "ago" / "from now" suffix?
	 *
	 * @return  string  For example, "10 seconds ago"
	 */
	protected function timeAgo($referenceTimestamp = 0, $currentTimestamp = null, $timeUnit = '', $autoSuffix = true)
	{
		if (is_null($currentTimestamp))
		{
			$currentTimestamp = time();
		}

		// Raw time difference
		$raw   = $currentTimestamp - $referenceTimestamp;
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

		$effectiveTimeUnit = $timeUnit;

		if ($timeUnit == '')
		{
			$effectiveTimeUnit = 's';

			for ($i = 0; $i < count($calcNum); $i++)
			{
				if ($clean <= $calcNum[$i][1])
				{
					$effectiveTimeUnit = $calcNum[$i][0];
					$i                 = count($calcNum);
				}
			}
		}

		$timeDifference = floor($clean / $calc[$effectiveTimeUnit][0]);
		$textSuffix     = '';

		if ($autoSuffix == true && ($currentTimestamp == time()))
		{
			if ($raw < 0)
			{
				$textSuffix = ' from now';
			}
			else
			{
				$textSuffix = ' ago';
			}
		}

		if ($referenceTimestamp != 0)
		{
			if ($timeDifference == 1)
			{
				return $timeDifference . ' ' . $calc[$effectiveTimeUnit][1] . ' ' . $textSuffix;
			}

			return $timeDifference . ' ' . $calc[$effectiveTimeUnit][1] . 's ' . $textSuffix;
		}

		return '(no reference timestamp was provided).';
	}

}
