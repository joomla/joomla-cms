<?php

/**
 * @package       JED
 *
 * @subpackage    VEL
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
use Joomla\Database\QueryInterface;
use stdClass;

use function defined;

/**
 * VEL Developer Updates Model Class.
 *
 * @since 4.0.0
 */
class VeldeveloperupdatesModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see              JController
     * @since            4.0.0
     * @throws Exception
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'contact_fullname', 'a.contact_fullname',
                'contact_organisation', 'a.contact_organisation',
                'contact_email', 'a.contact_email',
                'vulnerable_item_name', 'a.vulnerable_item_name',
                'vulnerable_item_version', 'a.vulnerable_item_version',
                'extension_update', 'a.extension_update',
                'new_version_number', 'a.new_version_number',
                'update_notice_url', 'a.update_notice_url',
                'changelog_url', 'a.changelog_url',
                'download_url', 'a.download_url',
                'consent_to_process', 'a.consent_to_process',
                'vel_item_id', 'a.vel_item_id',
                'update_data_source', 'a.update_data_source',
                'update_date_submitted', 'a.update_date_submitted',
                'update_user_ip', 'a.update_user_ip',
                'created_by', 'a.created_by',
                'modified_by', 'a.modified_by',
                'created', 'a.created',
                'modified', 'a.modified',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to get an array of data items
     *
     * @return  mixed An array of data on success, false on failure.
     * @since 4.0.0
     */
    public function getItems()
    {
        $items = parent::getItems();

        foreach ($items as $item) {
            if (!empty($item->consent_to_process)) {
                $item->consent_to_process = Text::_('COM_JED_GENERAL_CONSENT_TO_PROCESS_OPTION_' . strtoupper($item->consent_to_process));
            }

            if (!empty($item->update_data_source)) {
                $item->update_data_source = Text::_('COM_JED_VEL_GENERAL_FIELD_DATA_SOURCE_OPTION_' . strtoupper($item->update_data_source));
            }
        }

        return $items;
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return   QueryInterface
     *
     * @since 4.0.0
     */
    protected function getListQuery(): QueryInterface
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

        $query->from('`#__jed_vel_developer_update` AS a');


        // Join over the created by field 'created_by'
        $query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

        // Join over the created by field 'modified_by'
        $query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');
        if (!JedHelper::isAdminOrSuperUser()) {
            $query->where("a.created_by = " . JedHelper::getUser()->get("id"));
        }


        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('( a.vulnerable_item_name LIKE ' . $search . ' )');
            }
        }


        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'id');
        $orderDirn = $this->state->get('list.direction', 'DESC');

        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }

    /**
     * Overrides the default function to check Date fields format, identified by
     * "_dateformat" suffix, and erases the field if it's not correct.
     *
     * @return mixed
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
     * @since 4.0.0
     * @throws Exception
     *
     */
    protected function populateState($ordering = null, $direction = null)
    {
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
