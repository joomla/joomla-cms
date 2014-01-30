<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Methods supporting a list of plugin records.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 * @since       3.2
 */
class PluginsModelPlugins extends JModelCmslist
{
	/**
	 * Constructor.
	 *
	 * @param   array  An optional associative array of configuration settings.
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'extension_id', 'a.extension_id',
				'name', 'a.name',
				'folder', 'a.folder',
				'element', 'a.element',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'state', 'a.state',
				'enabled', 'a.enabled',
				'access', 'a.access', 'access_level',
				'ordering', 'a.ordering',
				'client_id', 'a.client_id',
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
	protected function populateState($ordering = null, $direction = null)
	{
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->state->set('filter.search', $search);

		$accessId = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', null, 'int');
		$this->state->set('filter.access', $accessId);

		$state = $this->getUserStateFromRequest($this->context . '.filter.enabled', 'filter_enabled', '', 'string');
		$this->state->set('filter.enabled', $state);

		$folder = $this->getUserStateFromRequest($this->context . '.filter.folder', 'filter_folder', null, 'cmd');
		$this->state->set('filter.folder', $folder);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->state->set('filter.language', $language);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_plugins');
		$this->state->set('params', $params);

		// List state information.
		parent::populateState('folder', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string    A prefix for the store id.
	 *
	 * @return  string    A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->state->get('filter.search');
		$id .= ':' . $this->state->get('filter.access');
		$id .= ':' . $this->state->get('filter.state');
		$id .= ':' . $this->state->get('filter.folder');
		$id .= ':' . $this->state->get('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * Returns an object list
	 *
	 * @param   string   $query  The query
	 * @param   integer  Offset
	 * @param   integer  The number of records
	 *
	 * @return  array
	 *
	 * @since  3.2
	 */
	protected function getList($query, $limitstart = 0, $limit = 0)
	{
		$search = $this->state->get('filter.search');
		$ordering = $this->state->get('list.ordering', 'ordering');

		if ($ordering == 'name' || (!empty($search) && stripos($search, 'id:') !== 0))
		{
			$this->_db->setQuery($query);
			$result = $this->_db->loadObjectList();
			$this->translate($result);
			if (!empty($search))
			{
				foreach ($result as $i => $item)
				{
					if (!preg_match("/$search/i", $item->name))
					{
						unset($result[$i]);
					}
				}
			}

			$direction = ($this->state->get('list.direction') == 'desc') ? -1 : 1;
			JArrayHelper::sortObjects($result, $ordering, $direction, true, true);

			$total = count($result);
			$this->cache[$this->getStoreId('getTotal')] = $total;
			if ($total < $limitstart)
			{
				$limitstart = 0;
				$this->state->set('list.start', 0);
			}
			return array_slice($result, $limitstart, $limit ? $limit : null);
		}
		else
		{
			if ($ordering == 'ordering')
			{
				$query->order('a.folder ASC');
				$ordering = 'a.ordering';
			}
			$query->order($this->_db->quoteName($ordering) . ' ' . $this->state->get('list.direction'));

			if ($ordering == 'folder')
			{
				$query->order('a.ordering ASC');
			}
			$result = parent::getList($query, $limitstart, $limit);
			$this->translate($result);
			return $result;
		}
	}

	/**
	 * Translate a list of objects
	 *
	 * @param   array  $items  The array of objects
	 *
	 * @return  array  The array of translated objects
	 *
	 * @since  3/2
	 */
	protected function translate(&$items)
	{
		$lang = JFactory::getLanguage();

		foreach ($items as &$item)
		{
			$source = JPATH_PLUGINS . '/' . $item->folder . '/' . $item->element;
			$extension = 'plg_' . $item->folder . '_' . $item->element;
			$lang->load($extension . '.sys', JPATH_ADMINISTRATOR, null, false, false)
				|| $lang->load($extension . '.sys', $source, null, false, false)
				|| $lang->load($extension . '.sys', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
				|| $lang->load($extension . '.sys', $source, $lang->getDefault(), false, false);
			$item->name = JText::_($item->name);
		}
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since  3.2
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$query = $this->_db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->state->get(
				'list.select',
				'a.extension_id , a.name, a.element, a.folder, a.checked_out, a.checked_out_time,' .
					' a.enabled, a.access, a.ordering'
			)
		)
			->from($this->_db->quoteName('#__extensions') . ' AS a')
			->where($this->_db->quoteName('type') . ' = ' . $this->_db->quote('plugin'));

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
			->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level')
			->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Filter by access level.
		if ($access = $this->state->get('filter.access'))
		{
			$query->where('a.access = ' . (int) $access);
		}

		// Filter by published state
		$published = $this->state->get('filter.enabled');
		if (is_numeric($published))
		{
			$query->where('a.enabled = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.enabled IN (0, 1))');
		}

		// Filter by state
		$query->where('a.state >= 0');

		// Filter by folder.
		if ($folder = $this->state->get('filter.folder'))
		{
			$query->where('a.folder = ' . $this->_db->quote($folder));
		}

		// Filter by search in name or id
		$search = $this->state->get('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.extension_id = ' . (int) substr($search, 3));
			}
		}

		return $query;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type    The table type to instantiate
	 * @param   string  A prefix for the table class name. Optional.
	 * @param   array   Configuration array for model. Optional.
	 * @return  JTable  A database object
	 */
	public function getTable($type = 'Extension', $prefix = 'JTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
}
