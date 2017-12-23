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

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\Component\Workflow\Administrator\Helper\WorkflowHelper;

/**
 * Methods supporting a list of article records.
 *
 * @since  1.6
 */
class ArticlesModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.6
	 * @see     \Joomla\CMS\MVC\Controller\BaseController
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

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$condition = $this->getUserStateFromRequest($this->context . '.filter.condition', 'filter_condition', '');
		$this->setState('filter.condition', $condition);

		$level = $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level');
		$this->setState('filter.level', $level);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$formSubmited = $app->input->post->get('form_submited');

		$access     = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
		$authorId   = $this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');
		$tag        = $this->getUserStateFromRequest($this->context . '.filter.tag', 'filter_tag', '');

		if ($formSubmited)
		{
			$access = $app->input->post->get('access');
			$this->setState('filter.access', $access);

			$authorId = $app->input->post->get('author_id');
			$this->setState('filter.author_id', $authorId);

			$categoryId = $app->input->post->get('category_id');
			$this->setState('filter.category_id', $categoryId);

			$tag = $app->input->post->get('tag');
			$this->setState('filter.tag', $tag);
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
		$id .= ':' . serialize($this->getState('filter.access'));
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . serialize($this->getState('filter.category_id'));
		$id .= ':' . serialize($this->getState('filter.author_id'));
		$id .= ':' . $this->getState('filter.language');
		$id .= ':' . serialize($this->getState('filter.tag'));

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
				'DISTINCT a.id, a.title, a.alias, a.checked_out, a.checked_out_time, a.catid' .
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
		$query->select('wa.state_id AS state_id')
			->join('LEFT', '#__workflow_associations AS wa ON wa.item_id = a.id');

		// Join over the states.
		$query->select('ws.title AS state_title, ws.condition AS state_condition')
			->join('LEFT', '#__workflow_states AS ws ON ws.id = wa.state_id');

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
		$access = $this->getState('filter.access');
		if (is_numeric($access))
		{
			$query->where('a.access = ' . (int) $access);
		}
		elseif (is_array($access))
		{
			$access = ArrayHelper::toInteger($access);
			$access = implode(',', $access);
			$query->where('a.access IN (' . $access . ')');
		}

		// Filter by access level on categories.
		if (!$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
			$query->where('c.access IN (' . $groups . ')');
		}

		// Filter by published state
		$workflowState = (string) $this->getState('filter.state');

		if (is_numeric($workflowState))
		{
			$query->where('wa.state_id = ' . (int) $workflowState);
		}

		$condition = (string) $this->getState('filter.condition');

		if (is_numeric($condition))
		{
			$query->where($db->qn('ws.condition') . '=' . $db->quote($condition));
		}
		elseif (!is_numeric($workflowState))
		{
			$query->where($db->qn('ws.condition') . ' IN ("0","1")');
		}

		$query->where($db->qn('wa.extension') . '=' . $db->quote('com_content'));

		// Filter by categories and by level
		$categoryId = $this->getState('filter.category_id');
		$level = $this->getState('filter.level');

		$categoryId = $categoryId && !is_array($categoryId)
			? array($categoryId)
			: $categoryId;

		// Case: Using both categories filter and by level filter
		if (count($categoryId))
		{
			$categoryId = ArrayHelper::toInteger($categoryId);
			$categoryTable = \JTable::getInstance('Category', 'JTable');
			$subCatItemsWhere = array();

			foreach ($categoryId as $filter_catid)
			{
				$categoryTable->load($filter_catid);
				$subCatItemsWhere[] = '(' .
					($level ? 'c.level <= ' . ((int) $level + (int) $categoryTable->level - 1) . ' AND ' : '') .
					'c.lft >= ' . (int) $categoryTable->lft . ' AND ' .
					'c.rgt <= ' . (int) $categoryTable->rgt . ')';
			}

			$query->where(implode(' OR ', $subCatItemsWhere));
		}

		// Case: Using only the by level filter
		elseif ($level)
		{
			$query->where('c.level <= ' . (int) $level);
		}

		// Filter by author
		$authorId = $this->getState('filter.author_id');

		if (is_numeric($authorId))
		{
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
			$query->where('a.created_by ' . $type . (int) $authorId);
		}
		elseif (is_array($authorId))
		{
			$authorId = ArrayHelper::toInteger($authorId);
			$authorId = implode(',', $authorId);
			$query->where('a.created_by IN (' . $authorId . ')');
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

		// Filter by a single or group of tags.
		$hasTag = false;
		$tagId  = $this->getState('filter.tag');

		if (is_numeric($tagId))
		{
			$hasTag = true;

			$query->where($db->quoteName('tagmap.tag_id') . ' = ' . (int) $tagId);
		}
		elseif (is_array($tagId))
		{
			$tagId = ArrayHelper::toInteger($tagId);
			$tagId = implode(',', $tagId);
			if (!empty($tagId))
			{
				$hasTag = true;

				$query->where($db->quoteName('tagmap.tag_id') . ' IN (' . $tagId . ')');
			}
		}

		if ($hasTag)
		{
			$query->join('LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
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
	 * @since   __DEPLOY_VERSION__
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

		$ids = ArrayHelper::getColumn($items, 'state_id');
		$ids = ArrayHelper::toInteger($ids);
		$ids = array_unique(array_filter($ids));

		$this->cache[$store] = array();

		try
		{
			if (count($ids))
			{
				Factory::getLanguage()->load('com_workflow', JPATH_ADMINISTRATOR);

				$query = $db->getQuery(true);

				$select = $db->quoteName(
					array(
						't.id',
						't.title',
						't.from_state_id',
						's.id',
						's.title',
						's.condition'
					),
					array(
						'value',
						'text',
						'from_state_id',
						'state_id',
						'state_title',
						'state_condition'
					)
				);

				$query->select($select)
					->from($db->quoteName('#__workflow_transitions', 't'))
					->leftJoin($db->quoteName('#__workflow_states', 's') . ' ON ' . $db->qn('t.from_state_id') . ' IN(' . implode(',', $ids) . ')')
					->where($db->quoteName('t.to_state_id') . ' = ' . $db->quoteName('s.id'))
					->where($db->quoteName('t.published') . ' = 1')
					->where($db->quoteName('s.published') . ' = 1')
					->order($db->qn('t.ordering'));

				$transitions = $db->setQuery($query)->loadAssocList();

				foreach ($transitions as $key => $transition)
				{
					if (!$user->authorise('core.execute.transition', 'com_content.transition.' . (int) $transition['value']))
					{
						unset($transitions[$key]);
					}
					else
					{
						// Update the transition text with final state value
						$conditionName = WorkflowHelper::getConditionName($transitions[$key]['state_condition']);
						$transitions[$key]['text'] .=  ' [' . \JText::_($conditionName) . ']';
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
	 * @return  \stdClass[]
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
}
