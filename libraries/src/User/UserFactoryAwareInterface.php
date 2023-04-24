<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\User;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface to be implemented by classes depending on a user factory.
 *
 * @since  __DEPLOY_VERSION__
 */
interface UserFactoryAwareInterface
{
    /**
     * Set the user factory to use.
     *
     * @param   UserFactoryInterface  $factory  The user factory to use.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setUserFactory(UserFactoryInterface $factory): void;
}
