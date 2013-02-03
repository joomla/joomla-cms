<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Stemmer base class for the Finder indexer package.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 * @since       2.5
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
		if (isset(self::$branches[$title]))
		{
			return self::$branches[$title]->id;
		}

		// Check to see if the branch is in the table.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__finder_taxonomy'));
		$query->where($db->quoteName('parent_id') . ' = 1');
		$query->where($db->quoteName('title') . ' = ' . $db->quote($title));
		$db->setQuery($query);

		// Get the result.
		$result = $db->loadObject();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		// Check if the database matches the input data.
		if (!empty($result) && $result->state == $state && $result->access == $access)
		{
			// The data matches, add the item to the cache.
			self::$branches[$title] = $result;

			return self::$branches[$title]->id;
		}

		// The database did not match the input. This could be because the
		// state has changed or because the branch does not exist. Let's figure
		// out which case is true and deal with it.
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
		self::storeNode($branch);

		// Add the branch to the cache.
		self::$branches[$title] = $branch;

		return self::$branches[$title]->id;
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
		if (isset(self::$nodes[$branch][$title]))
		{
			return self::$nodes[$branch][$title]->id;
		}

		// Get the branch id, inserted it if it does not exist.
		$branchId = self::addBranch($branch);

		// Check to see if the node is in the table.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__finder_taxonomy'));
		$query->where($db->quoteName('parent_id') . ' = ' . $db->quote($branchId));
		$query->where($db->quoteName('title') . ' = ' . $db->quote($title));
		$db->setQuery($query);

		// Get the result.
		$result = $db->loadObject();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		// Check if the database matches the input data.
		if (!empty($result) && $result->state == $state && $result->access == $access)
		{
			// The data matches, add the item to the cache.
			self::$nodes[$branch][$title] = $result;

			return self::$nodes[$branch][$title]->id;
		}

		// The database did not match the input. This could be because the
		// state has changed or because the node does not exist. Let's figure
		// out which case is true and deal with it.
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
		self::storeNode($node);

		// Add the node to the cache.
		self::$nodes[$branch][$title] = $node;

		return self::$nodes[$branch][$title]->id;
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
		$db = JFactory::getDBO();

		$query = $db->getQuery(true);
		$query->select($db->quoteName('link_id'));
		$query->from($db->quoteName('#__finder_taxonomy_map'));
		$query->where($db->quoteName('link_id') . ' = ' . (int)$linkId);
		$query->where($db->quoteName('node_id') . ' = ' . (int)$nodeId);
		$db->setQuery($query);
		$db->query();
		$id = (int) $db->loadResult();

		$map = new JObject();
		$map->link_id = (int) $linkId;
		$map->node_id = (int) $nodeId;

		if ($id) {
			$db->updateObject('#__finder_taxonomy_map', $map);
		}
		else {
			$db->insertObject('#__finder_taxonomy_map', $map);
		}

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
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
		$db = JFactory::getDBO();

		// Set user variables
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		// Create a query to get the taxonomy branch titles.
		$query = $db->getQuery(true);
		$query->select($db->quoteName('title'));
		$query->from($db->quoteName('#__finder_taxonomy'));
		$query->where($db->quoteName('parent_id') . ' = 1');
		$query->where($db->quoteName('state') . ' = 1');
		$query->where($db->quoteName('access') . ' IN (' . $groups . ')');

		// Get the branch titles.
		$db->setQuery($query);
		$results = $db->loadColumn();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		return $results;
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
		$db = JFactory::getDBO();

		// Set user variables
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		// Create a query to get the node.
		$query = $db->getQuery(true);
		$query->select('t1.*');
		$query->from($db->quoteName('#__finder_taxonomy') . ' AS t1');
		$query->join('INNER', $db->quoteName('#__finder_taxonomy') . ' AS t2 ON t2.id = t1.parent_id');
		$query->where('t1.' . $db->quoteName('access') . ' IN (' . $groups . ')');
		$query->where('t1.' . $db->quoteName('state') . ' = 1');
		$query->where('t1.' . $db->quoteName('title') . ' LIKE "' . $db->escape($title) . '%"');
		$query->where('t2.' . $db->quoteName('access') . ' IN (' . $groups . ')');
		$query->where('t2.' . $db->quoteName('state') . ' = 1');
		$query->where('t2.' . $db->quoteName('title') . ' = ' . $db->quote($branch));

		// Get the node.
		$db->setQuery($query, 0, 1);
		$result = $db->loadObject();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		return $result;
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
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->delete();
		$query->from($db->quoteName('#__finder_taxonomy_map'));
		$query->where($db->quoteName('link_id') . ' = ' . (int) $linkId);
		$db->setQuery($query);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

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
		$db = JFactory::getDBO();
		/*$query = $db->getQuery(true);
		$query->delete();
		$query->from($db->quoteName('#__finder_taxonomy') . ' AS t');
		$query->join('LEFT', $db->quoteName('#__finder_taxonomy_map') . ' AS m ON m.node_id = t.id');
		$query->where('t.' . $db->quoteName('parent_id') . ' > 1');
		$query->where('m.' . $db->quoteName('link_id') . ' IS NULL');*/
		//@TODO: Query does not work with JDatabaseQuery, does not support DELETE t.*, must be DELETE FROM ...
		$query = 'DELETE t.*' .
			' FROM ' . $db->quoteName('#__finder_taxonomy') . ' AS t' .
			' LEFT JOIN ' . $db->quoteName('#__finder_taxonomy_map') . ' AS m ON m.node_id = t.id' .
			' WHERE t.' . $db->quoteName('parent_id') . ' > 1' .
			' AND m.' . $db->quoteName('link_id') . ' IS NULL';
		$db->setQuery($query);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

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
		$db = JFactory::getDBO();

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

		// Check for a database error.
		if ($db->getErrorNum())
		{
			// Throw database error exception.
			throw new Exception($db->getErrorMsg(), 500);
		}

		return true;
	}
}
