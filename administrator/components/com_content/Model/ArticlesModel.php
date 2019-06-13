<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Workflow\Workflow;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

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

			if (Associations::isEnabled())
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
		$app = Factory::getApplication();

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

		$featured = $this->getUserStateFromRequest($this->context . '.filter.featured', 'filter_featured', '');
		$this->setState('filter.featured', $featured);

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
	 * @return  \Joomla\Database\DatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$user  = Factory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'DISTINCT a.id, a.title, a.alias, a.checked_out, a.checked_out_time, a.catid' .
				', a.state, a.access, a.created, a.created_by, a.created_by_alias, a.modified, a.ordering, a.featured, a.language, a.hits' .
				', a.publish_up, a.publish_down, a.introtext, a.note'
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
		$query->select('c.title AS category_title, c.created_user_id AS category_uid, c.level AS category_level')
			->join('LEFT', '#__categories AS c ON c.id = a.catid');

		// Join over the parent categories.
		$query->select('parent.title AS parent_category_title, parent.id AS parent_category_id,
								parent.created_user_id AS parent_category_uid, parent.level AS parent_category_level')
			->join('LEFT', '#__categories AS parent ON parent.id = c.parent_id');

		// Join over the users for the author.
		$query->select('ua.name AS author_name')
			->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

		// Join over the associations.
		$query->select($query->quoteName('wa.stage_id', 'stage_id'))
			->innerJoin(
				$query->quoteName('#__workflow_associations', 'wa') 
				. ' ON ' . $query->quoteName('wa.item_id') . ' = ' . $query->quoteName('a.id')
			);

		// Join over the workflow stages.
		$query->select(
			$query->quoteName(
				[
					'ws.title',
					'ws.condition',
					'ws.workflow_id'
				],
				[
					'stage_title',
					'stage_condition',
					'workflow_id'
				]
			)
		)
		->innerJoin(
			$query->quoteName('#__workflow_stages', 'ws')
			. ' ON ' . $query->quoteName('ws.id') . ' = ' . $query->quoteName('wa.stage_id')
		);

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
			'c.created_user_id',
			'c.level',
			'ua.name',
			'ws.title',
			'ws.workflow_id',
			'ws.condition',
			'wa.stage_id',
			'parent.id',
		);

		if (PluginHelper::isEnabled('content', 'vote'))
		{
			$query->select('COALESCE(NULLIF(ROUND(v.rating_sum  / v.rating_count, 0), 0), 0) AS rating,
					COALESCE(NULLIF(v.rating_count, 0), 0) as rating_count')
				->join('LEFT', '#__content_rating AS v ON a.id = v.content_id');

			array_push($associationsGroupBy, 'v.rating_sum', 'v.rating_count');
		}

		// Join over the associations.
		if (Associations::isEnabled())
		{
			$query->select('CASE WHEN COUNT(asso2.id)>1 THEN 1 ELSE 0 END as association')
				->join('LEFT', '#__associations AS asso ON asso.id = a.id AND asso.context=' . $db->quote('com_content.item'))
				->join('LEFT', $db->quoteName('#__associations', 'asso2'), $db->quoteName('asso2.key') . ' = ' . $db->quoteName('asso.key'))
				->group($db->quoteName($associationsGroupBy));
		}

		// Filter by access level.
		$access = $this->getState('filter.access');

		if (is_numeric($access))
		{
			$access = (int) $access;
			$query->where($db->quoteName('a.access') . ' = :access')
				->bind(':access', $access, ParameterType::INTEGER);
		}
		elseif (is_array($access))
		{
			$access = ArrayHelper::toInteger($access);
			$query->whereIn($db->quoteName('a.access'), $access);
		}

		// Filter by featured.
		$featured = (string) $this->getState('filter.featured');

		if (in_array($featured, ['0','1']))
		{
			$featured = (int) $featured;
			$query->where($db->quoteName('a.featured') . ' = :featured')
				->bind(':featured', $featured, ParameterType::INTEGER);
		}

		// Filter by access level on categories.
		if (!$user->authorise('core.admin'))
		{
			$groups = $user->getAuthorisedViewLevels();
			$query->whereIn($db->quoteName('a.access'), $groups);
			$query->whereIn($db->quoteName('c.access'), $groups);
		}

		// Filter by published state
		$workflowStage = (string) $this->getState('filter.stage');

		if (is_numeric($workflowStage))
		{
			$workflowStage = (int) $workflowStage;
			$query->where($db->quoteName('wa.stage_id') . ' = :workflowstage')
				->bind(':workflowstage', $workflowStage, ParameterType::INTEGER);
		}

		$condition = (string) $this->getState('filter.condition');

		if ($condition !== '*')
		{
			if (is_numeric($condition))
			{
				$condition = (int) $condition;
				$query->where($db->quoteName('ws.condition') . ' = :condition')
					->bind(':condition', $condition, ParameterType::INTEGER);
			}
			elseif (!is_numeric($workflowStage))
			{
				$query->whereIn(
					$db->quoteName('ws.condition'),
					[
						ContentComponent::CONDITION_PUBLISHED,
						ContentComponent::CONDITION_UNPUBLISHED
					]
				);
			}
		}

		$query->where($db->quoteName('wa.extension') . '=' . $db->quote('com_content'));

		// Filter by categories and by level
		$categoryId = $this->getState('filter.category_id', array());
		$level = $this->getState('filter.level');

		if (!is_array($categoryId))
		{
			$categoryId = $categoryId ? array($categoryId) : array();
		}

		// Case: Using both categories filter and by level filter
		if (count($categoryId))
		{
			$categoryId = ArrayHelper::toInteger($categoryId);
			$categoryTable = Table::getInstance('Category', 'JTable');
			$subCatItemsWhere = array();

			foreach ($categoryId as $filter_catid)
			{
				$categoryTable->load($filter_catid);
				$subCatItemsWhere[] = '(' .
					($level ? 'c.level <= ' . ((int) $level + (int) $categoryTable->level - 1) . ' AND ' : '') .
					'c.lft >= ' . (int) $categoryTable->lft . ' AND ' .
					'c.rgt <= ' . (int) $categoryTable->rgt . ')';
			}

			$query->where('(' . implode(' OR ', $subCatItemsWhere) . ')');
		}

		// Case: Using only the by level filter
		elseif ($level)
		{
			$level = (int) $level;
			$query->where($db->quoteName('c.level') . ' <= :level')
				->bind(':level', $level, ParameterType::INTEGER);
		}

		// Filter by author
		$authorId = $this->getState('filter.author_id');

		if (is_numeric($authorId))
		{
			$authorId = (int) $authorId;
			$type = $this->getState('filter.author_id.include', true) ? '= ' : '<>';
			$query->where($db->quoteName('a.created_by') . $type . ' :createdby')
				->bind(':createdby', $authorId, ParameterType::INTEGER);
		}
		elseif (is_array($authorId))
		{
			$authorId = ArrayHelper::toInteger($authorId);
			$query->whereIn($db->quoteName('a.created'), $authorId);
		}

		// Filter by search in title.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$ids = (int) substr($search, 3);
				$query->where($db->quoteName('a.id') . ' = :id');
				$query->bind(':id', $ids, ParameterType::INTEGER);
			}
			elseif (stripos($search, 'author:') === 0)
			{
				$search = '%' . substr($search, 7) . '%';
				$query->where($db->quoteName('ua.name') . ' LIKE :name')
					->orWhere($db->quoteName('ua.username') . ' LIKE :uname')
					->bind(':name', $search)
					->bind(':uname', $search);
			}
			elseif (stripos($search, 'content:') === 0)
			{
				$search = '%' . substr($search, 8) . '%';
				$query->where($db->quoteName('a.introtext') . ' LIKE :intro')
					->orWhere($db->quoteName('a.fulltext') . ' LIKE :full')
					->bind(':intro', $search)
					->bind(':full', $search);
			}
			else
			{
				$search = '%' . trim($search) . '%';
				$query->where($db->quoteName('a.title') . ' LIKE :title')
					->orWhere($db->quoteName('a.alias') . ' LIKE :alias')
					->orWhere($db->quoteName('a.note') . ' LIKE :note')
					->bind(':title', $search)
					->bind(':alias', $search)
					->bind(':note', $search);	
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where($db->quoteName('a.language') . ' = :language')
				->bind(':language', $language);
		}

		// Filter by a single or group of tags.
		$hasTag = false;
		$tagId  = $this->getState('filter.tag');

		if (is_numeric($tagId))
		{
			$hasTag = true;
			$tagId = (int) $tagId;

			$query->where($db->quoteName('tagmap.tag_id') . ' = :tagid')
				->bind(':tagid', $tagId, ParameterType::INTEGER);
		}
		elseif (is_array($tagId))
		{
			$tagId = ArrayHelper::toInteger($tagId);

			if (!empty($tagId))
			{
				$hasTag = true;

				$query->whereIn($db->quoteName('tagmap.tag_id'), $tagId);
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
	 * @return  array|boolean
	 *
	 * @since   4.0.0
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

		if ($items === false)
		{
			return false;
		}

		$ids = ArrayHelper::getColumn($items, 'stage_id');
		$ids = ArrayHelper::toInteger($ids);
		$ids = array_unique(array_filter($ids));

		$ids[] = -1;

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
						't.from_stage_id',
						't.to_stage_id',
						's.id',
						's.title',
						's.condition',
						's.workflow_id'
					),
					array(
						'value',
						'text',
						'from_stage_id',
						'to_stage_id',
						'stage_id',
						'stage_title',
						'stage_condition',
						'workflow_id'
					)
				);

				$query->select($select)
					->from($db->quoteName('#__workflow_transitions', 't'))
					->leftJoin(
						$db->quoteName('#__workflow_stages', 's') . ' ON '
						. $db->quoteName('t.from_stage_id') . ' IN (' . implode(',', $ids) . ')'
					)
					->where($db->quoteName('t.to_stage_id') . ' = ' . $db->quoteName('s.id'))
					->where($db->quoteName('t.published') . ' = 1')
					->where($db->quoteName('s.published') . ' = 1')
					->order($db->quoteName('t.ordering'));

				$transitions = $db->setQuery($query)->loadAssocList();

				$workflow = new Workflow(['extension' => 'com_content']);

				foreach ($transitions as $key => $transition)
				{
					if (!$user->authorise('core.execute.transition', 'com_content.transition.' . (int) $transition['value']))
					{
						unset($transitions[$key]);
					}
					else
					{
						// Update the transition text with final state value
						$conditionName = $workflow->getConditionName($transition['stage_condition']);

						$transitions[$key]['text'] .= ' [' . Text::_($conditionName) . ']';
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
	 * Method to get a list of articles.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   4.0.0
	 */
	public function getItems()
	{
		$items = parent::getItems();

		$asset = new \Joomla\CMS\Table\Asset($this->getDbo());

		foreach (array_keys($items) as $x)
		{
			$items[$x]->typeAlias = 'com_content.article';

			$asset->loadByName('com_content.article.' . $items[$x]->id);

			// Re-inject the asset id.
			$items[$x]->asset_id = $asset->id;
		}

		return $items;
	}
}
