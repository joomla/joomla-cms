<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Filesystem;

\defined('_JEXEC') or die;

use Joomla\Filesystem\Path as FilesystemPath;

if (!\defined('JPATH_ROOT')) {
    // Define a string constant for the root directory of the file system in native format
    \define('JPATH_ROOT', Path::clean(JPATH_SITE));
}

/**
 * A Path handling class
 *
 * @since  1.7.0
 * @deprecated  4.4 will be removed in 6.0
 *              Use Joomla\Filesystem\Path instead.
 */
class Path extends FilesystemPath
{
    /**
     * Checks for snooping outside of the file system root.
     *
     * @param   string  $path  A file system path to check.
     *
     * @return  string  A cleaned version of the path or exit on error.
     *
     * @throws  \Exception
     * @since   1.7.0
     * @deprecated  4.4 will be removed in 6.0
     *              Use Joomla\Filesystem\Path::check() instead.
     */
    public static function check($path, $basePath = '')
    {
        if ($basePath == '') {
            $basePath = JPATH_ROOT;
        }

        return parent::check($path, $basePath);
    }

    /**
     * Method to determine if script owns the path.
     *
     * @param   string  $path  Path to check ownership.
     *
     * @return  boolean  True if the php script owns the path passed.
     *
     * @since   1.7.0
     * @deprecated  4.4 will be removed in 6.0
     *              Use Joomla\Filesystem\Path::isOwner() instead.
     */
    public static function isOwner($path)
    {
        $tmp = md5(random_bytes(16));
        $ssp = \ini_get('session.save_path');
        $jtp = JPATH_SITE . '/tmp';

        // Try to find a writable directory
        $dir = false;

        foreach ([$jtp, $ssp, '/tmp'] as $currentDir) {
            if (is_writable($currentDir)) {
                $dir = $currentDir;

                break;
            }
        }

        if ($dir) {
            $test = $dir . '/' . $tmp;

            // Create the test file
            $blank = '';
            File::write($test, $blank, false);

            // Test ownership
            $return = (fileowner($test) == fileowner($path));

            // Delete the test file
            File::delete($test);

            return $return;
        }

        return false;
    }
}
