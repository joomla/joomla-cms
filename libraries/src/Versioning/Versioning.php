<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Versioning;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Workflow\WorkflowServiceInterface;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Handle the versioning of content items
 *
 * @since  4.0.0
 */
class Versioning
{
    /**
     * Method to get a list of available versions of this item.
     *
     * @param   string   $typeAlias  Typealias of the component
     * @param   integer  $id         ID of the content item to get
     *
     * @return  object[]   A list of history items
     *
     * @since   4.0.0
     */
    public static function get($typeAlias, $id)
    {
        $db = Factory::getDbo();
        $itemid = $typeAlias . '.' . $id;
        $query = $db->getQuery(true);
        $query->select($db->quoteName('h.version_note') . ',' . $db->quoteName('h.save_date') . ',' . $db->quoteName('u.name'))
            ->from($db->quoteName('#__history', 'h'))
            ->leftJoin($db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('h.editor_user_id'))
            ->where($db->quoteName('item_id') . ' = :item_id')
            ->bind(':item_id', $itemid, ParameterType::STRING)
            ->order($db->quoteName('save_date') . ' DESC ');
        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * Method to delete the history for an item.
     *
     * @param   string   $typeAlias  Typealias of the component
     * @param   integer  $id         ID of the content item to delete
     *
     * @return  boolean  true on success, otherwise false.
     *
     * @since   4.0.0
     */
    public static function delete($typeAlias, $id)
    {
        $db = Factory::getDbo();
        $itemid = $typeAlias . '.' . $id;
        $query = $db->getQuery(true);
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
     * @since   4.0.0
     */
    public static function store($typeAlias, $id, $data, $note = '')
    {
        $typeTable = Table::getInstance('Contenttype', 'JTable');
        $typeTable->load(['type_alias' => $typeAlias]);

        $historyTable = Table::getInstance('Contenthistory', 'JTable');
        $historyTable->item_id = $typeAlias . '.' . $id;

        $aliasParts = explode('.', $typeAlias);

        // Don't store unless we have a non-zero item id
        if (!$historyTable->item_id) {
            return true;
        }

        // We should allow workflow items interact with the versioning
        $component = Factory::getApplication()->bootComponent($aliasParts[0]);

        if ($component instanceof WorkflowServiceInterface && $component->isWorkflowActive($typeAlias)) {
            PluginHelper::importPlugin('workflow');

            // Pre-processing by observers
            $event = AbstractEvent::create(
                'onContentVersioningPrepareTable',
                [
                    'subject'   => $historyTable,
                    'extension' => $typeAlias
                ]
            );

            Factory::getApplication()->getDispatcher()->dispatch('onContentVersioningPrepareTable', $event);
        }

        $historyTable->version_data = json_encode($data);
        $historyTable->version_note = $note;

        // Don't save if hash already exists and same version note
        $historyTable->sha1_hash = $historyTable->getSha1($data, $typeTable);

        if ($historyRow = $historyTable->getHashMatch()) {
            if (!$note || ($historyRow->version_note === $note)) {
                return true;
            } else {
                // Update existing row to set version note
                $historyTable->version_id = $historyRow->version_id;
            }
        }

        $result = $historyTable->store();

        // Load history_limit config from extension.
        $context = $aliasParts[1] ?? '';

        $maxVersionsContext = ComponentHelper::getParams($aliasParts[0])->get('history_limit_' . $context, 0);

        if ($maxVersionsContext) {
            $historyTable->deleteOldVersions($maxVersionsContext);
        } elseif ($maxVersions = ComponentHelper::getParams($aliasParts[0])->get('history_limit', 0)) {
            $historyTable->deleteOldVersions($maxVersions);
        }

        return $result;
    }
}
