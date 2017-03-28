<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Extended Utility class for render debug information.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class JHtmlDebug
{
	/**
	 * xdebug.file_link_format from the php.ini.
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $xdebugLinkFormat;

	/**
	 * Render backtrace table by JLayout.
	 *
	 * @param   array  $backtrace The backtrace array from exception or debug_backtrace() function.
	 *
	 * @return  string  The backtrace table HTML.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function backtrace(array $backtrace)
	{
		if (!$backtrace)
		{
			return '';
		}

		return JLayoutHelper::render('joomla.error.backtrace', array('backtrace' => $backtrace));
	}

	/**
	 * Replaces the Joomla! root with "JROOT" to improve readability.
	 * Formats a link with a special value xdebug.file_link_format
	 * from the php.ini file.
	 *
	 * @param   string $file The full path to the file.
	 * @param   string $line The line number.
	 *
	 * @return  string
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function xdebuglink($file, $line = '')
	{
		if (static::$xdebugLinkFormat === null)
		{
			static::$xdebugLinkFormat = ini_get('xdebug.file_link_format');
		}

		$link = str_replace(JPATH_ROOT, 'JROOT', $file);
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
