<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Methods supporting a list of banner records.
 *
 * @since  1.6
 */
class ClientsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @since   1.6
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'name', 'a.name',
                'contact', 'a.contact',
                'state', 'a.state',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'purchase_type', 'a.purchase_type',
            ];
        }

        parent::__construct($config);
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
    protected function populateState($ordering = 'a.name', $direction = 'asc')
    {
        // Load the parameters.
        $this->setState('params', ComponentHelper::getParams('com_banners'));

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
        $id .= ':' . $this->getState('filter.state');
        $id .= ':' . $this->getState('filter.purchase_type');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  \Joomla\Database\DatabaseQuery
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $defaultPurchase = (int) ComponentHelper::getParams('com_banners')->get('purchase_type', 3);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                [
                    $db->quoteName('a.id'),
                    $db->quoteName('a.name'),
                    $db->quoteName('a.contact'),
                    $db->quoteName('a.checked_out'),
                    $db->quoteName('a.checked_out_time'),
                    $db->quoteName('a.state'),
                    $db->quoteName('a.metakey'),
                    $db->quoteName('a.purchase_type'),
                ]
            )
        )
            ->select(
                [
                    'COUNT(' . $db->quoteName('b.id') . ') AS ' . $db->quoteName('nbanners'),
                    $db->quoteName('uc.name', 'editor'),
                ]
            );

        $query->from($db->quoteName('#__banner_clients', 'a'));

        // Join over the banners for counting
        $query->join('LEFT', $db->quoteName('#__banners', 'b'), $db->quoteName('a.id') . ' = ' . $db->quoteName('b.cid'));

        // Join over the users for the checked out user.
        $query->join('LEFT', $db->quoteName('#__users', 'uc'), $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out'));

        // Filter by published state
        $published = (string) $this->getState('filter.state');

        if (is_numeric($published)) {
            $published = (int) $published;
            $query->where($db->quoteName('a.state') . ' = :published')
                ->bind(':published', $published, ParameterType::INTEGER);
        } elseif ($published === '') {
            $query->where($db->quoteName('a.state') . ' IN (0, 1)');
        }

        $query->group(
            [
                $db->quoteName('a.id'),
                $db->quoteName('a.name'),
                $db->quoteName('a.contact'),
                $db->quoteName('a.checked_out'),
                $db->quoteName('a.checked_out_time'),
                $db->quoteName('a.state'),
                $db->quoteName('a.metakey'),
                $db->quoteName('a.purchase_type'),
                $db->quoteName('uc.name'),
            ]
        );

        // Filter by search in title
        if ($search = trim($this->getState('filter.search', ''))) {
            if (stripos($search, 'id:') === 0) {
                $search = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :search')
                    ->bind(':search', $search, ParameterType::INTEGER);
            } else {
                $search = '%' . str_replace(' ', '%', $search) . '%';
                $query->where($db->quoteName('a.name') . ' LIKE :search')
                    ->bind(':search', $search);
            }
        }

        // Filter by purchase type
        if ($purchaseType = (int) $this->getState('filter.purchase_type')) {
            if ($defaultPurchase === $purchaseType) {
                $query->where('(' . $db->quoteName('a.purchase_type') . ' = :type OR ' . $db->quoteName('a.purchase_type') . ' = -1)');
            } else {
                $query->where($db->quoteName('a.purchase_type') . ' = :type');
            }

            $query->bind(':type', $purchaseType, ParameterType::INTEGER);
        }

        // Add the list ordering clause.
        $query->order(
            $db->quoteName($db->escape($this->getState('list.ordering', 'a.name'))) . ' ' . $db->escape($this->getState('list.direction', 'ASC'))
        );

        return $query;
    }

    /**
     * Overrides the getItems method to attach additional metrics to the list.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   3.6
     */
    public function getItems()
    {
        // Get a storage key.
        $store = $this->getStoreId('getItems');

        // Try to load the data from internal storage.
        if (!empty($this->cache[$store])) {
            return $this->cache[$store];
        }

        // Load the list items.
        $items = parent::getItems();

        // If empty or an error, just return.
        if (empty($items)) {
            return [];
        }

        // Getting the following metric by joins is WAY TOO SLOW.
        // Faster to do three queries for very large banner trees.

        // Get the clients in the list.
        $db        = $this->getDatabase();
        $clientIds = array_column($items, 'id');

        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('cid'),
                    'COUNT(' . $db->quoteName('cid') . ') AS ' . $db->quoteName('count_published'),
                ]
            )
            ->from($db->quoteName('#__banners'))
            ->where($db->quoteName('state') . ' = :state')
            ->whereIn($db->quoteName('cid'), $clientIds)
            ->group($db->quoteName('cid'))
            ->bind(':state', $state, ParameterType::INTEGER);

        $db->setQuery($query);

        // Get the published banners count.
        try {
            $state          = 1;
            $countPublished = $db->loadAssocList('cid', 'count_published');
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Get the unpublished banners count.
        try {
            $state            = 0;
            $countUnpublished = $db->loadAssocList('cid', 'count_published');
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Get the trashed banners count.
        try {
            $state        = -2;
            $countTrashed = $db->loadAssocList('cid', 'count_published');
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Get the archived banners count.
        try {
            $state         = 2;
            $countArchived = $db->loadAssocList('cid', 'count_published');
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Inject the values back into the array.
        foreach ($items as $item) {
            $item->count_published   = isset($countPublished[$item->id]) ? $countPublished[$item->id] : 0;
            $item->count_unpublished = isset($countUnpublished[$item->id]) ? $countUnpublished[$item->id] : 0;
            $item->count_trashed     = isset($countTrashed[$item->id]) ? $countTrashed[$item->id] : 0;
            $item->count_archived    = isset($countArchived[$item->id]) ? $countArchived[$item->id] : 0;
        }

        // Add the items to the internal cache.
        $this->cache[$store] = $items;

        return $this->cache[$store];
    }
}
