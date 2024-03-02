<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.urlinstaller
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Installer\Url\Extension;

use Joomla\CMS\Event\Installer\AddInstallationTabEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * UrlFolderInstaller Plugin.
 *
 * @since  3.6.0
 */
final class UrlInstaller extends CMSPlugin implements SubscriberInterface
{
    /**
     * Application object.
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  4.0.0
     * @deprecated 6.0 Is needed for template overrides, use getApplication instead
     */
    protected $app;

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
        $tab['name']    = 'url';
        $tab['label']   = $this->getApplication()->getLanguage()->_('PLG_INSTALLER_URLINSTALLER_TEXT');

        // Render the input
        ob_start();
        include PluginHelper::getLayoutPath('installer', 'urlinstaller');
        $tab['content'] = ob_get_clean();

        $event->addResult($tab);
    }
}
