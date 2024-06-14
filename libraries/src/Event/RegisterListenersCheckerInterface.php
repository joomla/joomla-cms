<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Provides a method to check whether the Subscriber (or event listener) should be registered.
 *
 * @since  __DEPLOY_VERSION__
 */
interface SubscriberRegistrationCheckerInterface
{
    /**
     * Check whether the Subscriber (or event listener) should be registered.
     *
     * @return bool
     *
     * @since  __DEPLOY_VERSION__
     */
    public function shouldRegisterListeners(): bool;
}
