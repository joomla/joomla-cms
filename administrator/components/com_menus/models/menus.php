<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Menu List Model for Menus.
 *
 * @since  1.6
 */
class MenusModelMenus extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'menutype', 'a.menutype',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Overrides the getItems method to attach additional metrics to the list.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
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

		// If emtpy or an error, just return.
		if (empty($items))
		{
			return array();
		}

		// Getting the following metric by joins is WAY TOO SLOW.
		// Faster to do three queries for very large menu trees.

		// Get the menu types of menus in the list.
		$db = $this->getDbo();
		$menuTypes = JArrayHelper::getColumn($items, 'menutype');

		// Quote the strings.
		$menuTypes = implode(
			',',
			array_map(array($db, 'quote'), $menuTypes)
		);

		// Get the published menu counts.
		$query = $db->getQuery(true)
			->select('m.menutype, COUNT(DISTINCT m.id) AS count_published')
			->from('#__menu AS m')
			->where('m.published = 1')
			->where('m.menutype IN (' . $menuTypes . ')')
			->group('m.menutype');

		$db->setQuery($query);

		try
		{
			$countPublished = $db->loadAssocList('menutype', 'count_published');
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Get the unpublished menu counts.
		$query->clear('where')
			->where('m.published = 0')
			->where('m.menutype IN (' . $menuTypes . ')');
		$db->setQuery($query);

		try
		{
			$countUnpublished = $db->loadAssocList('menutype', 'count_published');
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Get the trashed menu counts.
		$query->clear('where')
			->where('m.published = -2')
			->where('m.menutype IN (' . $menuTypes . ')');
		$db->setQuery($query);

		try
		{
			$countTrashed = $db->loadAssocList('menutype', 'count_published');
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Inject the values back into the array.
		foreach ($items as $item)
		{
			$item->count_published = isset($countPublished[$item->menutype]) ? $countPublished[$item->menutype] : 0;
			$item->count_unpublished = isset($countUnpublished[$item->menutype]) ? $countUnpublished[$item->menutype] : 0;
			$item->count_trashed = isset($countTrashed[$item->menutype]) ? $countTrashed[$item->menutype] : 0;
		}

		// Add the items to the internal cache.
		$this->cache[$store] = $items;

		return $this->cache[$store];
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return  string  An SQL query
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select all fields from the table.
		$query->select($this->getState('list.select', 'a.id, a.menutype, a.title, a.description'))
			->from($db->quoteName('#__menu_types') . ' AS a')
			->where('a.id > 0');

		// Filter by search in title or menutype
		if ($search = trim($this->getState('filter.search')))
		{
			$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			$query->where('(' . 'a.title LIKE ' . $search . ' OR a.menutype LIKE ' . $search . ')');
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.id')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
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
	protected function populateState($ordering = null, $direction = null)
	{
		$search = $this->getUserStateFromRequest($this->context . '.search', 'filter_search');
		$this->setState('filter.search', $search);

		// List state information.
		parent::populateState('a.title', 'asc');
	}

	/**
	 * Gets the extension id of the core mod_menu module.
	 *
	 * @return  integer
	 *
	 * @since   2.5
	 */
	public function getModMenuId()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('e.extension_id')
			->from('#__extensions AS e')
			->where('e.type = ' . $db->quote('module'))
			->where('e.element = ' . $db->quote('mod_menu'))
			->where('e.client_id = 0');
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Gets a list of all mod_mainmenu modules and collates them by menutype
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public function &getModules()
	{
		$model = JModelLegacy::getInstance('Menu', 'MenusModel', array('ignore_request' => true));
		$result = $model->getModules();

		return $result;
	}
}
