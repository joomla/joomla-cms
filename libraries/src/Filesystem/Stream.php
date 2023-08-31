<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Filesystem;

use Joomla\CMS\Object\LegacyErrorHandlingTrait;
use Joomla\Filesystem\Exception\FilesystemException;
use Joomla\Filesystem\Stream as FilesystemStream;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
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
 * @deprecated  4.4 will be removed in 6.0
 *              Use Joomla\Filesystem\Stream instead.
 */
class Stream extends FilesystemStream
{
    use LegacyErrorHandlingTrait;

    /**
     * Upload a file
     *
     * @param   string    $src        The file path to copy from (usually a temp folder).
     * @param   string    $dest       The file path to copy to.
     * @param   resource  $context    A valid context resource (optional) created with stream_context_create.
     * @param   boolean   $usePrefix  Controls the use of a prefix (optional).
     * @param   boolean   $relative   Determines if the filename given is relative. Relative paths do not have JPATH_ROOT stripped.
     *
     * @return  mixed
     *
     * @since   1.7.0
     * @deprecated  4.4 will be removed in 6.0
     *              Use Joomla\Filesystem\Stream::upload() instead.
     *              The framework class throws Exceptions in case of error which you have to catch.
     */
    public function upload($src, $dest, $context = null, $usePrefix = true, $relative = false)
    {
        try {
            return parent::upload($src, $dest, $context, $usePrefix, $relative);
        } catch (FilesystemException $e) {
            $this->setError($e->getMessage());

            return false;
        }
    }
}
