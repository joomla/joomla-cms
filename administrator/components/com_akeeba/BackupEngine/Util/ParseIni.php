<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Util;

defined('AKEEBAENGINE') || die();

/**
 * A utility class to parse INI files.
 *
 * This is marked deprecated since Akeeba Engine 6.4.1. The configuration of the engine is no longer stored as INI data.
 * Moreover, we will be migrating away from the current INI files used for defining engine and GUI configuration
 * parameters.
 *
 * @package     Akeeba\Engine\Util
 *
 * @deprecated  6.4.1
 */
abstract class ParseIni
{
	/**
	 * Parse an INI file and return an associative array. This monstrosity is required because some so-called hosts
	 * have disabled PHP's parse_ini_file() function for "security reasons". Apparently their blatant ignorance doesn't
	 * allow them to discern between the innocuous parse_ini_file and the potentially dangerous ini_set, leading them to
	 * disable the former and let the latter enabled.
	 *
	 * @param   string  $file              The file name or raw INI data to process
	 * @param   bool    $process_sections  True to also process INI sections
	 * @param   bool    $rawdata           Is this raw INI data? False when $file is a filepath.
	 * @param   bool    $forcePHP          Should I force the use of the pure-PHP INI file parser?
	 *
	 * @return   array    An associative array of sections, keys and values
	 */
	public static function parse_ini_file($file, $process_sections = false, $rawdata = false, $forcePHP = false)
	{
		/**
		 * WARNING: DO NOT USE INI_SCANNER_RAW IN THE parse_ini_string / parse_ini_file FUNCTION CALLS WITHOUT POST-
		 *          PROCESSING!
		 *
		 * Sometimes we need to save data which is either multiline or has double quotes in the Engine's
		 * configuration. For this reason we have to manually escape \r, \n, \t and \" in
		 * Akeeba\Engine\Configuration::dumpObject(). If we don't we end up with multiline INI values which
		 * won't work. However, if we are using INI_SCANNER_RAW these characters are not escaped back to their
		 * original form. As a result we end up with broken data which cause various problems, the most visible
		 * of which is that Google Storage integration is broken since the JSON data included in the config is
		 * now unparseable.
		 *
		 * However, not using raw mode introduces other problems. For example, the sequence \$ is converted to $ because
		 * it's assumed to be an escaped dollar sign. Things like $foo are addressed as variable interpolation, i.e.
		 * "This is ${foo} wrong" results in "This is  wrong" because $foo is considered as an interpolated variable.
		 *
		 * The solution to that is to use raw mode to parse the INI files and THEN unescape the variables. However, we
		 * cannot simply use stripslashes/stripcslashes because we could end up replacing more than we should (unlike
		 * addcslashes we cannot specify a list of escaped characters to consider). We have to do a slower string
		 * replace instead.
		 *
		 * The next problem to consider is that when $process_sections is true some of the values generated are arrays
		 * or even nested arrays. If you try to string replace on them hilarity ensues. Therefore we have the recursive
		 * unescape method which takes care of that. To make things faster and maintain the array keys we use array_map
		 * to apply recursiveUnescape to the array.
		 */

		if ($rawdata)
		{
			if (!function_exists('parse_ini_string'))
			{
				return self::parse_ini_file_php($file, $process_sections, $rawdata);
			}

			// !!! VERY IMPORTANT !!! Read the warning above before touching this line
			return array_map([
				__CLASS__, 'recursiveUnescape',
			], parse_ini_string($file, $process_sections, INI_SCANNER_RAW));
		}

		if (!function_exists('parse_ini_file'))
		{
			return self::parse_ini_file_php($file, $process_sections);
		}

		// !!! VERY IMPORTANT !!! Read the warning above before touching this line
		return array_map([__CLASS__, 'recursiveUnescape'], parse_ini_file($file, $process_sections, INI_SCANNER_RAW));
	}

	/**
	 * Recursively unescape values which have been escaped by Akeeba\Engine\Configuration::dumpObject().
	 *
	 * @param   string|array  $value
	 *
	 * @return  string|array  Unescaped result
	 */
	static function recursiveUnescape($value)
	{
		if (is_array($value))
		{
			return array_map([__CLASS__, 'recursiveUnescape'], $value);
		}

		return str_replace(['\r', '\n', '\t', '\"'], ["\r", "\n", "\t", '"'], $value);
	}

	/**
	 * A PHP based INI file parser.
	 *
	 * Thanks to asohn ~at~ aircanopy ~dot~ net for posting this handy function on
	 * the parse_ini_file page on http://gr.php.net/parse_ini_file
	 *
	 * @param   string  $file              Filename to process
	 * @param   bool    $process_sections  True to also process INI sections
	 * @param   bool    $rawdata           If true, the $file contains raw INI data, not a filename
	 *
	 * @return    array    An associative array of sections, keys and values
	 */
	static function parse_ini_file_php($file, $process_sections = false, $rawdata = false)
	{
		$process_sections = ($process_sections !== true) ? false : true;

		if (!$rawdata)
		{
			$ini = file($file);
		}
		else
		{
			$file = str_replace("\r", "", $file);
			$ini  = explode("\n", $file);
		}

		if (!is_array($ini))
		{
			return [];
		}

		if (count($ini) == 0)
		{
			return [];
		}

		$sections = [];
		$values   = [];
		$result   = [];
		$globals  = [];
		$i        = 0;
		foreach ($ini as $line)
		{
			$line = trim($line);
			$line = str_replace("\t", " ", $line);

			// Comments
			if (!preg_match('/^[a-zA-Z0-9[]/', $line))
			{
				continue;
			}

			// Sections
			if ($line[0] == '[')
			{
				$tmp        = explode(']', $line);
				$sections[] = trim(substr($tmp[0], 1));
				$i++;
				continue;
			}

			// Key-value pair
			$lineParts = explode('=', $line, 2);
			if (count($lineParts) != 2)
			{
				continue;
			}
			$key   = trim($lineParts[0]);
			$value = trim($lineParts[1]);
			unset($lineParts);

			if (strstr($value, ";"))
			{
				$tmp = explode(';', $value);
				if (count($tmp) == 2)
				{
					if ((($value[0] != '"') && ($value[0] != "'")) ||
						preg_match('/^".*"\s*;/', $value) || preg_match('/^".*;[^"]*$/', $value) ||
						preg_match("/^'.*'\s*;/", $value) || preg_match("/^'.*;[^']*$/", $value)
					)
					{
						$value = $tmp[0];
					}
				}
				else
				{
					if ($value[0] == '"')
					{
						$value = preg_replace('/^"(.*)".*/', '$1', $value);
					}
					elseif ($value[0] == "'")
					{
						$value = preg_replace("/^'(.*)'.*/", '$1', $value);
					}
					else
					{
						$value = $tmp[0];
					}
				}
			}
			$value = trim($value);
			$value = trim($value, "'\"");

			if ($i == 0)
			{
				if (substr($line, -1, 2) == '[]')
				{
					$globals[$key][] = $value;
				}
				else
				{
					$globals[$key] = $value;
				}
			}
			else
			{
				if (substr($line, -1, 2) == '[]')
				{
					$values[$i - 1][$key][] = $value;
				}
				else
				{
					$values[$i - 1][$key] = $value;
				}
			}
		}

		for ($j = 0; $j < $i; $j++)
		{
			if ($process_sections === true)
			{
				if (isset($sections[$j]) && isset($values[$j]))
				{
					$result[$sections[$j]] = $values[$j];
				}
			}
			else
			{
				if (isset($values[$j]))
				{
					$result[] = $values[$j];
				}
			}
		}

		return $result + $globals;
	}
}
