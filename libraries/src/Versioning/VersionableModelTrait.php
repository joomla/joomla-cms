<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Versioning;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\ContentHistory;
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
     * Method to get the item id from the version history table.
     *
     * @param   integer  $historyId  Key to the version history table.
     *
     * @return  integer  False on failure or error, id otherwise.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getItemIdFromHistory($historyId)
    {
        $rowArray = $this->getHistoryData($historyId);

        if (false === $rowArray) {
            return false;
        }

        $table = $this->getTable();
        $key   = $table->getKeyName();

        if (isset($rowArray[$key])) {
            return $rowArray[$key];
        }

        return false;
    }

    /**
     * Method to get the version data from the version history table.
     *
     * @param   integer  $historyId  Key to the version history table.
     *
     * @return  mixed    False on failure or error, data otherwise.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getHistoryData($historyId)
    {
        // Get an instance of the row to checkout.
        $historyTable = new ContentHistory($this->getDatabase());

        if (!$historyTable->load($historyId)) {
            return false;
        }

        $rowArray = ArrayHelper::fromObject(json_decode($historyTable->version_data));

        return $rowArray;
    }

    /**
     * Method to get a version history table.
     *
     * @param   integer  $historyId  Key to the version history table.
     *
     * @return  mixed    False on failure or error, table otherwise.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getHistoryTable($historyId)
    {
        if (empty($historyId)) {
            return false;
        }

        // Get an instance of the row to checkout.
        $historyTable = new ContentHistory($this->getDatabase());

        if (!$historyTable->load($historyId)) {
            return false;
        }

        return $historyTable;
    }

    /**
     * Method to load a row for editing from the version history table.
     *
     * @param   integer  $historyId  Key to the version history table.
     *
     * @return  boolean  False on failure or error, true otherwise.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function loadHistory(int $historyId)
    {
        $rowArray = $this->getHistoryData($historyId);

        if (false === $rowArray) {
            return false;
        }

        // Fix null ordering when restoring history
        if (\array_key_exists('ordering', $rowArray) && $rowArray['ordering'] === null) {
            $rowArray['ordering'] = 0;
        }

        [$extension, $type] = explode('.', $this->typeAlias);

        $app  = Factory::getApplication();
        $app->setUserState($extension . '.edit.' . $type . '.data', $rowArray);

        $historyTable = $this->getHistoryTable($historyId);

        $this->setState('save_date', $historyTable->save_date);
        $this->setState('version_note', $historyTable->version_note);

        return true;
    }
}
