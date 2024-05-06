<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event;

use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface for configurable event subscriber.
 *
 * @since  __DEPLOY_VERSION__
 */
interface ConfigurableSubscriberInterface
{
    /**
     * Method allows to set up custom event listeners, wich is not possible to set up with generic SubscriberInterface
     * (such as LazyServiceEventListener, private listeners etc.).
     * And/or set the listeners only when specific environment requirement are meets (such as the app client id).
     *
     * @param  \Joomla\Event\DispatcherInterface  $dispatcher  The dispatcher instance.
     *
     * @return void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function configureListeners(DispatcherInterface $dispatcher): void;
}
