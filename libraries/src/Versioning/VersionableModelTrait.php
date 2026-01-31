<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Versioning;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\ContentHistory;
use Joomla\CMS\Table\ContentType;
use Joomla\CMS\Workflow\WorkflowServiceInterface;
use Joomla\Database\ParameterType;
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
     * Fields to be ignored when calculating the hash.
     *
     * @var    array
     * @since  6.0.0
     */
    protected $ignoreChanges = [
        'modified_by',
        'modified_user_id',
        'modified',
        'modified_time',
        'checked_out',
        'checked_out_time',
        'tagsHelper',
        'version',
        'articletext',
        'rules',
        'hits',
        'path',
        'newTags',
    ];

    /**
     * Fields to be converted to int when calculating the hash.
     *
     * @var    array
     * @since  6.0.0
     */
    protected $convertToInt = ['publish_up', 'publish_down', 'ordering', 'featured'];

    /**
     * Method to get the item id from the version history table.
     *
     * @param   integer  $historyId  Key to the version history table.
     *
     * @return  integer  False on failure or error, id otherwise.
     *
     * @since   6.0.0
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
     * @since   6.0.0
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
     * @since   6.0.0
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
     * @since   6.0.0
     */
    public function loadHistory(int $historyId)
    {
        $rowArray = $this->getHistoryData($historyId);

        if (false === $rowArray) {
            return false;
        }

        $table = $this->getTable();

        // We set checked_out to the current user
        if ($table->hasField('checked_out')) {
            $rowArray[$table->getColumnAlias('checked_out')] = $this->getCurrentUser()->id;
        }

        if ($table->hasField('checked_out_time')) {
            $rowArray[$table->getColumnAlias('checked_out_time')] = (new Date())->toSql();
        }

        // Fix null ordering when restoring history
        if (\array_key_exists('ordering', $rowArray) && $rowArray['ordering'] === null) {
            $rowArray['ordering'] = 0;
        }

        // Fix null note when restoring history
        if (\array_key_exists('note', $rowArray) && $rowArray['note'] === null) {
            $rowArray['note'] = '';
        }

        $historyTable = $this->getHistoryTable($historyId);

        $this->setState('save_date', $historyTable->save_date);
        $this->setState('version_note', $historyTable->version_note);

        /**
         * The version history is created prior to Joomla 6.0, so we can only restore item data, not a full
         * restore like we can do with Joomla 6.0+ version history.
         */
        if ($historyTable->is_legacy) {
            $key = $table->getKeyName();

            /**
             * Load data from current version before replacing it with data from history to avoid error
             * if there are some required keys missing in the history data
             */
            if (isset($rowArray[$key])) {
                $table->load($rowArray[$key]);
            }

            $table->bind($rowArray);

            if (!$table->check() || !$table->store()) {
                $ret = false;
            } else {
                $ret = true;
            }
        } else {
            $ret = $this->save($rowArray);
        }

        // Mark the restored version as current
        if ($ret) {
            $this->markVersionAsCurrent($historyTable->version_id, $historyTable->item_id);
        }

        return $ret;
    }

    /**
     * Utility method to get the hash after removing selected values. This lets us detect changes other than
     * modified date (which will change on every save).
     *
     * @param   mixed        $data   Either an object or an array
     *
     * @return  string  SHA1 hash on success. Empty string on failure.
     *
     * @since   6.0.0
     */
    public function getSha1($data)
    {
        $object = \is_object($data) ? $data : ArrayHelper::toObject($data);

        foreach ($this->ignoreChanges as $remove) {
            if (property_exists($object, $remove)) {
                unset($object->$remove);
            }
        }

        // Convert integers, booleans, and nulls to strings to get a consistent hash value
        foreach ($object as $name => $value) {
            if (\is_object($value)) {
                // Go one level down for JSON column values
                foreach ($value as $subName => $subValue) {
                    $object->$subName = \is_int($subValue) || \is_bool($subValue) || $subValue === null ? (string) $subValue : $subValue;
                }
            } else {
                $object->$name = \is_int($value) || \is_bool($value) || $value === null ? (string) $value : $value;
            }
        }

        // Work around empty values
        foreach ($this->convertToInt as $convert) {
            if (isset($object->$convert)) {
                $object->$convert = (int) $object->$convert;
            }
        }

        if (isset($object->review_time)) {
            $object->review_time = (int) $object->review_time;
        }

        return sha1(json_encode($object));
    }

    /**
     * Setter for the value
     *
     * @param   array  $ignoreChanges
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setIgnoreChanges(array $ignoreChanges): void
    {
        $this->ignoreChanges = $ignoreChanges;
    }

    /**
     * Setter for the value
     *
     * @param   array  $convertToInt
     *
     * @return  void
     *
     * @since   6.0.0
     */
    public function setConvertToInt(array $convertToInt): void
    {
        $this->convertToInt = $convertToInt;
    }

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
    public function saveHistory(array $data, string $context)
    {
        if (!$this->versionHistoryEnabled($context)) {
            return false;
        }

        $id = $this->getState($this->getName() . '.id');

        /**
         * Merge item data and form data so that we write all data to the history. We have to rel
         * from database to have default data populated for fields which are not passed in $data array,
         * avoid error such as Column 'created_by_alias' cannot be null" when restore from version history
         */
        $table = $this->getTable();
        $table->load($id);
        $itemData = ArrayHelper::fromObject($table);
        $data     = array_merge($data, $itemData);

        $versionNote = '';

        if (\array_key_exists('version_note', $data)) {
            $versionNote = $data['version_note'];
            unset($data['version_note']);
        }

        foreach ($this->ignoreChanges as $ignore) {
            if (\array_key_exists($ignore, $data)) {
                unset($data[$ignore]);
            }
        }

        return $this->storeHistory($context, $id, ArrayHelper::toObject($data), $versionNote);
    }

    /**
     * Method to delete the history for an item.
     *
     * @param   string   $typeAlias  Typealias of the component
     * @param   integer  $id         ID of the content item to delete
     *
     * @return  boolean  true on success, otherwise false.
     *
     * @since   6.0.0
     */
    public function deleteHistory($typeAlias, $id)
    {
        $db     = $this->getDatabase();
        $itemid = $typeAlias . '.' . $id;
        $query  = $db->createQuery();
        $query->delete($db->quoteName('#__history'))
            ->where($db->quoteName('item_id') . ' = :item_id')
            ->bind(':item_id', $itemid, ParameterType::STRING);
        $db->setQuery($query);

        return $db->execute();
    }

    /**
     * Method to save a version snapshot to the content history table.
     *
     * @param   string   $typeAlias  Typealias of the content type
     * @param   integer  $id         ID of the content item
     * @param   mixed    $data       Array or object of data that can be
     *                               en- and decoded into JSON
     * @param   string   $note       Note for the version to store
     *
     * @return  boolean  True on success, otherwise false.
     *
     * @since   6.0.0
     * @throws \Exception
     */
    protected function storeHistory(string $typeAlias, int $id, mixed $data, string $note = '')
    {
        $typeTable = new ContentType($this->getDatabase());
        $typeTable->load(['type_alias' => $typeAlias]);

        $historyTable          = new ContentHistory($this->getDatabase());
        $historyTable->item_id = $typeAlias . '.' . $id;

        [$extension, $type] =  explode('.', $typeAlias);

        // We should allow workflow items interact with the versioning
        $component = Factory::getApplication()->bootComponent($extension);

        if ($component instanceof WorkflowServiceInterface && $component->isWorkflowActive($typeAlias)) {
            PluginHelper::importPlugin('workflow');

            // Pre-processing by observers
            $event = AbstractEvent::create(
                'onContentVersioningPrepareTable',
                [
                    'subject'   => $historyTable,
                    'extension' => $typeAlias,
                ]
            );

            $this->getDispatcher()->dispatch('onContentVersioningPrepareTable', $event);
        }

        // Fix for null ordering - set to 0 if null
        if (\is_object($data)) {
            if (property_exists($data, 'ordering') && $data->ordering === null) {
                $data->ordering = 0;
            }
        } elseif (\is_array($data)) {
            if (\array_key_exists('ordering', $data) && $data['ordering'] === null) {
                $data['ordering'] = 0;
            }
        }

        $historyTable->version_data = json_encode($data);
        $historyTable->version_note = $note;

        // Don't save if hash already exists and same version note
        $historyTable->sha1_hash = $this->getSha1($data);

        $historyRow = $historyTable->getHashMatch();

        if ($historyRow) {
            if (!$note || ($historyRow->version_note === $note)) {
                return true;
            }

            // Update existing row to set version note
            $historyTable->version_id = $historyRow->version_id;
        }

        $result = $historyTable->store();

        // Load history_limit config from extension.
        $context = $type ?? '';

        $maxVersionsContext = ComponentHelper::getParams($extension)->get('history_limit_' . $context, 0);
        $maxVersions        = ComponentHelper::getParams($extension)->get('history_limit', 0);

        if ($maxVersionsContext) {
            $historyTable->deleteOldVersions($maxVersionsContext);
        } elseif ($maxVersions) {
            $historyTable->deleteOldVersions($maxVersions);
        }

        // Mark this version as current
        $this->markVersionAsCurrent($historyTable->version_id, $historyTable->item_id);

        return $result;
    }

    /**
     * Method to mark a version as current. When a version is marked as current, all other versions of same
     * content item will be marked as not current.
     *
     * @param   integer  $versionId  The version id to mark as current
     * @param   string   $itemId     The item id of the content item
     *
     * @return  void
     *
     * @since   6.0.0
     */
    protected function markVersionAsCurrent(int $versionId, string $itemId): void
    {
        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->update($db->quoteName('#__history'))
            ->set($db->quoteName('is_current') . ' = 0')
            ->where($db->quoteName('item_id') . ' = :item_id')
            ->bind(':item_id', $itemId, ParameterType::STRING);
        $db->setQuery($query);
        $db->execute();

        $query->clear()
            ->update($db->quoteName('#__history'))
            ->set($db->quoteName('is_current') . ' = 1')
            ->where($db->quoteName('version_id') . ' = :version_id')
            ->bind(':version_id', $versionId, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Method to check if version history is enabled for a specific context.
     *
     * @param   string  $context  The model context.
     *
     * @return  boolean  True if version history is enabled, false otherwise.
     *
     * @since   6.0.0
     */
    protected function versionHistoryEnabled(string $context): bool
    {
        [$extension, $type] = explode('.', $context);

        return (bool) ComponentHelper::getParams($extension)->get('save_history', 0);
    }
}
