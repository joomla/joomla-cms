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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of featured article records.
 *
 * @since  1.6
 */
class FeaturedModel extends ArticlesModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     \Joomla\CMS\MVC\Controller\BaseController
	 * @since   1.6
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
				'created_by', 'a.created_by',
				'created_by_alias', 'a.created_by_alias',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'hits', 'a.hits',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'fp.ordering',
				'published', 'a.published',
				'author_id',
				'category_id',
				'level',
				'tag',
				'rating_count', 'rating',
			);
		}

		parent::__construct($config);
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
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = Factory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.alias, a.checked_out, a.checked_out_time, a.catid, a.state, a.access, a.created, a.hits,' .
					'a.created_by, a.featured, a.language, a.created_by_alias, a.publish_up, a.publish_down, a.note'
			)
		);
		$query->from('#__content AS a');

		// Join over the language
		$query->select('l.title AS language_title, l.image AS language_image')
			->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

		// Join over the content table.
		$query->select('fp.ordering')
			->join('INNER', '#__content_frontpage AS fp ON fp.content_id = a.id');

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

		// Join over the workflow asociations.
		$query->select('wa.stage_id AS stage_id')
			->join('LEFT', '#__workflow_associations AS wa ON wa.item_id = a.id');

		// Join over the workflow stages.
		$query	->select(
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
				->join('INNER', '#__workflow_stages AS ws ON ' . $query->quoteName('ws.id') . ' = ' . $query->quoteName('wa.stage_id'));

		// Join on voting table
		if (PluginHelper::isEnabled('content', 'vote'))
		{
			$query->select('COALESCE(NULLIF(ROUND(v.rating_sum  / v.rating_count, 0), 0), 0) AS rating,
							COALESCE(NULLIF(v.rating_count, 0), 0) as rating_count')
				->join('LEFT', '#__content_rating AS v ON a.id = v.content_id');
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

		// Filter by access level on categories.
		if (!$user->authorise('core.admin'))
		{
			$groups = $user->getAuthorisedViewLevels();
			$query->whereIn($db->quoteName('a.access'), $groups);
			$query->whereIn($db->quoteName('c.access'), $groups);
		}

		// Filter by workflows stages
		$workflowStage = (string) $this->getState('filter.stage');

		if (is_numeric($workflowStage))
		{
			$workflowStage = (int) $workflowStage;
			$query->where($db->quoteName('wa.stage_id') . ' = :workflowstage')
				->bind('workflowstage', $workflowStage, ParameterType::INTEGER);
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

		// Filter by a single or group of categories.
		$baselevel = 1;
		$categoryId = $this->getState('filter.category_id');

		if (is_array($categoryId) && count($categoryId) === 1)
		{
			$cat_tbl = Table::getInstance('Category', 'JTable');
			$cat_tbl->load($categoryId[0]);
			$rgt = $cat_tbl->rgt;
			$lft = $cat_tbl->lft;
			$baselevel = (int) $cat_tbl->level;
			$query->where('c.lft >= ' . (int) $lft)
				->where('c.rgt <= ' . (int) $rgt);
		}
		elseif (is_array($categoryId))
		{
			$categoryId = ArrayHelper::toInteger($categoryId);
			$query->whereIn($db->quoteName('a.catid'), $categoryId);
		}

		// Filter on the level.
		if ($level = $this->getState('filter.level'))
		{
			$level = ((int) $level + (int) $baselevel - 1);
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
			$query->where($db->quoteName('a.created_by'), $authorId);
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
		$tagId = $this->getState('filter.tag');

		if (is_array($tagId) && count($tagId) === 1)
		{
			$tagId = current($tagId);
		}

		if (is_array($tagId))
		{
			$tagId = ArrayHelper::toInteger($tagId);

			if ($tagId)
			{
				$subQuery = $db->getQuery(true)
					->select('DISTINCT ' . $db->quoteName('content_item_id'))
					->from($db->quoteName('#__contentitem_tag_map'))
					->whereIn($db->quoteName('tag_id'), $tagId)
					->where($db->quoteName('type_alias') . ' = ' . $db->quote('com_content.article'));

				$query->join('INNER', '(' . (string) $subQuery . ') AS tagmap ON tagmap.content_item_id = a.id');
			}
		}
		elseif ($tagId)
		{
			$query->join(
				'INNER',
				$db->quoteName('#__contentitem_tag_map', 'tagmap')
				. ' ON tagmap.tag_id = ' . (int) $tagId
				. ' AND tagmap.content_item_id = a.id'
				. ' AND tagmap.type_alias = ' . $db->quote('com_content.article')
			);
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.title');
		$orderDirn = $this->state->get('list.direction', 'ASC');

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

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
	 * @since   3.5
	 */
	protected function populateState($ordering = 'a.title', $direction = 'asc')
	{
		parent::populateState($ordering, $direction);
	}
}
