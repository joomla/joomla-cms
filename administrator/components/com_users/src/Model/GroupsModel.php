<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\UserGroupsHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Methods supporting a list of user group records.
 *
 * @since  1.6
 */
class GroupsModel extends ListModel
{
    /**
     * Override parent constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
     * @since   3.2
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'parent_id', 'a.parent_id',
                'title', 'a.title',
                'lft', 'a.lft',
                'rgt', 'a.rgt',
            ];
        }

        parent::__construct($config, $factory);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = 'a.lft', $direction = 'asc')
    {
        // Load the parameters.
        $params = ComponentHelper::getParams('com_users');
        $this->setState('params', $params);

        // List state information.
        parent::populateState($ordering, $direction);
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
     * @return  string  A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
    }

    /**
     * Gets the list of groups and adds expensive joins to the result set.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6
     */
    public function getItems()
    {
        // Get a storage key.
        $store = $this->getStoreId();

        // Try to load the data from internal storage.
        if (empty($this->cache[$store])) {
            $items = parent::getItems();

            // Bail out on an error or empty list.
            if (empty($items)) {
                $this->cache[$store] = $items;

                return $items;
            }

            try {
                $items = $this->populateExtraData($items);
            } catch (\RuntimeException $e) {
                $this->setError($e->getMessage());

                return false;
            }

            // Add the items to the internal cache.
            $this->cache[$store] = $items;
        }

        return $this->cache[$store];
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  QueryInterface
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.*'
            )
        );
        $query->from($db->quoteName('#__usergroups') . ' AS a');

        // Filter the comments over the search string if set.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $ids = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :id');
                $query->bind(':id', $ids, ParameterType::INTEGER);
            } else {
                $search = '%' . trim($search) . '%';
                $query->where($db->quoteName('a.title') . ' LIKE :title');
                $query->bind(':title', $search);
            }
        }

        // Add the list ordering clause.
        $query->order($db->escape($this->getState('list.ordering', 'a.lft')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

        return $query;
    }

    /**
     * Populate level & path for items.
     *
     * @param   array  $items  Array of \stdClass objects
     *
     * @return  array
     *
     * @since   3.6.3
     */
    private function populateExtraData(array $items)
    {
        // First pass: get list of the group ids and reset the counts.
        $groupsByKey = [];

        foreach ($items as $item) {
            $groupsByKey[(int) $item->id] = $item;
        }

        $groupIds = array_keys($groupsByKey);

        $db = $this->getDatabase();

        // Get total enabled users in group.
        $query = $db->getQuery(true);

        // Count the objects in the user group.
        $query->select('map.group_id, COUNT(DISTINCT map.user_id) AS user_count')
            ->from($db->quoteName('#__user_usergroup_map', 'map'))
            ->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('map.user_id'))
            ->whereIn($db->quoteName('map.group_id'), $groupIds)
            ->where($db->quoteName('u.block') . ' = 0')
            ->group($db->quoteName('map.group_id'));
        $db->setQuery($query);

        try {
            $countEnabled = $db->loadAssocList('group_id', 'count_enabled');
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Get total disabled users in group.
        $query->clear();
        $query->select('map.group_id, COUNT(DISTINCT map.user_id) AS user_count')
            ->from($db->quoteName('#__user_usergroup_map', 'map'))
            ->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('map.user_id'))
            ->whereIn($db->quoteName('map.group_id'), $groupIds)
            ->where($db->quoteName('u.block') . ' = 1')
            ->group($db->quoteName('map.group_id'));
        $db->setQuery($query);

        try {
            $countDisabled = $db->loadAssocList('group_id', 'count_disabled');
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Inject the values back into the array.
        foreach ($groupsByKey as &$item) {
            $item->count_enabled   = isset($countEnabled[$item->id]) ? (int) $countEnabled[$item->id]['user_count'] : 0;
            $item->count_disabled  = isset($countDisabled[$item->id]) ? (int) $countDisabled[$item->id]['user_count'] : 0;
            $item->user_count      = $item->count_enabled + $item->count_disabled;
        }

        $groups = new UserGroupsHelper($groupsByKey);

        return array_values($groups->getAll());
    }
}
