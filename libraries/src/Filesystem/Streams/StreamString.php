<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Filesystem\Streams;

\defined('JPATH_PLATFORM') or die;

use Joomla\Filesystem\Stream\StringWrapper as Filesystem_StringWrapper;

/**
 * String Stream Wrapper
 *
 * This class allows you to use a PHP string in the same way that
 * you would normally use a regular stream wrapper
 *
 * @since  1.7.0
 * @deprecated  5.0 will be removed in 6.0 use Joomla\Filesystem\Streams\StringWrapper instead
 */
class StreamString extends Filesystem_StringWrapper
{
}
