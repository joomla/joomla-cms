<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Methods supporting a list of article records.
 *
 * @since  3.7.0
 */
class AssociationsModelAssociations extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   3.7.0
	 *
	 * @see     JController
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id',
				'title',
				'ordering',
				'itemtype',
				'language',
				'association',
				'menutype',
				'menutype_title',
				'level',
				'state',
				'category_id',
				'category_title',
				'access',
				'access_level',
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
	 * @since  3.7.0
	 */
	protected function populateState($ordering = 'ordering', $direction = 'asc')
	{
		$app = JFactory::getApplication();

		$forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');
		$forcedItemType = $app->input->get('forcedItemType', '', 'string');

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

		// Adjust the context to support forced component item types.
		if ($forcedItemType)
		{
			$this->context .= '.' . $forcedItemType;
		}

		$this->setState('itemtype', $this->getUserStateFromRequest($this->context . '.itemtype', 'itemtype', '', 'string'));
		$this->setState('language', $this->getUserStateFromRequest($this->context . '.language', 'language', '', 'string'));

		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'cmd'));
		$this->setState('filter.category_id', $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id', '', 'cmd'));
		$this->setState('filter.menutype', $this->getUserStateFromRequest($this->context . '.filter.menutype', 'filter_menutype', '', 'string'));
		$this->setState('filter.access', $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', '', 'string'));
		$this->setState('filter.level', $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level', '', 'cmd'));

		// List state information.
		parent::populateState($ordering, $direction);

		// Force a language.
		if (!empty($forcedLanguage))
		{
			$this->setState('language', $forcedLanguage);
		}

		// Force a component item type.
		if (!empty($forcedItemType))
		{
			$this->setState('itemtype', $forcedItemType);
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
	 * @since   3.7.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('itemtype');
		$id .= ':' . $this->getState('language');
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.category_id');
		$id .= ':' . $this->getState('filter.menutype');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.level');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery|bool
	 *
	 * @since   3.7.0
	 */
	protected function getListQuery()
	{
		$type         = null;

		list($extensionName, $typeName) = explode('.', $this->state->get('itemtype'));

		$extension = AssociationsHelper::getSupportedExtension($extensionName);
		$types     = $extension->get('types');

		if (array_key_exists($typeName, $types))
		{
			$type = $types[$typeName];
		}

		if (is_null($type))
		{
			return false;
		}

		// Create a new query object.
		$user     = JFactory::getUser();
		$db       = $this->getDbo();
		$query    = $db->getQuery(true);

		$details = $type->get('details');

		if (!array_key_exists('support', $details))
		{
			return false;
		}

		$support = $details['support'];

		if (!array_key_exists('fields', $details))
		{
			return false;
		}

		$fields = $details['fields'];

		// Main query.
		$query->select($db->qn($fields['id'], 'id'))
			->select($db->qn($fields['title'], 'title'))
			->select($db->qn($fields['alias'], 'alias'));

		if (!array_key_exists('tables', $details))
		{
			return false;
		}

		$tables = $details['tables'];

		foreach ($tables as $key => $table)
		{
			$query->from($db->qn($table, $key));
		}

		if (!array_key_exists('joins', $details))
		{
			return false;
		}

		$joins = $details['joins'];

		foreach ($joins as $join)
		{
			$query->join($join['type'], $db->qn($join['condition']));
		}

		// Join over the language.
		$query->select($db->qn($fields['language'], 'language'))
			->select($db->qn('l.title', 'language_title'))
			->select($db->qn('l.image', 'language_image'))
			->join('LEFT', $db->qn('#__languages', 'l') . ' ON ' . $db->qn('l.lang_code') . ' = ' . $db->qn($fields['language']));

		// Join over the associations.
		$query->select('COUNT(' . $db->qn('asso2.id') . ') > 1 AS ' . $db->qn('association'))
			->join(
				'LEFT',
				$db->qn('#__associations', 'asso') . ' ON ' . $db->qn('asso.id') . ' = ' . $db->qn($fields['id'])
				. ' AND ' . $db->qn('asso.context') . ' = ' . $db->quote($extensionName . '.' . 'item')
			)
			->join('LEFT', $db->qn('#__associations', 'asso2') . ' ON ' . $db->qn('asso2.key') . ' = ' . $db->qn('asso.key'));

		// Prepare the group by clause.
		$groupby = array(
			$fields['id'],
			$fields['title'],
			$fields['language'],
			'l.title',
			'l.image',
		);

		// Select author for ACL checks.
		if (!empty($fields['created_user_id']))
		{
			$query->select($db->qn($fields['created_user_id'], 'created_user_id'));
		}

		// Select checked out data for check in checkins.
		if (!empty($fields['checked_out']) && !empty($fields['checked_out_time']))
		{
			$query->select($db->qn($fields['checked_out'], 'checked_out'))
				->select($db->qn($fields['checked_out_time'], 'checked_out_time'));

			// Join over the users.
			$query->select($db->qn('u.name', 'editor'))
				->join('LEFT', $db->qn('#__users', 'u') . ' ON ' . $db->qn('u.id') . ' = ' . $db->qn($fields['checked_out']));

			$groupby[] = 'u.name';
		}

		// If component item type supports ordering, select the ordering also.
		if (!empty($fields['ordering']))
		{
			$query->select($db->qn($fields['ordering'], 'ordering'));
		}

		// If component item type supports state, select the item state also.
		if (!empty($fields['state']))
		{
			$query->select($db->qn($fields['state'], 'state'));
		}

		// If component item type supports level, select the level also.
		if (!empty($fields['level']))
		{
			$query->select($db->qn($fields['level'], 'level'));
		}

		// If component item type supports categories, select the category also.
		if (!empty($fields['catid']))
		{
			$query->select($db->qn($fields['catid'], 'catid'));

			// Join over the categories.
			$query->select($db->qn('c.title', 'category_title'))
				->join('LEFT', $db->qn('#__categories', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn($fields['catid']));

			$groupby[] = 'c.title';
		}

		// If component item type supports menu type, select the menu type also.
		if (!empty($fields['menutype']))
		{
			$query->select($db->qn($fields['menutype'], 'menutype'));

			// Join over the menu types.
			$query->select($db->qn('mt.title', 'menutype_title'))
				->select($db->qn('mt.id', 'menutypeid'))
				->join('LEFT', $db->qn('#__menu_types', 'mt') . ' ON ' . $db->qn('mt.menutype') . ' = ' . $db->qn($fields['menutype']));

			$groupby[] = 'mt.title';
			$groupby[] = 'mt.id';
		}

		// If component item type supports access level, select the access level also.
		if (array_key_exists('acl', $support) && $support['acl'] == true && !empty($fields['access']))
		{
			$query->select($db->qn($fields['access'], 'access'));

			// Join over the access levels.
			$query->select($db->qn('ag.title', 'access_level'))
				->join('LEFT', $db->qn('#__viewlevels', 'ag') . ' ON ' . $db->qn('ag.id') . ' = ' . $db->qn($fields['access']));

			$groupby[] = 'ag.title';

			// Implement View Level Access.
			if (!$user->authorise('core.admin', $extensionName))
			{
				$query->where($fields['access'] . ' IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
			}
		}

		// If component item type is menus we need to remove the root item and the administrator menu.
		if ($extensionName === 'com_menus')
		{
			$query->where($db->qn($fields['id']) . ' > 1')
				->where($db->qn('a.client_id') . ' = 0');
		}

		// If component item type is category we need to remove all other component categories.
		if ($typeName === 'category')
		{
			$query->where($db->qn('a.extension') . ' = ' . $db->quote($extensionName));
		}

		// Filter on the language.
		if ($language = $this->getState('language'))
		{
			$query->where($db->qn($fields['language']) . ' = ' . $db->quote($language));
		}

		// Filter by item state.
		$state = $this->getState('filter.state');

		if (is_numeric($state))
		{
			$query->where($db->qn($fields['state']) . ' = ' . (int) $state);
		}
		elseif ($state === '')
		{
			$query->where($db->qn($fields['state']) . ' IN (0, 1)');
		}

		// Filter on the category.
		$baselevel = 1;

		if ($categoryId = $this->getState('filter.category_id'))
		{
			$categoryTable = JTable::getInstance('Category', 'JTable');
			$categoryTable->load($categoryId);
			$baselevel = (int) $categoryTable->level;

			$query->where($db->qn('c.lft') . ' >= ' . (int) $categoryTable->lft)
				->where($db->qn('c.rgt') . ' <= ' . (int) $categoryTable->rgt);
		}

		// Filter on the level.
		if ($level = $this->getState('filter.level'))
		{
			$query->where($db->qn('a.level') . ' <= ' . ((int) $level + (int) $baselevel - 1));
		}

		// Filter by menu type.
		if ($menutype = $this->getState('filter.menutype'))
		{
			$query->where($fields['menutype'] . ' = ' . $db->quote($menutype));
		}

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where($fields['access'] . ' = ' . (int) $access);
		}

		// Filter by search in name.
		if ($search = $this->getState('filter.search'))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->qn($fields['id']) . ' = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(' . $db->qn($fields['title']) . ' LIKE ' . $search
					. ' OR ' . $db->qn($fields['alias']) . ' LIKE ' . $search . ')');
			}
		}

		// Add the group by clause
		$query->group($db->qn($groupby));

		// Add the list ordering clause
		$listOrdering  = $this->state->get('list.ordering', 'id');
		$orderDirn     = $this->state->get('list.direction', 'ASC');

		$query->order($db->escape($listOrdering) . ' ' . $db->escape($orderDirn));

		return $query;
	}

	/**
	 * Delete associations from #__associations table.
	 *
	 * @param   string  $context  The associations context. Empty for all.
	 * @param   string  $key      The associations key. Empty for all.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.7.0
	 */
	public function purge($context = '', $key = '')
	{
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$query = $db->getQuery(true)->delete($db->qn('#__associations'));

		// Filter by associations context.
		if ($context)
		{
			$query->where($db->qn('context') . ' = ' . $db->quote($context));
		}

		// Filter by key.
		if ($key)
		{
			$query->where($db->qn('key') . ' = ' . $db->quote($key));
		}

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			$app->enqueueMessage(JText::_('COM_ASSOCIATIONS_PURGE_FAILED'), 'error');

			return false;
		}

		$app->enqueueMessage(
			JText::_((int) $db->getAffectedRows() > 0 ? 'COM_ASSOCIATIONS_PURGE_SUCCESS' : 'COM_ASSOCIATIONS_PURGE_NONE'),
			'message'
		);

		return true;
	}

	/**
	 * Delete orphans from the #__associations table.
	 *
	 * @param   string  $context  The associations context. Empty for all.
	 * @param   string  $key      The associations key. Empty for all.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.7.0
	 */
	public function clean($context = '', $key = '')
	{
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('key') . ', COUNT(*)')
			->from($db->qn('#__associations'))
			->group($db->qn('key'))
			->having('COUNT(*) = 1');

		// Filter by associations context.
		if ($context)
		{
			$query->where($db->qn('context') . ' = ' . $db->quote($context));
		}

		// Filter by key.
		if ($key)
		{
			$query->where($db->qn('key') . ' = ' . $db->quote($key));
		}

		$db->setQuery($query);

		$assocKeys = $db->loadObjectList();

		$count = 0;

		// We have orphans. Let's delete them.
		foreach ($assocKeys as $value)
		{
			$query->clear()
				->delete($db->qn('#__associations'))
				->where($db->qn('key') . ' = ' . $db->quote($value->key));

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (JDatabaseExceptionExecuting $e)
			{
				$app->enqueueMessage(JText::_('COM_ASSOCIATIONS_DELETE_ORPHANS_FAILED'), 'error');

				return false;
			}

			$count += (int) $db->getAffectedRows();
		}

		$app->enqueueMessage(
			JText::_($count > 0 ? 'COM_ASSOCIATIONS_DELETE_ORPHANS_SUCCESS' : 'COM_ASSOCIATIONS_DELETE_ORPHANS_NONE'),
			'message'
		);

		return true;
	}
}
