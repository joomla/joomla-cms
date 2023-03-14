<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Filesystem;

use Joomla\Filesystem\Stream as FilesystemStream;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Stream Interface
 *
 * The Joomla! stream interface is designed to handle files as streams
 * where as the legacy File static class treated files in a rather
 * atomic manner.
 *
 * @note   This class adheres to the stream wrapper operations:
 * @link   https://www.php.net/manual/en/function.stream-get-wrappers.php
 * @link   https://www.php.net/manual/en/intro.stream.php PHP Stream Manual
 * @link   https://www.php.net/manual/en/wrappers.php Stream Wrappers
 * @link   https://www.php.net/manual/en/filters.php Stream Filters
 * @link   https://www.php.net/manual/en/transports.php Socket Transports (used by some options, particularly HTTP proxy)
 * @since  1.7.0
 */
class Stream extends FilesystemStream
{
}
