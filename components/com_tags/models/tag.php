<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
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
	 * The tag that applies.
	 *
	 * @access  protected
	 * @var     object
	 */
	protected $tag = null;

	/**
	 * The list of items associated with the tag.
	 *
	 * @access  protected
	 * @var     array
	 */
	protected $items = null;

	/**
	 * Method to get a list of items for a tag.
	 *
	 * @return  mixed  An array of objects on success, false on failure.
	 *
	 * @since   3.1
	 */
	public function getItems()
	{
		// Invoke the parent getItems method to get the main list
		$items = parent::getItems();

			$contentTypes = new JTagsHelper;
			$types = $contentTypes->getTypes('objectList');
			$tableArray[] = '';
			foreach ($types as $type)
			{
				$tableArray[$type->table] = '';
			}

			$query = '';
			// Get the data from the content item source table.
			foreach ($items as $item)
			{
				$tagsHelper = new JTagsHelper;

				$explodedItemName = $tagsHelper->explodeTagItemName($item->item_name);
				$item->link = 'index.php?option=' . $explodedItemName[0] . '&view=' . $explodedItemName[1] . '&id=' . $explodedItemName[2] ;
				$item_id = $explodedItemName[2];
				$table = $tagsHelper->getTableName($item->item_name, $explodedItemName);

				// Keep track of the items we want from each table.
				$tableArray[$table] .= $item_id . ',';

				// Initialize some variables.
				$db = JFactory::getDbo();

				$query = $db->getQuery(true);
				$query->clear();

				$query->select('*');
				$query->from($db->quoteName($table));
				$query->where($db->quoteName('id') . ' = ' . (int) $item_id);

				$db->setQuery($query);
				$item->itemData = $db->loadAssoc();

				// Deal with alternative field names.
				if (array_key_exists('name', $item->itemData) && !array_key_exists('title', $item->itemData))
				{
					$item->itemData['title'] = $item->itemData['name'];
				}

				if (array_key_exists('state', $item->itemData) && !array_key_exists('published', $item->itemData))
				{
					$item->itemData['published'] = $item->itemData['state'];
				}

				// Convert parameter fields to objects.
				/*$registry = new JRegistry;
				$registry->loadString($item->itemData->params);

				$data->params = clone $this->getState('params');
				$data->params->merge($registry);

				$registry = new JRegistry;
				$registry->loadString($item->itemData->metadata);
				$item->metadata = $registry;*/
			}
var_dump($tableArray);die;
		return $items;
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
		$user	= JFactory::getUser();
		// Need to decide on a model for access .. maybe just do an access IN groups condition
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$tagId = $this->getState('tag.id');
		$tagTreeArray = implode(',', $this->getTagTreeArray($tagId));

		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select required fields from the tags.
		$query->select('*');
		$query->group($db->quoteName('a.item_name'));
		$query->from($db->quoteName('#__contentitem_tag_map') . ' AS a ');
		$query->where($db->quoteName('a.tag_id') . ' IN (' . $tagTreeArray . ')');

		// If we ever wanted not to do the nesting we could use the below instead.
		// $query->where($db->quoteName('a.tag_id') . ' = ' . (int) $tagId);

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   3.1
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app    = JFactory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('tag.id', $pk);

		$offset = $app->input->get('limitstart', 0, 'uint');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$user = JFactory::getUser();
		if ((!$user->authorise('core.edit.state', 'com_tags')) &&  (!$user->authorise('core.edit', 'com_tags')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}
	}

	/**
	 * Method to get tag data for the current tag
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

			// Attempt to load the row.
			if ($table->load($id))
			{
				// Check published state.
				if ($published = $this->getState('filter.published'))
				{
					if ($table->published != $published) {
						return $this->item;
					}
				}

				// Convert the JTable to a clean JObject.
				$properties = $table->getProperties(1);
				$this->item = JArrayHelper::toObject($properties, 'JObject');
			}
			elseif ($error = $table->getError())
			{
				$this->setError($error);
			}
		}

		return $this->item;
	}

	/**
	 * Method to get an array of tag ids for the current tag and its children
	 *
	 * @param   integer $id  An optional ID
	 *
	 * @return  object
	 * @since   3.1
	 */
	public function getTagTreeArray($id = null)
	{
			if (empty($id))
			{
				$id = $this->getState('tag.id');
			}

			// Get a level row instance.
			$table = JTable::getInstance('Tag', 'TagsTable');

			$tagTree = $table->getTree($id);
			// Attempt to load the tree
			if ($tagTree)
			{
				foreach ($tagTree as $tag)
				{
					// Check published state.
					if ($published = $this->getState('filter.published'))
					{
						if ($tag->published == $published)
						{
							$this->tagTreeArray[] = $tag->id;
						}
					}
				}
			}
			elseif ($error = $table->getError())
			{
				$this->setError($error);
			}

		return $this->tagTreeArray;
	}
}
