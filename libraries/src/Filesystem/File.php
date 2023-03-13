<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Filesystem;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Filesystem\File as Filesystem_File;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A File handling class
 *
 * @since  1.7.0
 */
class File extends Filesystem_File
{
    /**
     * @var    boolean  true if OPCache enabled, and we have permission to invalidate files
     * @since  4.0.1
     */
    protected static $canFlushFileCache;

    /**
     * Gets the extension of a file name
     *
     * @param   string  $file  The file name
     *
     * @return  string  The file extension
     *
     * @since   1.7.0
     */
    public static function getExt($file)
    {
        // String manipulation should be faster than pathinfo() on newer PHP versions.
        $dot = strrpos($file, '.');

        if ($dot === false) {
            return '';
        }

        $ext = substr($file, $dot + 1);

        // Extension cannot contain slashes.
        if (strpos($ext, '/') !== false || (DIRECTORY_SEPARATOR === '\\' && strpos($ext, '\\') !== false)) {
            return '';
        }

        return $ext;
    }

    /**
     * Makes file name safe to use
     *
     * @param   string    $file        The name of the file [not full path]
     * @param   string[]  $stripChars  Array of regex (by default will remove any leading periods)
     *
     * @return  string  The sanitised string
     *
     * @since   1.7.0
     */
    public static function makeSafe($file, array $stripChars = ['#^\.#'])
    {
        // Try transliterating the file name using the native php function
        if (function_exists('transliterator_transliterate') && function_exists('iconv')) {
            // Using iconv to ignore characters that can't be transliterated
            $file = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", transliterator_transliterate('Any-Latin; Latin-ASCII', $file));
        }

        return parent::makeSafe($file, $stripChars);
    }

    /**
     * First we check if opcache is enabled
     * Then we check if the opcache_invalidate function is available
     * Lastly we check if the host has restricted which scripts can use opcache_invalidate using opcache.restrict_api.
     *
     * `$_SERVER['SCRIPT_FILENAME']` approximates the origin file's path, but `realpath()`
     * is necessary because `SCRIPT_FILENAME` can be a relative path when run from CLI.
     * If the host has this set, check whether the path in `opcache.restrict_api` matches
     * the beginning of the path of the origin file.
     *
     * @return boolean TRUE if we can proceed to use opcache_invalidate to flush a file from the OPCache
     *
     * @since 4.0.1
     */
    public static function canFlushFileCache()
    {
        if (isset(static::$canFlushFileCache)) {
            return static::$canFlushFileCache;
        }

        if (
            ini_get('opcache.enable')
            && function_exists('opcache_invalidate')
            && (!ini_get('opcache.restrict_api') || stripos(realpath($_SERVER['SCRIPT_FILENAME']), ini_get('opcache.restrict_api')) === 0)
        ) {
            static::$canFlushFileCache = true;
        } else {
            static::$canFlushFileCache = false;
        }

        return static::$canFlushFileCache;
    }

    /**
     * Append contents to a file
     *
     * @param   string   $file        The full file path
     * @param   string   $buffer      The buffer to write
     * @param   boolean  $useStreams  Use streams
     *
     * @return  boolean  True on success
     *
     * @since   3.6.0
     */
    public static function append($file, $buffer, $useStreams = false)
    {
        @set_time_limit(ini_get('max_execution_time'));

        // If the file doesn't exist, just write instead of append
        if (!file_exists($file)) {
            return self::write($file, $buffer, $useStreams);
        }

        if ($useStreams) {
            $stream = Factory::getStream();

            // Beef up the chunk size to a meg
            $stream->set('chunksize', (1024 * 1024));

            if ($stream->open($file, 'ab') && $stream->write($buffer) && $stream->close()) {
                self::invalidateFileCache($file);

                return true;
            }

            Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_WRITE_STREAMS', __METHOD__, $file, $stream->getError()), Log::WARNING, 'jerror');

            return false;
        } else {
            $file = Path::clean($file);
            $ret  = \is_int(file_put_contents($file, $buffer, FILE_APPEND));

            self::invalidateFileCache($file);

            return $ret;
        }
    }

    /**
     * Moves an uploaded file to a destination folder
     *
     * @param   string  $src              The name of the php (temporary) uploaded file
     * @param   string  $dest             The path (including filename) to move the uploaded file to
     * @param   bool    $useStreams       True to use streams
     * @param   bool    $allowUnsafe      Allow the upload of unsafe files
     * @param   array   $safeFileOptions  Options to InputFilter::isSafeFile
     *
     * @return  bool  True on success
     *
     * @since   1.7.0
     */
    public static function upload($src, $dest, $useStreams = false, $allowUnsafe = false, $safeFileOptions = [])
    {
        if (!$allowUnsafe) {
            $descriptor = [
                'tmp_name' => $src,
                'name'     => basename($dest),
                'type'     => '',
                'error'    => '',
                'size'     => '',
            ];

            $isSafe = InputFilter::isSafeFile($descriptor, $safeFileOptions);

            if (!$isSafe) {
                Log::add(Text::sprintf('JLIB_FILESYSTEM_ERROR_WARNFS_ERR03', $dest), Log::WARNING, 'jerror');

                return false;
            }
        }

        return parent::upload($src, $dest, $useStreams);
    }

    /**
     * Wrapper for the standard file_exists function
     *
     * @param   string  $file  File path
     *
     * @return  boolean  True if path is a file
     *
     * @since   1.7.0
     * @deprecated  5.0 will be removed in 6.0 use is_file() directly
     */
    public static function exists($file)
    {
        return is_file(Path::clean($file));
    }
}
