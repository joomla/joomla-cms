<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Database
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
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
	public $parent_id = null;

	/**
	 * Object property holding the depth level of the node in the tree.
	 *
	 * @var integer
	 */
	public $level = null;

	/**
	 * Object property holding the left value of the node for managing its
	 * placement in the nested sets tree.
	 *
	 * @var integer
	 */
	public $lft = null;

	/**
	 * Object property holding the right value of the node for managing its
	 * placement in the nested sets tree.
	 *
	 * @var integer
	 */
	public $rgt = null;

	/**
	 * Object property holding the alias of this node used to constuct the
	 * full text path, forward-slash delimited.
	 *
	 * @var string
	 */
	public $alias = null;

	/**
	 * Object property to hold the location type to use when storing the row.
	 * Possible values are: ['before', 'after', 'first-child', 'last-child'].
	 *
	 * @var string
	 */
	protected $_location = null;

	/**
	 * Object property to hold the primary key of the location reference node to
	 * use when storing the row.  A combination of location type and reference
	 * node describes where to store the current node in the tree.
	 *
	 * @var integer
	 */
	protected $_location_id = null;

	/**
	 * @var	array	An array to cache values in recursive processes.
	 */
	protected $_cache = array();

	protected $_debug = false;

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
		// Initialize variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the path from the node to the root.
		$select = ($diagnostic) ? 'SELECT p.'.$k.', p.parent_id, p.level, p.lft, p.rgt' : 'SELECT p.*';
		$this->_db->setQuery(
			$select .
			' FROM `'.$this->_tbl.'` AS n, `'.$this->_tbl.'` AS p' .
			' WHERE n.lft BETWEEN p.lft AND p.rgt' .
			' AND n.'.$k.' = '.(int) $pk .
			' ORDER BY p.lft'
		);
		$path = $this->_db->loadObjectList();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
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
		// Initialize variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the node and children as a tree.
		$select = ($diagnostic) ? 'SELECT n.'.$k.', n.parent_id, n.level, n.lft, n.rgt' : 'SELECT n.*';
		$this->_db->setQuery(
			$select .
			' FROM `'.$this->_tbl.'` AS n, `'.$this->_tbl.'` AS p' .
			' WHERE n.lft BETWEEN p.lft AND p.rgt' .
			' AND p.'.$k.' = '.(int) $pk .
			' ORDER BY n.lft'
		);
		$tree = $this->_db->loadObjectList();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
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
		// Initialize variables.
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
			return false;
		}

		// Set the location properties.
		$this->_location = $position;
		$this->_location_id = $referenceId;

		return true;
	}

	/**
	 * Method to move a node and its children to a new location in the tree.
	 *
	 * @param	integer	The primary key of the node to reference new location by.
	 * @param	string	Location type string. ['before', 'after', 'first-child', 'last-child']
	 * @param	integer	The primary key of the node to move.
	 * @return	boolean	True on success.
	 * @since	1.6
	 * @link	http://docs.joomla.org/JTableNested/move
	 */
	public function move($referenceId, $position = 'after', $pk = null)
	{
		// Initialize variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the node by id.
		if (!$node = $this->_getNode($pk)) {
			// Error message set in getNode method.
			return false;
		}

		// Get the ids of child nodes.
		$this->_db->setQuery(
			'SELECT `'.$k.'`' .
			' FROM `'.$this->_tbl.'`' .
			' WHERE `lft` BETWEEN '.(int) $node->lft.' AND '.(int) $node->rgt
		);
		$children = $this->_db->loadResultArray();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		if ($this->_debug) {
			$this->_logtable(false);
		}

		// Cannot move the node to be a child of itself.
		if (in_array($referenceId, $children)) {
			$this->setError(JText::_('Invalid_Node_Recursion'));
			return false;
		}

		// Lock the table for writing.
		if (!$this->_lock()) {
			// Error message set in lock method.
			return false;
		}

		// We are moving the tree relative to a reference node.
		if ($referenceId)
		{
			// Get the reference node by primary key.
			if (!$reference = $this->_getNode($referenceId)) {
				// Error message set in getNode method.
				$this->_unlock();
				return false;
			}

			// Get the reposition data for shifting the tree and re-inserting the node.
			if (!$repositionData = $this->_getTreeRepositionData($reference, $node->width, $position)) {
				// Error message set in getNode method.
				$this->_unlock();
				return false;
			}
		}

		// We are moving the tree to be a new root node.
		else
		{
			// Get the last root node as the reference node.
			$this->_db->setQuery(
				'SELECT `'.$this->_tbl_key.'`, `parent_id`, `level`, `lft`, `rgt`' .
				' FROM `'.$this->_tbl.'`' .
				' WHERE `parent_id` = 0' .
				' ORDER BY `lft` DESC',
				0, 1
			);
			$reference = $this->_db->loadObject();

			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				$this->_unlock();
				return false;
			}
			if ($this->_debug) {
				$this->_logtable(false);
			}

			// Get the reposition data for re-inserting the node after the found root.
			if (!$repositionData = $this->_getTreeRepositionData($reference, $node->width, 'after')) {
				// Error message set in getNode method.
				$this->_unlock();
				return false;
			}
		}

		/*
		 * Move the sub-tree out of the nested sets by negating its left and right values.
		 */
		$this->_db->setQuery(
			'UPDATE `'.$this->_tbl.'`' .
			' SET `lft` = `lft` * (-1), `rgt` = `rgt` * (-1)' .
			' WHERE `lft` BETWEEN '.(int) $node->lft.' AND '.(int) $node->rgt
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
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
		$this->_db->setQuery(
			'UPDATE `'.$this->_tbl.'`' .
			' SET `lft` = `lft` - '.(int) $node->width .
			' WHERE `lft` > '.(int) $node->rgt
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			$this->_unlock();
			return false;
		}
		if ($this->_debug) {
			$this->_logtable();
		}

		// Compress the right values.
		$this->_db->setQuery(
			'UPDATE `'.$this->_tbl.'`' .
			' SET `rgt` = `rgt` - '.(int) $node->width .
			' WHERE `rgt` > '.(int) $node->rgt
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
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
		$this->_db->setQuery(
			'UPDATE `'.$this->_tbl.'`' .
			' SET `lft` = `lft` + '.(int) $node->width .
			' WHERE '.$repositionData->left_where
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			$this->_unlock();
			return false;
		}
		if ($this->_debug) {
			$this->_logtable();
		}

		// Shift right values.
		$this->_db->setQuery(
			'UPDATE `'.$this->_tbl.'`' .
			' SET `rgt` = `rgt` + '.(int) $node->width .
			' WHERE '.$repositionData->right_where
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
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
		$this->_db->setQuery(
			'UPDATE `'.$this->_tbl.'`' .
			' SET `rgt` = '.(int) $offset.' - `rgt`,' .
			'	  `lft` = '.(int) $offset.' - `lft`,' .
			'	  `level` = `level` + '.(int) $levelOffset .
			' WHERE `lft` < 0'
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			$this->_unlock();
			return false;
		}
		if ($this->_debug) {
			$this->_logtable();
		}

		// Set the correct parent id for the moved node if required.
		if ($node->parent_id != $repositionData->new_parent_id)
		{
			$this->_db->setQuery(
				'UPDATE `'.$this->_tbl.'`' .
				' SET `parent_id` = '.(int) $repositionData->new_parent_id .
				' WHERE `'.$this->_tbl_key.'` = '.(int) $node->$k
			);
			$this->_db->query();

			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
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
		// Initialize variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Lock the table for writing.
		if (!$this->_lock()) {
			// Error message set in lock method.
			return false;
		}

		// Get the node by id.
		if (!$node = $this->_getNode($pk)) {
			// Error message set in getNode method.
			$this->_unlock();
			return false;
		}

		// Should we delete all children along with the node?
		if ($children)
		{
			// Delete the node and all of its children.
			$this->_db->setQuery(
				'DELETE FROM `'.$this->_tbl.'`' .
				' WHERE `lft` BETWEEN '.(int) $node->lft.' AND '.(int) $node->rgt
			);
			$this->_db->query();

			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				$this->_unlock();
				return false;
			}

			// Compress the left values.
			$this->_db->setQuery(
				'UPDATE `'.$this->_tbl.'`' .
				' SET `lft` = `lft` - '.(int) $node->width .
				' WHERE `lft` > '.(int) $node->rgt
			);
			$this->_db->query();

			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				$this->_unlock();
				return false;
			}

			// Compress the right values.
			$this->_db->setQuery(
				'UPDATE `'.$this->_tbl.'`' .
				' SET `rgt` = `rgt` - '.(int) $node->width .
				' WHERE `rgt` > '.(int) $node->rgt
			);
			$this->_db->query();

			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				$this->_unlock();
				return false;
			}
		}

		// Leave the children and move them up a level.
		else
		{
			// Delete the node.
			$this->_db->setQuery(
				'DELETE FROM `'.$this->_tbl.'`' .
				' WHERE `lft` = '.(int) $node->lft
			);
			$this->_db->query();

			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				$this->_unlock();
				return false;
			}

			// Shift all node's children up a level.
			$this->_db->setQuery(
				'UPDATE `'.$this->_tbl.'`' .
				' SET `lft` = `lft` - 1,' .
				'	  `rgt` = `rgt` - 1,' .
				'	  `level` = `level` - 1' .
				' WHERE `lft` BETWEEN '.(int) $node->lft.' AND '.(int) $node->rgt
			);
			$this->_db->query();

			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				$this->_unlock();
				return false;
			}

			// Adjust all the parent values for direct children of the deleted node.
			$this->_db->setQuery(
				'UPDATE `'.$this->_tbl.'`' .
				' SET `parent_id` = '.(int) $node->parent_id .
				' WHERE `parent_id` = '.(int) $node->$k
			);
			$this->_db->query();

			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				$this->_unlock();
				return false;
			}

			// Shift all of the left values that are right of the node.
			$this->_db->setQuery(
				'UPDATE `'.$this->_tbl.'`' .
				' SET `lft` = `lft` - 2' .
				' WHERE `lft` > '.(int) $node->rgt
			);
			$this->_db->query();

			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				$this->_unlock();
				return false;
			}

			// Shift all of the right values that are right of the node.
			$this->_db->setQuery(
				'UPDATE `'.$this->_tbl.'`' .
				' SET `rgt` = `rgt` - 2' .
				' WHERE `rgt` > '.(int) $node->rgt
			);
			$this->_db->query();

			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
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
			$this->_db->setQuery(
				'SELECT COUNT(id)' .
				' FROM '.$this->_db->nameQuote($this->_tbl).
				' WHERE `id` = '.$this->parent_id
			);
			if ($this->_db->loadResult()) {
				return true;
			}
			else
			{
				if ($error = $this->_db->getErrorMsg()) {
					$this->setError($error);
				}
				else {
					$this->setError('JError_Invalid_parent_id');
				}
			}
		}
		else {
			$this->setError('JError_Invalid_parent_id');
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
		// Initialize variables.
		$k = $this->_tbl_key;

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
					$this->_db->setQuery(
						'SELECT `'.$this->_tbl_key.'`, `parent_id`, `level`, `lft`, `rgt`' .
						' FROM `'.$this->_tbl.'`' .
						' WHERE `parent_id` = 0' .
						' ORDER BY `lft` DESC',
						0, 1
					);
					$reference = $this->_db->loadObject();

					// Check for a database error.
					if ($this->_db->getErrorNum()) {
						$this->setError($this->_db->getErrorMsg());
						$this->_unlock();
						return false;
					}
				}

				// We have a real node set as a location reference.
				else
				{
					// Get the reference node by primary key.
					if (!$reference = $this->_getNode($this->_location_id)) {
						// Error message set in getNode method.
						$this->_unlock();
						return false;
					}
				}

				// Get the reposition data for shifting the tree and re-inserting the node.
				if (!$repositionData = $this->_getTreeRepositionData($reference, 2, $this->_location)) {
					// Error message set in getNode method.
					$this->_unlock();
					return false;
				}

				// Create space in the tree at the new location for the new node in right ids.
				$this->_db->setQuery(
					'UPDATE `'.$this->_tbl.'`' .
					' SET `rgt` = `rgt` + 2' .
					' WHERE '.$repositionData->right_where
				);
				$this->_db->query();

				// Check for a database error.
				if ($this->_db->getErrorNum()) {
					$this->setError($this->_db->getErrorMsg());
					$this->_unlock();
					return false;
				}

				// Create space in the tree at the new location for the new node in left ids.
				$this->_db->setQuery(
					'UPDATE `'.$this->_tbl.'`' .
					' SET `lft` = `lft` + 2' .
					' WHERE '.$repositionData->left_where
				);
				$this->_db->query();

				// Check for a database error.
				if ($this->_db->getErrorNum()) {
					$this->setError($this->_db->getErrorMsg());
					$this->_unlock();
					return false;
				}

				// Set the object values.
				$this->parent_id	= $repositionData->new_parent_id;
				$this->level		= $repositionData->new_level;
				$this->lft		= $repositionData->new_lft;
				$this->rgt		= $repositionData->new_rgt;
			}
			else
			{
				// Negative parent ids are invalid
				$this->setError(JText::_('Invalid_Parent'));
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
				if (!$this->move($this->_location_id, $this->_location, $this->$k)) {
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
		if (!parent::store()) {
			$this->_unlock();
			return false;
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
	 * 					set the instance property value is used.
	 * @param	integer The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param	integer The user id of the user performing the operation.
	 * @return	boolean	True on success.
	 * @since	1.0.4
	 * @link	http://docs.joomla.org/JTableNested/publish
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialize variables.
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
			else {
				$this->setError(JText::_('No_Rows_Selected'));
				return false;
			}
		}

		// Determine if there is checkout support for the table.
		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time')) {
			$checkoutSupport = false;
		}
		else {
			$checkoutSupport = true;
		}

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
				$this->_db->setQuery(
					'SELECT COUNT('.$this->_tbl_key.')' .
					' FROM `'.$this->_tbl.'`' .
					' WHERE `lft` BETWEEN '.(int) $node->lft.' AND '.(int) $node->rgt .
					' AND (checked_out <> 0 AND checked_out <> '.(int) $userId.')'
				);

				// Check for checked out children.
				if ($this->_db->loadResult()) {
					$this->setError('Child_Rows_Checked_Out');
					return false;
				}
			}

			// If any parent nodes have lower published state values, we cannot continue.
			if ($node->parent_id)
			{
				// Get any ancestor nodes that have a lower publishing state.
				$this->_db->setQuery(
					'SELECT p.'.$k .
					' FROM `'.$this->_tbl.'` AS n, `'.$this->_tbl.'` AS p' .
					' WHERE n.lft BETWEEN p.lft AND p.rgt' .
					' AND n.'.$k.' = '.(int) $pk .
					' AND p.parent_id > 0' .
					' AND p.published < '.(int) $state .
					' ORDER BY p.lft DESC',
					1, 0
				);
				$rows = $this->_db->loadResultArray();

				// Check for a database error.
				if ($this->_db->getErrorNum()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}

				if (!empty($rows)) {
					$this->setError('Ancestor_Nodes_Lower_Published_State');
					return false;
				}
			}

			// Update the publishing state.
			$this->_db->setQuery(
				'UPDATE `'.$this->_tbl.'`' .
				' SET `published` = '.(int) $state .
				' WHERE `'.$this->_tbl_key.'` = '.(int) $pk
			);
			$this->_db->query();

			// Check for a database error.
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			// If checkout support exists for the object, check the row in.
			if ($checkoutSupport) {
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks)) {
			$this->published = $state;
		}

		$this->_errors = array();
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
		// Initialize variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Lock the table for writing.
		if (!$this->_lock()) {
			// Error message set in lock method.
			return false;
		}

		// Get the node by primary key.
		if (!$node = $this->_getNode($pk)) {
			// Error message set in getNode method.
			$this->_unlock();
			return false;
		}

		// Get the left sibling node.
		if (!$sibling = $this->_getNode($node->lft - 1, 'right')) {
			// Error message set in getNode method.
			$this->_unlock();
			return false;
		}

		// Get the primary keys of child nodes.
		$this->_db->setQuery(
			'SELECT `'.$this->_tbl_key.'`' .
			' FROM `'.$this->_tbl.'`' .
			' WHERE `lft` BETWEEN '.(int) $node->lft.' AND '.(int) $node->rgt
		);
		$children = $this->_db->loadResultArray();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			$this->_unlock();
			return false;
		}

		// Shift left and right values for the node and it's children.
		$this->_db->setQuery(
			'UPDATE `'.$this->_tbl.'`' .
			' SET `lft` = `lft` - '.(int) $sibling->width.', `rgt` = `rgt` - '.(int) $sibling->width.'' .
			' WHERE `lft` BETWEEN '.(int) $node->lft.' AND '.(int) $node->rgt
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			$this->_unlock();
			return false;
		}

		// Shift left and right values for the sibling and it's children.
		$this->_db->setQuery(
			'UPDATE `'.$this->_tbl.'`' .
			' SET `lft` = `lft` + '.(int) $node->width.', `rgt` = `rgt` + '.(int) $node->width .
			' WHERE `lft` BETWEEN '.(int) $sibling->lft.' AND '.(int) $sibling->rgt .
			' AND `'.$this->_tbl_key.'` NOT IN ('.implode(',', $children).')'
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
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
		// Initialize variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Lock the table for writing.
		if (!$this->_lock()) {
			// Error message set in lock method.
			return false;
		}

		// Get the node by primary key.
		if (!$node = $this->_getNode($pk)) {
			// Error message set in getNode method.
			$this->_unlock();
			return false;
		}

		// Get the right sibling node.
		if (!$sibling = $this->_getNode($node->rgt + 1, 'left')) {
			// Error message set in getNode method.
			$this->_unlock();
			return false;
		}

		// Get the primary keys of child nodes.
		$this->_db->setQuery(
			'SELECT `'.$this->_tbl_key.'`' .
			' FROM `'.$this->_tbl.'`' .
			' WHERE `lft` BETWEEN '.(int) $node->lft.' AND '.(int) $node->rgt
		);
		$children = $this->_db->loadResultArray();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			$this->_unlock();
			return false;
		}

		// Shift left and right values for the node and it's children.
		$this->_db->setQuery(
			'UPDATE `'.$this->_tbl.'`' .
			' SET `lft` = `lft` + '.(int) $sibling->width.', `rgt` = `rgt` + '.(int) $sibling->width.'' .
			' WHERE `lft` BETWEEN '.(int) $node->lft.' AND '.(int) $node->rgt
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			$this->_unlock();
			return false;
		}

		// Shift left and right values for the sibling and it's children.
		$this->_db->setQuery(
			'UPDATE `'.$this->_tbl.'`' .
			' SET `lft` = `lft` - '.(int) $node->width.', `rgt` = `rgt` - '.(int) $node->width .
			' WHERE `lft` BETWEEN '.(int) $sibling->lft.' AND '.(int) $sibling->rgt .
			' AND `'.$this->_tbl_key.'` NOT IN ('.implode(',', $children).')'
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
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

		try
		{
			// Test for a unique record with parent_id = 0
			$this->_db->setQuery(
				'SELECT '.$this->_db->nameQuote($k).
				' FROM '.$this->_tbl .
				' WHERE `parent_id` = 0'
			);
			$result = $this->_db->loadResultArray();
			if ($this->_db->getErrorNum()) {
				throw new Exception($this->_db->getErrorMsg());
			}

			if (count($result) == 1) {
				$parentId = $result[0];
			}
			else
			{
				// Test for a unique record with lft = 0
				$this->_db->setQuery(
					'SELECT '.$this->_db->nameQuote($k).
					' FROM '.$this->_tbl .
					' WHERE `lft` = 0'
				);
				$result = $this->_db->loadResultArray();
				if ($this->_db->getErrorNum()) {
					throw new Exception($this->_db->getErrorMsg());
				}

				if (count($result) == 1) {
					$parentId = $result[0];
				}
				else if (property_exists($this, 'alias'))
				{
					// Test for a unique record with lft = 0
					$this->_db->setQuery(
						'SELECT '.$this->_db->nameQuote($k).
						' FROM '.$this->_tbl .
						' WHERE `alias` = '.$this->_db->quote('root')
					);
					$result = $this->_db->loadResultArray();
					if ($this->_db->getErrorNum()) {
						throw new Exception($this->_db->getErrorMsg());
					}

					if (count($result) == 1) {
						$parentId = $result[0];
					}
					else {
						throw new Exception(JText::_('JTable_Error_Root_node_not_found'));
					}
				}
				else {
					throw new Exception(JText::_('JTable_Error_Root_node_not_found'));
				}
			}
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
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
			if ($parentId === false) {
				return false;
			}
		}

		// Build the structure of the recursive query.
		if (!isset($this->_cache['rebuild.sql']))
		{
			jimport('joomla.database.query');

			$query = new JQuery;
			$query->select('id, alias');
			$query->from($this->_tbl);
			$query->where('parent_id = %d');

			// If the table has an `ordering` field, use that for ordering.
			if (property_exists($this, 'ordering')) {
				$query->order('parent_id, ordering, lft');
			}
			else {
				$query->order('parent_id, lft');
			}
			$this->_cache['rebuild.sql'] = (string) $query;
		}

		// Make a shortcut to database object.
		$db = &$this->_db;

		// Assemble the query to find all children of this node.
		$db->setQuery(sprintf($this->_cache['rebuild.sql'], (int) $parentId));
		$children = $db->loadObjectList();

		// The right value of this node is the left value + 1
		$rightId = $leftId + 1;

		// execute this function recursively over all children
		for ($i = 0, $n = count($children); $i < $n; $i++)
		{
			// $rightId is the current right value, which is incremented on recursion return.
			// Increment the level for the children.
			// Add this item's alias to the path (but avoid a leading /)
			$rightId = $this->rebuild($children[$i]->id, $rightId, $level + 1, $path.(empty($path) ? '' : '/').$children[$i]->alias);

			// If there is an update failure, return false to break out of the recursion.
			if ($rightId === false) {
				return false;
			}
		}

		// We've got the left value, and now that we've processed
		// the children of this node we also know the right value.
		$db->setQuery(
			'UPDATE '. $this->_tbl .
			' SET lft = '. (int) $leftId .', rgt = '. (int) $rightId .
			' , level = '.(int) $level .
			' , path = '.$db->quote($path) .
			' WHERE id = '. (int)$parentId
		);

		// If there is an update failure, return false to break out of the recursion.
		if (!$db->query())
		{
			$this->setError($db->getErrorMsg());
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

		// Initialize variables.
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the aliases for the path from the node to the root node.
		$this->_db->setQuery(
			'SELECT p.alias' .
			' FROM '.$this->_tbl.' AS n, '.$this->_tbl.' AS p' .
			' WHERE n.lft BETWEEN p.lft AND p.rgt' .
			' AND n.'.$this->_tbl_key.' = '. (int) $pk .
			' ORDER BY p.lft'
		);
		$segments = $this->_db->loadResultArray();

		// Make sure to remove the root path if it exists in the list.
		if ($segments[0] == 'root') {
			array_shift($segments);
		}

		// Build the path.
		$path = trim(implode('/', $segments), ' /\\');

		// Update the path field for the node.
		$this->_db->setQuery(
			'UPDATE `'.$this->_tbl.'`' .
			' SET `path` = '.$this->_db->quote($path) .
			' WHERE `'.$this->_tbl_key.'` = '.(int) $pk
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
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
		$this->_db->setQuery(
			'SELECT `'.$this->_tbl_key.'`, `parent_id`, `level`, `lft`, `rgt`' .
			' FROM `'.$this->_tbl.'`' .
			' WHERE `'.$k.'` = '.(int) $id,
			0, 1
		);
		$row = $this->_db->loadObject();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
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
	 * 					which to make room in the tree around for a new node.
	 * @param	integer	The width of the node for which to make room in the tree.
	 * @param	string	The position relative to the reference node where the room
	 * 					should be made.
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
		if ($nodeWidth < 2) {
			return false;
		}

		// Initialize variables
		$k = $this->_tbl_key;
		$data = new stdClass;

		// Run the calculations and build the data object by reference position.
		switch ($position)
		{
			case 'first-child':
				$data->left_where = 'lft > '.$referenceNode->lft;
				$data->right_where = 'lft >= '.$referenceNode->lft;

				$data->new_lft 		= $referenceNode->lft + 1;
				$data->new_rgt		= $referenceNode->lft + $nodeWidth;
				$data->new_parent_id	= $referenceNode->$k;
				$data->new_level		= $referenceNode->level + 1;
				break;

			case 'last-child':
				$data->left_where = 'lft >= '.($referenceNode->rgt - $nodeWidth);
				$data->right_where = 'rgt >= '.($referenceNode->rgt - $nodeWidth);

				$data->new_lft		= $referenceNode->rgt - $nodeWidth;
				$data->new_rgt		= $referenceNode->rgt - 1;
				$data->new_parent_id	= $referenceNode->$k;
				$data->new_level		= $referenceNode->level + 1;
				break;

			case 'before':
				$data->left_where = 'lft >= '.$referenceNode->lft;
				$data->right_where = 'rgt >= '.$referenceNode->rgt;

				$data->new_lft		= $referenceNode->lft;
				$data->new_rgt 	= $referenceNode->lft + $nodeWidth - 1;
				$data->new_parent_id	= $referenceNode->parent_id;
				$data->new_level		= $referenceNode->level;
				break;

			default:
			case 'after':
				$data->left_where = 'lft > '.$referenceNode->lft;
				$data->right_where = 'rgt > '.$referenceNode->rgt;

				$data->new_lft 		= $referenceNode->rgt + 1;
				$data->new_rgt		= $referenceNode->rgt + $nodeWidth;
				$data->new_parent_id	= $referenceNode->parent_id;
				$data->new_level		= $referenceNode->level;
				break;
		}

		return $data;
	}

	protected function _logtable($showData = true)
	{
		$sep	= "\n".str_pad('', 40, '-');
		$buffer = "\n".$this->_db->getQuery().$sep;

		if ($showData)
		{
			$this->_db->setQuery(
				'SELECT id, parent_id, lft, rgt, level' .
				' FROM `'.$this->_tbl.'`' .
				' ORDER BY id'
			);
			$rows = $this->_db->loadRowList();
			$buffer .= sprintf("\n| %4s | %4s | %4s | %4s |", 'id', 'par', 'lft', 'rgt');
			$buffer .= $sep;
			foreach ($rows as $row)
			{
				$buffer .= sprintf("\n| %4s | %4s | %4s | %4s |", $row[0], $row[1], $row[2], $row[3]);
			}
			$buffer .= $sep;
		}
		echo $buffer;
	}
}
