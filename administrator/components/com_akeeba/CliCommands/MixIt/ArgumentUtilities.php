<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\CliCommands\MixIt;

defined('_JEXEC') || die;

/**
 * Utility methods to manage command arguments
 *
 * @since   7.5.0
 */
trait ArgumentUtilities
{
	/**
	 * Parse the overrides provided in the command line.
	 *
	 * Input: "key1=value1, key2= value2, key3 = value3"
	 * Output: ['key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3']
	 *
	 * @param   string  $rawString  The raw string
	 *
	 * @return  array  The parsed overrides
	 *
	 * @since   7.5.0
	 */
	private function commaListToMap(string $rawString): array
	{
		if (empty($rawString) || (trim($rawString) == ''))
		{
			return [];
		}

		$rawString = trim($rawString);
		$ret       = [];
		$lines     = explode($rawString, ",");

		foreach ($lines as $line)
		{
			if (strpos($line, '=') === false)
			{
				continue;
			}

			[$key, $value] = explode('=', $line);
			$key       = trim($key);
			$value     = trim($value);
			$ret[$key] = $value;
		}

		return $ret;
	}
}
