<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Important Notes:
 * - Unlike other files, this file requires multiple namespace declarations in order to overload core classes during the update process
 * - Also unlike other files, the normal constant defined checks must be within the global namespace declaration and can't be outside of it
 */

namespace {

    // Require the restoration environment or fail cold. Prevents direct web access.
    \defined('_JOOMLA_UPDATE') or die();

    // Fake a miniature Joomla environment
    if (!\defined('_JEXEC')) {
        \define('_JEXEC', 1);
    }

    if (!\function_exists('jimport')) {
        /**
         * This is deprecated but it may still be used in the update finalisation script.
         *
         * @param   string  $path  Ignored.
         * @param   string  $base  Ignored.
         *
         * @return  boolean  Always true.
         *
         * @since   1.7.0
         */
        function jimport(string $path, ?string $base = null): bool
        {
            // Do nothing
            return true;
        }
    }

    if (!\function_exists('finalizeUpdate')) {
        /**
         * Run part of the Joomla! finalisation script, namely the part that cleans up unused files/folders
         *
         * @param   string  $siteRoot     The root to the Joomla! site
         * @param   string  $restorePath  The base path to extract.php
         *
         * @return  void
         *
         * @since   3.5.1
         */
        function finalizeUpdate(string $siteRoot, string $restorePath): void
        {
            if (!\defined('JPATH_ROOT')) {
                \define('JPATH_ROOT', $siteRoot);
            }

            $filePath = JPATH_ROOT . '/administrator/components/com_admin/script.php';

            if (file_exists($filePath)) {
                require_once $filePath;
            }

            // Make sure Joomla!'s code can figure out which files exist and need be removed
            clearstatcache();

            // Remove obsolete files - prevents errors occurring in some system plugins
            if (class_exists('JoomlaInstallerScript')) {
                (new JoomlaInstallerScript())->deleteUnexistingFiles();
            }

            /**
             * Remove autoload_psr4.php so that namespace map is re-generated on the next request. This is needed
             * when there are new classes added to extensions on new Joomla! release.
             */
            $namespaceMapFile = JPATH_ROOT . '/administrator/cache/autoload_psr4.php';

            if (is_file($namespaceMapFile)) {
                \Joomla\Filesystem\File::delete($namespaceMapFile);
            }
        }
    }
}

namespace Joomla\Filesystem
{
    // Fake the File class
    if (!class_exists('\Joomla\Filesystem\File')) {
        /**
         * File mock class
         *
         * @since  3.5.1
         */
        abstract class File
        {
            /**
             * Proxies checking a file exists to the native php version
             *
             * @param   string  $fileName  The path to the file to be checked
             *
             * @return  boolean
             *
             * @since   3.5.1
             */
            public static function exists(string $fileName): bool
            {
                return @file_exists($fileName);
            }

            /**
             * Delete a file and invalidate the PHP OPcache
             *
             * @param   string  $fileName  The path to the file to be deleted
             *
             * @return  boolean
             *
             * @since   3.5.1
             */
            public static function delete(string $fileName): bool
            {
                self::invalidateFileCache($fileName);

                return @unlink($fileName);
            }

            /**
             * Rename a file and invalidate the PHP OPcache
             *
             * @param   string  $src   The path to the source file
             * @param   string  $dest  The path to the destination file
             *
             * @return  boolean  True on success
             *
             * @since   4.0.1
             */
            public static function move(string $src, string $dest): bool
            {
                self::invalidateFileCache($src);

                $result = @rename($src, $dest);

                if ($result) {
                    self::invalidateFileCache($dest);
                }

                return $result;
            }

            /**
             * Invalidate opcache for a newly written/deleted file immediately, if opcache* functions exist and if this was a PHP file.
             *
             * @param   string  $filepath   The path to the file just written to, to flush from opcache
             * @param   boolean $force      If set to true, the script will be invalidated regardless of whether invalidation is necessary
             *
             * @return  boolean TRUE if the opcode cache for script was invalidated/nothing to invalidate,
             *                  or FALSE if the opcode cache is disabled or other conditions returning
             *                  FALSE from opcache_invalidate (like file not found).
             *
             * @since  4.0.2
             */
            public static function invalidateFileCache($filepath, $force = true)
            {
                return clearFileInOPCache($filepath);
            }
        }
    }

    // Fake the Folder class, mapping it to Restore's post-processing class
    if (!class_exists('\Joomla\Filesystem\Folder')) {
        /**
         * Folder mock class
         *
         * @since  3.5.1
         */
        abstract class Folder
        {
            /**
             * Proxies checking a folder exists to the native php version
             *
             * @param   string  $folderName  The path to the folder to be checked
             *
             * @return  boolean
             *
             * @since   3.5.1
             */
            public static function exists(string $folderName): bool
            {
                return @is_dir($folderName);
            }

            /**
             * Delete a folder recursively and invalidate the PHP OPcache
             *
             * @param   string  $folderName  The path to the folder to be deleted
             *
             * @return  boolean
             *
             * @since   3.5.1
             */
            public static function delete(string $folderName): bool
            {
                if (str_ends_with($folderName, '/')) {
                    $folderName = substr($folderName, 0, -1);
                }

                if (!@file_exists($folderName) || !@is_dir($folderName) || !is_readable($folderName)) {
                    return false;
                }

                $di = new \DirectoryIterator($folderName);

                /** @var \DirectoryIterator $item */
                foreach ($di as $item) {
                    if ($item->isDot()) {
                        continue;
                    }

                    if ($item->isDir()) {
                        $status = self::delete($item->getPathname());

                        if (!$status) {
                            return false;
                        }

                        continue;
                    }

                    clearFileInOPCache($item->getPathname());

                    @unlink($item->getPathname());
                }

                return @rmdir($folderName);
            }
        }
    }

    if (!class_exists('\Joomla\CMS\Filesystem\File')) {
        class_alias('\\Joomla\\Filesystem\\File', '\\Joomla\\CMS\\Filesystem\\File');
    }

    if (!class_exists('\Joomla\CMS\Filesystem\Folder')) {
        class_alias('\\Joomla\\Filesystem\\Folder', '\\Joomla\\CMS\\Filesystem\\Folder');
    }
}

namespace Joomla\CMS\Language
{
    // Fake the Text class - we aren't going to show errors to people anyhow
    if (!class_exists('\Joomla\CMS\Language\Text')) {
        /**
         * Text mock class
         *
         * @since  3.5.1
         */
        abstract class Text
        {
            /**
             * No need for translations in a non-interactive script, so always return an empty string here
             *
             * @param   string  $text  A language constant
             *
             * @return  string
             *
             * @since   3.5.1
             */
            public static function sprintf(string $text): string
            {
                return '';
            }
        }
    }
}
