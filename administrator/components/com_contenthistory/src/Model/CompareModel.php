<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contenthistory\Administrator\Model;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\ContentHistory;
use Joomla\CMS\Table\ContentType;
use Joomla\CMS\Table\Table;
use Joomla\Component\Contenthistory\Administrator\Helper\ContenthistoryHelper;

/**
 * Methods supporting a list of contenthistory records.
 *
 * @since  3.2
 */
class CompareModel extends ListModel
{
    /**
     * Method to get a version history row.
     *
     * @return  array|boolean    On success, array of populated tables. False on failure.
     *
     * @since   3.2
     *
     * @throws  NotAllowed   Thrown if not authorised to edit an item
     */
    public function getItems()
    {
        $input = Factory::getApplication()->input;

        /** @var ContentHistory $table1 */
        $table1 = $this->getTable('ContentHistory');

        /** @var ContentHistory $table2 */
        $table2 = $this->getTable('ContentHistory');

        $id1 = $input->getInt('id1');
        $id2 = $input->getInt('id2');

        if (!$id1 || \is_array($id1) || !$id2 || \is_array($id2)) {
            $this->setError(Text::_('COM_CONTENTHISTORY_ERROR_INVALID_ID'));

            return false;
        }

        $result = array();

        if (!$table1->load($id1) || !$table2->load($id2)) {
            $this->setError(Text::_('COM_CONTENTHISTORY_ERROR_VERSION_NOT_FOUND'));

            // Assume a failure to load the content means broken data, abort mission
            return false;
        }

        // Get the first history record's content type record so we can check ACL
        /** @var ContentType $contentTypeTable */
        $contentTypeTable = $this->getTable('ContentType');
        $typeAlias        = explode('.', $table1->item_id);
        array_pop($typeAlias);
        $typeAlias        = implode('.', $typeAlias);

        if (!$contentTypeTable->load(array('type_alias' => $typeAlias))) {
            $this->setError(Text::_('COM_CONTENTHISTORY_ERROR_FAILED_LOADING_CONTENT_TYPE'));

            // Assume a failure to load the content type means broken data, abort mission
            return false;
        }

        $user = Factory::getUser();

        // Access check
        if (!$user->authorise('core.edit', $table1->item_id) && !$this->canEdit($table1)) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $nullDate = $this->getDatabase()->getNullDate();

        foreach (array($table1, $table2) as $table) {
            $object = new \stdClass();
            $object->data = ContenthistoryHelper::prepareData($table);
            $object->version_note = $table->version_note;

            // Let's use custom calendars when present
            $object->save_date = HTMLHelper::_('date', $table->save_date, Text::_('DATE_FORMAT_LC6'));

            $dateProperties = array (
                'modified_time',
                'created_time',
                'modified',
                'created',
                'checked_out_time',
                'publish_up',
                'publish_down',
            );

            foreach ($dateProperties as $dateProperty) {
                if (
                    property_exists($object->data, $dateProperty)
                    && $object->data->$dateProperty->value !== null
                    && $object->data->$dateProperty->value !== $nullDate
                ) {
                    $object->data->$dateProperty->value = HTMLHelper::_(
                        'date',
                        $object->data->$dateProperty->value,
                        Text::_('DATE_FORMAT_LC6')
                    );
                }
            }

            $result[] = $object;
        }

        return $result;
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  Table   A Table object
     *
     * @since   3.2
     */
    public function getTable($type = 'Contenthistory', $prefix = 'Joomla\\CMS\\Table\\', $config = array())
    {
        return Table::getInstance($type, $prefix, $config);
    }

    /**
     * Method to test whether a record is editable
     *
     * @param   ContentHistory  $record  A Table object.
     *
     * @return  boolean  True if allowed to edit the record. Defaults to the permission set in the component.
     *
     * @since   3.6
     */
    protected function canEdit($record)
    {
        $result = false;

        if (!empty($record->item_id)) {
            /**
             * Make sure user has edit privileges for this content item. Note that we use edit permissions
             * for the content item, not delete permissions for the content history row.
             */
            $user   = Factory::getUser();
            $result = $user->authorise('core.edit', $record->item_id);

            // Finally try session (this catches edit.own case too)
            if (!$result) {
                /** @var ContentType $contentTypeTable */
                $contentTypeTable = $this->getTable('ContentType');

                $typeAlias        = explode('.', $record->item_id);
                $id = array_pop($typeAlias);
                $typeAlias        = implode('.', $typeAlias);
                $contentTypeTable->load(array('type_alias' => $typeAlias));
                $typeEditables = (array) Factory::getApplication()->getUserState(str_replace('.', '.edit.', $contentTypeTable->type_alias) . '.id');
                $result = in_array((int) $id, $typeEditables);
            }
        }

        return $result;
    }
}
