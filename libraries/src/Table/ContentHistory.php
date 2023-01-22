<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Content History table.
 *
 * @since  3.2
 */
class ContentHistory extends Table
{
    /**
     * Array of object fields to unset from the data object before calculating SHA1 hash. This allows us to detect a meaningful change
     * in the database row using the hash. This can be read from the #__content_types content_history_options column.
     *
     * @var    array
     * @since  3.2
     */
    public $ignoreChanges = [];

    /**
     * Array of object fields to convert to integers before calculating SHA1 hash. Some values are stored differently
     * when an item is created than when the item is changed and saved. This works around that issue.
     * This can be read from the #__content_types content_history_options column.
     *
     * @var    array
     * @since  3.2
     */
    public $convertToInt = [];

    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  A database connector object
     *
     * @since   3.1
     */
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__history', 'version_id', $db);
        $this->ignoreChanges = [
            'modified_by',
            'modified_user_id',
            'modified',
            'modified_time',
            'checked_out',
            'checked_out_time',
            'version',
            'hits',
            'path',
        ];
        $this->convertToInt  = ['publish_up', 'publish_down', 'ordering', 'featured'];
    }

    /**
     * Overrides Table::store to set modified hash, user id, and save date.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @since   3.2
     */
    public function store($updateNulls = false)
    {
        $this->set('character_count', \strlen($this->get('version_data')));
        $typeTable = Table::getInstance('ContentType', 'JTable', ['dbo' => $this->getDbo()]);
        $typeAlias = explode('.', $this->item_id);
        array_pop($typeAlias);
        $typeTable->load(['type_alias' => implode('.', $typeAlias)]);

        if (!isset($this->sha1_hash)) {
            $this->set('sha1_hash', $this->getSha1($this->get('version_data'), $typeTable));
        }

        // Modify author and date only when not toggling Keep Forever
        if ($this->get('keep_forever') === null) {
            $this->set('editor_user_id', Factory::getUser()->id);
            $this->set('save_date', Factory::getDate()->toSql());
        }

        return parent::store($updateNulls);
    }

    /**
     * Utility method to get the hash after removing selected values. This lets us detect changes other than
     * modified date (which will change on every save).
     *
     * @param   mixed        $jsonData   Either an object or a string with json-encoded data
     * @param   ContentType  $typeTable  Table object with data for this content type
     *
     * @return  string  SHA1 hash on success. Empty string on failure.
     *
     * @since   3.2
     */
    public function getSha1($jsonData, ContentType $typeTable)
    {
        $object = \is_object($jsonData) ? $jsonData : json_decode($jsonData);

        if (isset($typeTable->content_history_options) && \is_object(json_decode($typeTable->content_history_options))) {
            $options = json_decode($typeTable->content_history_options);
            $this->ignoreChanges = $options->ignoreChanges ?? $this->ignoreChanges;
            $this->convertToInt = $options->convertToInt ?? $this->convertToInt;
        }

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
     * Utility method to get a matching row based on the hash value and id columns.
     * This lets us check to make sure we don't save duplicate versions.
     *
     * @return  string  SHA1 hash on success. Empty string on failure.
     *
     * @since   3.2
     */
    public function getHashMatch()
    {
        $db       = $this->_db;
        $itemId   = $this->get('item_id');
        $sha1Hash = $this->get('sha1_hash');
        $query    = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName('#__history'))
            ->where($db->quoteName('item_id') . ' = :item_id')
            ->where($db->quoteName('sha1_hash') . ' = :sha1_hash')
            ->bind(':item_id', $itemId, ParameterType::STRING)
            ->bind(':sha1_hash', $sha1Hash);

        $query->setLimit(1);
        $db->setQuery($query);

        return $db->loadObject();
    }

    /**
     * Utility method to remove the oldest versions of an item, saving only the most recent versions.
     *
     * @param   integer  $maxVersions  The maximum number of versions to save. All others will be deleted.
     *
     * @return  boolean   true on success, false on failure.
     *
     * @since   3.2
     */
    public function deleteOldVersions($maxVersions)
    {
        $result = true;

        // Get the list of version_id values we want to save
        $db        = $this->_db;
        $itemId = $this->get('item_id');
        $query     = $db->getQuery(true);
        $query->select($db->quoteName('version_id'))
            ->from($db->quoteName('#__history'))
            ->where($db->quoteName('item_id') . ' = :item_id')
            ->where($db->quoteName('keep_forever') . ' != 1')
            ->bind(':item_id', $itemId, ParameterType::STRING)
            ->order($db->quoteName('save_date') . ' DESC ');

        $query->setLimit((int) $maxVersions);
        $db->setQuery($query);
        $idsToSave = $db->loadColumn(0);

        // Don't process delete query unless we have at least the maximum allowed versions
        if (\count($idsToSave) === (int) $maxVersions) {
            // Delete any rows not in our list and and not flagged to keep forever.
            $query = $db->getQuery(true);
            $query->delete($db->quoteName('#__history'))
                ->where($db->quoteName('item_id') . ' = :item_id')
                ->whereNotIn($db->quoteName('version_id'), $idsToSave)
                ->where($db->quoteName('keep_forever') . ' != 1')
                ->bind(':item_id', $itemId, ParameterType::STRING);
            $db->setQuery($query);
            $result = (bool) $db->execute();
        }

        return $result;
    }
}
