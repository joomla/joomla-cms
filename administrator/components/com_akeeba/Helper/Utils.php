<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Helper;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Joomla\CMS\Uri\Uri;
use Joomla\Filter\InputFilter;

class Utils
{
	/**
	 * Returns the relative path of directory $to to root path $from
	 *
	 * @param   string  $from  Root directory
	 * @param   string  $to    The directory whose path we want to find relative to $from
	 *
	 * @return  string  The relative path
	 */
	public static function getRelativePath($from, $to)
	{
		// some compatibility fixes for Windows paths
		$from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
		$to   = is_dir($to) ? rtrim($to, '\/') . '/' : $to;
		$from = str_replace('\\', '/', $from);
		$to   = str_replace('\\', '/', $to);

		$from    = explode('/', $from);
		$to      = explode('/', $to);
		$relPath = $to;

		foreach ($from as $depth => $dir)
		{
			// find first non-matching dir
			if ($dir === $to[$depth])
			{
				// ignore this directory
				array_shift($relPath);

				continue;
			}

			// Get number of remaining dirs to $from
			$remaining = count($from) - $depth;

			if ($remaining > 1)
			{
				// add traversals up to first matching dir
				$padLength = (count($relPath) + $remaining - 1) * -1;
				$relPath   = array_pad($relPath, $padLength, '..');

				break;
			}

			$relPath[0] = './' . $relPath[0];
		}

		return implode('/', $relPath);
	}

	/**
	 * Escapes a string for use with Javascript
	 *
	 * @param   string  $string  The string to escape
	 * @param   string  $extras  The characters to escape
	 *
	 * @return  string
	 */
	static function escapeJS($string, $extras = '')
	{
		// Make sure we escape single quotes, slashes and brackets
		if (empty($extras))
		{
			$extras = "'\\[]";
		}

		return addcslashes($string, $extras);
	}

	/**
	 * Safely decode a return URL, used in the Backup view.
	 *
	 * Return URLs can have two sources:
	 * - The Backup on Update plugin. In this case the URL is base sixty four encoded and we need to decode it first.
	 * - A custom backend menu item. In this case the URL is a simple string which does not need decoding.
	 *
	 * Further to that, we have to make a few security checks:
	 * - The URL must be internal, i.e. starts with our site's base URL or index.php (this check is executed by Joomla)
	 * - It must not contain single quotes, double quotes, lower than or greater than signs (could be used to execute
	 *   arbitrary JavaScript).
	 *
	 * If any of these violations is detected we return an empty string.
	 *
	 * @param   ?string  $returnUrl
	 *
	 * @return  string
	 */
	static function safeDecodeReturnUrl($returnUrl)
	{
		// Nulls and non-strings are not allowed
		if (is_null($returnUrl) || !is_string($returnUrl))
		{
			return '';
		}

		// Make sure it's not an empty string
		$returnUrl = trim($returnUrl);

		if (empty($returnUrl))
		{
			return '';
		}

		// Decode a base sixty four encoded string.
		$filter  = new InputFilter();
		$encoded = $filter->clean($returnUrl, 'base64');

		if (($returnUrl == $encoded) && (strpos($returnUrl, 'index.php') === false))
		{
			$possibleReturnUrl = base64_decode($returnUrl);

			if ($possibleReturnUrl !== false)
			{
				$returnUrl = $possibleReturnUrl;
			}
		}

		// Check if it's an internal URL
		if (!Uri::isInternal($returnUrl))
		{
			return '';
		}

		$disallowedCharacters = ['"', "'", '>', '<'];

		foreach ($disallowedCharacters as $check)
		{
			if (strpos($returnUrl, $check) !== false)
			{
				return '';
			}
		}

		return $returnUrl;
	}
}
