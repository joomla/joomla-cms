<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Menu Item List Model for Menus.
 *
 * @since  1.6
 */
class MenusModelItems extends JModelList
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
				'menutype', 'a.menutype', 'menutype_title',
				'title', 'a.title',
				'alias', 'a.alias',
				'published', 'a.published',
				'access', 'a.access', 'access_level',
				'language', 'a.language',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'lft', 'a.lft',
				'rgt', 'a.rgt',
				'level', 'a.level',
				'path', 'a.path',
				'client_id', 'a.client_id',
				'home', 'a.home',
				'a.ordering'
			);

			$app = JFactory::getApplication();
			$assoc = JLanguageAssociations::isEnabled();

			if ($assoc)
			{
				$config['filter_fields'][] = 'association';
			}
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
	protected function populateState($ordering = 'a.lft', $direction = 'asc')
	{
		$app = JFactory::getApplication('administrator');
		$user = JFactory::getUser();

		$forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		// Adjust the context to support forced languages.
		if ($forcedLanguage)
		{
			$this->context .= '.' . $forcedLanguage;
		}

		$parentId = $this->getUserStateFromRequest($this->context . '.filter.parent_id', 'filter_parent_id');
		$this->setState('filter.parent_id', $parentId);

		$search = $this->getUserStateFromRequest($this->context . '.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
		$this->setState('filter.access', $access);

		$parentId = $this->getUserStateFromRequest($this->context . '.filter.parent_id', 'filter_parent_id');
		$this->setState('filter.parent_id', $parentId);

		$level = $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level');
		$this->setState('filter.level', $level);

		$currentMenuType = $app->getUserState($this->context . '.menutype', '');
		$menuType        = $app->input->getString('menutype', $currentMenuType);

		// If selected menu type different from current menu type reset pagination to 0
		if ($menuType != $currentMenuType)
		{
			$app->input->set('limitstart', 0);
		}

		if ($menuType)
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
						->select($db->qn(array('id', 'title')))
						->from($db->qn('#__menu_types'))
						->where($db->qn('menutype') . ' = ' . $db->q($menuType));

			$menuTypeItem = $db->setQuery($query)->loadObject();

			// Check if menu type exists.
			if (!$menuTypeItem)
			{
				$this->setError(JText::_('COM_MENUS_ERROR_MENUTYPE_NOT_FOUND'));
			}
			// Check if menu type was changed and if valid agains ACL
			elseif ($user->authorise('core.manage', 'com_menus.menu.' . $menuTypeItem->id))
			{
				$app->setUserState($this->context . '.menutype', $menuType);
				$this->setState('menutypetitle', !empty($menuTypeItem->title) ? $menuTypeItem->title : '');
				$this->setState('menutypeid', !empty($menuTypeItem->id) ? $menuTypeItem->id : '');
			}
			// Nope, not valid
			else
			{
				$this->setError(JText::_('JERROR_ALERTNOAUTHOR'));
			}
		}
		else
		{
			$app->setUserState($this->context . '.menutype', '');
			$this->setState('menutypetitle', '');
			$this->setState('menutypeid', '');
		}

		$this->setState('filter.menutype', $menuType);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// Component parameters.
		$params = JComponentHelper::getParams('com_menus');
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);

		// Force a language.
		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
		}
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
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.language');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.parent_id');
		$id .= ':' . $this->getState('filter.menutype');

		return parent::getStoreId($id);
	}

	/**
	 * Builds an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery    A query object.
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		// Select all fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				$db->quoteName(
					array(
						'a.id', 'a.menutype', 'a.title', 'a.alias', 'a.note', 'a.path', 'a.link', 'a.type', 'a.parent_id',
						'a.level', 'a.published', 'a.component_id', 'a.checked_out', 'a.checked_out_time', 'a.browserNav',
						'a.access', 'a.img', 'a.template_style_id', 'a.params', 'a.lft', 'a.rgt', 'a.home', 'a.language', 'a.client_id'
					),
					array(
						null, null, null, null, null, null, null, null, null,
						null, 'a.published', null, null, null, null,
						null, null, null, null, null, null, null, null, null
					)
				)
			)
		);
		$query->select(
			'CASE ' .
				' WHEN a.type = ' . $db->quote('component') . ' THEN a.published+2*(e.enabled-1) ' .
				' WHEN a.type = ' . $db->quote('url') . ' AND a.published != -2 THEN a.published+2 ' .
				' WHEN a.type = ' . $db->quote('url') . ' AND a.published = -2 THEN a.published-1 ' .
				' WHEN a.type = ' . $db->quote('alias') . ' AND a.published != -2 THEN a.published+4 ' .
				' WHEN a.type = ' . $db->quote('alias') . ' AND a.published = -2 THEN a.published-1 ' .
				' WHEN a.type = ' . $db->quote('separator') . ' AND a.published != -2 THEN a.published+6 ' .
				' WHEN a.type = ' . $db->quote('separator') . ' AND a.published = -2 THEN a.published-1 ' .
				' WHEN a.type = ' . $db->quote('heading') . ' AND a.published != -2 THEN a.published+8 ' .
				' WHEN a.type = ' . $db->quote('heading') . ' AND a.published = -2 THEN a.published-1 ' .
			' END AS published '
		);
		$query->from($db->quoteName('#__menu') . ' AS a');

		// Join over the language
		$query->select('l.title AS language_title, l.image AS language_image, l.sef AS language_sef')
			->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

		// Join over the users.
		$query->select('u.name AS editor')
			->join('LEFT', $db->quoteName('#__users') . ' AS u ON u.id = a.checked_out');

		// Join over components
		$query->select('c.element AS componentname')
			->join('LEFT', $db->quoteName('#__extensions') . ' AS c ON c.extension_id = a.component_id');

		// Join over the asset groups.
		$query->select('ag.title AS access_level')
			->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the menu types.
		$query->select($db->quoteName(array('mt.id', 'mt.title'), array('menutype_id', 'menutype_title')))
			->join('LEFT', $db->quoteName('#__menu_types', 'mt') . ' ON ' . $db->qn('mt.menutype') . ' = ' . $db->qn('a.menutype'));

		// Join over the associations.
		$assoc = JLanguageAssociations::isEnabled();

		if ($assoc)
		{
			$query->select('COUNT(asso2.id)>1 as association')
				->join('LEFT', '#__associations AS asso ON asso.id = a.id AND asso.context=' . $db->quote('com_menus.item'))
				->join('LEFT', '#__associations AS asso2 ON asso2.key = asso.key')
				->group(
					$db->quoteName(
						array(
							'a.id',
							'a.menutype',
							'a.title',
							'a.alias',
							'a.note',
							'a.path',
							'a.link',
							'a.type',
							'a.parent_id',
							'a.level',
							'a.published',
							'a.component_id',
							'a.checked_out',
							'a.checked_out_time',
							'a.browserNav',
							'a.access',
							'a.img',
							'a.template_style_id',
							'a.params',
							'a.lft',
							'a.rgt',
							'a.home',
							'a.language',
							'a.client_id',
							'l.title',
							'l.image',
							'l.sef',
							'u.name',
							'c.element',
							'ag.title',
							'e.enabled',
							'e.name',
							'mt.id',
							'mt.title',
						)
					)
				);
		}

		// Join over the extensions
		$query->select('e.name AS name')
			->join('LEFT', '#__extensions AS e ON e.extension_id = a.component_id');

		// Exclude the root category.
		$query->where('a.id > 1')
			->where('a.client_id = 0');

		// Filter on the published state.
		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.published IN (0, 1))');
		}

		// Filter by search in title, alias or id
		if ($search = trim($this->getState('filter.search')))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			elseif (stripos($search, 'link:') === 0)
			{
				if ($search = substr($search, 5))
				{
					$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
					$query->where('a.link LIKE ' . $search);
				}
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(' . 'a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ' OR a.note LIKE ' . $search . ')');
			}
		}

		// Filter the items over the parent id if set.
		$parentId = $this->getState('filter.parent_id');

		if (!empty($parentId))
		{
			$query->where('a.parent_id = ' . (int) $parentId);
		}

		// Filter the items over the menu id if set.
		$menuType = $this->getState('filter.menutype');

		// "" means all
		if ($menuType == '')
		{
			// Load all menu types we have manage access
			$query2 = $this->getDbo()->getQuery(true)
				->select($this->getDbo()->qn(array('id', 'menutype')))
				->from('#__menu_types')
				->order('title');

			$menuTypes = $this->getDbo()->setQuery($query2)->loadObjectList();

			$types = array();

			foreach ($menuTypes as $type)
			{
				if ($user->authorise('core.manage', 'com_menus.menu.' . (int) $type->id))
				{
					$types[] = $query->q($type->menutype);
				}
			}

			if (!empty($types))
			{
				$query->where('a.menutype IN(' . implode(',', $types) . ')');
			}
		}
		// Default behavior => load all items from a specific menu
		elseif (strlen($menuType))
		{
			$query->where('a.menutype = ' . $db->quote($menuType));
		}
		// Empty menu type => error
		else
		{
			$query->where('1 != 1');
		}

		// Filter on the access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where('a.access = ' . (int) $access);
		}

		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
			$groups = $user->getAuthorisedViewLevels();

			if (!empty($groups))
			{
				$query->where('a.access IN (' . implode(',', $groups) . ')');
			}
		}

		// Filter on the level.
		if ($level = $this->getState('filter.level'))
		{
			$query->where('a.level <= ' . (int) $level);
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where('a.language = ' . $db->quote($language));
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.lft')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}
}
