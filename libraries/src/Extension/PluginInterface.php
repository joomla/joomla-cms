<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension;

use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\Event;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Access to plugin specific services.
 *
 * @since  4.0.0
 */
interface PluginInterface extends DispatcherAwareInterface
{
    /**
     * Registers its listeners.
     *
     * @return  void
     *
     * @since   4.0.0
     * @deprecated 5.0 Use SubscriberInterface. This method will be removed in 6.0.
     */
    public function registerListeners();

    /**
     * Initialises the plugin before each event is handled.
     *
     * Override the doInitialise() method in your class with your initialisation code.
     *
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    public function initialisePlugin(Event $e): void;
}
