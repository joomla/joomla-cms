<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Modules Component Module Model
 *
 * @since  1.5
 */
class ModulesModelModules extends JModelList
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
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'published', 'a.published', 'state',
				'access', 'a.access',
				'ag.title', 'access_level',
				'ordering', 'a.ordering',
				'module', 'a.module',
				'language', 'a.language',
				'l.title', 'language_title',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'client_id', 'a.client_id',
				'position', 'a.position',
				'pages',
				'name', 'e.name',
				'menuitem',
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
	protected function populateState($ordering = 'a.position', $direction = 'asc')
	{
		$app = JFactory::getApplication();

		$layout = $app->input->get('layout', '', 'cmd');

		// Adjust the context to support modal layouts.
		if ($layout)
		{
			$this->context .= '.' . $layout;
		}

		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.position', $this->getUserStateFromRequest($this->context . '.filter.position', 'filter_position', '', 'string'));
		$this->setState('filter.module', $this->getUserStateFromRequest($this->context . '.filter.module', 'filter_module', '', 'string'));
		$this->setState('filter.menuitem', $this->getUserStateFromRequest($this->context . '.filter.menuitem', 'filter_menuitem', '', 'cmd'));
		$this->setState('filter.access', $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '', 'cmd'));

		// If in modal layout on the frontend, state and language are always forced.
		if ($app->isClient('site') && $layout === 'modal')
		{
			$this->setState('filter.language', 'current');
			$this->setState('filter.state', 1);
		}
		// If in backend (modal or not) we get the same fields from the user request.
		else
		{
			$this->setState('filter.language', $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '', 'string'));
			$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string'));
		}

		// Special case for the client id.
		if ($app->isClient('site') || $layout === 'modal')
		{
			$this->setState('client_id', 0);
		}
		else
		{
			$clientId = (int) $this->getUserStateFromRequest($this->context . '.client_id', 'client_id', 0, 'int');
			$clientId = (!in_array($clientId, array (0, 1))) ? 0 : $clientId;
			$this->setState('client_id', $clientId);
		}

		// Load the parameters.
		$params = JComponentHelper::getParams('com_modules');
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
	 * @return  string    A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('client_id');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.position');
		$id .= ':' . $this->getState('filter.module');
		$id .= ':' . $this->getState('filter.menuitem');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * Returns an object list
	 *
	 * @param   string  $query       The query
	 * @param   int     $limitstart  Offset
	 * @param   int     $limit       The number of records
	 *
	 * @return  array
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$listOrder = $this->getState('list.ordering', 'a.position');
		$listDirn  = $this->getState('list.direction', 'asc');

		// If ordering by fields that need translate we need to sort the array of objects after translating them.
		if (in_array($listOrder, array('pages', 'name')))
		{
			// Fetch the results.
			$this->_db->setQuery($query);
			$result = $this->_db->loadObjectList();

			// Translate the results.
			$this->translate($result);

			// Sort the array of translated objects.
			$result = ArrayHelper::sortObjects($result, $listOrder, strtolower($listDirn) == 'desc' ? -1 : 1, true, true);

			// Process pagination.
			$total = count($result);
			$this->cache[$this->getStoreId('getTotal')] = $total;
			if ($total < $limitstart)
			{
				$limitstart = 0;
				$this->setState('list.start', 0);
			}

			return array_slice($result, $limitstart, $limit ? $limit : null);
		}

		// If ordering by fields that doesn't need translate just order the query.
		if ($listOrder === 'a.ordering')
		{
			$query->order($this->_db->quoteName('a.position') . ' ASC')
				->order($this->_db->quoteName($listOrder) . ' ' . $this->_db->escape($listDirn));
		}
		elseif ($listOrder === 'a.position')
		{
			$query->order($this->_db->quoteName($listOrder) . ' ' . $this->_db->escape($listDirn))
				->order($this->_db->quoteName('a.ordering') . ' ASC');
		}
		else
		{
			$query->order($this->_db->quoteName($listOrder) . ' ' . $this->_db->escape($listDirn));
		}

		// Process pagination.
		$result = parent::_getList($query, $limitstart, $limit);

		// Translate the results.
		$this->translate($result);

		return $result;
	}

	/**
	 * Translate a list of objects
	 *
	 * @param   array  &$items  The array of objects
	 *
	 * @return  array The array of translated objects
	 */
	protected function translate(&$items)
	{
		$lang = JFactory::getLanguage();
		$clientPath = $this->getState('client_id') ? JPATH_ADMINISTRATOR : JPATH_SITE;

		foreach ($items as $item)
		{
			$extension = $item->module;
			$source = $clientPath . "/modules/$extension";
			$lang->load("$extension.sys", $clientPath, null, false, true)
				|| $lang->load("$extension.sys", $source, null, false, true);
			$item->name = JText::_($item->name);

			if (is_null($item->pages))
			{
				$item->pages = JText::_('JNONE');
			}
			elseif ($item->pages < 0)
			{
				$item->pages = JText::_('COM_MODULES_ASSIGNED_VARIES_EXCEPT');
			}
			elseif ($item->pages > 0)
			{
				$item->pages = JText::_('COM_MODULES_ASSIGNED_VARIES_ONLY');
			}
			else
			{
				$item->pages = JText::_('JALL');
			}
		}
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$app = JFactory::getApplication();

		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.note, a.position, a.module, a.language,' .
					'a.checked_out, a.checked_out_time, a.published AS published, e.enabled AS enabled, a.access, a.ordering, a.publish_up, a.publish_down'
			)
		);

		// From modules table.
		$query->from($db->quoteName('#__modules', 'a'));

		// Join over the language
		$query->select($db->quoteName('l.title', 'language_title'))
			->select($db->quoteName('l.image', 'language_image'))
			->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'));

		// Join over the users for the checked out user.
		$query->select($db->quoteName('uc.name', 'editor'))
			->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out'));

		// Join over the asset groups.
		$query->select($db->quoteName('ag.title', 'access_level'))
			->join('LEFT', $db->quoteName('#__viewlevels', 'ag') . ' ON ' . $db->quoteName('ag.id') . ' = ' . $db->quoteName('a.access'));

		// Join over the module menus
		$query->select('MIN(mm.menuid) AS pages')
			->join('LEFT', $db->quoteName('#__modules_menu', 'mm') . ' ON ' . $db->quoteName('mm.moduleid') . ' = ' . $db->quoteName('a.id'));

		// Join over the extensions
		$query->select($db->quoteName('e.name', 'name'))
			->join('LEFT', $db->quoteName('#__extensions', 'e') . ' ON ' . $db->quoteName('e.element') . ' = ' . $db->quoteName('a.module'));

		// Group (careful with PostgreSQL)
		$query->group(
				'a.id, a.title, a.note, a.position, a.module, a.language, a.checked_out, ' .
					'a.checked_out_time, a.published, a.access, a.ordering, l.title, l.image, uc.name, ag.title, e.name, ' .
					'l.lang_code, uc.id, ag.id, mm.moduleid, e.element, a.publish_up, a.publish_down, e.enabled'
			);

		// Filter by client.
		$clientId = $this->getState('client_id');
		$query->where($db->quoteName('a.client_id') . ' = ' . (int) $clientId . ' AND ' . $db->quoteName('e.client_id') . ' = ' . (int) $clientId);

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where($db->quoteName('a.access') . ' = ' . (int) $access);
		}

		// Filter by published state.
		$state = $this->getState('filter.state');
		if (is_numeric($state))
		{
			$query->where($db->quoteName('a.published') . ' = ' . (int) $state);
		}
		elseif ($state === '')
		{
			$query->where($db->quoteName('a.published') . ' IN (0, 1)');
		}

		// Filter by position.
		if ($position = $this->getState('filter.position'))
		{
			$query->where($db->quoteName('a.position') . ' = ' . $db->quote(($position === 'none') ? '' : $position));
		}

		// Filter by module.
		if ($module = $this->getState('filter.module'))
		{
			$query->where($db->quoteName('a.module') . ' = ' . $db->quote($module));
		}

		// Filter by menuitem id (only for site client).
		if ((int) $clientId === 0 && $menuItemId = $this->getState('filter.menuitem'))
		{
			// If user selected the modules not assigned to any page (menu item).
			if ((int) $menuItemId === -1)
			{
				$query->having('MIN(' . $db->quoteName('mm.menuid') . ') IS NULL');
			}
			// If user selected the modules assigned to some particlar page (menu item).
			else
			{
				// Modules in "All" pages.
				$subQuery1 = $db->getQuery(true);
				$subQuery1->select('MIN(' . $db->quoteName('menuid') . ')')
					->from($db->quoteName('#__modules_menu'))
					->where($db->quoteName('moduleid') . ' = ' . $db->quoteName('a.id'));

				// Modules in "Selected" pages that have the chosen menu item id.
				$subQuery2 = $db->getQuery(true);
				$subQuery2->select($db->quoteName('moduleid'))
					->from($db->quoteName('#__modules_menu'))
					->where($db->quoteName('menuid') . ' = ' . (int) $menuItemId);

				// Modules in "All except selected" pages that doesn't have the chosen menu item id.
				$subQuery3 = $db->getQuery(true);
				$subQuery3->select($db->quoteName('moduleid'))
					->from($db->quoteName('#__modules_menu'))
					->where($db->quoteName('menuid') . ' = -' . (int) $menuItemId);

				// Filter by modules assigned to the selected menu item.
				$query->where('(
					(' . $subQuery1 . ') = 0
					OR ((' . $subQuery1 . ') > 0 AND ' . $db->quoteName('a.id') . ' IN (' . $subQuery2 . '))
					OR ((' . $subQuery1 . ') < 0 AND ' . $db->quoteName('a.id') . ' NOT IN (' . $subQuery3 . '))
					)');
			}
		}

		// Filter by search in title or note or id:.
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->quoteName('a.id') . ' = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . strtolower($search) . '%');
				$query->where('(LOWER(a.title) LIKE ' . $search . ' OR LOWER(a.note) LIKE ' . $search . ')');
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			if ($language === 'current')
			{
				$query->where($db->quoteName('a.language') . ' IN (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
			}
			else
			{
				$query->where($db->quoteName('a.language') . ' = ' . $db->quote($language));
			}
		}

		return $query;
	}
}
