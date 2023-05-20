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
 * VEL Vunerable Items Model Class.
 *
 * @since  4.0.0
 */
class VelvulnerableitemsModel extends ListModel
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
                'title', 'a.`title`',
                'internal_description', 'a.`internal_description`',
                'status', 'a.`status`',
                'report_id', 'a.`report_id`',
                'jed', 'a.`jed`',
                'risk_level', 'a.`risk_level`',
                'start_version', 'a.`start_version`',
                'vulnerable_version', 'a.`vulnerable_version`',
                'patch_version', 'a.`patch_version`',
                'recommendation', 'a.`recommendation`',
                'update_notice', 'a.`update_notice`',
                'exploit_type', 'a.`exploit_type`',
                'exploit_other_description', 'a.`exploit_other_description`',
                'xml_manifest', 'a.`xml_manifest`',
                'manifest_location', 'a.`manifest_location`',
                'install_data', 'a.`install_data`',
                'discovered_by', 'a.`discovered_by`',
                'discoverer_public', 'a.`discoverer_public`',
                'fixed_by', 'a.`fixed_by`',
                'coordinated_by', 'a.`coordinated_by`',
                'jira', 'a.`jira`',
                'cve_id', 'a.`cve_id`',
                'cwe_id', 'a.`cwe_id`',
                'cvssthirty_base', 'a.`cvssthirty_base`',
                'cvssthirty_base_score', 'a.`cvssthirty_base_score`',
                'cvssthirty_temp', 'a.`cvssthirty_temp`',
                'cvssthirty_temp_score', 'a.`cvssthirty_temp_score`',
                'cvssthirty_env', 'a.`cvssthirty_env`',
                'cvssthirty_env_score', 'a.`cvssthirty_env_score`',
                'public_description', 'a.`public_description`',
                'alias', 'a.`alias`',
                'created_by', 'a.`created_by`',
                'modified_by', 'a.`modified_by`',
                'created', 'a.`created`',
                'modified', 'a.`modified`',
                'state', 'a.`state`',
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
    public function getItems(): mixed
    {
        $items = parent::getItems();


        foreach ($items as $oneItem) {
            $oneItem->status = Text::_('COM_JED_VEL_GENERAL_STATUS_OPTION_' . strtoupper($oneItem->status));
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
        $query->from('`#__jed_vel_vulnerable_item` AS a');

        // Join over the users for the checked out user
        $query->select("uc.name AS uEditor");
        $query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

        // Join over the foreign key 'report_id'
        $query->select('concat( `jvr`.`vulnerable_item_name`," - ", `jvr`.`vulnerable_item_version`, " (", `jvr`.`created`, ")") AS vel_report_title');
        $query->join('LEFT', '#__jed_vel_report AS jvr ON jvr.`id` = a.`report_id`');

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
