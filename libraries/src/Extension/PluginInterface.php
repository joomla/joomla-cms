<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension;

use Joomla\Event\DispatcherAwareInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
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
     *
     * @deprecated  5.2 will be removed in 7.0
     *              Plugin should implement SubscriberInterface.
     *              These plugins will be added to dispatcher in PluginHelper::import().
     */
    public function registerListeners();
}
