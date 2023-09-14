<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Filesystem;

use Joomla\Filesystem\Folder as FilesystemFolder;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A Folder handling class
 *
 * @since  1.7.0
 * @deprecated  4.4 will be removed in 6.0
 *              Use Joomla\Filesystem\Folder instead.
 */
abstract class Folder extends FilesystemFolder
{
    /**
     * Wrapper for the standard file_exists function
     *
     * @param   string  $path  Folder name relative to installation dir
     *
     * @return  boolean  True if path is a folder
     *
     * @since   1.7.0
     * @deprecated  4.4 will be removed in 6.0
     *              Use is_dir() instead.
     */
    public static function exists($path)
    {
        return is_dir(Path::clean($path));
    }

    /**
     * Utility function to read the files in a folder.
     *
     * @param   string   $path           The path of the folder to read.
     * @param   string   $filter         A filter for file names.
     * @param   mixed    $recurse        True to recursively search into sub-folders, or an integer to specify the maximum depth.
     * @param   boolean  $full           True to return the full path to the file.
     * @param   array    $exclude        Array with names of files which should not be shown in the result.
     * @param   array    $excludeFilter  Array of filter to exclude
     * @param   boolean  $naturalSort    False for asort, true for natsort
     *
     * @return  array|boolean  Files in the given folder.
     *
     * @since   1.7.0
     * @deprecated  4.4 will be removed in 6.0
     *              Use Joomla\Filesystem\Folder::files() instead.
     *              The framework class throws Exceptions in case of error which you have to catch.
     */
    public static function files(
        $path,
        $filter = '.',
        $recurse = false,
        $full = false,
        $exclude = ['.svn', 'CVS', '.DS_Store', '__MACOSX'],
        $excludeFilter = ['^\..*', '.*~'],
        $naturalSort = false
    ) {
        $arr = parent::files($path, $filter, $recurse, $full, $exclude, $excludeFilter);

        // Sort the files based on either natural or alpha method
        if ($naturalSort) {
            natsort($arr);
        } else {
            asort($arr);
        }

        return array_values($arr);
    }
}
