<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.packageinstaller
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Installer\Package\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * PackageInstaller Plugin.
 *
 * @since  3.6.0
 */
final class PackageInstaller extends CMSPlugin
{
    /**
     * Application object
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  4.0.0
     * @deprecated 6.0 Is needed for template overrides, use getApplication instead
     */
    protected $app;

    /**
     * Textfield or Form of the Plugin.
     *
     * @return  array  Returns an array with the tab information
     *
     * @since   3.6.0
     */
    public function onInstallerAddInstallationTab()
    {
        // Load language files
        $this->loadLanguage();

        $tab            = [];
        $tab['name']    = 'package';
        $tab['label']   = $this->getApplication()->getLanguage()->_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_PACKAGE_FILE');

        // Render the input
        ob_start();
        include PluginHelper::getLayoutPath('installer', 'packageinstaller');
        $tab['content'] = ob_get_clean();

        return $tab;
    }
}
