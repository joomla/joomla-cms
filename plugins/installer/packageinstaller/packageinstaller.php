<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.packageInstaller
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * PackageInstaller Plugin.
 *
 * @since  3.6.0
 */
class PlgInstallerPackageInstaller extends CMSPlugin
{
    /**
     * Application object
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  4.0.0
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

        $tab            = array();
        $tab['name']    = 'package';
        $tab['label']   = Text::_('PLG_INSTALLER_PACKAGEINSTALLER_UPLOAD_PACKAGE_FILE');

        // Render the input
        ob_start();
        include PluginHelper::getLayoutPath('installer', 'packageinstaller');
        $tab['content'] = ob_get_clean();

        return $tab;
    }
}
