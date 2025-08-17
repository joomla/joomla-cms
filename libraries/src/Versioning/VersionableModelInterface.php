<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Versioning;

// phpcs:disable PSR1.Files.SideEffects

\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface for a versionable model.
 *
 * @since  __DEPLOY_VERSION__
 */
interface VersionableModelInterface
{
    /**
     * Method to load a row for editing from the version history table.
     *
     * @param   integer  $historyId  Key to the version history table.
     *
     * @return  boolean  False on failure or error, true otherwise.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function loadHistory(int $historyId);
}
