<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Extended Utility class for render debug information.
 *
 * @since  3.7.0
 */
abstract class JHtmlDebug
{
	/**
	 * xdebug.file_link_format from the php.ini.
	 *
	 * Make this property public to support test.
	 *
	 * @var    string
	 *
	 * @since  3.7.0
	 */
	public static $xdebugLinkFormat;

	/**
	 * Replaces the Joomla! root with "JROOT" to improve readability.
	 * Formats a link with a special value xdebug.file_link_format
	 * from the php.ini file.
	 *
	 * @param   string  $file  The full path to the file.
	 * @param   string  $line  The line number.
	 *
	 * @return  string
	 *
	 * @throws  \InvalidArgumentException
	 *
	 * @since   3.7.0
	 */
	public static function xdebuglink($file, $line = '')
	{
		if (static::$xdebugLinkFormat === null)
		{
			static::$xdebugLinkFormat = ini_get('xdebug.file_link_format');
		}

		$link = str_replace(JPATH_ROOT, 'JROOT', JPath::clean($file));
		$link .= $line ? ':' . $line : '';

		if (static::$xdebugLinkFormat)
		{
			$href = static::$xdebugLinkFormat;
			$href = str_replace('%f', $file, $href);
			$href = str_replace('%l', $line, $href);

			$html = JHtml::_('link', $href, $link);
		}
		else
		{
			$html = $link;
		}

		return $html;
	}
}
