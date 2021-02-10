<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of banner records.
 *
 * @since  1.6
 */
class BannersModelClients extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JControllerLegacy
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'name', 'a.name',
				'contact', 'a.contact',
				'state', 'a.state',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'purchase_type', 'a.purchase_type'
			);
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
		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string'));
		$this->setState('filter.purchase_type', $this->getUserStateFromRequest($this->context . '.filter.purchase_type', 'filter_purchase_type'));

		// Load the parameters.
		$this->setState('params', JComponentHelper::getParams('com_banners'));

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
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$defaultPurchase = JComponentHelper::getParams('com_banners')->get('purchase_type', 3);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id AS id,'
				. 'a.name AS name,'
				. 'a.contact AS contact,'
				. 'a.checked_out AS checked_out,'
				. 'a.checked_out_time AS checked_out_time, '
				. 'a.state AS state,'
				. 'a.metakey AS metakey,'
				. 'a.purchase_type as purchase_type'
			)
		);

		$query->from($db->quoteName('#__banner_clients') . ' AS a');

		// Join over the banners for counting
		$query->select('COUNT(b.id) as nbanners')
			->join('LEFT', '#__banners AS b ON a.id = b.cid');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
			->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.state IN (0, 1))');
		}

		$query->group('a.id, a.name, a.contact, a.checked_out, a.checked_out_time, a.state, a.metakey, a.purchase_type, uc.name');

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('a.name LIKE ' . $search);
			}
		}

		// Filter by purchase type
		$purchaseType = $this->getState('filter.purchase_type');

		if (!empty($purchaseType))
		{
			if ($defaultPurchase == $purchaseType)
			{
				$query->where('(a.purchase_type = ' . (int) $purchaseType . ' OR a.purchase_type = -1)');
			}
			else
			{
				$query->where('a.purchase_type = ' . (int) $purchaseType);
			}
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.name')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

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
		if (!empty($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Load the list items.
		$items = parent::getItems();

		// If empty or an error, just return.
		if (empty($items))
		{
			return array();
		}

		// Getting the following metric by joins is WAY TOO SLOW.
		// Faster to do three queries for very large banner trees.

		// Get the clients in the list.
		$db = $this->getDbo();
		$clientIds = ArrayHelper::getColumn($items, 'id');

		// Quote the strings.
		$clientIds = implode(
			',',
			array_map(array($db, 'quote'), $clientIds)
		);

		// Get the published banners count.
		$query = $db->getQuery(true)
			->select('cid, COUNT(cid) AS count_published')
			->from('#__banners')
			->where('state = 1')
			->where('cid IN (' . $clientIds . ')')
			->group('cid');

		$db->setQuery($query);

		try
		{
			$countPublished = $db->loadAssocList('cid', 'count_published');
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Get the unpublished banners count.
		$query->clear('where')
			->where('state = 0')
			->where('cid IN (' . $clientIds . ')');
		$db->setQuery($query);

		try
		{
			$countUnpublished = $db->loadAssocList('cid', 'count_published');
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Get the trashed banners count.
		$query->clear('where')
			->where('state = -2')
			->where('cid IN (' . $clientIds . ')');
		$db->setQuery($query);

		try
		{
			$countTrashed = $db->loadAssocList('cid', 'count_published');
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Get the archived banners count.
		$query->clear('where')
			->where('state = 2')
			->where('cid IN (' . $clientIds . ')');
		$db->setQuery($query);

		try
		{
			$countArchived = $db->loadAssocList('cid', 'count_published');
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Inject the values back into the array.
		foreach ($items as $item)
		{
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
