<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Model\ListModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;

/**
 * Methods supporting a list of article records.
 *
 * @since  1.6
 */
class Articles extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 * @see     \Joomla\CMS\Controller\Controller
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'catid', 'a.catid', 'category_title',
				'state', 'a.state',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'modified', 'a.modified',
				'created_by', 'a.created_by',
				'created_by_alias', 'a.created_by_alias',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'hits', 'a.hits',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'published', 'a.published',
				'author_id',
				'category_id',
				'level',
				'tag',
				'rating_count', 'rating',
			);

			if (\JLanguageAssociations::isEnabled())
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
	protected function populateState($ordering = 'a.id', $direction = 'desc')
	{
		$app = \JFactory::getApplication();

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

		// List state information.
		parent::populateState($ordering, $direction);

		// Force a language
		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
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
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.category_id');
		$id .= ':' . $this->getState('filter.author_id');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  \JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$user  = \JFactory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.alias, a.checked_out, a.checked_out_time, a.catid' .
					', a.state, a.access, a.created, a.created_by, a.created_by_alias, a.modified, a.ordering, a.featured, a.language, a.hits' .
					', a.publish_up, a.publish_down'
			)
		);
		$query->from('#__content AS a');

		// Join over the language
		$query->select('l.title AS language_title, l.image AS language_image')
			->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
			->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level')
			->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the categories.
		$query->select('c.title AS category_title')
			->join('LEFT', '#__categories AS c ON c.id = a.catid');

		// Join over the users for the author.
		$query->select('ua.name AS author_name')
			->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

		// Join over the states.
		$query->select('ws.title AS state_title, ws.id AS state, ws.condition AS status')
			->join('LEFT', '#__workflow_states AS ws ON a.state = ws.id');

		// Join on voting table
		$associationsGroupBy = array(
			'a.id',
			'a.title',
			'a.alias',
			'a.checked_out',
			'a.checked_out_time',
			'a.state',
			'a.access',
			'a.created',
			'a.created_by',
			'a.created_by_alias',
			'a.modified',
			'a.ordering',
			'a.featured',
			'a.language',
			'a.hits',
			'a.publish_up',
			'a.publish_down',
			'a.catid',
			'l.title',
			'l.image',
			'uc.name',
			'ag.title',
			'c.title',
			'ua.name',
		);

		if (\JPluginHelper::isEnabled('content', 'vote'))
		{
			$query->select('COALESCE(NULLIF(ROUND(v.rating_sum  / v.rating_count, 0), 0), 0) AS rating,
					COALESCE(NULLIF(v.rating_count, 0), 0) as rating_count')
				->join('LEFT', '#__content_rating AS v ON a.id = v.content_id');

			array_push($associationsGroupBy, 'v.rating_sum', 'v.rating_count');
		}

		// Join over the associations.
		if (\JLanguageAssociations::isEnabled())
		{
			$query->select('COUNT(asso2.id)>1 as association')
				->join('LEFT', '#__associations AS asso ON asso.id = a.id AND asso.context=' . $db->quote('com_content.item'))
				->join('LEFT', '#__associations AS asso2 ON asso2.key = asso.key')
				->group($db->quoteName($associationsGroupBy));
		}

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where('a.access = ' . (int) $access);
		}

		// Filter by access level on categories.
		if (!$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
			$query->where('c.access IN (' . $groups . ')');
		}

		// Filter by published state
		$published = (string) $this->getState('filter.state');

		if (is_numeric($published) && (int) $published > 0)
		{
			$query->where('a.state = ' . (int) $published);
		}

		// Filter by a single or group of categories.
		$baselevel  = 1;
		$categoryId = $this->getState('filter.category_id');

		if (is_numeric($categoryId))
		{
			$categoryTable = \JTable::getInstance('Category', '\JTable');
			$categoryTable->load($categoryId);
			$rgt       = $categoryTable->rgt;
			$lft       = $categoryTable->lft;
			$baselevel = (int) $categoryTable->level;
			$query->where('c.lft >= ' . (int) $lft)
				->where('c.rgt <= ' . (int) $rgt);
		}
		elseif (is_array($categoryId))
		{
			$query->where('a.catid IN (' . implode(',', ArrayHelper::toInteger($categoryId)) . ')');
		}

		// Filter on the level.
		if ($level = $this->getState('filter.level'))
		{
			$query->where('c.level <= ' . ((int) $level + (int) $baselevel - 1));
		}

		// Filter by author
		$authorId = $this->getState('filter.author_id');

		if (is_numeric($authorId))
		{
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
			$query->where('a.created_by ' . $type . (int) $authorId);
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0)
			{
				$search = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
				$query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(a.title LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where('a.language = ' . $db->quote($language));
		}

		// Filter by a single tag.
		$tagId = $this->getState('filter.tag');

		if (is_numeric($tagId))
		{
			$query->where($db->quoteName('tagmap.tag_id') . ' = ' . (int) $tagId)
				->join(
					'LEFT',
					$db->quoteName('#__contentitem_tag_map', 'tagmap')
					. ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
					. ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_content.article')
				);
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'DESC');

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}

	/**
	 * Method to get all transitions at once for all articles
	 *
	 * @return  array
	 *
	 * @since   4.0
	 */
	public function getTransitions()
	{
		// Get a storage key.
		$store = $this->getStoreId('getTransitions');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$db   = $this->getDbo();
		$user = Factory::getUser();

		$items = $this->getItems();

		$ids = ArrayHelper::getColumn($items, 'state');
		$ids = ArrayHelper::toInteger($ids);
		$ids = array_unique(array_filter($ids));

		$this->cache[$store] = array();

		try
		{
			if (count($ids))
			{
				$query = $db->getQuery(true);

				$select = $db->quoteName(
					array(
						't.id',
						't.title',
						't.from_state_id',
						's.id',
						's.title'
					),
					array(
						'value',
						'text',
						'from_state_id',
						'state_id',
						'state_title'
					)
				);

				$query->select($select)
					->from($db->quoteName('#__workflow_transitions', 't'))
					->from($db->quoteName('#__workflow_states', 's'))
					->where($db->quoteName('t.from_state_id') . ' IN(' . implode(',', $ids) . ')')
					->where($db->quoteName('t.to_state_id') . ' = ' . $db->quoteName('s.id'))
					->where($db->quoteName('t.published') . ' = 1')
					->where($db->quoteName('s.published') . ' = 1');

				$transitions = $db->setQuery($query)->loadAssocList();

				foreach ($transitions as $key => $transition)
				{
					if (!$user->authorise('transition.run', 'com_content.transition.' . (int) $transition['value']))
					{
						unset($transitions[$key]);
					}
				}

				$this->cache[$store] = $transitions;
			}
		}
		catch (\RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return $this->cache[$store];
	}

	/**
	 * Build a list of authors
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.6
	 */
	public function getAuthors()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Construct the query
		$query->select('u.id AS value, u.name AS text')
			->from('#__users AS u')
			->join('INNER', '#__content AS c ON c.created_by = u.id')
			->group('u.id, u.name')
			->order('u.name');

		// Setup the query
		$db->setQuery($query);

		// Return the result
		return $db->loadObjectList();
	}

	/**
	 * Method to get a list of articles.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if (\JFactory::getApplication()->isClient('site'))
		{
			$groups = \JFactory::getUser()->getAuthorisedViewLevels();

			foreach (array_keys($items) as $x)
			{
				// Check the access level. Remove articles the user shouldn't see
				if (!in_array($items[$x]->access, $groups))
				{
					unset($items[$x]);
				}
			}
		}

		return $items;
	}

	/**
	 * Get the filter form
	 *
	 * @param   array    $data      data
	 * @param   boolean  $loadData  load current data
	 *
	 * @return  \JForm|boolean  The \JForm object or false on error
	 *
	 * @since   4.0
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$query
			->select($db->qn("state"))
			->from($db->qn("#__content"));
		$db->setQuery($query);
		$items = $db->loadAssocList();
		$query->clear();

		$form = parent::getFilterForm($data, $loadData);

		if (!empty($items))
		{
			$ids = ArrayHelper::getColumn($items, 'state');
			$ids = ArrayHelper::toInteger($ids);
			$ids = array_unique(array_filter($ids));


			if ($form && !empty($ids))
			{
				$select = $db->quoteName(
					array(
						'id',
						'title'
					),
					array(
						'value',
						'state'
					)
				);

				$query
					->select($select)
					->from($db->qn('#__workflow_states'))
					->where($db->qn('id') . ' IN (' . implode(',', $ids) . ')');
				$form->setFieldAttribute('state', 'query', (string) $query, 'filter');

				return $form;
			}
		}

		return $form;
	}
}
