<?php

/**
 * @package       JED
 *
 * @subpackage    VEL
 *
 * @copyright     (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
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
 * VEL Reports Model Class.
 *
 * @since    4.0.0
 */
class VelreportsModel extends ListModel
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
                'reporter_fullname', 'a.`reporter_fullname`',
                'reporter_email', 'a.`reporter_email`',
                'reporter_organisation', 'a.`reporter_organisation`',
                'pass_details_ok', 'a.`pass_details_ok`',
                'vulnerability_type', 'a.`vulnerability_type`',
                'vulnerable_item_name', 'a.`vulnerable_item_name`',
                'vulnerable_item_version', 'a.`vulnerable_item_version`',
                'exploit_type', 'a.`exploit_type`',
                'exploit_other_description', 'a.`exploit_other_description`',
                'vulnerability_description', 'a.`vulnerability_description`',
                'vulnerability_how_found', 'a.`vulnerability_how_found`',
                'vulnerability_actively_exploited', 'a.`vulnerability_actively_exploited`',
                'vulnerability_publicly_available', 'a.`vulnerability_publicly_available`',
                'vulnerability_publicly_url', 'a.`vulnerability_publicly_url`',
                'vulnerability_specific_impact', 'a.`vulnerability_specific_impact`',
                'developer_communication_type', 'a.`developer_communication_type`',
                'developer_patch_download_url', 'a.`developer_patch_download_url`',
                'developer_name', 'a.`developer_name`',
                'developer_contact_email', 'a.`developer_contact_email`',
                'tracking_db_name', 'a.`tracking_db_name`',
                'tracking_db_id', 'a.`tracking_db_id`',
                'jed_url', 'a.`jed_url`',
                'developer_additional_info', 'a.`developer_additional_info`',
                'download_url', 'a.`download_url`',
                'consent_to_process', 'a.`consent_to_process`',
                'passed_to_vel', 'a.`passed_to_vel`',
                'vel_item_id', 'a.`vel_item_id`',
                'data_source', 'a.`data_source`',
                'date_submitted', 'a.`date_submitted`',
                'user_ip', 'a.`user_ip`',
                'created_by', 'a.`created_by`',
                'modified_by', 'a.`modified_by`',
                'created', 'a.`created`',
                'modified', 'a.`modified`',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Get an array of data items
     *
     * @return mixed Array of data items on success, false on failure.
     *
     * @since    4.0.0
     */
    public function getItems(): mixed
    {
        $items = parent::getItems();

        foreach ($items as $oneItem) {
            $oneItem->pass_details_ok    = Text::_('COM_JED_VEL_GENERAL_FIELD_PASS_DETAILS_OK_OPTION_' . strtoupper($oneItem->pass_details_ok));
            $oneItem->vulnerability_type = Text::_('COM_JED_VEL_GENERAL_FIELD_VULNERABILITY_TYPE_OPTION_' . strtoupper($oneItem->vulnerability_type));
            $oneItem->exploit_type       = Text::_('COM_JED_VEL_GENERAL_FIELD_EXPLOIT_TYPE_OPTION_' . strtoupper($oneItem->exploit_type));
            $oneItem->consent_to_process = Text::_('COM_JED_VEL_GENERAL_FIELD_CONSENT_TO_PROCESS_OPTION_' . strtoupper($oneItem->consent_to_process));
            $oneItem->passed_to_vel      = Text::_('COM_JED_VEL_GENERAL_FIELD_PASSED_TO_VEL_OPTION_' . strtoupper($oneItem->passed_to_vel));
            $oneItem->data_source        = Text::_('COM_JED_VEL_GENERAL_FIELD_DATA_SOURCE_OPTION_' . strtoupper($oneItem->data_source));
        }

        return $items;
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return   QueryInterface
     *
     * @since    4.0.0
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
        $query->from('`#__jed_vel_report` AS a');


        // Join over the user field 'created_by'
        $query->select('`created_by`.name AS `created_by`');
        $query->join('LEFT', '#__users AS `created_by` ON `created_by`.id = a.`created_by`');

        // Join over the user field 'modified_by'
        $query->select('`modified_by`.name AS `modified_by`');
        $query->join('LEFT', '#__users AS `modified_by` ON `modified_by`.id = a.`modified_by`');


        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('( a.vulnerable_item_name LIKE ' . $search . '  OR  a.exploit_type LIKE ' . $search . ' )');
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
     * @since    4.0.0
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
     * @since    4.0.0
     * @throws Exception
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // List state information.
        parent::populateState('id', 'DESC');

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
