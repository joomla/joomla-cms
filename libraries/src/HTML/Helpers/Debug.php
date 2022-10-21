<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Extended Utility class for render debug information.
 *
 * @since  3.7.0
 */
abstract class Debug
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
        if (static::$xdebugLinkFormat === null) {
            static::$xdebugLinkFormat = ini_get('xdebug.file_link_format');
        }

        $link = str_replace(JPATH_ROOT, 'JROOT', Path::clean($file));
        $link .= $line ? ':' . $line : '';

        if (static::$xdebugLinkFormat) {
            $href = static::$xdebugLinkFormat;
            $href = str_replace('%f', $file, $href);
            $href = str_replace('%l', $line, $href);

            $html = HTMLHelper::_('link', $href, $link);
        } else {
            $html = $link;
        }

        return $html;
    }
}
