<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\UCM;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\TableInterface;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for implementing UCM
 *
 * @since  3.1
 */
class UCMContent extends UCMBase
{
    /**
     * The related table object
     *
     * @var    Table
     * @since  3.1
     */
    protected $table;

    /**
     * The UCM data array
     *
     * @var    array[]
     * @since  3.1
     */
    public $ucmData;

    /**
     * Instantiate UCMContent.
     *
     * @param   TableInterface  $table  The table object
     * @param   string          $alias  The type alias
     * @param   UCMType         $type   The type object
     *
     * @since   3.1
     */
    public function __construct(TableInterface $table = null, $alias = null, UCMType $type = null)
    {
        parent::__construct($alias, $type);

        if ($table) {
            $this->table = $table;
        } else {
            $tableObject = json_decode($this->type->type->table);
            $this->table = Table::getInstance($tableObject->special->type, $tableObject->special->prefix, $tableObject->special->config);
        }
    }

    /**
     * Method to save the data
     *
     * @param   array    $original  The original data to be saved
     * @param   UCMType  $type      The UCM Type object
     *
     * @return  boolean  true
     *
     * @since   3.1
     */
    public function save($original = null, UCMType $type = null)
    {
        $type    = $type ?: $this->type;
        $ucmData = $original ? $this->mapData($original, $type) : $this->ucmData;

        // Store the Common fields
        $this->store($ucmData['common']);

        // Store the special fields
        if (isset($ucmData['special'])) {
            $table = $this->table;
            $this->store($ucmData['special'], $table, '');
        }

        return true;
    }

    /**
     * Delete content from the Core Content table
     *
     * @param   mixed    $pk    Array or comma-separated string of ids to delete
     * @param   UCMType  $type  The content type object
     *
     * @return  boolean  True if success
     *
     * @since   3.1
     */
    public function delete($pk, UCMType $type = null)
    {
        $db   = Factory::getDbo();
        $type = $type ?: $this->type;

        if (!\is_array($pk)) {
            $pk = explode(',', $pk);
        }

        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__ucm_content'))
            ->where($db->quoteName('core_type_id') . ' = :typeId')
            ->whereIn($db->quoteName('core_content_item_id'), $pk)
            ->bind(':typeId', $type->type_id, ParameterType::INTEGER);

        $db->setQuery($query);
        $db->execute();

        return true;
    }

    /**
     * Map the original content to the Core Content fields
     *
     * @param   array    $original  The original data array
     * @param   UCMType  $type      Type object for this data
     *
     * @return  array[]  $ucmData  The mapped UCM data
     *
     * @since   3.1
     */
    public function mapData($original, UCMType $type = null)
    {
        $contentType = $type ?: $this->type;

        $fields = json_decode($contentType->type->field_mappings);

        $ucmData = [];

        $common = \is_object($fields->common) ? $fields->common : $fields->common[0];

        foreach ($common as $i => $field) {
            if ($field && $field !== 'null' && \array_key_exists($field, $original)) {
                $ucmData['common'][$i] = $original[$field];
            }
        }

        if (\array_key_exists('special', $ucmData)) {
            $special = \is_object($fields->special) ? $fields->special : $fields->special[0];

            foreach ($special as $i => $field) {
                if ($field && $field !== 'null' && \array_key_exists($field, $original)) {
                    $ucmData['special'][$i] = $original[$field];
                }
            }
        }

        $ucmData['common']['core_type_alias'] = $contentType->type->type_alias;
        $ucmData['common']['core_type_id']    = $contentType->type->type_id;

        if (isset($ucmData['special'])) {
            $ucmData['special']['ucm_id'] = $ucmData['common']['ucm_id'];
        }

        $this->ucmData = $ucmData;

        return $this->ucmData;
    }

    /**
     * Store data to the appropriate table
     *
     * @param   array           $data        Data to be stored
     * @param   TableInterface  $table       Table Object
     * @param   boolean         $primaryKey  Flag that is true for data that are using #__ucm_content as their primary table
     *
     * @return  boolean  true on success
     *
     * @since   3.1
     */
    protected function store($data, TableInterface $table = null, $primaryKey = null)
    {
        $table = $table ?: Table::getInstance('Corecontent');

        $typeId     = $this->getType()->type->type_id;
        $primaryKey = $primaryKey ?: $this->getPrimaryKey($typeId, $data['core_content_item_id']);

        if (!$primaryKey) {
            // Store the core UCM mappings
            $baseData                    = [];
            $baseData['ucm_type_id']     = $typeId;
            $baseData['ucm_item_id']     = $data['core_content_item_id'];
            $baseData['ucm_language_id'] = ContentHelper::getLanguageId($data['core_language']);

            if (parent::store($baseData)) {
                $primaryKey = $this->getPrimaryKey($typeId, $data['core_content_item_id']);
            }
        }

        return parent::store($data, $table, $primaryKey);
    }

    /**
     * Get the value of the primary key from #__ucm_base
     *
     * @param   integer  $typeId         The ID for the type
     * @param   integer  $contentItemId  Value of the primary key in the legacy or secondary table
     *
     * @return  integer  The integer of the primary key
     *
     * @since   3.1
     */
    public function getPrimaryKey($typeId, $contentItemId)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('ucm_id'))
            ->from($db->quoteName('#__ucm_base'))
            ->where(
                [
                    $db->quoteName('ucm_item_id') . ' = :itemId',
                    $db->quoteName('ucm_type_id') . ' = :typeId',
                ]
            )
            ->bind(':itemId', $contentItemId, ParameterType::INTEGER)
            ->bind(':typeId', $typeId, ParameterType::INTEGER);
        $db->setQuery($query);

        return $db->loadResult();
    }
}
