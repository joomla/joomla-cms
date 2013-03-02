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
	 * @access  protected
	 * @var     object
	 */
	protected $tag = null;

	/**
	 * The list of items associated with the tags.
	 *
	 * @access  protected
	 * @var     array
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
				$item->link = 'index.php?option=' . $explodedTypeAlias[0] . '&view=' . $explodedTypeAlias[1] . '&id=' . $item->content_item_id .':'. $item->core_alias ;

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
		$typesr = $this->getState('tag.typesr');
		// Create a new query object.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$nullDate = $db->q($db->getNullDate());
		$nowDate = $db->q(JFactory::getDate()->toSql());

		$tagId = $this->getState('tag.id')?:'';
		$ntagsr =  substr_count($tagId, ',') + 1;

		// If we want to include children we have to adjust the list of tags.
		// We do not search child tags when the match all option is selected.
		$includeChildren = $this->state->params->get('include_children');
		if ($includeChildren == 1)
		{
			$tagIdArray = explode(',', $tagId);
			$tagTreeList = '';
			foreach ($tagIdArray as $tag)
			{
					$tagTreeList .= implode(',', $this->getTagTreeArray($tag, $tagTreeArray)) . ',';
			}
			$tagId = trim($tagTreeList, ',');
		}

		// M is the mapping table. C is the core_content table. Ct is the content_types table.
		$query->select('m.type_alias, m.content_item_id, m.core_content_id, count(m.tag_id) AS match_count,  MAX(m.tag_date) as tag_date, MAX(c.core_title) AS core_title');
		$query->select('MAX(c.core_alias) AS core_alias, MAX(c.core_body) AS core_body, MAX(c.core_state) AS core_state, MAX(c.core_access) AS core_access');
		$query->select('MAX(c.core_metadata) AS core_metadata, MAX(c.core_created_user_id) AS core_created_user_id, MAX(c.core_created_by_alias) AS core_created_by_alias');
		$query->select('MAX(c.core_created_time) as core_created_time');
		$query->select('CASE WHEN c.core_modified_time = ' . $nullDate . ' THEN c.core_created_time ELSE c.core_modified_time END as core_modified_time');
		$query->select('MAX(c.core_language) AS core_language');
		$query->select('MAX(c.core_publish_up) AS core_publish_up, MAX(c.core_publish_down) as core_publish_down');
		$query->select('MAX(ct.title) AS content_type_title, MAX(ct.router) AS router');

		$query->from('#__contentitem_tag_map AS m');
		$query->join('INNER', '#__core_content AS c ON m.type_alias = c.core_type_alias AND m.core_content_id = c.core_content_id');
		$query->join('INNER', '#__content_types AS ct ON ct.alias = m.type_alias');

		// Join over the users for the author and email
		$query->select("CASE WHEN c.core_created_by_alias > ' ' THEN c.core_created_by_alias ELSE ua.name END AS author");
		$query->select("ua.email AS author_email");

		$query->join('LEFT', '#__users AS ua ON ua.id = c.core_created_user_id');

		$query->where('m.tag_id IN (' . $tagId . ')');

		$contentTypes = new JTags;

		// Get the type data, limited to types in the request if there are any specified.
		$typesarray = $contentTypes->getTypes('assocList', $typesr, false);

		$typeAliases = '';

		foreach ($typesarray as $type)
		{
			$typeAliases .= "'" . $type['alias'] . "'" . ',';
		}

		$typeAliases = rtrim($typeAliases, ',');
		$query->where('m.type_alias IN (' . $typeAliases . ')');

		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$query->where('c.core_access IN ('.$groups.')');
		$query->group('m.type_alias, m.content_item_id, m.core_content_id');

		// Use HAVING if matching all tags and we are matching more than one tag.
		if ($ntagsr > 1  && $this->getState('params')->get('return_any_or_all', 1) != 1 && $includeChildren != 1)
		{
			// The number of results should equal the number of tags requested.
			$query->having("COUNT('m.tag_id') = " . $ntagsr);
		}

		// Set up the order by using the option chosen
		$orderByOption = $this->getState('params')->get('tag_list_orderby');
		if ($orderByOption == 'match_count')
		{
			$orderBy = 'COUNT(m.tag_id)';
		}
		else
		{
			$orderBy = 'MAX(c.core_' . $orderByOption . ')';
		}
		$orderDir = $this->getState('params')->get('tag_list_orderby_direction', 'ASC');
		$query->order($orderBy . ' ' . $orderDir . ', MAX(c.core_title) ASC');

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
						if ($table->published != $published) {
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
	 * Method to get an array of tag ids for the current tag and its children
	 *
	 * @param   integer  $id  An optional ID
	 *
	 * @return  object
	 * @since   3.1
	 */
	public function getTagTreeArray($id = null, &$tagTreeArray = null)
	{
		if (empty($id))
		{
			$id = $this->getState('tag.id');
		}

		// Get a level row instance.
		$table = JTable::getInstance('Tag', 'TagsTable');

		if (!isset($tagTreeArray))
		{
			$tagTreeArray = array ();
		}

		if ($table->isLeaf($id))
		{
			$tagTreeArray[] .= $id;
			return $tagTreeArray;
		}
		$tagTree = $table->getTree($id);

		// Attempt to load the tree
		if ($tagTree)
		{
			foreach ($tagTree as $tag)
			{
						$tagTreeArray[] = $tag->id;
			}
			return $tagTreeArray;
		}
		elseif ($error = $table->getError())
		{
			$this->setError($error);
		}
	}
}
