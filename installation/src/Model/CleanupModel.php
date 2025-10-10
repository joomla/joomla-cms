<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Model
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Model;

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
        $return = Folder::delete(JPATH_INSTALLATION) && (!file_exists(JPATH_ROOT . '/joomla.xml') || File::delete(JPATH_ROOT . '/joomla.xml'));

        // Rename the robots.txt.dist file if robots.txt doesn't exist
        if ($return && !file_exists(JPATH_ROOT . '/robots.txt') && file_exists(JPATH_ROOT . '/robots.txt.dist')) {
            $return = File::move(JPATH_ROOT . '/robots.txt.dist', JPATH_ROOT . '/robots.txt');
        }

        clearstatcache(true, JPATH_INSTALLATION . '/index.php');

        return $return;
    }
}
