<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Extension.namespacemap
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Extension\NamespaceMap\Extension;

use Joomla\CMS\Event\Extension\AfterInstallEvent;
use Joomla\CMS\Event\Extension\AfterUninstallEvent;
use Joomla\CMS\Event\Extension\AfterUpdateEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! namespace map creator / updater.
 *
 * @since  4.0.0
 */
final class NamespaceMap extends CMSPlugin implements SubscriberInterface
{
    /**
     * The namespace map file creator
     *
     * @var \JNamespacePsr4Map
     */
    private $fileCreator = null;

    /**
     * Constructor
     *
     * @param   DispatcherInterface  $subject  The object to observe
     * @param   \JNamespacePsr4Map   $map      The namespace map creator
     * @param   array                $config   An optional associative array of configuration settings.
     *                                         Recognized key values include 'name', 'group', 'params', 'language'
     *                                         (this list is not meant to be comprehensive).
     *
     * @since   4.0.0
     */
    public function __construct(DispatcherInterface $dispatcher, \JNamespacePsr4Map $map, array $config = [])
    {
        $this->fileCreator = $map;

        parent::__construct($dispatcher, $config);
    }

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   5.2.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onExtensionAfterInstall'   => 'onExtensionAfterInstall',
            'onExtensionAfterUpdate'    => 'onExtensionAfterUpdate',
            'onExtensionAfterUninstall' => 'onExtensionAfterUninstall',
        ];
    }

    /**
     * Update / Create map on extension install
     *
     * @param   AfterInstallEvent $event  Event instance.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onExtensionAfterInstall(AfterInstallEvent $event): void
    {
        // Check that we have a valid extension
        if ($event->getEid()) {
            // Update / Create new map
            $this->fileCreator->create();
        }
    }

    /**
     * Update / Create map on extension uninstall
     *
     * @param   AfterUninstallEvent $event  Event instance.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onExtensionAfterUninstall(AfterUninstallEvent $event): void
    {
        // Check that we have a valid extension and that it has been removed
        if ($event->getEid() && $event->getRemoved()) {
            // Update / Create new map
            $this->fileCreator->create();
        }
    }

    /**
     * Update map on extension update
     *
     * @param   AfterUpdateEvent $event  Event instance.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onExtensionAfterUpdate(AfterUpdateEvent $event): void
    {
        // Check that we have a valid extension
        if ($event->getEid()) {
            // Update / Create new map
            $this->fileCreator->create();
        }
    }
}
