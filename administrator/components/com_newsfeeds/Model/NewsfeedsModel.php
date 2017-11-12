<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Newsfeeds\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Methods supporting a list of newsfeed records.
 *
 * @since  1.6
 */
class NewsfeedsModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 *
	 * @see    \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 * @since   3.2
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'name', 'a.name',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'catid', 'a.catid', 'category_id', 'category_title',
				'published', 'a.published',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'ordering', 'a.ordering',
				'language', 'a.language', 'language_title',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'cache_time', 'a.cache_time',
				'numarticles',
				'tag',
				'level', 'c.level',
				'tag',
			);

			$assoc =  Associations::isEnabled();
			if ($assoc)
			{
				$config['filter_fields'][] = 'association';
			}
		}

		parent::__construct($config, $factory);
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

		// Load the parameters.
		$params =  ComponentHelper::getParams('com_newsfeeds');
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
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.category_id');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.language');
		$id .= ':' . $this->getState('filter.level');
		$id .= ':' . serialize($this->getState('filter.tag'));

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  \JDatabaseQuery
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
				'DISTINCT a.id, a.name, a.alias, a.checked_out, a.checked_out_time, a.catid,' .
				' a.numarticles, a.cache_time, a.created_by,' .
				' a.published, a.access, a.ordering, a.language, a.publish_up, a.publish_down'
			)
		);
		$query->from($db->quoteName('#__newsfeeds', 'a'));

		// Join over the language
		$query->select($db->quoteName('l.title', 'language_title'))
			->select($db->quoteName('l.image', 'language_image'))
			->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON ' . $db->qn('l.lang_code') . ' = ' . $db->qn('a.language'));

		// Join over the users for the checked out user.
		$query->select($db->quoteName('uc.name', 'editor'))
			->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->qn('uc.id') . ' = ' . $db->qn('a.checked_out'));

		// Join over the asset groups.
		$query->select($db->quoteName('ag.title', 'access_level'))
			->join('LEFT', $db->quoteName('#__viewlevels', 'ag') . ' ON ' . $db->qn('ag.id') . ' = ' . $db->qn('a.access'));

		// Join over the categories.
		$query->select($db->quoteName('c.title', 'category_title'))
			->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON ' . $db->qn('c.id') . ' = ' . $db->qn('a.catid'));

		// Join over the associations.
		$assoc =  Associations::isEnabled();

		if ($assoc)
		{
			$query->select('COUNT(asso2.id)>1 AS association')
				->join('LEFT', $db->quoteName('#__associations', 'asso') . ' ON asso.id = a.id AND asso.context = ' . $db->quote('com_newsfeeds.item'))
				->join('LEFT', $db->quoteName('#__associations', 'asso2') . ' ON asso2.key = asso.key')
				->group(
					$db->quoteName(
						array(
							'a.id',
							'a.name',
							'a.alias',
							'a.checked_out',
							'a.checked_out_time',
							'a.catid',
							'a.numarticles',
							'a.cache_time',
							'a.created_by',
							'a.published',
							'a.access',
							'a.ordering',
							'a.language',
							'a.publish_up',
							'a.publish_down',
							'l.title',
							'l.image',
							'uc.name',
							'ag.title',
							'c.title',
						)
					)
				);
		}

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			$query->where($db->quoteName('a.access') . ' = ' . (int) $access);
		}

		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
			$query->where($db->quoteName('a.access') . ' IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
		}

		// Filter by published state.
		$published = (string) $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where($db->quoteName('a.published') . ' = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where($db->quoteName('a.published') . ' IN (0, 1)');
		}

		// Filter by category.
		$categoryId = $this->getState('filter.category_id');

		if (is_numeric($categoryId))
		{
			$query->where($db->quoteName('a.catid') . ' = ' . (int) $categoryId);
		}

		// Filter on the level.
		if ($level = $this->getState('filter.level'))
		{
			$query->where($db->quoteName('c.level') . ' <= ' . (int) $level);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->quoteName('a.id') . ' = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(a.name LIKE ' . $search . ' OR a.alias LIKE ' . $search . ')');
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where($db->quoteName('a.language') . ' = ' . $db->quote($language));
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
			ArrayHelper::toInteger($tagId);
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
				. ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_newsfeeds.newsfeed')
			);
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.name');
		$orderDirn = $this->state->get('list.direction', 'ASC');

		if ($orderCol == 'a.ordering' || $orderCol == 'category_title')
		{
			$orderCol = 'c.title ' . $orderDirn . ', a.ordering';
		}

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}
}
