<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
				switch ($this->state->params->get('list_show_date'))
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
		$orderByOption = $this->getState('params')->get('tag_list_orderby', 'title');
		$includeChildren = $this->state->params->get('include_children', 0);
		$orderDir = $this->getState('params')->get('tag_list_orderby_direction', 'ASC');
		$matchAll = $this->getState('params')->get('return_any_or_all', 1);
		$languageFilter = JComponentHelper::getParams('com_tags')->get('tag_list_language_filter', 'all');

		$listQuery = New JTags;
		$query = $listQuery->getTagItemsQuery($tagId, $typesr, $includeChildren, $orderByOption, $orderDir, $matchAll, $languageFilter);

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
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->getObject('id');
		$pk = (array) $pk;
		$pkString = '';

		foreach ($pk as $id)
		{
			$pkString .= (int) $id . ',';
		}
		$pkString = rtrim($pkString, ',');

		$this->setState('tag.id', $pkString);

		// Get the selected list of types from the request. If none are specified all are used.
		$typesr = $app->input->getObject('types');
		if ($typesr)
		{
			$typesr = (array) $typesr;
			$this->setState('tag.typesr', $typesr);
		}

		$language = $app->input->getString('tag_list_language_filter');
		$this->setState('tag.language', $language);

		$offset = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$user = JFactory::getUser();
		if ((!$user->authorise('core.edit.state', 'com_tags')) &&  (!$user->authorise('core.edit', 'com_tags')))
		{
			$this->setState('filter.published', 1);
		}

		// Optional filter text
		$this->setState('list.filter', $app->input->getString('filter-search'));
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
}
