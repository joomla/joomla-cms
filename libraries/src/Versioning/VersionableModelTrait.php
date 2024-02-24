<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Versioning;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Defines the trait for a Versionable Model Class.
 *
 * @since  3.10.0
 */
trait VersionableModelTrait
{
    /**
     * Method to load a row for editing from the version history table.
     *
     * @param   integer  $versionId  Key to the version history table.
     * @param   Table    $table      Content table object being loaded.
     *
     * @return  boolean  False on failure or error, true otherwise.
     *
     * @since   4.0.0
     */
    public function loadHistory($versionId, Table $table)
    {
        // Only attempt to check the row in if it exists, otherwise do an early exit.
        if (!$versionId) {
            return false;
        }

        // Get an instance of the row to checkout.
        $historyTable = Table::getInstance('ContentHistory');

        if (!$historyTable->load($versionId)) {
            $this->setError($historyTable->getError());

            return false;
        }

        $typeAlias = explode('.', $historyTable->item_id);
        array_pop($typeAlias);

        $rowArray = ArrayHelper::fromObject(json_decode($historyTable->version_data));

        $key = $table->getKeyName();

        if (implode('.', $typeAlias) != $this->typeAlias) {
            $this->setError(Text::_('JLIB_APPLICATION_ERROR_HISTORY_ID_MISMATCH'));

            if (isset($rowArray[$key])) {
                $table->checkIn($rowArray[$key]);
            }

            return false;
        }

        $this->setState('save_date', $historyTable->save_date);
        $this->setState('version_note', $historyTable->version_note);

        /**
         * Load data from current version before replacing it with data from history to avoid error
         * if there are some required keys missing in the history data
         */

        if (isset($rowArray[$key])) {
            $table->load($rowArray[$key]);
        }

        return $table->bind($rowArray);
    }
}
