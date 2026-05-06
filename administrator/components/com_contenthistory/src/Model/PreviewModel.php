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
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Table\ContentHistory;
use Joomla\CMS\Table\ContentType;
use Joomla\CMS\Table\Table;
use Joomla\Component\Contenthistory\Administrator\Helper\ContenthistoryHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Methods supporting a list of contenthistory records.
 *
 * @since  3.2
 */
class PreviewModel extends ItemModel
{
    /**
     * Method to get a version history row.
     *
     * @param   integer  $pk  The id of the item
     *
     * @return  \stdClass|boolean    On success, standard object with row data. False on failure.
     *
     * @since   3.2
     *
     * @throws  NotAllowed   Thrown if not authorised to edit an item
     */
    public function getItem($pk = null)
    {
        /** @var ContentHistory $table */
        $table     = $this->getTable('ContentHistory');
        $versionId = Factory::getApplication()->getInput()->getInt('version_id');

        if (!$versionId || \is_array($versionId) || !$table->load($versionId)) {
            return false;
        }

        // Access check — all permission logic is centralised in canEdit()
        if (!$this->canEdit($table)) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $result               = new \stdClass();
        $result->version_note = $table->version_note;
        $result->data         = ContenthistoryHelper::prepareData($table);

        // Let's use custom calendars when present
        $result->save_date = HTMLHelper::_('date', $table->save_date, Text::_('DATE_FORMAT_LC6'));

        $dateProperties = [
            'modified_time',
            'created_time',
            'modified',
            'created',
            'checked_out_time',
            'publish_up',
            'publish_down',
        ];

        $nullDate = $this->getDatabase()->getNullDate();

        foreach ($dateProperties as $dateProperty) {
            if (
                property_exists($result->data, $dateProperty)
                && $result->data->$dateProperty->value !== null
                && $result->data->$dateProperty->value !== $nullDate
            ) {
                $result->data->$dateProperty->value = HTMLHelper::_(
                    'date',
                    $result->data->$dateProperty->value,
                    Text::_('DATE_FORMAT_LC6')
                );
            }
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
    public function getTable($type = 'ContentHistory', $prefix = 'Joomla\\CMS\\Table\\', $config = [])
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
        if (empty($record->item_id)) {
            return false;
        }

        /**
         * Make sure user has edit privileges for this content item. Note that we use edit permissions
         * for the content item, not delete permissions for the content history row.
         *
         * Three checks are attempted in order, returning true as soon as one passes:
         *   1. core.edit       — standard edit right (e.g. Editor group and above).
         *   2. core.edit.own   — edit-own right combined with an ownership check on the record.
         *                        This was missing in the original code and caused Authors to receive
         *                        a 403 when previewing versions of their own articles.
         *   3. Session state   — fallback for items the user currently has open for editing.
         */
        $user = $this->getCurrentUser();

        // 1. Check core.edit
        if ($user->authorise('core.edit', $record->item_id)) {
            return true;
        }

        // 2. Check core.edit.own — only meaningful when the user owns the record.
        //    Parse the item_id (e.g. "com_content.article.123") to extract the numeric id,
        //    then load the content row to verify the created_by field matches the current user.
        $parts = explode('.', $record->item_id);
        $id    = (int) array_pop($parts);

        if ($id && $user->authorise('core.edit.own', $record->item_id)) {
            /** @var \Joomla\CMS\Table\Content $contentTable */
            $contentTable = Factory::getApplication()
                ->bootComponent('com_content')
                ->getMVCFactory()
                ->createTable('Article', 'Administrator');

            if ($contentTable->load($id) && (int) $contentTable->created_by === (int) $user->id) {
                return true;
            }
        }

        // 3. Session fallback — covers items the user currently has open for editing.
        $typeAlias     = implode('.', $parts); // re-use $parts already popped above
        $typeEditables = (array) Factory::getApplication()->getUserState(str_replace('.', '.edit.', $typeAlias) . '.id');

        return \in_array($id, $typeEditables);
    }
}
