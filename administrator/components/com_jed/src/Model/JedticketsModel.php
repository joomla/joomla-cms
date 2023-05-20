<?php

/**
 * @package       JED
 *
 * @subpackage    Tickets
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\Model;

// No direct access.
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\QueryInterface;

/**
 * Methods supporting a list of JED Tickets
 *
 * @since  4.0.0
 */
class JedticketsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see           ListModel
     * @since         4.0.0
     * @throws  Exception
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.`id`',
                'ticket_origin', 'a.`ticket_origin`',
                'ticket_category_type', 'a.`ticket_category_type`',
                'ticket_subject', 'a.`ticket_subject`',
                'ticket_text', 'a.`ticket_text`',
                'internal_notes', 'a.`internal_notes`',
                'uploaded_files_preview', 'a.`uploaded_files_preview`',
                'uploaded_files_location', 'a.`uploaded_files_location`',
                'allocated_group', 'a.`allocated_group`',
                'allocated_to', 'a.`allocated_to`',
                'linked_item_type', 'a.`linked_item_type`',
                'linked_item_id', 'a.`linked_item_id`',
                'ticket_status', 'a.`ticket_status`',
                'parent_id', 'a.`parent_id`',
                'state', 'a.`state`',
                'ordering', 'a.`ordering`',
                'created_by', 'a.`created_by`',
                'created_on', 'a.`created_on`',
                'modified_by', 'a.`modified_by`',
                'modified_on', 'a.`modified_on`',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Get an array of data items
     *
     * @return mixed Array of data items on success, false on failure.
     *
     * @since 4.0.0
     */
    public function getItems()
    {
        $items = parent::getItems();

        foreach ($items as $oneItem) {
            $oneItem->ticket_origin = Text::_('COM_JED_JEDTICKETS_FIELD_TICKET_ORIGIN_OPTION_' . strtoupper($oneItem->ticket_origin));

            /*  if (isset($oneItem->ticket_category_type))
                {
                    $values    = explode(',', $oneItem->ticket_category_type);
                    $textValue = array();

                    foreach ($values as $value)
                    {
                        $db = $this->getDatabase();

                        $query = $db->getQuery(true);
                        $query
                            ->select('`jtc`.`categorytype`')
                            ->from($db->quoteName('#__jed_ticket_categories', 'jtc'))
                            ->where($db->quoteName('jtc.id') . ' = ' . $db->quote($db->escape($value)));

                        $db->setQuery($query);
                        $results = $db->loadObject();

                        if ($results)
                        {
                            $textValue[] = $results->categorytype;
                        }
                    }

                    $oneItem->ticket_category_type = !empty($textValue) ? implode(', ', $textValue) : $oneItem->ticket_category_type;
                } */

            /*if (isset($oneItem->allocated_group))
            {
                $values    = explode(',', $oneItem->allocated_group);
                $textValue = array();

                foreach ($values as $value)
                {
                    $db = $this->getDatabase();

                    $query = $db->getQuery(true);
                    $query
                        ->select('`jtg`.`name`')
                        ->from($db->quoteName('#__jed_ticket_groups', 'jtg'))
                        ->where($db->quoteName('jtg.id') . ' = ' . $db->quote($db->escape($value)));

                    $db->setQuery($query);
                    $results = $db->loadObject();

                    if ($results)
                    {
                        $textValue[] = $results->name;
                    }
                }

                $oneItem->allocated_group = !empty($textValue) ? implode(', ', $textValue) : $oneItem->allocated_group;
            }*/

            /*if (isset($oneItem->linked_item_type))
            {
                $values    = explode(',', $oneItem->linked_item_type);
                $textValue = array();

                foreach ($values as $value)
                {
                    $db = $this->getDatabase();

                    $query = $db->getQuery(true);
                    $query
                        ->select('`jt_linked_item_types`.`title`')
                        ->from($db->quoteName('#__jed_ticket_linked_item_types', 'jt_linked_item_types'))
                        ->where($db->quoteName('jt_linked_item_types.id') . ' = ' . $db->quote($db->escape($value)));

                    $db->setQuery($query);
                    $results = $db->loadObject();

                    if ($results)
                    {
                        $textValue[] = $results->title;
                    }
                }

                $oneItem->linked_item_type = !empty($textValue) ? implode(', ', $textValue) : $oneItem->linked_item_type;
            }*/
            $oneItem->ticket_status = Text::_('COM_JED_JEDTICKETS_FIELD_TICKET_STATUS_OPTION_' . strtoupper($oneItem->ticket_status));
        }

        return $items;
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return   QueryInterface
     *
     * @since  4.0.0
     */
    protected function getListQuery(): QueryInterface
    {
        // Create a new query object.
        $db = $this->getDatabase();

        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'DISTINCT a.*'
            )
        );
        $query->from('`#__jed_jedtickets` AS a');
        $query->select("unix_timestamp(a.created_on) as created_time");
        // Join over the users for the checked out user
        $query->select("uc.name AS uEditor");
        $query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

        // Join over the foreign key 'ticket_category_type'
        $query->select('`jtc`.`categorytype` AS categorytype_string');
        $query->join('LEFT', '#__jed_ticket_categories AS jtc ON jtc.`id` = a.`ticket_category_type`');
        // Join over the foreign key 'allocated_group'
        $query->select('`jtg`.`name` AS ticketallocatedgroup_string');
        $query->join('LEFT', '#__jed_ticket_groups AS jtg ON jtg.`id` = a.`allocated_group`');

        // Join over the user field 'allocated_to'
        $query->select('`allocated_to`.name AS `allocated_to`');
        $query->join('LEFT', '#__users AS `allocated_to` ON `allocated_to`.id = a.`allocated_to`');
        // Join over the foreign key 'linked_item_type'
        $query->select('`jt_linked_item_types`.`title` AS ticketlinkeditemtypes_string');
        $query->join('LEFT', '#__jed_ticket_linked_item_types AS jt_linked_item_types ON jt_linked_item_types.`id` = a.`linked_item_type`');

        // Join over the user field 'created_by'
        $query->select('`created_by`.name AS `created_by`');
        $query->join('LEFT', '#__users AS `created_by` ON `created_by`.id = a.`created_by`');

        // Join over the user field 'modified_by'
        $query->select('`modified_by`.name AS `modified_by`');
        $query->join('LEFT', '#__users AS `modified_by` ON `modified_by`.id = a.`modified_by`');


        // Filter by published state
        $published = $this->getState('filter.state');

        if (is_numeric($published)) {
            $query->where('a.state = ' . (int) $published);
        } elseif (empty($published)) {
            $query->where('(a.state IN (0, 1))');
        }

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('(jtc.categorytype LIKE ' . $search . '  OR  a.ticket_subject LIKE ' . $search . ' )');
            }
        }


        // Filtering ticket_origin
        $filter_ticket_origin = $this->state->get("filter.ticket_origin");

        if ($filter_ticket_origin !== null && (is_numeric($filter_ticket_origin) || !empty($filter_ticket_origin))) {
            $query->where("a.`ticket_origin` = '" . $db->escape($filter_ticket_origin) . "'");
        }

        // Filtering ticket_category_type
        $filter_ticket_category_type = $this->state->get("filter.ticket_category_type");

        if ($filter_ticket_category_type !== null && !empty($filter_ticket_category_type)) {
            $query->where("a.`ticket_category_type` = '" . $db->escape($filter_ticket_category_type) . "'");
        }
        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'allocated_group');
        $orderDirn = $this->state->get('list.direction', 'DESC');

        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        //echo($query->__toString());exit();
        return $query;
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return   string A store id.
     *
     * @since  4.0.0
     */
    protected function getStoreId($id = ''): string
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');


        return parent::getStoreId($id);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   Elements order
     * @param   string  $direction  Order direction
     *
     * @return void
     *
     * @since 4.0.0
     * @throws Exception
     *
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // List state information.
        parent::populateState('allocated_group', 'DESC');

        $context = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $context);

        // Split context into component and optional section
        $parts = FieldsHelper::extract($context);

        if ($parts) {
            $this->setState('filter.component', $parts[0]);
            $this->setState('filter.section', $parts[1]);
        }
    }
}
