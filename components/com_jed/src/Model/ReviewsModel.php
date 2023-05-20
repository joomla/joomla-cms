<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Model;

// No direct access.
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * Methods supporting a list of Jed records.
 *
 * @since  4.0.0
 */
class ReviewsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see    JController
     * @since  4.0.0
     * @throws Exception
     * @throws Exception
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'extension_id', 'a.extension_id',
                'supply_option_id', 'a.supply_option_id',
                'title', 'a.title',
                'alias', 'a.alias',
                'body', 'a.body',
                'functionality', 'a.functionality',
                'functionality_comment', 'a.functionality_comment',
                'ease_of_use', 'a.ease_of_use',
                'ease_of_use_comment', 'a.ease_of_use_comment',
                'support', 'a.support',
                'support_comment', 'a.support_comment',
                'documentation', 'a.documentation',
                'documentation_comment', 'a.documentation_comment',
                'value_for_money', 'a.value_for_money',
                'value_for_money_comment', 'a.value_for_money_comment',
                'overall_score', 'a.overall_score',
                'used_for', 'a.used_for',
                'flagged', 'a.flagged',
                'ip_address', 'a.ip_address',
                'published', 'a.published',
                'created_on', 'a.created_on',
                'created_by', 'a.created_by',
                'ordering', 'a.ordering',
            ];
        }

        parent::__construct($config);
    }


       /**
        * Checks whether or not a user is manager or super user
        *
        * @return bool
        */
    public function isAdminOrSuperUser()
    {
        try {
            $user = Factory::getUser();
            return in_array("8", $user->groups) || in_array("7", $user->groups);
        } catch (Exception $exc) {
            return false;
        }
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   Elements order
     * @param   string  $direction  Order direction
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws Exception
     *
     * @throws  Exception
     * @throws Exception
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // List state information.
        parent::populateState('a.id', 'DESC');

        $app  = Factory::getApplication();
        $list = $app->getUserState($this->context . '.list');

        $value         = $app->getUserState($this->context . '.list.limit', $app->get('list_limit', 25));
        $list['limit'] = $value;

        $this->setState('list.limit', $value);

        $value = $app->input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $value);

        $ordering  = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'a.id');
        $direction = strtoupper($this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'DESC'));

        if (!empty($ordering) || !empty($direction)) {
            $list['fullordering'] = $ordering . ' ' . $direction;
        }

        $app->setUserState($this->context . '.list', $list);



        $context = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $context);

        // Split context into component and optional section
        $parts = FieldsHelper::extract($context);

        if ($parts) {
            $this->setState('filter.component', $parts[0]);
            $this->setState('filter.section', $parts[1]);
        }
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  DatabaseQuery
     *
     * @since   4.0.0
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'DISTINCT a.*'
            )
        );

        $query->from('`#__jed_reviews` AS a');

        // Join over the users for the checked out user.
        $query->select('uc.name AS uEditor');
        $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');
        // Join over the foreign key 'extension_id'
        $query->select('`#__jed_extensions_3715042`.`title` AS extensions_fk_value_3715042');
        $query->join('LEFT', '#__jed_extensions AS #__jed_extensions_3715042 ON #__jed_extensions_3715042.`id` = a.`extension_id`');
        // Join over the foreign key 'supply_option_id'
        $query->select('`#__jed_extension_supply_options_3727708`.`title` AS extensionsupplyoptions_fk_value_3727708');
        $query->join('LEFT', '#__jed_extension_supply_options AS #__jed_extension_supply_options_3727708 ON #__jed_extension_supply_options_3727708.`id` = a.`supply_option_id`');

        // Join over the created by field 'created_by'
        $query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');
        if (!$this->isAdminOrSuperUser()) {
            $query->where("a.created_by = " . Factory::getUser()->get("id"));
        }


        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('( a.title LIKE ' . $search . ' )');
            }
        }




        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'a.id');
        $orderDirn = $this->state->get('list.direction', 'DESC');

        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }

    /**
     * Method to get an array of data items
     *
     * @return  mixed An array of data on success, false on failure.
     */
    public function getItems()
    {
        $items = parent::getItems();

        foreach ($items as $item) {
            if (isset($item->extension_id)) {
                $values    = explode(',', $item->extension_id);
                $textValue = [];

                foreach ($values as $value) {
                    $db    = Factory::getDbo();
                    $query = $db->getQuery(true);
                    $query
                        ->select('`#__jed_extensions_3715042`.`title`')
                        ->from($db->quoteName('#__jed_extensions', '#__jed_extensions_3715042'))
                        ->where($db->quoteName('#__jed_extensions_3715042.id') . ' = ' . $db->quote($db->escape($value)));

                    $db->setQuery($query);
                    $results = $db->loadObject();

                    if ($results) {
                        $textValue[] = $results->title;
                    }
                }

                $item->extension_id = !empty($textValue) ? implode(', ', $textValue) : $item->extension_id;
            }


            if (isset($item->supply_option_id)) {
                $values    = explode(',', $item->supply_option_id);
                $textValue = [];

                foreach ($values as $value) {
                    $db    = Factory::getDbo();
                    $query = $db->getQuery(true);
                    $query
                        ->select('`#__jed_extension_supply_options_3727708`.`title`')
                        ->from($db->quoteName('#__jed_extension_supply_options', '#__jed_extension_supply_options_3727708'))
                        ->where($db->quoteName('#__jed_extension_supply_options_3727708.id') . ' = ' . $db->quote($db->escape($value)));

                    $db->setQuery($query);
                    $results = $db->loadObject();

                    if ($results) {
                        $textValue[] = $results->title;
                    }
                }

                $item->supply_option_id = !empty($textValue) ? implode(', ', $textValue) : $item->supply_option_id;
            }
        }

        return $items;
    }

    /**
     * Overrides the default function to check Date fields format, identified by
     * "_dateformat" suffix, and erases the field if it's not correct.
     *
     * @return void
     * @throws Exception
     * @throws Exception
     */
    protected function loadFormData()
    {
        $app              = Factory::getApplication();
        $filters          = $app->getUserState($this->context . '.filter', []);
        $error_dateformat = false;

        foreach ($filters as $key => $value) {
            if (strpos($key, '_dateformat') && !empty($value) && $this->isValidDate($value) == null) {
                $filters[$key]    = '';
                $error_dateformat = true;
            }
        }

        if ($error_dateformat) {
            $app->enqueueMessage(Text::_("COM_JED_SEARCH_FILTER_DATE_FORMAT"), "warning");
            $app->setUserState($this->context . '.filter', $filters);
        }

        return parent::loadFormData();
    }

    /**
     * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
     *
     * @param   string  $date  Date to be checked
     *
     * @return bool
     */
    private function isValidDate($date)
    {
        $date = str_replace('/', '-', $date);
        return (date_create($date)) ? Factory::getDate($date)->format("Y-m-d") : null;
    }
}
