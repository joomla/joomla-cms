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
 * @since  6.0.0
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
     * @since   6.0.0
     */
    public function loadHistory(int $historyId);

    /**
     * Method to save the history.
     *
     * @param   array   $data     The form data.
     * @param   string  $context  The model context.
     *
     * @return  boolean  True on success, False on error.
     *
     * @since   6.0.0
     */
    public function saveHistory(array $data, string $context);

    /**
     * Method to delete the history for an item.
     *
     * @param   string   $typeAlias  Typealias of the content type
     * @param   integer  $id         ID of the content item to delete
     *
     * @return  boolean  true on success, otherwise false.
     *
     * @since   6.0.0
     */
    public function deleteHistory($typeAlias, $id);
}
