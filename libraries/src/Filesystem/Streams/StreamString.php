<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Filesystem\Streams;

\defined('_JEXEC') or die;

use Joomla\Filesystem\Stream\StringWrapper as FilesystemStringWrapper;

/**
 * String Stream Wrapper
 *
 * This class allows you to use a PHP string in the same way that
 * you would normally use a regular stream wrapper
 *
 * @since  1.7.0
 * @deprecated  4.4 will be removed in 6.0
 *              Use Joomla\Filesystem\Stream\StringWrapper instead.
 */
class StreamString extends FilesystemStringWrapper
{
}
