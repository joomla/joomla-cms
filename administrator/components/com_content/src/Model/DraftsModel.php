<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of article records.
 *
 * @since  1.6
 */
class DraftsModel extends ListModel
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
				'featured_up', 'fp.featured_up',
				'featured_down', 'fp.featured_down',
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
				'stage', 'wa.stage_id',
				'ws.title'
			);

			if (Associations::isEnabled())
			{
				$config['filter_fields'][] = 'association';
			}
		}

		parent::__construct($config);
	}

	/**
	 * Get the filter form
	 *
	 * @param   array    $data      data
	 * @param   boolean  $loadData  load current data
	 *
	 * @return  Form|null  The \JForm object or null if the form can't be found
	 *
	 * @since   3.2
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		$form = parent::getFilterForm($data, $loadData);

		$params = ComponentHelper::getParams('com_content');

		if (!$params->get('workflow_enabled'))
		{
			$form->removeField('stage', 'filter');
		}
		else
		{
			$ordering = $form->getField('fullordering', 'list');

			$ordering->addOption('JSTAGE_ASC', ['value' => 'ws.title ASC']);
			$ordering->addOption('JSTAGE_DESC', ['value' => 'ws.title DESC']);
		}

		return $form;
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

		$level = $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level');
		$this->setState('filter.level', $level);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		$formSubmited = $app->input->post->get('form_submited');

		// Gets the value of a user state variable and sets it in the session
		$this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
		$this->getUserStateFromRequest($this->context . '.filter.author_id', 'filter_author_id');
		$this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');
		$this->getUserStateFromRequest($this->context . '.filter.tag', 'filter_tag', '');

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

		// Select the required fields from the table.
		$query->select(
			[
				$db->quoteName('a.id'),
				$db->quoteName('a.article_id'),
				$db->quoteName('a.state'),
				$db->quoteName('a.url'),
				$db->quoteName('a.shared_date'),
				$db->quoteName('b.title'),
				$db->quoteName('b.alias'),
				$db->quoteName('c.title', 'category_title')
			]
		)
			->from($db->quoteName('#__draft', 'a'))
			->join('INNER', $db->quoteName('#__content', 'b'), $db->quoteName('a.article_id') . ' = ' . $db->quoteName('b.id'))
			->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('b.catid'));

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

		$stage_ids = ArrayHelper::getColumn($items, 'stage_id');
		$stage_ids = ArrayHelper::toInteger($stage_ids);
		$stage_ids = array_values(array_unique(array_filter($stage_ids)));

		$workflow_ids = ArrayHelper::getColumn($items, 'workflow_id');
		$workflow_ids = ArrayHelper::toInteger($workflow_ids);
		$workflow_ids = array_values(array_unique(array_filter($workflow_ids)));

		$this->cache[$store] = array();

		try
		{
			if (count($stage_ids) || count($workflow_ids))
			{
				Factory::getLanguage()->load('com_workflow', JPATH_ADMINISTRATOR);

				$query = $db->getQuery(true);

				$query->select(
					[
						$db->quoteName('t.id', 'value'),
						$db->quoteName('t.title', 'text'),
						$db->quoteName('t.from_stage_id'),
						$db->quoteName('t.to_stage_id'),
						$db->quoteName('s.id', 'stage_id'),
						$db->quoteName('s.title', 'stage_title'),
						$db->quoteName('t.workflow_id'),
					]
				)
					->from($db->quoteName('#__workflow_transitions', 't'))
					->innerJoin(
						$db->quoteName('#__workflow_stages', 's'),
						$db->quoteName('t.to_stage_id') . ' = ' . $db->quoteName('s.id')
					)
					->where(
						[
							$db->quoteName('t.published') . ' = 1',
							$db->quoteName('s.published') . ' = 1',
						]
					)
					->order($db->quoteName('t.ordering'));

				$where = [];

				if (count($stage_ids))
				{
					$where[] = $db->quoteName('t.from_stage_id') . ' IN (' . implode(',', $query->bindArray($stage_ids)) . ')';
				}

				if (count($workflow_ids))
				{
					$where[] = '(' . $db->quoteName('t.from_stage_id') . ' = -1 AND ' . $db->quoteName('t.workflow_id') . ' IN (' . implode(',', $query->bindArray($workflow_ids)) . '))';
				}

				$query->where('((' . implode(') OR (', $where) . '))');

				$transitions = $db->setQuery($query)->loadAssocList();

				foreach ($transitions as $key => $transition)
				{
					if (!$user->authorise('core.execute.transition', 'com_content.transition.' . (int) $transition['value']))
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
	 * Method to get a list of articles.
	 * Overridden to add item type alias.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   4.0.0
	 */
	public function getItems()
	{
		$items = parent::getItems();

		foreach ($items as $item)
		{
			$item->typeAlias = 'com_content.article';

			if (isset($item->metadata))
			{
				$registry = new Registry($item->metadata);
				$item->metadata = $registry->toArray();
			}
		}

		return $items;
	}
}
