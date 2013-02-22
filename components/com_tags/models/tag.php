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
				$item->link = 'index.php?option=' . $explodedTypeAlias[0] . '&view=' . $explodedTypeAlias[1] . '&id=' . $item->id .':'. $item->alias ;

				// Get display date
				switch ($this->state->params->get('list_show_date'))
				{
					case 'modified':
						$item->displayDate = $item->modified_date;
						break;

					case 'created':
						$item->displayDate = $item->created_date;
						break;

					default:
					case 'published':
						$item->displayDate = ($item->publish_up == 0) ? $item->created_date : $item->publish_up;
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
		if (!empty($typesr))
		{
			$typesrlist= implode(',', $typesr);
		}
		$contentTypes = new JTags;

		// Get the type data, limited to types in the request if there are any specified.
		$typesarray = $contentTypes->getTypes('assocList', $typesr, false);

		$typeAliases = '';
		foreach ($typesarray as $type)
		{
			$typeAliases .= "'" . $type['alias'] . "'" . ',';
		}

		$typeAliases = rtrim($typeAliases, ',');

		$tagId = '';
		$tagId = $this->getState('tag.id');

		// Quick check to cut out types with no results.
		$db2 = JFactory::getDbo();
		$queryt2 = $db2->getQuery(true);
		$queryt2->select($db2->qn('type_alias'));
		$queryt2->from($db2->qn('#__contentitem_tag_map'));
		$queryt2->where($db2->qn('tag_id') . ' IN (' . $tagId . ')');
		if (!empty($typeAliases))
		{
			$queryt2->where('type_alias' . ' IN (' . $typeAliases .')');
		}
		$queryt2->group(array($db2->qn('type_alias')));
		$db2->setQuery($queryt2);
		$types = $db2->loadObjectList();

		// If we get no types, return.
		if (empty($types))
		{
			return null;
		}

		$typeAliases = '';

		foreach ($types as $type)
		{
			$typeAliases .= "'" . $type->type_alias . "',";
		}

		$typeAliases = rtrim($typeAliases, ',');
		$typesobject = $contentTypes->getTypes('objectList', $typeAliases);

		// Start a fresh DBo.
		$db = JFactory::getDbo();
		$queryt = $db->getQuery(true);

		foreach ($typesobject as $type)
		{
			$aliasExplode = explode('.', $type->alias);
			$type->component = $aliasExplode[0];

			// We need to create the SELECT clause that correctly maps differently named fields to the common names.
			$fieldsArray = json_decode($type->field_mappings);
			$type->fieldlist = '';

			foreach ($fieldsArray as $common => $specific)
			{
				if ($specific != 'null')
				{
					$newstring = $db->qn('CI.' . rtrim($specific), $common) . ',';

				}
				else
				{
					$newstring = 'null' . ' AS ' . $db->qn($common) . ',';

				}
				$type->fieldlist .= $newstring;
			}

			$type->fieldlist = rtrim($type->fieldlist, ',');

			// Get the select query we need for each view and add it to the array.
			$queryt->clear();

			if (!empty($type->fieldlist))
			{
				$user	= JFactory::getUser();
				$groups	= implode(',', $user->getAuthorisedViewLevels());
				$tagId = '';
				$tagId = $this->getState('tag.id');
				$tagCount	= substr_count($tagId, ',') + 1;
				$tagTreeArray = '';

				if ($this->state->params->get('include_children') == 1)
				{
					$tagIdArray = explode(',', $tagId);
					$tagTreeArray = '';
					foreach ($tagIdArray as $tag)
					{
						$tagTreeArray = implode(',', $this->getTagTreeArray($tag));
					}
				}

				// Select required fields from the map table.
				$queryt->select(array($db->qn('a.type_alias'), $db->qn('a.content_item_id')));
				$queryt->group(array($db->quoteName('a.type_alias'), $db->quoteName('a.content_item_id')));
				$queryt->from($db->quoteName('#__contentitem_tag_map','a'));

				// Modify the query based on whether or not items with child tags should be returned.
				if ($this->state->params->get('include_children') == 1)
				{
					$queryt->join('inner', $db->quoteName($type->table,'CI') . ' ON ' . $db->qn('CI.id') . ' = '  . $db->qn('a.content_item_id')
					. ' AND ' . $db->quoteName('a.tag_id') . ' IN (' . $tagTreeArray . ')'
					. ' AND ' . $db->qn('a.type_alias') . ' = ' .  $db->q($type->alias));
				}
				else
				{
					$queryt->join('inner', $db->quoteName($type->table) . ' AS ' . $db->qn('CI')
						. ' ON ' . $db->qn('CI.id') . ' = '  . $db->qn('a.content_item_id')
						. ' AND ' . $db->quoteName('a.tag_id') . ' IN (' .  $tagId . ')'
						. ' AND ' . $db->qn('a.type_alias') . ' = ' .  $db->q($type->alias));
				}

				// For AND search make sure the number matches, but if there is just one tag do not bother.
				if ($this->state->params->get('return_any_or_all') == 0 && $tagCount > 1)
				{
					$queryt->having($db->qn('ntags') . ' = ' . $tagCount );
				}

				$queryt->select($type->fieldlist . ', ' . $db->q($type->router) . ' AS ' . $db->quoteName('router') . ', ' . $db->q($type->component) . ' AS ' . $db->qn('component') . ', COUNT(DISTINCT ' . $db->qn('a.tag_id') . ') AS ' . $db->qn('ntags'));

				$queryString = $queryt->__toString();
				$tablequeries[] = $queryString;
			}
		}

		// Now we want to put the queries for each table together using Union.
		$queryu = $db->getQuery(true);

		foreach ($tablequeries as $i => $uquery)
		{
			if ($i > 0)
			{
				$queryu->union($uquery);
			}
		}

		if ($queryu->union)
		{
			$unionString  = $queryu->union->__toString();
			$queryStringu = $tablequeries[0] . $unionString . ' ORDER BY ' . $db->qn($this->state->params->get('tag_list_orderby', 'title')) . ' ' . $this->state->params->get('tag_list_orderby_direction', 'ASC') . ' LIMIT 0,' . $this->state->params->get('maximum', 5);
		}
		else
		{
			$queryStringu = $tablequeries[0] . ' ORDER BY ' . $db->qn($this->state->params->get('tag_list_orderby', 'title')) . ' ' . $this->state->params->get('tag_list_orderby_direction', 'ASC') . ' LIMIT 0,' . $this->state->params->get('maximum', 200);
		}

		// Until we have UNION ALL in the platform do this to avoid an unneeded sort.
		$queryStringu = str_replace('UNION ', 'UNION ALL ', $queryStringu);

		return $queryStringu;
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
	public function getTagTreeArray($id = null)
	{
		if (empty($id))
		{
			$id = $this->getState('tag.id');
		}

		// Get a level row instance.
		$table = JTable::getInstance('Tag', 'TagsTable');

		if ($table->isLeaf($id))
		{
			$this->tagTreeArray[] = $id;
			return $this->tagTreeArray;
		}
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
