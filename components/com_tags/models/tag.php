<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Tags Component Tag Model
 *
 * @package     Joomla.Site
 * @subpackage  com_tags
 * @since       3.1
 */
class TagsModelTag extends JModelList
{
	/**
	 * The tags that apply.
	 *
	 * @var    object
	 * @since  3.1
	 */
	protected $tag = null;

	/**
	 * The list of items associated with the tags.
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $items = null;

	/**
	 * Constructor.
	 *
	 * @param   array  An optional associative array of configuration settings.
	 * @see     JController
	 * @since   3.1
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'core_content_id', 'c.core_content_id',
				'core_title', 'c.core_title',
				'core_type_alias', 'c.core_type_alias',
				'core_checked_out_user_id', 'c.core_checked_out_user_id',
				'core_checked_out_time', 'c.core_checked_out_time',
				'core_catid', 'c.core_catid',
				'core_state', 'c.core_state',
				'core_access', 'c.core_access',
				'core_created_user_id', 'c.core_created_user_id',
				'core_created_time', 'c.core_created_time',
				'core_modified_time', 'c.core_modified_time',
				'core_ordering', 'c.core_ordering',
				'core_featured', 'c.core_featured',
				'core_language', 'c.core_language',
				'core_hits', 'c.core_hits',
				'core_publish_up', 'c.core_publish_up',
				'core_publish_down', 'c.core_publish_down',
				'core_images', 'c.core_images',
				'core_urls', 'c.core_urls',
				'match_count',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to get a list of items for a list of tags.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 *
	 * @since   3.1
	 */
	public function getItems()
	{
		// Invoke the parent getItems method to get the main list
		$items = parent::getItems();

		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$explodedTypeAlias = explode('.', $item->type_alias);
				$item->link = 'index.php?option=' . $explodedTypeAlias[0] . '&view=' . $explodedTypeAlias[1] . '&id=' . $item->content_item_id . ':' . $item->core_alias;

				// Get display date
				switch ($this->state->params->get('tag_list_show_date'))
				{
					case 'modified':
						$item->displayDate = $item->core_modified_time;
						break;

					case 'created':
						$item->displayDate = $item->core_created_time;
						break;

					default:
					case 'published':
						$item->displayDate = ($item->core_publish_up == 0) ? $item->core_created_time : $item->core_publish_up;
						break;
				}
			}

			return $items;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to build an SQL query to load the list data of all items with a given tag.
	 *
	 * @return  string  An SQL query
	 *
	 * @since   3.1
	 */
	protected function getListQuery()
	{

		$tagId  = $this->getState('tag.id') ? : '';

		$typesr = $this->getState('tag.typesr');
		$orderByOption = $this->getState('list.ordering', 'c.core_title');
		$includeChildren = $this->state->params->get('include_children', 0);
		$orderDir = $this->getState('list.direction', 'ASC');
		$matchAll = $this->getState('params')->get('return_any_or_all', 1);
		$language = $this->getState('tag.language');
		$stateFilter = $this->getState('tag.state');

	// Optionally filter on language
		if (empty($language))
		{
			$language = JComponentHelper::getParams('com_tags')->get('tag_list_language_filter', 'all');
		}

		$tagsHelper = new JHelperTags;
		$query = $tagsHelper->getTagItemsQuery($tagId, $typesr, $includeChildren, $orderByOption, $orderDir, $matchAll, $language, $stateFilter);

		if ($this->state->get('list.filter'))
		{
			$query->where($this->_db->quoteName('c.core_title') . ' LIKE ' . $this->_db->quote('%' . $this->state->get('list.filter') . '%'));
		}

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
	 * @since   3.1
	 */
	protected function populateState($ordering = 'c.core_title', $direction = 'ASC')
	{
		$app = JFactory::getApplication('site');

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		// Load state from the request.
		$ids = $app->input->get('id', array(), 'array');

		JArrayHelper::toInteger($ids);

		$pkString = implode(',', $ids);

		$this->setState('tag.id', $pkString);

		// Get the selected list of types from the request. If none are specified all are used.
		$typesr = $app->input->get('types', array(), 'array');

		if ($typesr)
		{
			// Implode is needed because the array can contain a string with a coma separated list of ids
			$typesr = implode(',', $typesr);

			// Sanitise
			$typesr = explode(',', $typesr);
			JArrayHelper::toInteger($typesr);

			$this->setState('tag.typesr', $typesr);
		}

		$language = $app->input->getString('tag_list_language_filter');
		$this->setState('tag.language', $language);

		// List state information
		$format = $app->input->getWord('format');

		if ($format == 'feed')
		{
			$limit = $app->getCfg('feed_limit');
		}
		else
		{
			if ($this->state->params->get('show_pagination_limit'))
			{
				$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'uint');
			}
			else
			{
				$limit = $this->state->params->get('maximum', 20);
			}
		}

		$this->setState('list.limit', $limit);

		$offset = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.start', $offset);

		$itemid = $pkString . ':' . $app->input->get('Itemid', 0, 'int');
		$orderCol = $app->getUserStateFromRequest('com_tags.tag.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
		$orderCol = !$orderCol ? $this->state->params->get('tag_list_orderby', 'c.core_title') : $orderCol;

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'c.core_title';
		}

		$this->setState('list.ordering', $orderCol);

		$listOrder = $app->getUserStateFromRequest('com_tags.tag.list.' . $itemid . '.filter_order_direction', 'filter_order_Dir', '', 'string');
		$listOrder = !$listOrder ? $this->state->params->get('tag_list_orderby_direction', 'ASC') : $listOrder;

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		$this->setState('list.direction', $listOrder);

		$this->setState('tag.state', 1);

		// Optional filter text
		$filterSearch = $app->getUserStateFromRequest('com_tags.tag.list.' . $itemid . '.filter_search', 'filter-search', '', 'string');
		$this->setState('list.filter', $filterSearch);
	}

	/**
	 * Method to get tag data for the current tag or tags
	 *
	 * @param   integer  An optional ID
	 *
	 * @return  object
	 * @since   3.1
	 */
	public function getItem($pk = null)
	{
		if (!isset($this->item) ||$this->item === null)
		{
			$this->item = false;

			if (empty($id))
			{
				$id = $this->getState('tag.id');
			}

			// Get a level row instance.
			$table = JTable::getInstance('Tag', 'TagsTable');

			$idsArray = explode(',', $id);
			// Attempt to load the rows into an array.
			foreach ($idsArray as $id)
			{
				try
				{
					$table->load($id);

					// Check published state.
					if ($published = $this->getState('filter.published'))
					{
						if ($table->published != $published)
						{
							return $this->item;
						}
					}

					// Convert the JTable to a clean JObject.
					$properties = $table->getProperties(1);
					$this->item[] = JArrayHelper::toObject($properties, 'JObject');
				}
				catch (RuntimeException $e)
				{
					$this->setError($e->getMessage());
					return false;
				}
			}
		}

		return $this->item;
	}

	/**
	 * Increment the hit counter.
	 *
	 * @param   integer  Optional primary key of the article to increment.
	 *
	 * @return  boolean  True if successful; false otherwise and internal error set.
	 *
	 * @since   3.2
	 */
	public function hit($pk = 0)
	{
		$input = JFactory::getApplication()->input;
		$hitcount = $input->getInt('hitcount', 1);

		if ($hitcount)
		{
			$pk = (!empty($pk)) ? $pk : (int) $this->getState('tag.id');

			$table = JTable::getInstance('Tag', 'TagsTable');
			$table->load($pk);
			$table->hit($pk);
		}

		return true;
	}
}
