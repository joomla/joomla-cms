<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_version
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Version\Administrator\Helper;

use Joomla\CMS\Version;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_version
 *
 * @since  1.6
 */
class VersionHelper
{
    /**
     * Get the Joomla version number.
     *
     * @return  string  String containing the current Joomla version.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getVersionString()
    {
        $version = new Version();

        return '&#x200E;' . $version->getShortVersion();
    }

    /**
     * Get the Joomla version number.
     *
     * @return  string  String containing the current Joomla version.
     *
     * @deprecated __DEPLOY_VERSION__ will be removed in 6.0
     *             Use the non-static method getVersionString
     *             Example: Factory::getApplication()->bootModule('mod_version', 'administrator')
     *                            ->getHelper('VersionHelper')
     *                            ->getVersionString()
     */
    public function getVersion()
    {
        return (new self())->getVersionString();
    }
}
