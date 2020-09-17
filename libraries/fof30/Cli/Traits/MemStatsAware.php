<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Cli\Traits;

defined('_JEXEC') || die;

/**
 * Memory statistics
 *
 * This is an optional trait which allows the developer to print memory usage statistics and format byte sizes into
 * human-readable strings.
 *
 * @package FOF30\Cli\Traits
 */
trait MemStatsAware
{
	/**
	 * Formats a number of bytes in human readable format
	 *
	 * @param   int  $size  The size in bytes to format, e.g. 8254862
	 *
	 * @return  string  The human-readable representation of the byte size, e.g. "7.87 Mb"
	 */
	protected function formatByteSize($size)
	{
		$unit = ['b', 'KB', 'MB', 'GB', 'TB', 'PB'];

		return @round($size / 1024 ** ($i = floor(log($size, 1024))), 2) . ' ' . $unit[$i];
	}

	/**
	 * Returns the current memory usage, formatted
	 *
	 * @return  string
	 */
	protected function memUsage()
	{
		if (function_exists('memory_get_usage'))
		{
			$size = memory_get_usage();

			return $this->formatByteSize($size);
		}
		else
		{
			return "(unknown)";
		}
	}

	/**
	 * Returns the peak memory usage, formatted
	 *
	 * @return  string
	 */
	protected function peakMemUsage()
	{
		if (function_exists('memory_get_peak_usage'))
		{
			$size = memory_get_peak_usage();

			return $this->formatByteSize($size);
		}
		else
		{
			return "(unknown)";
		}
	}
}
