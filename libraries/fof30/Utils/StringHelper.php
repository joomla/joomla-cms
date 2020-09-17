<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Utils;

defined('_JEXEC') || die;

use JLoader;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

abstract class StringHelper
{
	/**
	 * Convert a string into a slug (alias), suitable for use in URLs. Please
	 * note that transliteration support is rudimentary at this stage.
	 *
	 * @param   string  $value  A string to convert to slug
	 *
	 * @return  string  The slug
	 *
	 * @deprecated  3.0  Use \JApplicationHelper::stringURLSafe instead
	 *
	 * @codeCoverageIgnore
	 */
	public static function toSlug($value)
	{
		if (class_exists('\JLog'))
		{
			Log::add('FOF30\\Utils\\StringHelper::toSlug is deprecated. Use \\JApplicationHelper::stringURLSafe instead', Log::WARNING, 'deprecated');
		}

		return ApplicationHelper::stringURLSafe($value);
	}

	/**
	 * Convert common northern European languages' letters into plain ASCII. This
	 * is a rudimentary transliteration.
	 *
	 * @param   string  $value  The value to convert to ASCII
	 *
	 * @return  string  The converted string
	 *
	 * @deprecated   3.0  Use JFactory::getLanguage()->transliterate instead
	 *
	 * @codeCoverageIgnore
	 */
	public static function toASCII($value)
	{
		if (class_exists('\JLog'))
		{
			Log::add('FOF30\\Utils\\StringHelper::toASCII is deprecated. Use JFactory::getLanguage()->transliterate instead', Log::WARNING, 'deprecated');
		}

		$lang = Factory::getLanguage();

		return $lang->transliterate($value);
	}

	/**
	 * Convert a string to a boolean.
	 *
	 * @param   string  $string  The string.
	 *
	 * @return  boolean  The converted string
	 */
	public static function toBool($string)
	{
		$string = trim((string) $string);
		$string = strtolower($string);

		if (in_array($string, [1, 'true', 'yes', 'on', 'enabled'], true))
		{
			return true;
		}

		if (in_array($string, [0, 'false', 'no', 'off', 'disabled'], true))
		{
			return false;
		}

		return (bool) $string;
	}
}
