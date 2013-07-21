<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Table class supporting modified pre-order tree traversal behavior.
 *
 * @package     Joomla.Platform
 * @subpackage  Table
 * @link        http://docs.joomla.org/JTableNested
 * @since       11.1
 */
class JTableNested extends JTable
{
	/**
	 * Object property holding the primary key of the parent node.  Provides
	 * adjacency list data for nodes.
	 *
	 * @var    integer
	 * @since  11.1
	 */
	public $parent_id;

	/**
	 * Object property holding the depth level of the node in the tree.
	 *
	 * @var    integer
	 * @since  11.1
	 */
	public $level;

	/**
	 * Object property holding the left value of the node for managing its
	 * placement in the nested sets tree.
	 *
	 * @var    integer
	 * @since  11.1
	 */
	public $lft;

	/**
	 * Object property holding the right value of the node for managing its
	 * placement in the nested sets tree.
	 *
	 * @var    integer
	 * @since  11.1
	 */
	public $rgt;

	/**
	 * Object property holding the alias of this node used to constuct the
	 * full text path, forward-slash delimited.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $alias;

	/**
	 * Object property to hold the location type to use when storing the row.
	 * Possible values are: ['before', 'after', 'first-child', 'last-child'].
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_location;

	/**
	 * Object property to hold the primary key of the location reference node to
	 * use when storing the row.  A combination of location type and reference
	 * node describes where to store the current node in the tree.
	 *
	 * @var    integer
	 * @since  11.1
	 */
	protected $_location_id;

	/**
	 * An array to cache values in recursive processes.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $_cache = array();

	/**
	 * Debug level
	 *
	 * @var    integer
	 * @since  11.1
	 */
	protected $_debug = 0;

	/**
	 * Sets the debug level on or off
	 *
	 * @param   integer  $level  0 = off, 1 = on
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function debug($level)
	{
		$this->_debug = (int) $level;
	}

	/**
	 * Method to get an array of nodes from a given node to its root.
	 *
	 * @param   integer  $pk          Primary key of the node for which to get the path.
	 * @param   boolean  $diagnostic  Only select diagnostic data for the nested sets.
	 *
	 * @return  mixed    An array of node objects including the start node.
	 *
	 * @since   11.1
	 * @throws  RuntimeException on database error
	 */
	public function getPath($pk = null, $diagnostic = false)
	{
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the path from the node to the root.
		$select = ($diagnostic) ? 'p.' . $k . ', p.parent_id, p.level, p.lft, p.rgt' : 'p.*';
		$query = $this->_db->getQuery(true)
			->select($select)
			->from($this->_tbl . ' AS n, ' . $this->_tbl . ' AS p')
			->where('n.lft BETWEEN p.lft AND p.rgt')
			->where('n.' . $k . ' = ' . (int) $pk)
			->order('p.lft');

		$this->_db->setQuery($query);

		return $this->_db->loadObjectList();
	}

	/**
	 * Method to get a node and all its child nodes.
	 *
	 * @param   integer  $pk          Primary key of the node for which to get the tree.
	 * @param   boolean  $diagnostic  Only select diagnostic data for the nested sets.
	 *
	 * @return  mixed    Boolean false on failure or array of node objects on success.
	 *
	 * @since   11.1
	 * @throws  RuntimeException on database error.
	 */
	public function getTree($pk = null, $diagnostic = false)
	{
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the node and children as a tree.
		$select = ($diagnostic) ? 'n.' . $k . ', n.parent_id, n.level, n.lft, n.rgt' : 'n.*';
		$query = $this->_db->getQuery(true)
			->select($select)
			->from($this->_tbl . ' AS n, ' . $this->_tbl . ' AS p')
			->where('n.lft BETWEEN p.lft AND p.rgt')
			->where('p.' . $k . ' = ' . (int) $pk)
			->order('n.lft');

		return $this->_db->setQuery($query)->loadObjectList();
	}

	/**
	 * Method to determine if a node is a leaf node in the tree (has no children).
	 *
	 * @param   integer  $pk  Primary key of the node to check.
	 *
	 * @return  boolean  True if a leaf node, false if not or null if the node does not exist.
	 *
	 * @note    Since 12.1 this method returns null if the node does not exist.
	 * @since   11.1
	 * @throws  RuntimeException on database error.
	 */
	public function isLeaf($pk = null)
	{
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;
		$node = $this->_getNode($pk);

		// Get the node by primary key.
		if (empty($node))
		{
			// Error message set in getNode method.
			return null;
		}

		// The node is a leaf node.
		return (($node->rgt - $node->lft) == 1);
	}

	/**
	 * Method to set the location of a node in the tree object.  This method does not
	 * save the new location to the database, but will set it in the object so
	 * that when the node is stored it will be stored in the new location.
	 *
	 * @param   integer  $referenceId  The primary key of the node to reference new location by.
	 * @param   string   $position     Location type string. ['before', 'after', 'first-child', 'last-child']
	 *
	 * @return  void
	 *
	 * @note    Since 12.1 this method returns void and throws an InvalidArgumentException when an invalid position is passed.
	 * @since   11.1
	 * @throws  InvalidArgumentException
	 */
	public function setLocation($referenceId, $position = 'after')
	{
		// Make sure the location is valid.
		if (($position != 'before') && ($position != 'after') && ($position != 'first-child') && ($position != 'last-child'))
		{
			throw new InvalidArgumentException(sprintf('%s::setLocation(%d, *%s*)', get_class($this), $referenceId, $position));
		}

		// Set the location properties.
		$this->_location = $position;
		$this->_location_id = $referenceId;
	}

	/**
	 * Method to move a row in the ordering sequence of a group of rows defined by an SQL WHERE clause.
	 * Negative numbers move the row up in the sequence and positive numbers move it down.
	 *
	 * @param   integer  $delta  The direction and magnitude to move the row in the ordering sequence.
	 * @param   string   $where  WHERE clause to use for limiting the selection of rows to compact the
	 * ordering values.
	 *
	 * @return  mixed    Boolean true on success.
	 *
	 * @link    http://docs.joomla.org/JTable/move
	 * @since   11.1
	 */
	public function move($delta, $where = '')
	{
		$k = $this->_tbl_key;
		$pk = $this->$k;

		$query = $this->_db->getQuery(true)
			->select($k)
			->from($this->_tbl)
			->where('parent_id = ' . $this->parent_id);
		if ($where)
		{
			$query->where($where);
		}
		if ($delta > 0)
		{
			$query->where('rgt > ' . $this->rgt)
				->order('rgt ASC');
			$position = 'after';
		}
		else
		{
			$query->where('lft < ' . $this->lft)
				->order('lft DESC');
			$position = 'before';
		}

		$this->_db->setQuery($query);
		$referenceId = $this->_db->loadResult();

		if ($referenceId)
		{
			return $this->moveByReference($referenceId, $position, $pk);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to move a node and its children to a new location in the tree.
	 *
	 * @param   integer  $referenceId  The primary key of the node to reference new location by.
	 * @param   string   $position     Location type string. ['before', 'after', 'first-child', 'last-child']
	 * @param   integer  $pk           The primary key of the node to move.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTableNested/moveByReference
	 * @since   11.1
	 * @throws  RuntimeException on database error.
	 */

	public function moveByReference($referenceId, $position = 'after', $pk = null)
	{
		// @codeCoverageIgnoreStart
		if ($this->_debug)
		{
			echo "\nMoving ReferenceId:$referenceId, Position:$position, PK:$pk";
		}
		// @codeCoverageIgnoreEnd

		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the node by id.
		if (!$node = $this->_getNode($pk))
		{
			// Error message set in getNode method.
			return false;
		}

		// Get the ids of child nodes.
		$query = $this->_db->getQuery(true)
			->select($k)
			->from($this->_tbl)
			->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt);

		$children = $this->_db->setQuery($query)->loadColumn();

		// @codeCoverageIgnoreStart
		if ($this->_debug)
		{
			$this->_logtable(false);
		}
		// @codeCoverageIgnoreEnd

		// Cannot move the node to be a child of itself.
		if (in_array($referenceId, $children))
		{
			$e = new UnexpectedValueException(
				sprintf('%s::moveByReference(%d, %s, %d) parenting to child.', get_class($this), $referenceId, $position, $pk)
			);
			$this->setError($e);
			return false;
		}

		// Lock the table for writing.
		if (!$this->_lock())
		{
			return false;
		}

		/*
		 * Move the sub-tree out of the nested sets by negating its left and right values.
		 */
		$query->clear()
			->update($this->_tbl)
			->set('lft = lft * (-1), rgt = rgt * (-1)')
			->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt);
		$this->_db->setQuery($query);

		$this->_runQuery($query, 'JLIB_DATABASE_ERROR_MOVE_FAILED');

		/*
		 * Close the hole in the tree that was opened by removing the sub-tree from the nested sets.
		 */
		// Compress the left values.
		$query->clear()
			->update($this->_tbl)
			->set('lft = lft - ' . (int) $node->width)
			->where('lft > ' . (int) $node->rgt);
		$this->_db->setQuery($query);

		$this->_runQuery($query, 'JLIB_DATABASE_ERROR_MOVE_FAILED');

		// Compress the right values.
		$query->clear()
			->update($this->_tbl)
			->set('rgt = rgt - ' . (int) $node->width)
			->where('rgt > ' . (int) $node->rgt);
		$this->_db->setQuery($query);

		$this->_runQuery($query, 'JLIB_DATABASE_ERROR_MOVE_FAILED');

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

			// Get the reposition data for shifting the tree and re-inserting the node.
			if (!$repositionData = $this->_getTreeRepositionData($reference, $node->width, $position))
			{
				// Error message set in getNode method.
				$this->_unlock();
				return false;
			}
		}
		// We are moving the tree to be the last child of the root node
		else
		{
			// Get the last root node as the reference node.
			$query->clear()
				->select($this->_tbl_key . ', parent_id, level, lft, rgt')
				->from($this->_tbl)
				->where('parent_id = 0')
				->order('lft DESC');
			$this->_db->setQuery($query, 0, 1);
			$reference = $this->_db->loadObject();

			// @codeCoverageIgnoreStart
			if ($this->_debug)
			{
				$this->_logtable(false);
			}
			// @codeCoverageIgnoreEnd

			// Get the reposition data for re-inserting the node after the found root.
			if (!$repositionData = $this->_getTreeRepositionData($reference, $node->width, 'last-child'))
			{
				// Error message set in getNode method.
				$this->_unlock();
				return false;
			}
		}

		/*
		 * Create space in the nested sets at the new location for the moved sub-tree.
		 */

		// Shift left values.
		$query->clear()
			->update($this->_tbl)
			->set('lft = lft + ' . (int) $node->width)
			->where($repositionData->left_where);
		$this->_db->setQuery($query);

		$this->_runQuery($query, 'JLIB_DATABASE_ERROR_MOVE_FAILED');

		// Shift right values.
		$query->clear()
			->update($this->_tbl)
			->set('rgt = rgt + ' . (int) $node->width)
			->where($repositionData->right_where);
		$this->_db->setQuery($query);

		$this->_runQuery($query, 'JLIB_DATABASE_ERROR_MOVE_FAILED');

		/*
		 * Calculate the offset between where the node used to be in the tree and
		 * where it needs to be in the tree for left ids (also works for right ids).
		 */
		$offset = $repositionData->new_lft - $node->lft;
		$levelOffset = $repositionData->new_level - $node->level;

		// Move the nodes back into position in the tree using the calculated offsets.
		$query->clear()
			->update($this->_tbl)
			->set('rgt = ' . (int) $offset . ' - rgt')
			->set('lft = ' . (int) $offset . ' - lft')
			->set('level = level + ' . (int) $levelOffset)
			->where('lft < 0');
		$this->_db->setQuery($query);

		$this->_runQuery($query, 'JLIB_DATABASE_ERROR_MOVE_FAILED');

		// Set the correct parent id for the moved node if required.
		if ($node->parent_id != $repositionData->new_parent_id)
		{
			$query = $this->_db->getQuery(true)
				->update($this->_tbl);

			// Update the title and alias fields if they exist for the table.
			$fields = $this->getFields();

			if (property_exists($this, 'title') && $this->title !== null)
			{
				$query->set('title = ' . $this->_db->quote($this->title));
			}

			if (array_key_exists('alias', $fields)  && $this->alias !== null)
			{
				$query->set('alias = ' . $this->_db->quote($this->alias));
			}

			$query->set('parent_id = ' . (int) $repositionData->new_parent_id)
				->where($this->_tbl_key . ' = ' . (int) $node->$k);
			$this->_db->setQuery($query);

			$this->_runQuery($query, 'JLIB_DATABASE_ERROR_MOVE_FAILED');
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
	 * Method to delete a node and, optionally, its child nodes from the table.
	 *
	 * @param   integer  $pk        The primary key of the node to delete.
	 * @param   boolean  $children  True to delete child nodes, false to move them up a level.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 */
	public function delete($pk = null, $children = true)
	{
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Lock the table for writing.
		if (!$this->_lock())
		{
			// Error message set in lock method.
			return false;
		}

		// If tracking assets, remove the asset first.
		if ($this->_trackAssets)
		{
			$name = $this->_getAssetName();
			$asset = JTable::getInstance('Asset');

			// Lock the table for writing.
			if (!$asset->_lock())
			{
				// Error message set in lock method.
				return false;
			}

			if ($asset->loadByName($name))
			{
				// Delete the node in assets table.
				if (!$asset->delete(null, $children))
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
		$node = $this->_getNode($pk);
		if (empty($node))
		{
			// Error message set in getNode method.
			$this->_unlock();
			return false;
		}

		$query = $this->_db->getQuery(true);

		// Should we delete all children along with the node?
		if ($children)
		{
			// Delete the node and all of its children.
			$query->clear()
				->delete($this->_tbl)
				->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt);
			$this->_runQuery($query, 'JLIB_DATABASE_ERROR_DELETE_FAILED');

			// Compress the left values.
			$query->clear()
				->update($this->_tbl)
				->set('lft = lft - ' . (int) $node->width)
				->where('lft > ' . (int) $node->rgt);
			$this->_runQuery($query, 'JLIB_DATABASE_ERROR_DELETE_FAILED');

			// Compress the right values.
			$query->clear()
				->update($this->_tbl)
				->set('rgt = rgt - ' . (int) $node->width)
				->where('rgt > ' . (int) $node->rgt);
			$this->_runQuery($query, 'JLIB_DATABASE_ERROR_DELETE_FAILED');
		}
		// Leave the children and move them up a level.
		else
		{
			// Delete the node.
			$query->clear()
				->delete($this->_tbl)
				->where('lft = ' . (int) $node->lft);
			$this->_runQuery($query, 'JLIB_DATABASE_ERROR_DELETE_FAILED');

			// Shift all node's children up a level.
			$query->clear()
				->update($this->_tbl)
				->set('lft = lft - 1')
				->set('rgt = rgt - 1')
				->set('level = level - 1')
				->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt);
			$this->_runQuery($query, 'JLIB_DATABASE_ERROR_DELETE_FAILED');

			// Adjust all the parent values for direct children of the deleted node.
			$query->clear()
				->update($this->_tbl)
				->set('parent_id = ' . (int) $node->parent_id)
				->where('parent_id = ' . (int) $node->$k);
			$this->_runQuery($query, 'JLIB_DATABASE_ERROR_DELETE_FAILED');

			// Shift all of the left values that are right of the node.
			$query->clear()
				->update($this->_tbl)
				->set('lft = lft - 2')
				->where('lft > ' . (int) $node->rgt);
			$this->_runQuery($query, 'JLIB_DATABASE_ERROR_DELETE_FAILED');

			// Shift all of the right values that are right of the node.
			$query->clear()
				->update($this->_tbl)
				->set('rgt = rgt - 2')
				->where('rgt > ' . (int) $node->rgt);
			$this->_runQuery($query, 'JLIB_DATABASE_ERROR_DELETE_FAILED');
		}

		// Unlock the table for writing.
		$this->_unlock();

		return true;
	}

	/**
	 * Checks that the object is valid and able to be stored.
	 *
	 * This method checks that the parent_id is non-zero and exists in the database.
	 * Note that the root node (parent_id = 0) cannot be manipulated with this class.
	 *
	 * @return  boolean  True if all checks pass.
	 *
	 * @since   11.1
	 * @throws  RuntimeException on database error.
	 */
	public function check()
	{
		$this->parent_id = (int) $this->parent_id;

		// Set up a mini exception handler.
		try
		{
			// Check that the parent_id field is valid.
			if ($this->parent_id == 0)
			{
				throw new UnexpectedValueException(sprintf('Invalid `parent_id` [%d] in %s', $this->parent_id, get_class($this)));
			}

			$query = $this->_db->getQuery(true)
				->select('COUNT(' . $this->_tbl_key . ')')
				->from($this->_tbl)
				->where($this->_tbl_key . ' = ' . $this->parent_id);

			if (!$this->_db->setQuery($query)->loadResult())
			{
				throw new UnexpectedValueException(sprintf('Invalid `parent_id` [%d] in %s', $this->parent_id, get_class($this)));
			}
		}
		catch (UnexpectedValueException $e)
		{
			// Validation error - record it and return false.
			$this->setError($e);

			return false;
		}
		// @codeCoverageIgnoreStart
		catch (Exception $e)
		{
			// Database error - rethrow.
			throw $e;
		}
		// @codeCoverageIgnoreEnd

		return true;
	}

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTableNested/store
	 * @since   11.1
	 */
	public function store($updateNulls = false)
	{
		$k = $this->_tbl_key;

		// Implement JObservableInterface: Pre-processing by observers
		$this->_observers->update('onBeforeStore', array($updateNulls, $k));

		// @codeCoverageIgnoreStart
		if ($this->_debug)
		{
			echo "\n" . get_class($this) . "::store\n";
			$this->_logtable(true, false);
		}
		// @codeCoverageIgnoreEnd

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
				if (!$this->_lock())
				{
					// Error message set in lock method.
					return false;
				}

				// We are inserting a node relative to the last root node.
				if ($this->_location_id == 0)
				{
					// Get the last root node as the reference node.
					$query = $this->_db->getQuery(true)
						->select($this->_tbl_key . ', parent_id, level, lft, rgt')
						->from($this->_tbl)
						->where('parent_id = 0')
						->order('lft DESC');
					$this->_db->setQuery($query, 0, 1);
					$reference = $this->_db->loadObject();

					// @codeCoverageIgnoreStart
					if ($this->_debug)
					{
						$this->_logtable(false);
					}
					// @codeCoverageIgnoreEnd
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
				$query = $this->_db->getQuery(true)
					->update($this->_tbl)
					->set('lft = lft + 2')
					->where($repositionData->left_where);
				$this->_runQuery($query, 'JLIB_DATABASE_ERROR_STORE_FAILED');

				// Create space in the tree at the new location for the new node in right ids.
				$query->clear()
					->update($this->_tbl)
					->set('rgt = rgt + 2')
					->where($repositionData->right_where);
				$this->_runQuery($query, 'JLIB_DATABASE_ERROR_STORE_FAILED');

				// Set the object values.
				$this->parent_id = $repositionData->new_parent_id;
				$this->level = $repositionData->new_level;
				$this->lft = $repositionData->new_lft;
				$this->rgt = $repositionData->new_rgt;
			}
			else
			{
				// Negative parent ids are invalid
				$e = new UnexpectedValueException(sprintf('%s::store() used a negative _location_id', get_class($this)));
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
				if (!$this->moveByReference($this->_location_id, $this->_location, $this->$k))
				{
					// Error message set in move method.
					return false;
				}
			}

			// Lock the table for writing.
			if (!$this->_lock())
			{
				// Error message set in lock method.
				return false;
			}
		}

		// Store the row to the database.

		// Implement JObservableInterface: We do not want parent::store to update observers,
		// since tables are locked and we are updating it from this level of store():
		$oldCallObservers = $this->_observers->doCallObservers(false);

		$result = parent::store($updateNulls);

		// Implement JObservableInterface: Restore previous callable observers state:
		$this->_observers->doCallObservers($oldCallObservers);

		if ($result)
		{
			// @codeCoverageIgnoreStart
			if ($this->_debug)
			{
				$this->_logtable();
			}
			// @codeCoverageIgnoreEnd
		}
		// Unlock the table for writing.
		$this->_unlock();

		// Implement JObservableInterface: Post-processing by observers
		$this->_observers->update('onAfterStore', array(&$result));

		return $result;
	}

	/**
	 * Method to set the publishing state for a node or list of nodes in the database
	 * table.  The method respects rows checked out by other users and will attempt
	 * to checkin rows that it can after adjustments are made. The method will not
	 * allow you to set a publishing state higher than any ancestor node and will
	 * not allow you to set a publishing state on a node with a checked out child.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not
	 *                            set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTableNested/publish
	 * @since   11.1
	 * @throws UnexpectedValueException
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		$k = $this->_tbl_key;
		$query = $this->_db->getQuery(true);

		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state = (int) $state;

		// If $state > 1, then we allow state changes even if an ancestor has lower state
		// (for example, can change a child state to Archived (2) if an ancestor is Published (1)
		$compareState = ($state > 1) ? 1 : $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = explode(',', $this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				$e = new UnexpectedValueException(sprintf(__CLASS__ . '::' . __FUNCTION__ . '(%s, %d, %d) empty.', get_class($this), $state, $userId));
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
			if (!$node = $this->_getNode($pk))
			{
				// Error message set in getNode method.
				return false;
			}

			// If the table has checkout support, verify no children are checked out.
			if ($checkoutSupport)
			{
				// Ensure that children are not checked out.
				$query->clear()
					->select('COUNT(' . $k . ')')
					->from($this->_tbl)
					->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt)
					->where('(checked_out <> 0 AND checked_out <> ' . (int) $userId . ')');
				$this->_db->setQuery($query);

				// Check for checked out children.
				if ($this->_db->loadResult())
				{
					// TODO Convert to a conflict exception when available.
					$implodedPks = implode(',', $pks);
					$e = new RuntimeException(sprintf(__CLASS__ . '::' . __FUNCTION__ . '(%s, %d, %d) checked-out conflict.', get_class($this), $implodedPks, $state, $userId));
					$this->setError($e);

					return false;
				}
			}

			// If any parent nodes have lower published state values, we cannot continue.
			if ($node->parent_id)
			{
				// Get any ancestor nodes that have a lower publishing state.
				$query->clear()
					->select('n.' . $k)
					->from($this->_db->quoteName($this->_tbl) . ' AS n')
					->where('n.lft < ' . (int) $node->lft)
					->where('n.rgt > ' . (int) $node->rgt)
					->where('n.parent_id > 0')
					->where('n.published < ' . (int) $compareState);

				// Just fetch one row (one is one too many).
				$this->_db->setQuery($query, 0, 1);

				$rows = $this->_db->loadColumn();

					if (!empty($rows))
					{
						$pksImploded = implode(',', $pks);
						throw new UnexpectedValueException(
							sprintf(__CLASS__ . '::' . __FUNCTION__ . '(%s, %d, %d) ancestors have lower state.', $pksImploded, $state, $userId)
						);

					}
			}

			// Update and cascade the publishing state.
			$query->clear()
				->update($this->_db->quoteName($this->_tbl))
				->set('published = ' . (int) $state)
				->where('(lft > ' . (int) $node->lft . ' AND rgt < ' . (int) $node->rgt . ') OR ' . $k . ' = ' . (int) $pk);
			$this->_db->setQuery($query)->execute();

			// If checkout support exists for the object, check the row in.
			if ($checkoutSupport)
			{
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->published = $state;
		}

		$this->setError('');

		return true;
	}

	/**
	 * Method to move a node one position to the left in the same level.
	 *
	 * @param   integer  $pk  Primary key of the node to move.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 * @throws  RuntimeException on database error.
	 */
	public function orderUp($pk)
	{
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Lock the table for writing.
		if (!$this->_lock())
		{
			// Error message set in lock method.
			return false;
		}

		// Get the node by primary key.
		$node = $this->_getNode($pk);
		if (empty($node))
		{
			// Error message set in getNode method.
			$this->_unlock();
			return false;
		}

		// Get the left sibling node.
		$sibling = $this->_getNode($node->lft - 1, 'right');

		if (empty($sibling))
		{
			// Error message set in getNode method.
			$this->_unlock();
			return false;
		}

		try
		{
			// Get the primary keys of child nodes.
			$query = $this->_db->getQuery(true)
				->select($this->_tbl_key)
				->from($this->_tbl)
				->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt);

			$children = $this->_db->setQuery($query)->loadColumn();

			// Shift left and right values for the node and it's children.
			$query->clear()
				->update($this->_tbl)
				->set('lft = lft - ' . (int) $sibling->width)
				->set('rgt = rgt - ' . (int) $sibling->width)
				->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt);
			$this->_db->setQuery($query)->execute();

			// Shift left and right values for the sibling and it's children.
			$query->clear()
				->update($this->_tbl)
				->set('lft = lft + ' . (int) $node->width)
				->set('rgt = rgt + ' . (int) $node->width)
				->where('lft BETWEEN ' . (int) $sibling->lft . ' AND ' . (int) $sibling->rgt)
				->where($this->_tbl_key . ' NOT IN (' . implode(',', $children) . ')');
			$this->_db->setQuery($query)->execute();
		}
		catch (RuntimeException $e)
		{
			$this->_unlock();
			throw $e;
		}

		// Unlock the table for writing.
		$this->_unlock();

		return true;
	}

	/**
	 * Method to move a node one position to the right in the same level.
	 *
	 * @param   integer  $pk  Primary key of the node to move.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 * @throws  RuntimeException on database error.
	 */
	public function orderDown($pk)
	{
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Lock the table for writing.
		if (!$this->_lock())
		{
			// Error message set in lock method.
			return false;
		}

		// Get the node by primary key.
		$node = $this->_getNode($pk);
		if (empty($node))
		{
			// Error message set in getNode method.
			$this->_unlock();
			return false;
		}

		$query = $this->_db->getQuery(true);

		// Get the right sibling node.
		$sibling = $this->_getNode($node->rgt + 1, 'left');
		if (empty($sibling))
		{
			// Error message set in getNode method.
			$query->_unlock($this->_db);
			$this->_locked = false;
			return false;
		}

		try
		{
			// Get the primary keys of child nodes.
			$query->clear()
				->select($this->_tbl_key)
				->from($this->_tbl)
				->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt);
			$this->_db->setQuery($query);
			$children = $this->_db->loadColumn();

			// Shift left and right values for the node and it's children.
			$query->clear()
				->update($this->_tbl)
				->set('lft = lft + ' . (int) $sibling->width)
				->set('rgt = rgt + ' . (int) $sibling->width)
				->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt);
			$this->_db->setQuery($query)->execute();

			// Shift left and right values for the sibling and it's children.
			$query->clear()
				->update($this->_tbl)
				->set('lft = lft - ' . (int) $node->width)
				->set('rgt = rgt - ' . (int) $node->width)
				->where('lft BETWEEN ' . (int) $sibling->lft . ' AND ' . (int) $sibling->rgt)
				->where($this->_tbl_key . ' NOT IN (' . implode(',', $children) . ')');
			$this->_db->setQuery($query)->execute();
		}
		catch (RuntimeException $e)
		{
			$this->_unlock();
			throw $e;
		}

		// Unlock the table for writing.
		$this->_unlock();

		return true;
	}

	/**
	 * Gets the ID of the root item in the tree
	 *
	 * @return  mixed  The primary id of the root row, or false if not found and the internal error is set.
	 *
	 * @since   11.1
	 */
	public function getRootId()
	{
		// Get the root item.
		$k = $this->_tbl_key;

		// Test for a unique record with parent_id = 0
		$query = $this->_db->getQuery(true)
			->select($k)
			->from($this->_tbl)
			->where('parent_id = 0');

		$result = $this->_db->setQuery($query)->loadColumn();

		if (count($result) == 1)
		{
			return $result[0];
		}

		// Test for a unique record with lft = 0
		$query->clear()
			->select($k)
			->from($this->_tbl)
			->where('lft = 0');

		$result = $this->_db->setQuery($query)->loadColumn();

		if (count($result) == 1)
		{
			return $result[0];
		}

		$fields = $this->getFields();

		if (array_key_exists('alias', $fields))
		{
			// Test for a unique record alias = root
			$query->clear()
				->select($k)
				->from($this->_tbl)
				->where('alias = ' . $this->_db->quote('root'));

			$result = $this->_db->setQuery($query)->loadColumn();

			if (count($result) == 1)
			{
				return $result[0];
			}
		}

		$e = new UnexpectedValueException(sprintf('%s::getRootId', get_class($this)));
		$this->setError($e);

		return false;
	}

	/**
	 * Method to recursively rebuild the whole nested set tree.
	 *
	 * @param   integer  $parentId  The root of the tree to rebuild.
	 * @param   integer  $leftId    The left id to start with in building the tree.
	 * @param   integer  $level     The level to assign to the current nodes.
	 * @param   string   $path      The path to the current nodes.
	 *
	 * @return  integer  1 + value of root rgt on success, false on failure
	 *
	 * @link    http://docs.joomla.org/JTableNested/rebuild
	 * @since   11.1
	 * @throws  RuntimeException on database error.
	 */
	public function rebuild($parentId = null, $leftId = 0, $level = 0, $path = '')
	{
		// If no parent is provided, try to find it.
		if ($parentId === null)
		{
			// Get the root item.
			$parentId = $this->getRootId();
			if ($parentId === false)
			{
				return false;
			}
		}

		$query = $this->_db->getQuery(true);

		// Build the structure of the recursive query.
		if (!isset($this->_cache['rebuild.sql']))
		{
			$query->clear()
				->select($this->_tbl_key . ', alias')
				->from($this->_tbl)
				->where('parent_id = %d');

			// If the table has an ordering field, use that for ordering.
			if (property_exists($this, 'ordering'))
			{
				$query->order('parent_id, ordering, lft');
			}
			else
			{
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

		// Execute this function recursively over all children
		foreach ($children as $node)
		{
			/*
			 * $rightId is the current right value, which is incremented on recursion return.
			 * Increment the level for the children.
			 * Add this item's alias to the path (but avoid a leading /)
			 */
			$rightId = $this->rebuild($node->{$this->_tbl_key}, $rightId, $level + 1, $path . (empty($path) ? '' : '/') . $node->alias);

			// If there is an update failure, return false to break out of the recursion.
			if ($rightId === false)
			{
				return false;
			}
		}

		// We've got the left value, and now that we've processed
		// the children of this node we also know the right value.
		$query->clear()
			->update($this->_tbl)
			->set('lft = ' . (int) $leftId)
			->set('rgt = ' . (int) $rightId)
			->set('level = ' . (int) $level)
			->set('path = ' . $this->_db->quote($path))
			->where($this->_tbl_key . ' = ' . (int) $parentId);
		$this->_db->setQuery($query)->execute();

		// Return the right value of this node + 1.
		return $rightId + 1;
	}

	/**
	 * Method to rebuild the node's path field from the alias values of the
	 * nodes from the current node to the root node of the tree.
	 *
	 * @param   integer  $pk  Primary key of the node for which to get the path.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    http://docs.joomla.org/JTableNested/rebuildPath
	 * @since   11.1
	 */
	public function rebuildPath($pk = null)
	{
		$fields = $this->getFields();

		// If there is no alias or path field, just return true.
		if (!array_key_exists('alias', $fields) || !array_key_exists('path', $fields))
		{
			return true;
		}

		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		// Get the aliases for the path from the node to the root node.
		$query = $this->_db->getQuery(true)
			->select('p.alias')
			->from($this->_tbl . ' AS n, ' . $this->_tbl . ' AS p')
			->where('n.lft BETWEEN p.lft AND p.rgt')
			->where('n.' . $this->_tbl_key . ' = ' . (int) $pk)
			->order('p.lft');
		$this->_db->setQuery($query);

		$segments = $this->_db->loadColumn();

		// Make sure to remove the root path if it exists in the list.
		if ($segments[0] == 'root')
		{
			array_shift($segments);
		}

		// Build the path.
		$path = trim(implode('/', $segments), ' /\\');

		// Update the path field for the node.
		$query->clear()
			->update($this->_tbl)
			->set('path = ' . $this->_db->quote($path))
			->where($this->_tbl_key . ' = ' . (int) $pk);

		$this->_db->setQuery($query)->execute();

		// Update the current record's path to the new one:
		$this->path = $path;

		return true;
	}

	/**
	 * Method to update order of table rows
	 *
	 * @param   array  $idArray    id numbers of rows to be reordered.
	 * @param   array  $lft_array  lft values of rows to be reordered.
	 *
	 * @return  integer  1 + value of root rgt on success, false on failure.
	 *
	 * @since   11.1
	 * @throws  RuntimeException on database error.
	 */
	public function saveorder($idArray = null, $lft_array = null)
	{
		try
		{
			$query = $this->_db->getQuery(true);

			// Validate arguments
			if (is_array($idArray) && is_array($lft_array) && count($idArray) == count($lft_array))
			{
				for ($i = 0, $count = count($idArray); $i < $count; $i++)
				{
					// Do an update to change the lft values in the table for each id
					$query->clear()
						->update($this->_tbl)
						->where($this->_tbl_key . ' = ' . (int) $idArray[$i])
						->set('lft = ' . (int) $lft_array[$i]);

					$this->_db->setQuery($query)->execute();

					// @codeCoverageIgnoreStart
					if ($this->_debug)
					{
						$this->_logtable();
					}
					// @codeCoverageIgnoreEnd
				}

				return $this->rebuild();
			}
			else
			{
				return false;
			}
		}
		catch (Exception $e)
		{
			$this->_unlock();
			throw $e;
		}
	}

	/**
	 * Method to get nested set properties for a node in the tree.
	 *
	 * @param   integer  $id   Value to look up the node by.
	 * @param   string   $key  An optional key to look up the node by (parent | left | right).
	 *                         If omitted, the primary key of the table is used.
	 *
	 * @return  mixed    Boolean false on failure or node object on success.
	 *
	 * @since   11.1
	 * @throws  RuntimeException on database error.
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
		$query = $this->_db->getQuery(true)
			->select($this->_tbl_key . ', parent_id, level, lft, rgt')
			->from($this->_tbl)
			->where($k . ' = ' . (int) $id);

		$row = $this->_db->setQuery($query, 0, 1)->loadObject();

		// Check for no $row returned
		if (empty($row))
		{
			$e = new UnexpectedValueException(sprintf('%s::_getNode(%d, %s) failed.', get_class($this), $id, $key));
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
	 * @param   object   $referenceNode  A node object with at least a 'lft' and 'rgt' with
	 *                                   which to make room in the tree around for a new node.
	 * @param   integer  $nodeWidth      The width of the node for which to make room in the tree.
	 * @param   string   $position       The position relative to the reference node where the room
	 * should be made.
	 *
	 * @return  mixed    Boolean false on failure or data object on success.
	 *
	 * @since   11.1
	 */
	protected function _getTreeRepositionData($referenceNode, $nodeWidth, $position = 'before')
	{
		// Make sure the reference an object with a left and right id.
		if (!is_object($referenceNode) || !(isset($referenceNode->lft) && isset($referenceNode->rgt)))
		{
			return false;
		}

		// A valid node cannot have a width less than 2.
		if ($nodeWidth < 2)
		{
			return false;
		}

		$k = $this->_tbl_key;
		$data = new stdClass;

		// Run the calculations and build the data object by reference position.
		switch ($position)
		{
			case 'first-child':
				$data->left_where = 'lft > ' . $referenceNode->lft;
				$data->right_where = 'rgt >= ' . $referenceNode->lft;

				$data->new_lft = $referenceNode->lft + 1;
				$data->new_rgt = $referenceNode->lft + $nodeWidth;
				$data->new_parent_id = $referenceNode->$k;
				$data->new_level = $referenceNode->level + 1;
				break;

			case 'last-child':
				$data->left_where = 'lft > ' . ($referenceNode->rgt);
				$data->right_where = 'rgt >= ' . ($referenceNode->rgt);

				$data->new_lft = $referenceNode->rgt;
				$data->new_rgt = $referenceNode->rgt + $nodeWidth - 1;
				$data->new_parent_id = $referenceNode->$k;
				$data->new_level = $referenceNode->level + 1;
				break;

			case 'before':
				$data->left_where = 'lft >= ' . $referenceNode->lft;
				$data->right_where = 'rgt >= ' . $referenceNode->lft;

				$data->new_lft = $referenceNode->lft;
				$data->new_rgt = $referenceNode->lft + $nodeWidth - 1;
				$data->new_parent_id = $referenceNode->parent_id;
				$data->new_level = $referenceNode->level;
				break;

			default:
			case 'after':
				$data->left_where = 'lft > ' . $referenceNode->rgt;
				$data->right_where = 'rgt > ' . $referenceNode->rgt;

				$data->new_lft = $referenceNode->rgt + 1;
				$data->new_rgt = $referenceNode->rgt + $nodeWidth;
				$data->new_parent_id = $referenceNode->parent_id;
				$data->new_level = $referenceNode->level;
				break;
		}

		// @codeCoverageIgnoreStart
		if ($this->_debug)
		{
			echo "\nRepositioning Data for $position" . "\n-----------------------------------" . "\nLeft Where:    $data->left_where"
				. "\nRight Where:   $data->right_where" . "\nNew Lft:       $data->new_lft" . "\nNew Rgt:       $data->new_rgt"
				. "\nNew Parent ID: $data->new_parent_id" . "\nNew Level:     $data->new_level" . "\n";
		}
		// @codeCoverageIgnoreEnd

		return $data;
	}

	/**
	 * Method to create a log table in the buffer optionally showing the query and/or data.
	 *
	 * @param   boolean  $showData   True to show data
	 * @param   boolean  $showQuery  True to show query
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 * @since   11.1
	 */
	protected function _logtable($showData = true, $showQuery = true)
	{
		$sep = "\n" . str_pad('', 40, '-');
		$buffer = '';
		if ($showQuery)
		{
			$buffer .= "\n" . $this->_db->getQuery() . $sep;
		}

		if ($showData)
		{
			$query = $this->_db->getQuery(true)
				->select($this->_tbl_key . ', parent_id, lft, rgt, level')
				->from($this->_tbl)
				->order($this->_tbl_key);
			$this->_db->setQuery($query);

			$rows = $this->_db->loadRowList();
			$buffer .= sprintf("\n| %4s | %4s | %4s | %4s |", $this->_tbl_key, 'par', 'lft', 'rgt');
			$buffer .= $sep;

			foreach ($rows as $row)
			{
				$buffer .= sprintf("\n| %4s | %4s | %4s | %4s |", $row[0], $row[1], $row[2], $row[3]);
			}
			$buffer .= $sep;
		}
		echo $buffer;
	}

	/**
	 * Runs a query and unlocks the database on an error.
	 *
	 * @param   mixed   $query         A string or JDatabaseQuery object.
	 * @param   string  $errorMessage  Unused.
	 *
	 * @return  boolean  void
	 *
	 * @note    Since 12.1 this method returns void and will rethrow the database exception.
	 * @since   11.1
	 * @throws  RuntimeException on database error.
	 */
	protected function _runQuery($query, $errorMessage)
	{
		// Prepare to catch an exception.
		try
		{
			$this->_db->setQuery($query)->execute();

			// @codeCoverageIgnoreStart
			if ($this->_debug)
			{
				$this->_logtable();
			}
			// @codeCoverageIgnoreEnd
		}
		catch (Exception $e)
		{
			// Unlock the tables and rethrow.
			$this->_unlock();

			throw $e;
		}
	}
}
