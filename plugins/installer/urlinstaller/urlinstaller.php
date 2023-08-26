<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.urlinstaller
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * UrlFolderInstaller Plugin.
 *
 * @since  3.6.0
 */
class PlgInstallerUrlInstaller extends CMSPlugin
{
    /**
     * Application object.
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

        $tab            = [];
        $tab['name']    = 'url';
        $tab['label']   = Text::_('PLG_INSTALLER_URLINSTALLER_TEXT');

        // Render the input
        ob_start();
        include PluginHelper::getLayoutPath('installer', 'urlinstaller');
        $tab['content'] = ob_get_clean();

        return $tab;
    }
}
