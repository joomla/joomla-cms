<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Database
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.database.table');

/**
 * Table class supporting modified pre-order tree traversal behavior.
 *
 * @package		Joomla.Framework
 * @subpackage	Database
 * @since		1.6
 * @link		http://docs.joomla.org/JTableNested
 */
class JTableNested extends JTable
{
	/**
	 * Object property holding the primary key of the parent node.  Provides
	 * adjacency list data for nodes.
	 *
	 * @var integer
	 */
	public $parent_id;

	/**
	 * Object property holding the depth level of the node in the tree.
	 *
	 * @var integer
	 */
	public $level;

	/**
	 * Object property holding the left value of the node for managing its
	 * placement in the nested sets tree.
	 *
	 * @var integer
	 */
	public $lft;

	/**
	 * Object property holding the right value of the node for managing its
	 * placement in the nested sets tree.
	 *
	 * @var integer
	 */
	public $rgt;

	/**
	 * Object property holding the alias of this node used to constuct the
	 * full text path, forward-slash delimited.
	 *
	 * @var string
	 */
	public $alias;

	/**
	 * Object property to hold the location type to use when storing the row.
	 * Possible values are: ['before', 'after', 'first-child', 'last-child'].
	 *
	 * @var string
	 */
	protected $_location;

	/**
	 * Object property to hold the primary key of the location reference node to
	 * use when storing the row.  A combination of location type and reference
	 * node describes where to store the current node in the tree.
	 *
	 * @var integer
	 */
	protected $_location_id;

	/**
	 * @var	array	An array to cache values in recursive processes.
	 */
	protected $_cache = array();

	protected $_debug = 0;

	/**
	 * Sets the debug level on or off
	 *
	 * @param	int	0 = off, 1 = on
	 */
	public function debug($level)
	{
		$this->_debug = intval($level);
	}

	/**
	 * Method to get an array of nodes from a given node to its root.
	 *
	 * @param	integer	Primary key of the node for which to get the path.
	 * @param	boolean	Only select diagnostic data for the nested sets.
	 * @return	mixed	Boolean false on failure or array of node objects on success.
	 * @since	1.6
	 * @link	http://docs.joomla.org/JTableNested/getPath
	 */
	public function getPath($pk = null, $diagnostic = false)
	{
		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the path from the node to the root.
		$query = $this->_db->getQuery(true);
		$select = ($diagnostic) ? 'p.'.$k.', p.parent_id, p.level, p.lft, p.rgt' : 'p.*';
		$query->select($select);
		$query->from($this->_tbl.' AS n, '.$this->_tbl.' AS p');
		$query->where('n.lft BETWEEN p.lft AND p.rgt');
		$query->where('n.'.$k.' = '.(int) $pk);
		$query->order('p.lft');

		$this->_db->setQuery($query);
		$path = $this->_db->loadObjectList();

		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_GET_PATH_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			return false;
		}

		return $path;
	}

	/**
	 * Method to get a node and all its child nodes.
	 *
	 * @param	integer	Primary key of the node for which to get the tree.
	 * @param	boolean	Only select diagnostic data for the nested sets.
	 * @return	mixed	Boolean false on failure or array of node objects on success.
	 * @since	1.6
	 * @link	http://docs.joomla.org/JTableNested/getTree
	 */
	public function getTree($pk = null, $diagnostic = false)
	{
		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the node and children as a tree.
		$query = $this->_db->getQuery(true);
		$select = ($diagnostic) ? 'n.'.$k.', n.parent_id, n.level, n.lft, n.rgt' : 'n.*';
		$query->select($select);
		$query->from($this->_tbl.' AS n, '.$this->_tbl.' AS p');
		$query->where('n.lft BETWEEN p.lft AND p.rgt');
		$query->where('p.'.$k.' = '.(int) $pk);
		$query->order('n.lft');
		$this->_db->setQuery($query);
		$tree = $this->_db->loadObjectList();

		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_GET_TREE_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			return false;
		}

		return $tree;
	}

	/**
	 * Method to determine if a node is a leaf node in the tree (has no children).
	 *
	 * @param	integer	Primary key of the node to check.
	 * @return	boolean	True if a leaf node.
	 * @since	1.6
	 * @link	http://docs.joomla.org/JTableNested/isLeaf
	 */
	public function isLeaf($pk = null)
	{
		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the node by primary key.
		if (!$node = $this->_getNode($pk)) {
			// Error message set in getNode method.
			return false;
		}

		// The node is a leaf node.
		return (($node->rgt - $node->lft) == 1);
	}

	/**
	 * Method to set the location of a node in the tree object.  This method does not
	 * save the new location to the database, but will set it in the object so
	 * that when the node is stored it will be stored in the new location.
	 *
	 * @param	integer	The primary key of the node to reference new location by.
	 * @param	string	Location type string. ['before', 'after', 'first-child', 'last-child']
	 * @return	boolean	True on success.
	 * @since	1.6
	 * @link	http://docs.joomla.org/JTableNested/setLocation
	 */
	public function setLocation($referenceId, $position = 'after')
	{
		// Make sure the location is valid.
		if (($position != 'before') && ($position != 'after') &&
			($position != 'first-child') && ($position != 'last-child')) {
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_INVALID_LOCATION', get_class($this)));
			$this->setError($e);
			return false;
		}

		// Set the location properties.
		$this->_location = $position;
		$this->_location_id = $referenceId;

		return true;
	}

	/**
	 * Method to move a row in the ordering sequence of a group of rows defined by an SQL WHERE clause.
	 * Negative numbers move the row up in the sequence and positive numbers move it down.
	 *
	 * @param	integer	The direction and magnitude to move the row in the ordering sequence.
	 * @param	string	WHERE clause to use for limiting the selection of rows to compact the
	 *					ordering values.
	 * @return	mixed	Boolean true on success.
	 * @since	1.0
	 * @link	http://docs.joomla.org/JTable/move
	 */
	public function move($delta, $where)
	{
		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		$query = $this->_db->getQuery(true);
		$query->select($k);
		$query->from($this->_tbl);
		$query->where('parent_id = '.$this->parent_id);
		$position = 'after';
		if($delta > 0)
		{
			$query->where('rgt > '.$this->rgt);
			$query->order('rgt ASC');
			$position = 'after';
		} else {
			$query->where('lft < '.$this->lft);
			$query->order('lft DESC');
			$position = 'before';
		}

		$this->_db->setQuery($query);
		$referenceId = $this->_db->loadResult();

		return $this->moveByReference($referenceId, $position, $pk);
	}

	/**
	 * Method to move a node and its children to a new location in the tree.
	 *
	 * @param	integer	The primary key of the node to reference new location by.
	 * @param	string	Location type string. ['before', 'after', 'first-child', 'last-child']
	 * @param	integer	The primary key of the node to move.
	 * @return	boolean	True on success.
	 * @since	1.6
	 * @link	http://docs.joomla.org/JTableNested/moveByReference
	 */

	public function moveByReference($referenceId, $position = 'after', $pk = null)
	{
		if ($this->_debug) {
			echo "\nMoving ReferenceId:$referenceId, Position:$position, PK:$pk";
		}

		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the node by id.
		if (!$node = $this->_getNode($pk)) {
			// Error message set in getNode method.
			return false;
		}

		// Get the ids of child nodes.
		$query = $this->_db->getQuery(true);
		$query->select($k);
		$query->from($this->_tbl);
		$query->where('lft BETWEEN '.(int) $node->lft.' AND '.(int) $node->rgt);
		$this->_db->setQuery($query);
		$children = $this->_db->loadResultArray();

		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			return false;
		}
		if ($this->_debug) {
			$this->_logtable(false);
		}

		// Cannot move the node to be a child of itself.
		if (in_array($referenceId, $children))
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_INVALID_NODE_RECURSION', get_class($this)));
			$this->setError($e);
			return false;
		}

		// Lock the table for writing.
		if (!$this->_lock()) return false;

		// We are moving the tree relative to a reference node.
		if ($referenceId)
		{
			// Get the reference node by primary key.
			if (!$reference = $this->_getNode($referenceId))
			{
				// Error message set in getNode method.
				$this->_unlock();
				return false;
			}

			// If moving "down" the tree, adjust $reference lft, rgt for $node width
			if ($node->rgt < $reference->rgt)
			{
				$reference->lft -= $node->width;
				$reference->rgt -= $node->width;
			}

			// Get the reposition data for shifting the tree and re-inserting the node.
			if (!$repositionData = $this->_getTreeRepositionData($reference, $node->width, $position))
			{
				// Error message set in getNode method.
				$this->_unlock();
				return false;
			}
		}

		// We are moving the tree to be a new root node.
		else
		{
			// Get the last root node as the reference node.
			$query = $this->_db->getQuery(true);
			$query->select($this->_tbl_key.', parent_id, level, lft, rgt');
			$query->from($this->_tbl);
			$query->where('parent_id = 0');
			$query->order('lft DESC');
			$this->_db->setQuery($query, 0, 1);
			$reference = $this->_db->loadObject();

			// Check for a database error.
			if ($this->_db->getErrorNum())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);
				$this->_unlock();
				return false;
			}
			if ($this->_debug) {
				$this->_logtable(false);
			}

			// Get the reposition data for re-inserting the node after the found root.
			if (!$repositionData = $this->_getTreeRepositionData($reference, $node->width, 'after'))
			{
				// Error message set in getNode method.
				$this->_unlock();
				return false;
			}
		}

		/*
		 * Move the sub-tree out of the nested sets by negating its left and right values.
		 */
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('lft = lft * (-1), rgt = rgt * (-1)');
		$query->where('lft BETWEEN '.(int) $node->lft.' AND '.(int) $node->rgt);
		$this->_db->setQuery($query);

		// Check for a database error.
		if (!$this->_db->query())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			$this->_unlock();
			return false;
		}
		if ($this->_debug) {
			$this->_logtable();
		}

		/*
		 * Close the hole in the tree that was opened by removing the sub-tree from the nested sets.
		 */
		// Compress the left values.
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('lft = lft - '.(int) $node->width);
		$query->where('lft > '.(int) $node->rgt);
		$this->_db->setQuery($query);

		// Check for a database error.
		if (!$this->_db->query())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			$this->_unlock();
			return false;
		}
		if ($this->_debug) {
			$this->_logtable();
		}

		// Compress the right values.
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('rgt = rgt - '.(int) $node->width);
		$query->where('rgt > '.(int) $node->rgt);
		$this->_db->setQuery($query);

		// Check for a database error.
		if (!$this->_db->query())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			$this->_unlock();
			return false;
		}
		if ($this->_debug) {
			$this->_logtable();
		}

		/*
		 * Create space in the nested sets at the new location for the moved sub-tree.
		 */
		// Shift left values.
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('lft = lft + '.(int) $node->width);
		$query->where($repositionData->left_where);
		$this->_db->setQuery($query);

		// Check for a database error.
		if (!$this->_db->query())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			$this->_unlock();
			return false;
		}
		if ($this->_debug) {
			$this->_logtable();
		}

		// Shift right values.
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('rgt = rgt + '.(int) $node->width);
		$query->where($repositionData->right_where);
		$this->_db->setQuery($query);

		// Check for a database error.
		if (!$this->_db->query())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			$this->_unlock();
			return false;
		}
		if ($this->_debug) {
			$this->_logtable();
		}

		/*
		 * Calculate the offset between where the node used to be in the tree and
		 * where it needs to be in the tree for left ids (also works for right ids).
		 */
		$offset = $repositionData->new_lft - $node->lft;
		$levelOffset = $repositionData->new_level - $node->level;

		// Move the nodes back into position in the tree using the calculated offsets.
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('rgt = '.(int) $offset.' - rgt');
		$query->set('lft = '.(int) $offset.' - lft');
		$query->set('level = level + '.(int) $levelOffset);
		$query->where('lft < 0');
		$this->_db->setQuery($query);

		// Check for a database error.
		if (!$this->_db->query())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			$this->_unlock();
			return false;
		}
		if ($this->_debug) {
			$this->_logtable();
		}

		// Set the correct parent id for the moved node if required.
		if ($node->parent_id != $repositionData->new_parent_id)
		{
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('parent_id = '.(int) $repositionData->new_parent_id);
			$query->where($this->_tbl_key.' = '.(int) $node->$k);
			$this->_db->setQuery($query);

			// Check for a database error.
			if (!$this->_db->query())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);
				$this->_unlock();
				return false;
			}
			if ($this->_debug) {
				$this->_logtable();
			}
		}

		// Unlock the table for writing.
		$this->_unlock();

		// Set the object values.
		$this->parent_id = $repositionData->new_parent_id;
		$this->level = $repositionData->new_level;
		$this->lft = $repositionData->new_lft;
		$this->rgt = $repositionData->new_rgt;

		return true;
	}

	/**
	 * Method to delete a node, and optionally its child nodes, from the table.
	 *
	 * @param	integer	The primary key of the node to delete.
	 * @param	boolean	True to delete child nodes, false to move them up a level.
	 * @return	boolean	True on success.
	 * @since	1.6
	 * @link	http://docs.joomla.org/JTableNested/delete
	 */
	public function delete($pk = null, $children = true)
	{
		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Lock the table for writing.
		if (!$this->_lock()) {
			// Error message set in lock method.
			return false;
		}

		// If tracking assets, remove the asset first.
		if ($this->_trackAssets) 
		{
			$name		= $this->_getAssetName();
			$asset		= JTable::getInstance('Asset');
			
			// Lock the table for writing.
			if (!$asset->_lock()) 
			{
				// Error message set in lock method.
				return false;
			}
			if ($asset->loadByName($name)) 
			{
				// Delete the node in assets table.
				if (!$asset->delete()) 
				{
					$this->setError($asset->getError());
					$asset->_unlock();
					return false;
				}
				$asset->_unlock();
			}
			else 
			{
				$this->setError($asset->getError());
				$asset->_unlock();
				return false;
			}
		}
		
		// Get the node by id.
		if (!$node = $this->_getNode($pk))
		{
			// Error message set in getNode method.
			$this->_unlock();
			return false;
		}

		// Should we delete all children along with the node?
		if ($children)
		{
			// Delete the node and all of its children.
			$query = $this->_db->getQuery(true);
			$query->delete();
			$query->from($this->_tbl);
			$query->where('lft BETWEEN '.(int) $node->lft.' AND '.(int) $node->rgt);
			$this->_db->setQuery($query);

			// Check for a database error.
			if (!$this->_db->query())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);
				$this->_unlock();
				return false;
			}

			// Compress the left values.
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('lft = lft - '.(int) $node->width);
			$query->where('lft > '.(int) $node->rgt);
			$this->_db->setQuery($query);

			// Check for a database error.
			if (!$this->_db->query())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);
				$this->_unlock();
				return false;
			}

			// Compress the right values.
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('rgt = rgt - '.(int) $node->width);
			$query->where('rgt > '.(int) $node->rgt);
			$this->_db->setQuery($query);

			// Check for a database error.
			if (!$this->_db->query())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);
				$this->_unlock();
				return false;
			}
		}

		// Leave the children and move them up a level.
		else
		{
			// Delete the node.
			$query = $this->_db->getQuery(true);
			$query->delete();
			$query->from($this->_tbl);
			$query->where('lft = '.(int) $node->lft);
			$this->_db->setQuery($query);


			// Check for a database error.
			if (!$this->_db->query())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);
				$this->_unlock();
				return false;
			}

			// Shift all node's children up a level.
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('lft = lft - 1');
			$query->set('rgt = rgt - 1');
			$query->set('level = level - 1');
			$query->where('lft BETWEEN '.(int) $node->lft.' AND '.(int) $node->rgt);
			$this->_db->setQuery($query);

			// Check for a database error.
			if (!$this->_db->query())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);
				$this->_unlock();
				return false;
			}

			// Adjust all the parent values for direct children of the deleted node.
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('parent_id = '.(int) $node->parent_id);
			$query->where('parent_id = '.(int) $node->$k);
			$this->_db->setQuery($query);

			// Check for a database error.
			if (!$this->_db->query())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);
				$this->_unlock();
				return false;
			}

			// Shift all of the left values that are right of the node.
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('lft = lft - 2');
			$query->where('lft > '.(int) $node->rgt);
			$this->_db->setQuery($query);

			// Check for a database error.
			if (!$this->_db->query())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);
				$this->_unlock();
				return false;
			}

			// Shift all of the right values that are right of the node.
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('rgt = rgt - 2');
			$query->where('rgt > '.(int) $node->rgt);
			$this->_db->setQuery($query);

			// Check for a database error.
			if (!$this->_db->query())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);
				$this->_unlock();
				return false;
			}
		}

		// Unlock the table for writing.
		$this->_unlock();

		return true;
	}

	/**
	 * Asset that the nested set data is valid.
	 *
	 * @return	boolean	True if the instance is sane and able to be stored in the database.
	 * @since	1.0
	 * @link	http://docs.joomla.org/JTable/check
	 */
	public function check()
	{
		$this->parent_id = (int) $this->parent_id;
		if ($this->parent_id > 0)
		{
			$query = $this->_db->getQuery(true);
			$query->select('COUNT(id)');
			$query->from($this->_tbl);
			$query->where('id = '.$this->parent_id);
			$this->_db->setQuery($query);

			if ($this->_db->loadResult()) {
				return true;
			}
			else
			{
				if ($this->_db->getErrorNum())
				{
					$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_CHECK_FAILED', get_class($this), $this->_db->getErrorMsg()));
					$this->setError($e);
				}
				else
				{
					$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_INVALID_PARENT_ID', get_class($this)));
					$this->setError($e);
				}
			}
		}
		else
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_INVALID_PARENT_ID', get_class($this)));
			$this->setError($e);
		}

		return false;
	}

	/**
	 * Method to store a node in the database table.
	 *
	 * @param	boolean	True to update null values as well.
	 * @return	boolean	True on success.
	 * @since	1.6
	 * @link	http://docs.joomla.org/JTableNested/store
	 */
	public function store($updateNulls = false)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		if ($this->_debug) {
			echo "\n".get_class($this)."::store\n";
			$this->_logtable(true, false);
		}
		/*
		 * If the primary key is empty, then we assume we are inserting a new node into the
		 * tree.  From this point we would need to determine where in the tree to insert it.
		 */
		if (empty($this->$k))
		{
			/*
			 * We are inserting a node somewhere in the tree with a known reference
			 * node.  We have to make room for the new node and set the left and right
			 * values before we insert the row.
			 */
			if ($this->_location_id >= 0)
			{
				// Lock the table for writing.
				if (!$this->_lock()) {
					// Error message set in lock method.
					return false;
				}

				// We are inserting a node relative to the last root node.
				if ($this->_location_id == 0)
				{
					// Get the last root node as the reference node.
					$query = $this->_db->getQuery(true);
					$query->select($this->_tbl_key.', parent_id, level, lft, rgt');
					$query->from($this->_tbl);
					$query->where('parent_id = 0');
					$query->order('lft DESC');
					$this->_db->setQuery($query, 0, 1);
					$reference = $this->_db->loadObject();

					// Check for a database error.
					if ($this->_db->getErrorNum())
					{
						$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), $this->_db->getErrorMsg()));
						$this->setError($e);
						$this->_unlock();
						return false;
					}
					if ($this->_debug) {
						$this->_logtable(false);
					}
				}

				// We have a real node set as a location reference.
				else
				{
					// Get the reference node by primary key.
					if (!$reference = $this->_getNode($this->_location_id))
					{
						// Error message set in getNode method.
						$this->_unlock();
						return false;
					}
				}

				// Get the reposition data for shifting the tree and re-inserting the node.
				if (!($repositionData = $this->_getTreeRepositionData($reference, 2, $this->_location)))
				{
					// Error message set in getNode method.
					$this->_unlock();
					return false;
				}

				// Create space in the tree at the new location for the new node in left ids.
				$query = $this->_db->getQuery(true);
				$query->update($this->_tbl);
				$query->set('lft = lft + 2');
				$query->where($repositionData->left_where);
				$this->_db->setQuery($query);

				// Check for a database error.
				if (!$this->_db->query())
				{
					$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), $this->_db->getErrorMsg()));
					$this->setError($e);
					$this->_unlock();
					return false;
				}
				if ($this->_debug) {
					$this->_logtable();
				}

				// Create space in the tree at the new location for the new node in right ids.
				$query = $this->_db->getQuery(true);
				$query->update($this->_tbl);
				$query->set('rgt = rgt + 2');
				$query->where($repositionData->right_where);
				$this->_db->setQuery($query);

				// Check for a database error.
				if (!$this->_db->query())
				{
					$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), $this->_db->getErrorMsg()));
					$this->setError($e);
					$this->_unlock();
					return false;
				}
				if ($this->_debug) {
					$this->_logtable();
				}

				// Set the object values.
				$this->parent_id	= $repositionData->new_parent_id;
				$this->level		= $repositionData->new_level;
				$this->lft			= $repositionData->new_lft;
				$this->rgt			= $repositionData->new_rgt;
			}
			else
			{
				// Negative parent ids are invalid
				$e = new JException(JText::_('JLIB_DATABASE_ERROR_INVALID_PARENT_ID'));
				$this->setError($e);
				return false;
			}
		}

		/*
		 * If we have a given primary key then we assume we are simply updating this
		 * node in the tree.  We should assess whether or not we are moving the node
		 * or just updating its data fields.
		 */
		else
		{
			// If the location has been set, move the node to its new location.
			if ($this->_location_id > 0)
			{
				if (!$this->moveByReference($this->_location_id, $this->_location, $this->$k)) {
					// Error message set in move method.
					return false;
				}
			}

			// Lock the table for writing.
			if (!$this->_lock()) {
				// Error message set in lock method.
				return false;
			}
		}

		// Store the row to the database.
		if (!parent::store())
		{
			$this->_unlock();
			return false;
		}
		if ($this->_debug) {
			$this->_logtable();
		}

		// Unlock the table for writing.
		$this->_unlock();

		return true;
	}

	/**
	 * Method to set the publishing state for a node or list of nodes in the database
	 * table.  The method respects rows checked out by other users and will attempt
	 * to checkin rows that it can after adjustments are made.  The method will now
	 * allow you to set a publishing state higher than any ancestor node and will
	 * not allow you to set a publishing state on a node with a checked out child.
	 *
	 * @param	mixed	An optional array of primary key values to update.  If not
	 *					set the instance property value is used.
	 * @param	integer The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param	integer The user id of the user performing the operation.
	 * @return	boolean	True on success.
	 * @since	1.0.4
	 * @link	http://docs.joomla.org/JTableNested/publish
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state  = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k) {
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED', get_class($this)));
				$this->setError($e);
				return false;
			}
		}

		// Determine if there is checkout support for the table.
		$checkoutSupport = (property_exists($this, 'checked_out') || property_exists($this, 'checked_out_time'));

		// Iterate over the primary keys to execute the publish action if possible.
		foreach ($pks as $pk)
		{
			// Get the node by primary key.
			if (!$node = $this->_getNode($pk)) {
				// Error message set in getNode method.
				return false;
			}

			// If the table has checkout support, verify no children are checked out.
			if ($checkoutSupport)
			{
				// Ensure that children are not checked out.
				$query = $this->_db->getQuery(true);
				$query->select('COUNT('.$this->_tbl_key.')');
				$query->from($this->_tbl);
				$query->where('lft BETWEEN '.(int) $node->lft.' AND '.(int) $node->rgt);
				$query->where('(checked_out <> 0 AND checked_out <> '.(int) $userId.')');
				$this->_db->setQuery($query);

				// Check for checked out children.
				if ($this->_db->loadResult())
				{
					$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_CHILD_ROWS_CHECKED_OUT', get_class($this)));
					$this->setError($e);
					return false;
				}
			}

			// If any parent nodes have lower published state values, we cannot continue.
			if ($node->parent_id)
			{
				// Get any ancestor nodes that have a lower publishing state.
				$query = $this->_db->getQuery(true);
				$query->select('p.'.$k);
				$query->from($this->_tbl.' AS n, '.$this->_tbl.' AS p');
				$query->where('n.lft BETWEEN p.lft AND p.rgt');
				$query->where('n.'.$k.' = '.(int) $pk);
				$query->where('p.parent_id > 0');
				$query->where('p.published < '.(int) $state);
				$query->order('p.lft DESC');
				$this->_db->setQuery($query, 1,0);


				$rows = $this->_db->loadResultArray();

				// Check for a database error.
				if ($this->_db->getErrorNum())
				{
					$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_PUBLISH_FAILED', get_class($this), $this->_db->getErrorMsg()));
					$this->setError($e);
					return false;
				}

				if (!empty($rows))
				{
					$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_ANCESTOR_NODES_LOWER_PUBLISHED_STATE', get_class($this)));
					$this->setError($e);
					return false;
				}
			}

			// Update the publishing state.
			$query = $this->_db->getQuery(true);
			$query->update($this->_tbl);
			$query->set('published = '.(int) $state);
			$query->where($this->_tbl_key.' = '.(int) $pk);
			$this->_db->setQuery($query);

			// Check for a database error.
			if (!$this->_db->query())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_PUBLISH_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);
				return false;
			}

			// If checkout support exists for the object, check the row in.
			if ($checkoutSupport) $this->checkin($pk);

		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks)) $this->published = $state;

		$this->setError('');
		return true;
	}

	/**
	 * Method to move a node one position to the left in the same level.
	 *
	 * @param	integer	Primary key of the node to move.
	 * @return	boolean	True on success.
	 * @since	1.6
	 * @link	http://docs.joomla.org/JTableNested/orderUp
	 */
	public function orderUp($pk)
	{
		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Lock the table for writing.
		if (!$this->_lock()) {
			// Error message set in lock method.
			return false;
		}

		// Get the node by primary key.
		if (!$node = $this->_getNode($pk))
		{
			// Error message set in getNode method.
			$this->_unlock();
			return false;
		}

		// Get the left sibling node.
		if (!$sibling = $this->_getNode($node->lft - 1, 'right'))
		{
			// Error message set in getNode method.
			$this->_unlock();
			return false;
		}

		// Get the primary keys of child nodes.
		$query = $this->_db->getQuery(true);
		$query->select($this->_tbl_key);
		$query->from($this->_tbl);
		$query->where('lft BETWEEN '.(int) $node->lft.' AND '.(int) $node->rgt);
		$this->_db->setQuery($query);
		$children = $this->_db->loadResultArray();

		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_ORDERUP_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			$this->_unlock();
			return false;
		}

		// Shift left and right values for the node and it's children.
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('lft = lft - '.(int) $sibling->width);
		$query->set('rgt = rgt - '.(int) $sibling->width);
		$query->where('lft BETWEEN '.(int) $node->lft.' AND '.(int) $node->rgt);
		$this->_db->setQuery($query);

		// Check for a database error.
		if (!$this->_db->query())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_ORDERUP_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			$this->_unlock();
			return false;
		}

		// Shift left and right values for the sibling and it's children.
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('lft = lft + '.(int) $node->width);
		$query->set('rgt = rgt + '.(int) $node->width);
		$query->where('lft BETWEEN '.(int) $sibling->lft.' AND '.(int) $sibling->rgt);
		$query->where($this->_tbl_key.' NOT IN ('.implode(',', $children).')');
		$this->_db->setQuery($query);

		// Check for a database error.
		if (!$this->_db->query())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_ORDERUP_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			$this->_unlock();
			return false;
		}

		// Unlock the table for writing.
		$this->_unlock();

		return true;
	}

	/**
	 * Method to move a node one position to the right in the same level.
	 *
	 * @param	integer	Primary key of the node to move.
	 * @return	boolean	True on success.
	 * @since	1.6
	 * @link	http://docs.joomla.org/JTableNested/orderDown
	 */
	public function orderDown($pk)
	{
		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Lock the table for writing.
		if (!$this->_lock()) {
			// Error message set in lock method.
			return false;
		}

		// Get the node by primary key.
		if (!$node = $this->_getNode($pk))
		{
			// Error message set in getNode method.
			$this->_unlock();
			return false;
		}

		// Get the right sibling node.
		if (!$sibling = $this->_getNode($node->rgt + 1, 'left'))
		{
			// Error message set in getNode method.
			$this->_unlock();
			return false;
		}

		// Get the primary keys of child nodes.
		$query = $this->_db->getQuery(true);
		$query->select($this->_tbl_key);
		$query->from($this->_tbl);
		$query->where('lft BETWEEN '.(int) $node->lft.' AND '.(int) $node->rgt);
		$this->_db->setQuery($query);
		$children = $this->_db->loadResultArray();

		// Check for a database error.
		if ($this->_db->getErrorNum())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_ORDERDOWN_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			$this->_unlock();
			return false;
		}

		// Shift left and right values for the node and it's children.
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('lft = lft + '.(int) $sibling->width);
		$query->set('rgt = rgt + '.(int) $sibling->width);
		$query->where('lft BETWEEN '.(int) $node->lft.' AND '.(int) $node->rgt);
		$this->_db->setQuery($query);

		// Check for a database error.
		if (!$this->_db->query())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_ORDERDOWN_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			$this->_unlock();
			return false;
		}

		// Shift left and right values for the sibling and it's children.
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('lft = lft - '.(int) $node->width);
		$query->set('rgt = rgt - '.(int) $node->width);
		$query->where('lft BETWEEN '.(int) $sibling->lft.' AND '.(int) $sibling->rgt);
		$query->where($this->_tbl_key.' NOT IN ('.implode(',', $children).')');
		$this->_db->setQuery($query);

		// Check for a database error.
		if (!$this->_db->query())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_ORDERDOWN_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			$this->_unlock();
			return false;
		}

		// Unlock the table for writing.
		$this->_unlock();

		return true;
	}

	/**
	 * Gets the ID of the root item in the tree
	 *
	 * @return	mixed	The ID of the root row, or false and the internal error is set.
	 */
	public function getRootId()
	{
		// Get the root item.
		$k = $this->_tbl_key;

		// Test for a unique record with parent_id = 0
		$query = $this->_db->getQuery(true);
		$query->select($k);
		$query->from($this->_tbl);
		$query->where('parent_id = 0');
		$this->_db->setQuery($query);

		$result = $this->_db->loadResultArray();

		if ($this->_db->getErrorNum())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_GETROOTID_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			return false;
		}

		if (count($result) == 1) {
			$parentId = $result[0];
		}
		else
		{
			// Test for a unique record with lft = 0
			$query = $this->_db->getQuery(true);
			$query->select($k);
			$query->from($this->_tbl);
			$query->where('lft = 0');
			$this->_db->setQuery($query);

			$result = $this->_db->loadResultArray();
			if ($this->_db->getErrorNum())
			{
				$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_GETROOTID_FAILED', get_class($this), $this->_db->getErrorMsg()));
				$this->setError($e);
				return false;
			}

			if (count($result) == 1) {
				$parentId = $result[0];
			}
			elseif (property_exists($this, 'alias'))
			{
				// Test for a unique record with lft = 0
				$query = $this->_db->getQuery(true);
				$query->select($k);
				$query->from($this->_tbl);
				$query->where('alias = '.$this->_db->quote('root'));
				$this->_db->setQuery($query);

				$result = $this->_db->loadResultArray();
				if ($this->_db->getErrorNum())
				{
					$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_GETROOTID_FAILED', get_class($this), $this->_db->getErrorMsg()));
					$this->setError($e);
					return false;
				}

				if (count($result) == 1) {
					$parentId = $result[0];
				}
				else
				{
					$e = new JException(JText::_('JLIB_DATABASE_ERROR_ROOT_NODE_NOT_FOUND'));
					$this->setError($e);
					return false;
				}
			}
			else
			{
				$e = new JException(JText::_('JLIB_DATABASE_ERROR_ROOT_NODE_NOT_FOUND'));
				$this->setError($e);
				return false;
			}
		}

		return $parentId;
	}

	/**
	 * Method to recursively rebuild the whole nested set tree.
	 *
	 * @param	integer	The root of the tree to rebuild.
	 * @param	integer	The left id to start with in building the tree.
	 * @param	integer	The level to assign to the current nodes.
	 * @param	string	The path to the current nodes.
	 * @return	boolean	True on success
	 * @since	1.6
	 * @link	http://docs.joomla.org/JTableNested/rebuild
	 */
	public function rebuild($parentId = null, $leftId = 0, $level = 0, $path = '')
	{
		// If no parent is provided, try to find it.
		if ($parentId === null)
		{
			// Get the root item.
			$parentId = $this->getRootId();
			if ($parentId === false) return false;

		}

		// Build the structure of the recursive query.
		if (!isset($this->_cache['rebuild.sql']))
		{
			$query	= $this->_db->getQuery(true);
			$query->select('id, alias');
			$query->from($this->_tbl);
			$query->where('parent_id = %d');

			// If the table has an `ordering` field, use that for ordering.
			if (property_exists($this, 'ordering')) {
				$query->order('parent_id, ordering, lft');
			} else {
				$query->order('parent_id, lft');
			}
			$this->_cache['rebuild.sql'] = (string) $query;
		}

		// Make a shortcut to database object.

		// Assemble the query to find all children of this node.
		$this->_db->setQuery(sprintf($this->_cache['rebuild.sql'], (int) $parentId));
		$children = $this->_db->loadObjectList();

		// The right value of this node is the left value + 1
		$rightId = $leftId + 1;

		// execute this function recursively over all children
		foreach ($children as $node)
		{
			// $rightId is the current right value, which is incremented on recursion return.
			// Increment the level for the children.
			// Add this item's alias to the path (but avoid a leading /)
			$rightId = $this->rebuild($node->id, $rightId, $level + 1, $path.(empty($path) ? '' : '/').$node->alias);

			// If there is an update failure, return false to break out of the recursion.
			if ($rightId === false) return false;
		}

		// We've got the left value, and now that we've processed
		// the children of this node we also know the right value.
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('lft = '. (int) $leftId);
		$query->set('rgt = '. (int) $rightId);
		$query->set('level = '.(int) $level);
		$query->set('path = '.$this->_db->quote($path));
		$query->where('id = '. (int)$parentId);
		$this->_db->setQuery($query);

		// If there is an update failure, return false to break out of the recursion.
		if (!$this->_db->query())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_REBUILD_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			return false;
		}

		// Return the right value of this node + 1.
		return $rightId + 1;
	}

	/**
	 * Method to rebuild the node's path field from the alias values of the
	 * nodes from the current node to the root node of the tree.
	 *
	 * @param	integer	Primary key of the node for which to get the path.
	 * @return	boolean	True on success.
	 * @since	1.6
	 * @link	http://docs.joomla.org/JTableNested/rebuildPath
	 */
	public function rebuildPath($pk = null)
	{
		// If there is no alias or path field, just return true.
		if (!property_exists($this, 'alias') || !property_exists($this, 'path')) {
			return true;
		}

		// Initialise variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the aliases for the path from the node to the root node.
		$query = $this->_db->getQuery(true);
		$query->select('p.alias');
		$query->from($this->_tbl.' AS n, '.$this->_tbl.' AS p');
		$query->where('n.lft BETWEEN p.lft AND p.rgt');
		$query->where('n.'.$this->_tbl_key.' = '. (int) $pk);
		$query->order('p.lft');
		$this->_db->setQuery($query);

		$segments = $this->_db->loadResultArray();

		// Make sure to remove the root path if it exists in the list.
		if ($segments[0] == 'root') {
			array_shift($segments);
		}

		// Build the path.
		$path = trim(implode('/', $segments), ' /\\');

		// Update the path field for the node.
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('path = '.$this->_db->quote($path));
		$query->where($this->_tbl_key.' = '.(int) $pk);
		$this->_db->setQuery($query);

		// Check for a database error.
		if (!$this->_db->query())
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_REBUILDPATH_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			return false;
		}

		return true;
	}
	
	/**
	 * Method to update order of table rows
	 *
	 * @param	array	id's of rows to be reordered
	 * @param	array	lft values of rows to be reordered
	 *
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	public function saveorder($idArray = null, $lft_array = null)
	{
		// Validate arguments
		if (is_array($idArray) && is_array($lft_array) && count($idArray == count($lft_array)))
		{
			for ($i = 0; $i < count($idArray); $i++)
			{
				// Do an update to change the lft values in the table for each id
				$query = $this->_db->getQuery(true);
				$query->update($this->_tbl);
				$query->where($this->_tbl_key . ' = ' . (int) $idArray[$i]);
				$query->set('lft = ' . (int) $lft_array[$i]);
				$this->_db->setQuery($query);

				// Check for a database error.
				if (!$this->_db->query())
				{
					$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_REORDER_FAILED', get_class($this), $this->_db->getErrorMsg()));
					$this->setError($e);
					$this->_unlock();
					return false;
				}
				if ($this->_debug)
				{
					$this->_logtable();
				}

			}
			return $this->rebuild();
		}
	}

	/**
	 * Method to get nested set properties for a node in the tree.
	 *
	 * @param	integer	Value to look up the node by.
	 * @param	string	Key to look up the node by.
	 * @return	mixed	Boolean false on failure or node object on success.
	 * @since	1.6
	 */
	protected function _getNode($id, $key = null)
	{
		// Determine which key to get the node base on.
		switch ($key)
		{
			case 'parent':
				$k = 'parent_id';
				break;
			case 'left':
				$k = 'lft';
				break;
			case 'right':
				$k = 'rgt';
				break;
			default:
				$k = $this->_tbl_key;
				break;
		}

		// Get the node data.
		$query = $this->_db->getQuery(true);
		$query->select($this->_tbl_key.', parent_id, level, lft, rgt');
		$query->from($this->_tbl);
		$query->where($k.' = '.(int) $id);
		$this->_db->setQuery($query, 0, 1);

		$row = $this->_db->loadObject();

		// Check for a database error or no $row returned
		if ((!$row) || ($this->_db->getErrorNum()))
		{
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_GETNODE_FAILED', get_class($this), $this->_db->getErrorMsg()));
			$this->setError($e);
			return false;
		}

		// Do some simple calculations.
		$row->numChildren = (int) ($row->rgt - $row->lft - 1) / 2;
		$row->width = (int) $row->rgt - $row->lft + 1;

		return $row;
	}

	/**
	 * Method to get various data necessary to make room in the tree at a location
	 * for a node and its children.  The returned data object includes conditions
	 * for SQL WHERE clauses for updating left and right id values to make room for
	 * the node as well as the new left and right ids for the node.
	 *
	 * @param	object	A node object with at least a 'lft' and 'rgt' with
	 *					which to make room in the tree around for a new node.
	 * @param	integer	The width of the node for which to make room in the tree.
	 * @param	string	The position relative to the reference node where the room
	 *					should be made.
	 * @return	mixed	Boolean false on failure or data object on success.
	 * @since	1.6
	 */
	protected function _getTreeRepositionData($referenceNode, $nodeWidth, $position = 'before')
	{
		// Make sure the reference an object with a left and right id.
		if (!is_object($referenceNode) && isset($referenceNode->lft) && isset($referenceNode->rgt)) {
			return false;
		}

		// A valid node cannot have a width less than 2.
		if ($nodeWidth < 2) return false;

		// Initialise variables.
		$k = $this->_tbl_key;
		$data = new stdClass;

		// Run the calculations and build the data object by reference position.
		switch ($position)
		{
			case 'first-child':
				$data->left_where		= 'lft > '.$referenceNode->lft;
				$data->right_where		= 'rgt >= '.$referenceNode->lft;

				$data->new_lft			= $referenceNode->lft + 1;
				$data->new_rgt			= $referenceNode->lft + $nodeWidth;
				$data->new_parent_id	= $referenceNode->$k;
				$data->new_level		= $referenceNode->level + 1;
				break;

			case 'last-child':
				$data->left_where		= 'lft > '.($referenceNode->rgt);
				$data->right_where		= 'rgt >= '.($referenceNode->rgt);

				$data->new_lft			= $referenceNode->rgt;
				$data->new_rgt			= $referenceNode->rgt + $nodeWidth - 1;
				$data->new_parent_id	= $referenceNode->$k;
				$data->new_level		= $referenceNode->level + 1;
				break;

			case 'before':
				$data->left_where		= 'lft >= '.$referenceNode->lft;
				$data->right_where		= 'rgt >= '.$referenceNode->rgt;

				$data->new_lft			= $referenceNode->lft;
				$data->new_rgt			= $referenceNode->lft + $nodeWidth - 1;
				$data->new_parent_id	= $referenceNode->parent_id;
				$data->new_level		= $referenceNode->level;
				break;

			default:
			case 'after':
				$data->left_where		= 'lft > '.$referenceNode->lft;
				$data->right_where		= 'rgt > '.$referenceNode->rgt;

				$data->new_lft			= $referenceNode->rgt + 1;
				$data->new_rgt			= $referenceNode->rgt + $nodeWidth;
				$data->new_parent_id	= $referenceNode->parent_id;
				$data->new_level		= $referenceNode->level;
				break;
		}

		if ($this->_debug)
		{
			echo "\nRepositioning Data for $position" .
					"\n-----------------------------------" .
					"\nLeft Where:    $data->left_where" .
					"\nRight Where:   $data->right_where" .
					"\nNew Lft:       $data->new_lft" .
					"\nNew Rgt:       $data->new_rgt".
					"\nNew Parent ID: $data->new_parent_id".
					"\nNew Level:     $data->new_level" .
					"\n";
		}

		return $data;
	}

	protected function _logtable($showData = true, $showQuery = true)
	{
		$sep	= "\n".str_pad('', 40, '-');
		$buffer	= '';
		if ($showQuery) {
			$buffer .= "\n".$this->_db->getQuery().$sep;
		}

		if ($showData)
		{
			$query = $this->_db->getQuery(true);
			$query->select('id, parent_id, lft, rgt, level');
			$query->from($this->_tbl);
			$query->order('id');
			$this->_db->setQuery($query);

			$rows = $this->_db->loadRowList();
			$buffer .= sprintf("\n| %4s | %4s | %4s | %4s |", 'id', 'par', 'lft', 'rgt');
			$buffer .= $sep;

			foreach ($rows as $row) {
				$buffer .= sprintf("\n| %4s | %4s | %4s | %4s |", $row[0], $row[1], $row[2], $row[3]);
			}
			$buffer .= $sep;
		}
		echo $buffer;
	}

}
