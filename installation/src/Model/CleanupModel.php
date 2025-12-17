<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Model
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Model;

use Joomla\Filesystem\Exception\FilesystemException;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Cleanup model for the Joomla Core Installer.
 *
 * @since  4.0.0
 */
class CleanupModel extends BaseInstallationModel
{
    /**
     * Deletes the installation folder. Returns true on success.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function deleteInstallationFolder()
    {
        // First, we try to delete the installation folder
        try {
            /**
             * Windows workaround: The installation folder may fail to be deleted
             * because the php process still holds a lock on index.php until
             * the request ends. This can be solved by moving the index.php out
             * of the installation folder before deleting the folder.
             * The index.php will be deleted immediately after the move.
             * The OS will finish the deletion after the php process ends.
             */
            if (PHP_OS_FAMILY === 'Windows') {
                // Create a temporary unique filename in case the file couldn't be deleted
                $tmpFile = JPATH_ROOT . '/tmp/deleted_' . uniqid() . '.php';
                File::move(JPATH_INSTALLATION . '/index.php', $tmpFile);
                File::delete($tmpFile);
            }

            Folder::delete(JPATH_INSTALLATION);
        } catch (FilesystemException $e) {
            return false;
        }

        // Remove file joomla.xml in root folder if it exists
        if (file_exists(JPATH_ROOT . '/joomla.xml')) {
            try {
                File::delete(JPATH_ROOT . '/joomla.xml');
            } catch (FilesystemException $e) {
                return false;
            }
        }

        // Rename the robots.txt.dist file if robots.txt doesn't exist
        if (!file_exists(JPATH_ROOT . '/robots.txt') && file_exists(JPATH_ROOT . '/robots.txt.dist')) {
            try {
                File::move(JPATH_ROOT . '/robots.txt.dist', JPATH_ROOT . '/robots.txt');
            } catch (FilesystemException $e) {
                return false;
            }
        }

        clearstatcache(true, JPATH_INSTALLATION . '/index.php');

        return true;
    }
}
