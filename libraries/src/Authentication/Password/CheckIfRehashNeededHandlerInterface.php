<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Authentication\Password;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface for a password handler which supports checking if the password requires rehashing
 *
 * @since  4.0.0
 */
interface CheckIfRehashNeededHandlerInterface
{
    /**
     * Check if the password requires rehashing
     *
     * @param   string  $hash  The password hash to check
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function checkIfRehashNeeded(string $hash): bool;
}
