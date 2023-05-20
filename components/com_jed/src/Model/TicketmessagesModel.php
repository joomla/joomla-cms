<?php

/**
 * @package       JED
 *
 * @subpackage    TICKETS
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Model;

// No direct access.
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Jed\Component\Jed\Site\Helper\JedHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use stdClass;

/**
 * Methods supporting a list of Jed records.
 *
 * @since  4.0.0
 */
class TicketmessagesModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see              ListModel
     * @since            4.0.0
     * @throws Exception
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'ordering', 'a.ordering',
                'state', 'a.state',
                'created_by', 'a.created_by',
                'modified_by', 'a.modified_by',
                'subject', 'a.subject',
                'message', 'a.message',
                'ticket_id', 'a.ticket_id',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to get an array of data items
     *
     * @return  mixed An array of data on success, false on failure.
     *
     * @since 4.0.0
     */
    public function getItems()
    {
        $items = parent::getItems();

        foreach ($items as $item) {
            if (isset($item->ticket_id)) {
                $values    = explode(',', $item->ticket_id);
                $textValue = [];

                foreach ($values as $value) {
                    $db    = Factory::getContainer()->get('DatabaseDriver');
                    $query = $db->getQuery(true);
                    $query
                        ->select('`#__jed_jedtickets_3591992`.`ticket_subject`')
                        ->from($db->quoteName('#__jed_jedtickets', '#__jed_jedtickets_3591992'))
                        ->where($db->quoteName('#__jed_jedtickets_3591992.id') . ' = ' . $db->quote($db->escape($value)));

                    $db->setQuery($query);
                    $results = $db->loadObject();

                    if ($results) {
                        $textValue[] = $results->ticket_subject;
                    }
                }

                $item->ticket_id = !empty($textValue) ? implode(', ', $textValue) : $item->ticket_id;
            }
        }

        return $items;
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  object  A \JDatabaseQuery object to retrieve the data set.
     *
     * @since    4.0.0
     */
    protected function getListQuery(): object
    {
        // Create a new query object.
        $db    =  Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'DISTINCT a.*'
            )
        );

        $query->from('`#__jed_ticket_messages` AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS uEditor');
        $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

        // Join over the created by field 'created_by'
        $query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

        // Join over the created by field 'modified_by'
        $query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');
        // Join over the foreign key 'ticket_id'
        $query->select('`jt`.`ticket_subject` AS jt_ticket_subject');
        $query->join('LEFT', '#__jed_jedtickets AS jt ON jt.`id` = a.`ticket_id`');
        if (JedHelper::isAdminOrSuperUser()) {
            $query->where("a.created_by = " . JedHelper::getUser()->get("id"));
        }

        if (!JedHelper::getUser()->authorise('core.edit', 'com_jed')) {
            $query->where('a.state = 1');
        } else {
            $query->where('(a.state IN (0, 1))');
        }

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('( a.subject LIKE ' . $search . '  OR #__jed_jedtickets_3591992.ticket_subject LIKE ' . $search . ' )');
            }
        }


        // Filtering ticket_id
        $filter_ticket_id = $this->state->get("filter.ticket_id");

        if ($filter_ticket_id) {
            $query->where("a.`ticket_id` = '" . $db->escape($filter_ticket_id) . "'");
        }


        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'id');
        $orderDirn = $this->state->get('list.direction', 'ASC');

        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }

    /**
     * Overrides the default function to check Date fields format, identified by
     * "_dateformat" suffix, and erases the field if it's not correct.
     *
     * @return stdClass
     * @since 4.0.0
     * @throws Exception
     */
    protected function loadFormData(): stdClass
    {
        $app              = Factory::getApplication();
        $filters          = $app->getUserState($this->context . '.filter', []);
        $error_dateformat = false;

        foreach ($filters as $key => $value) {
            if (strpos($key, '_dateformat') && !empty($value) && JedHelper::isValidDate($value) == null) {
                $filters[$key]    = '';
                $error_dateformat = true;
            }
        }

        if ($error_dateformat) {
            $app->enqueueMessage(Text::_("COM_JED_VEL_GENERAL_DATE_FORMAT"), "warning");
            $app->setUserState($this->context . '.filter', $filters);
        }

        return parent::loadFormData();
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
     * @since    4.0.0
     * @throws Exception
     *
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();

        $list = $app->getUserState($this->context . '.list');

        $ordering  = $list['filter_order'] ?? null;
        $direction = $list['filter_order_Dir'] ?? null;
        if (empty($ordering)) {
            $ordering = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', $app->get('filter_order'));
            if (!in_array($ordering, $this->filter_fields)) {
                $ordering = 'id';
            }
            $this->setState('list.ordering', $ordering);
        }
        if (empty($direction)) {
            $direction = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', $app->get('filter_order_Dir'));
            if (!in_array(strtoupper($direction), ['ASC', 'DESC', ''])) {
                $direction = 'ASC';
            }
            $this->setState('list.direction', $direction);
        }

        $list['limit']     = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $app->get('list_limit'), 'uint');
        $list['start']     = $app->input->getInt('start', 0);
        $list['ordering']  = $ordering;
        $list['direction'] = $direction;

        $app->setUserState($this->context . '.list', $list);
        $app->input->set('list', null);


        // List state information.

        parent::populateState($ordering, $direction);

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
