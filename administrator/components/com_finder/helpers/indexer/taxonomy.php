<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Stemmer base class for the Finder indexer package.
 *
 * @since  2.5
 */
class FinderIndexerTaxonomy
{
	/**
	 * An internal cache of taxonomy branch data.
	 *
	 * @var    array
	 * @since  2.5
	 */
	public static $branches = array();

	/**
	 * An internal cache of taxonomy node data.
	 *
	 * @var    array
	 * @since  2.5
	 */
	public static $nodes = array();

	/**
	 * Method to add a branch to the taxonomy tree.
	 *
	 * @param   string   $title   The title of the branch.
	 * @param   integer  $state   The published state of the branch. [optional]
	 * @param   integer  $access  The access state of the branch. [optional]
	 *
	 * @return  integer  The id of the branch.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public static function addBranch($title, $state = 1, $access = 1)
	{
		// Check to see if the branch is in the cache.
		if (isset(static::$branches[$title]))
		{
			return static::$branches[$title]->id;
		}

		// Check to see if the branch is in the table.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__finder_taxonomy'))
			->where($db->quoteName('parent_id') . ' = 1')
			->where($db->quoteName('title') . ' = ' . $db->quote($title));
		$db->setQuery($query);

		// Get the result.
		$result = $db->loadObject();

		// Check if the database matches the input data.
		if ((bool) $result && $result->state == $state && $result->access == $access)
		{
			// The data matches, add the item to the cache.
			static::$branches[$title] = $result;

			return static::$branches[$title]->id;
		}

		/*
		 * The database did not match the input. This could be because the
		 * state has changed or because the branch does not exist. Let's figure
		 * out which case is true and deal with it.
		 */
		$branch = new JObject;

		if (empty($result))
		{
			// Prepare the branch object.
			$branch->parent_id = 1;
			$branch->title = $title;
			$branch->state = (int) $state;
			$branch->access = (int) $access;
		}
		else
		{
			// Prepare the branch object.
			$branch->id = (int) $result->id;
			$branch->parent_id = (int) $result->parent_id;
			$branch->title = $result->title;
			$branch->state = (int) $result->title;
			$branch->access = (int) $result->access;
			$branch->ordering = (int) $result->ordering;
		}

		// Store the branch.
		static::storeNode($branch);

		// Add the branch to the cache.
		static::$branches[$title] = $branch;

		return static::$branches[$title]->id;
	}

	/**
	 * Method to add a node to the taxonomy tree.
	 *
	 * @param   string   $branch  The title of the branch to store the node in.
	 * @param   string   $title   The title of the node.
	 * @param   integer  $state   The published state of the node. [optional]
	 * @param   integer  $access  The access state of the node. [optional]
	 *
	 * @return  integer  The id of the node.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public static function addNode($branch, $title, $state = 1, $access = 1)
	{
		// Check to see if the node is in the cache.
		if (isset(static::$nodes[$branch][$title]))
		{
			return static::$nodes[$branch][$title]->id;
		}

		// Get the branch id, insert it if it does not exist.
		$branchId = static::addBranch($branch);

		// Check to see if the node is in the table.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__finder_taxonomy'))
			->where($db->quoteName('parent_id') . ' = ' . $db->quote($branchId))
			->where($db->quoteName('title') . ' = ' . $db->quote($title));
		$db->setQuery($query);

		// Get the result.
		$result = $db->loadObject();

		// Check if the database matches the input data.
		if ((bool) $result && $result->state == $state && $result->access == $access)
		{
			// The data matches, add the item to the cache.
			static::$nodes[$branch][$title] = $result;

			return static::$nodes[$branch][$title]->id;
		}

		/*
		 * The database did not match the input. This could be because the
		 * state has changed or because the node does not exist. Let's figure
		 * out which case is true and deal with it.
		 */
		$node = new JObject;

		if (empty($result))
		{
			// Prepare the node object.
			$node->parent_id = (int) $branchId;
			$node->title = $title;
			$node->state = (int) $state;
			$node->access = (int) $access;
		}
		else
		{
			// Prepare the node object.
			$node->id = (int) $result->id;
			$node->parent_id = (int) $result->parent_id;
			$node->title = $result->title;
			$node->state = (int) $result->title;
			$node->access = (int) $result->access;
			$node->ordering = (int) $result->ordering;
		}

		// Store the node.
		static::storeNode($node);

		// Add the node to the cache.
		static::$nodes[$branch][$title] = $node;

		return static::$nodes[$branch][$title]->id;
	}

	/**
	 * Method to add a map entry between a link and a taxonomy node.
	 *
	 * @param   integer  $linkId  The link to map to.
	 * @param   integer  $nodeId  The node to map to.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public static function addMap($linkId, $nodeId)
	{
		// Insert the map.
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('link_id'))
			->from($db->quoteName('#__finder_taxonomy_map'))
			->where($db->quoteName('link_id') . ' = ' . (int) $linkId)
			->where($db->quoteName('node_id') . ' = ' . (int) $nodeId);
		$db->setQuery($query);
		$db->execute();
		$id = (int) $db->loadResult();

		$map = new JObject;
		$map->link_id = (int) $linkId;
		$map->node_id = (int) $nodeId;

		if ($id)
		{
			$db->updateObject('#__finder_taxonomy_map', $map, array('link_id', 'node_id'));
		}
		else
		{
			$db->insertObject('#__finder_taxonomy_map', $map);
		}

		return true;
	}

	/**
	 * Method to get the title of all taxonomy branches.
	 *
	 * @return  array  An array of branch titles.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public static function getBranchTitles()
	{
		$db = JFactory::getDbo();

		// Set user variables
		$groups = implode(',', JFactory::getUser()->getAuthorisedViewLevels());

		// Create a query to get the taxonomy branch titles.
		$query = $db->getQuery(true)
			->select($db->quoteName('title'))
			->from($db->quoteName('#__finder_taxonomy'))
			->where($db->quoteName('parent_id') . ' = 1')
			->where($db->quoteName('state') . ' = 1')
			->where($db->quoteName('access') . ' IN (' . $groups . ')');

		// Get the branch titles.
		$db->setQuery($query);

		return $db->loadColumn();
	}

	/**
	 * Method to find a taxonomy node in a branch.
	 *
	 * @param   string  $branch  The branch to search.
	 * @param   string  $title   The title of the node.
	 *
	 * @return  mixed  Integer id on success, null on no match.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public static function getNodeByTitle($branch, $title)
	{
		$db = JFactory::getDbo();

		// Set user variables
		$groups = implode(',', JFactory::getUser()->getAuthorisedViewLevels());

		// Create a query to get the node.
		$query = $db->getQuery(true)
			->select('t1.*')
			->from($db->quoteName('#__finder_taxonomy') . ' AS t1')
			->join('INNER', $db->quoteName('#__finder_taxonomy') . ' AS t2 ON t2.id = t1.parent_id')
			->where('t1.access IN (' . $groups . ')')
			->where('t1.state = 1')
			->where('t1.title LIKE ' . $db->quote($db->escape($title) . '%'))
			->where('t2.access IN (' . $groups . ')')
			->where('t2.state = 1')
			->where('t2.title = ' . $db->quote($branch));

		// Get the node.
		$db->setQuery($query, 0, 1);

		return $db->loadObject();
	}

	/**
	 * Method to remove map entries for a link.
	 *
	 * @param   integer  $linkId  The link to remove.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public static function removeMaps($linkId)
	{
		// Delete the maps.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__finder_taxonomy_map'))
			->where($db->quoteName('link_id') . ' = ' . (int) $linkId);
		$db->setQuery($query);
		$db->execute();

		return true;
	}

	/**
	 * Method to remove orphaned taxonomy nodes and branches.
	 *
	 * @return  integer  The number of deleted rows.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	public static function removeOrphanNodes()
	{
		// Delete all orphaned nodes.
		$db = JFactory::getDbo();
		$query     = $db->getQuery(true);
		$subquery  = $db->getQuery(true);
		$subquery1 = $db->getQuery(true);

		$subquery1->select($db->quoteName('t.id'))
			->from($db->quoteName('#__finder_taxonomy', 't'))
			->join('LEFT', $db->quoteName('#__finder_taxonomy_map', 'm') . ' ON ' . $db->quoteName('m.node_id') . '=' . $db->quoteName('t.id'))
			->where($db->quoteName('t.parent_id') . ' > 1 ')
			->where($db->quoteName('m.link_id') . ' IS NULL');

		$subquery->select($db->quoteName('id'))
			->from('(' . $subquery1 . ') temp');

		$query->delete($db->quoteName('#__finder_taxonomy'))
			->where($db->quoteName('id') . ' IN (' . $subquery . ')');

		$db->setQuery($query);
		$db->execute();

		return $db->getAffectedRows();
	}

	/**
	 * Method to store a node to the database.  This method will accept either a branch or a node.
	 *
	 * @param   object  $item  The item to store.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 * @throws  Exception on database error.
	 */
	protected static function storeNode($item)
	{
		$db = JFactory::getDbo();

		// Check if we are updating or inserting the item.
		if (empty($item->id))
		{
			// Insert the item.
			$db->insertObject('#__finder_taxonomy', $item, 'id');
		}
		else
		{
			// Update the item.
			$db->updateObject('#__finder_taxonomy', $item, 'id');
		}

		return true;
	}
}
