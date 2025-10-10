<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.folderinstaller
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Installer\Folder\Extension;

use Joomla\CMS\Event\Installer\AddInstallationTabEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * FolderInstaller Plugin.
 *
 * @since  3.6.0
 */
final class FolderInstaller extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return ['onInstallerAddInstallationTab' => 'onInstallerAddInstallationTab'];
    }

    /**
     * Installer add Installation Tab listener.
     *
     * @param   AddInstallationTabEvent  $event  The event instance
     *
     * @return  void
     *
     * @since   3.6.0
     */
    public function onInstallerAddInstallationTab(AddInstallationTabEvent $event)
    {
        // Load language files
        $this->loadLanguage();

        $tab            = [];
        $tab['name']    = 'folder';
        $tab['label']   = $this->getApplication()->getLanguage()->_('PLG_INSTALLER_FOLDERINSTALLER_TEXT');

        // Render the input
        ob_start();
        include PluginHelper::getLayoutPath('installer', 'folderinstaller');
        $tab['content'] = ob_get_clean();

        $event->addResult($tab);
    }
}
