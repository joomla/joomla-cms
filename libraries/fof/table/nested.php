<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  table
 * @copyright   Copyright (C) 2010 - 2015 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * A class to manage tables holding nested sets (hierarchical data)
 *
 * @property int    $lft   Left value (for nested set implementation)
 * @property int    $rgt   Right value (for nested set implementation)
 * @property string $hash  Slug hash (optional; for faster searching)
 * @property string $slug  Node's slug (optional)
 * @property string $title Title of the node (optional)
 */
class FOFTableNested extends FOFTable
{
	/** @var int The level (depth) of this node in the tree */
	protected $treeDepth = null;

	/** @var FOFTableNested The root node in the tree */
	protected $treeRoot = null;

	/** @var FOFTableNested The parent node of ourselves */
	protected $treeParent = null;

	/** @var bool Should I perform a nested get (used to query ascendants/descendants) */
	protected $treeNestedGet = false;

	/** @var   array  A collection of custom, additional where clauses to apply during buildQuery */
	protected $whereClauses = array();

	/**
	 * Public constructor. Overrides the parent constructor, making sure there are lft/rgt columns which make it
	 * compatible with nested sets.
	 *
	 * @param   string          $table  Name of the database table to model.
	 * @param   string          $key    Name of the primary key field in the table.
	 * @param   JDatabaseDriver &$db    Database driver
	 * @param   array           $config The configuration parameters array
	 *
	 * @throws \RuntimeException When lft/rgt columns are not found
	 */
	public function __construct($table, $key, &$db, $config = array())
	{
		parent::__construct($table, $key, $db, $config);

		if (!$this->hasField('lft') || !$this->hasField('rgt'))
		{
			throw new \RuntimeException("Table " . $this->getTableName() . " is not compatible with FOFTableNested: it does not have lft/rgt columns");
		}
	}

	/**
	 * Overrides the automated table checks to handle the 'hash' column for faster searching
	 *
	 * @return boolean
	 */
	public function check()
	{
		// Create a slug if there is a title and an empty slug
		if ($this->hasField('title') && $this->hasField('slug') && empty($this->slug))
		{
			$this->slug = FOFStringUtils::toSlug($this->title);
		}

		// Create the SHA-1 hash of the slug for faster searching (make sure the hash column is CHAR(64) to take
		// advantage of MySQL's optimised searching for fixed size CHAR columns)
		if ($this->hasField('hash') && $this->hasField('slug'))
		{
			$this->hash = sha1($this->slug);
		}

		// Reset cached values
		$this->resetTreeCache();

		return parent::check();
	}

	/**
	 * Delete a node, either the currently loaded one or the one specified in $id. If an $id is specified that node
	 * is loaded before trying to delete it. In the end the data model is reset. If the node has any children nodes
	 * they will be removed before the node itself is deleted.
	 *
	 * @param   integer $oid       The primary key value of the item to delete
	 *
	 * @throws  UnexpectedValueException
	 *
	 * @return  boolean  True on success
	 */
	public function delete($oid = null)
	{
		// Load the specified record (if necessary)
		if (!empty($oid))
		{
			$this->load($oid);
		}

        $k  = $this->_tbl_key;
        $pk = (!$oid) ? $this->$k : $oid;

        // If no primary key is given, return false.
        if (!$pk)
        {
            throw new UnexpectedValueException('Null primary key not allowed.');
        }

        // Execute the logic only if I have a primary key, otherwise I could have weird results
        // Perform the checks on the current node *BEFORE* starting to delete the children
        if (!$this->onBeforeDelete($oid))
        {
            return false;
        }

        $result = true;

		// Recursively delete all children nodes as long as we are not a leaf node and $recursive is enabled
		if (!$this->isLeaf())
		{
			// Get all sub-nodes
			$table = $this->getClone();
			$table->bind($this->getData());
			$subNodes = $table->getDescendants();

			// Delete all subnodes (goes through the model to trigger the observers)
			if (!empty($subNodes))
			{
				/** @var FOFTableNested $item */
				foreach ($subNodes as $item)
				{
                    // We have to pass the id, so we are getting it again from the database.
                    // We have to do in this way, since a previous child could have changed our lft and rgt values
					if(!$item->delete($item->$k))
                    {
                        // A subnode failed or prevents the delete, continue deleting other nodes,
                        // but preserve the current node (ie the parent)
                        $result = false;
                    }
				};

                // Load it again, since while deleting a children we could have updated ourselves, too
                $this->load($pk);
			}
		}

        if($result)
        {
            // Delete the row by primary key.
            $query = $this->_db->getQuery(true);
            $query->delete();
            $query->from($this->_tbl);
            $query->where($this->_tbl_key . ' = ' . $this->_db->q($pk));

            $this->_db->setQuery($query)->execute();

            $result = $this->onAfterDelete($oid);
        }

		return $result;
	}

    protected function onAfterDelete($oid)
    {
        $db = $this->getDbo();

        $myLeft  = $this->lft;
        $myRight = $this->rgt;

        $fldLft = $db->qn($this->getColumnAlias('lft'));
        $fldRgt = $db->qn($this->getColumnAlias('rgt'));

        // Move all siblings to the left
        $width = $this->rgt - $this->lft + 1;

        // Wrap everything in a transaction
        $db->transactionStart();

        try
        {
            // Shrink lft values
            $query = $db->getQuery(true)
                        ->update($db->qn($this->getTableName()))
                        ->set($fldLft . ' = ' . $fldLft . ' - '.$width)
                        ->where($fldLft . ' > ' . $db->q($myLeft));
            $db->setQuery($query)->execute();

            // Shrink rgt values
            $query = $db->getQuery(true)
                        ->update($db->qn($this->getTableName()))
                        ->set($fldRgt . ' = ' . $fldRgt . ' - '.$width)
                        ->where($fldRgt . ' > ' . $db->q($myRight));
            $db->setQuery($query)->execute();

            // Commit the transaction
            $db->transactionCommit();
        }
        catch (\Exception $e)
        {
            // Roll back the transaction on error
            $db->transactionRollback();

            throw $e;
        }

        return parent::onAfterDelete($oid);
    }

	/**
	 * Not supported in nested sets
	 *
	 * @param   string $where Ignored
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 */
	public function reorder($where = '')
	{
		throw new RuntimeException('reorder() is not supported by FOFTableNested');
	}

	/**
	 * Not supported in nested sets
	 *
	 * @param   integer $delta Ignored
	 * @param   string  $where Ignored
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 */
	public function move($delta, $where = '')
	{
		throw new RuntimeException('move() is not supported by FOFTableNested');
	}

	/**
	 * Create a new record with the provided data. It is inserted as the last child of the current node's parent
	 *
	 * @param   array $data The data to use in the new record
	 *
	 * @return  static  The new node
	 */
	public function create($data)
	{
		$newNode = $this->getClone();
		$newNode->reset();
		$newNode->bind($data);

		if ($this->isRoot())
		{
			return $newNode->insertAsChildOf($this);
		}
		else
		{
			return $newNode->insertAsChildOf($this->getParent());
		}
	}

	/**
	 * Makes a copy of the record, inserting it as the last child of the given node's parent.
	 *
	 * @param   integer|array  $cid  The primary key value (or values) or the record(s) to copy. 
	 *                               If null, the current record will be copied
	 * 
	 * @return self|FOFTableNested	 The last copied node
	 */
	public function copy($cid = null)
	{
		//We have to cast the id as array, or the helper function will return an empty set
		if($cid)
		{
			$cid = (array) $cid;
		}

        FOFUtilsArray::toInteger($cid);
		$k = $this->_tbl_key;

		if (count($cid) < 1)
		{
			if ($this->$k)
			{
				$cid = array($this->$k);
			}
			else
			{
				// Even if it's null, let's still create the record
				$this->create($this->getData());
				
				return $this;
			}
		}

		foreach ($cid as $item)
		{
			// Prevent load with id = 0

			if (!$item)
			{
				continue;
			}

			$this->load($item);
			
			$this->create($this->getData());
		}

		return $this;
	}

	/**
	 * Method to reset class properties to the defaults set in the class
	 * definition. It will ignore the primary key as well as any private class
	 * properties.
	 *
	 * @return void
	 */
	public function reset()
	{
		$this->resetTreeCache();

		parent::reset();
	}

	/**
	 * Insert the current node as a tree root. It is a good idea to never use this method, instead providing a root node
	 * in your schema installation and then sticking to only one root.
	 *
	 * @return self
	 */
	public function insertAsRoot()
	{
        // You can't insert a node that is already saved i.e. the table has an id
        if($this->getId())
        {
            throw new RuntimeException(__METHOD__.' can be only used with new nodes');
        }

		// First we need to find the right value of the last parent, a.k.a. the max(rgt) of the table
		$db = $this->getDbo();

		// Get the lft/rgt names
		$fldRgt = $db->qn($this->getColumnAlias('rgt'));

		$query = $db->getQuery(true)
			->select('MAX(' . $fldRgt . ')')
			->from($db->qn($this->getTableName()));
		$maxRgt = $db->setQuery($query, 0, 1)->loadResult();

		if (empty($maxRgt))
		{
			$maxRgt = 0;
		}

		$this->lft = ++$maxRgt;
		$this->rgt = ++$maxRgt;

		$this->store();

		return $this;
	}

	/**
	 * Insert the current node as the first (leftmost) child of a parent node.
	 *
	 * WARNING: If it's an existing node it will be COPIED, not moved.
	 *
	 * @param FOFTableNested $parentNode The node which will become our parent
	 *
	 * @return $this for chaining
	 *
	 * @throws Exception
	 * @throws RuntimeException
	 */
	public function insertAsFirstChildOf(FOFTableNested &$parentNode)
	{
        if($parentNode->lft >= $parentNode->rgt)
        {
            throw new RuntimeException('Invalid position values for the parent node');
        }

		// Get a reference to the database
		$db = $this->getDbo();

		// Get the field names
		$fldRgt = $db->qn($this->getColumnAlias('rgt'));
		$fldLft = $db->qn($this->getColumnAlias('lft'));

        // Nullify the PK, so a new record will be created
        $pk = $this->getKeyName();
        $this->$pk = null;

		// Get the value of the parent node's rgt
		$myLeft = $parentNode->lft;

		// Update my lft/rgt values
		$this->lft = $myLeft + 1;
		$this->rgt = $myLeft + 2;

		// Update parent node's right (we added two elements in there, remember?)
		$parentNode->rgt += 2;

		// Wrap everything in a transaction
		$db->transactionStart();

		try
		{
			// Make a hole (2 queries)
			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($fldLft . ' = ' . $fldLft . '+2')
				->where($fldLft . ' > ' . $db->q($myLeft));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($fldRgt . ' = ' . $fldRgt . '+ 2')
				->where($fldRgt . '>' . $db->q($myLeft));
			$db->setQuery($query)->execute();

			// Insert the new node
			$this->store();

			// Commit the transaction
			$db->transactionCommit();
		}
		catch (\Exception $e)
		{
			// Roll back the transaction on error
			$db->transactionRollback();

			throw $e;
		}

		return $this;
	}

	/**
	 * Insert the current node as the last (rightmost) child of a parent node.
	 *
	 * WARNING: If it's an existing node it will be COPIED, not moved.
	 *
	 * @param FOFTableNested $parentNode The node which will become our parent
	 *
	 * @return $this for chaining
	 *
	 * @throws Exception
	 * @throws RuntimeException
	 */
	public function insertAsLastChildOf(FOFTableNested &$parentNode)
	{
        if($parentNode->lft >= $parentNode->rgt)
        {
            throw new RuntimeException('Invalid position values for the parent node');
        }

		// Get a reference to the database
		$db = $this->getDbo();

		// Get the field names
		$fldRgt = $db->qn($this->getColumnAlias('rgt'));
		$fldLft = $db->qn($this->getColumnAlias('lft'));

        // Nullify the PK, so a new record will be created
        $pk = $this->getKeyName();
        $this->$pk = null;

		// Get the value of the parent node's lft
		$myRight = $parentNode->rgt;

		// Update my lft/rgt values
		$this->lft = $myRight;
		$this->rgt = $myRight + 1;

		// Update parent node's right (we added two elements in there, remember?)
		$parentNode->rgt += 2;

		// Wrap everything in a transaction
		$db->transactionStart();

		try
		{
			// Make a hole (2 queries)
			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($fldRgt . ' = ' . $fldRgt . '+2')
				->where($fldRgt . '>=' . $db->q($myRight));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($fldLft . ' = ' . $fldLft . '+2')
				->where($fldLft . '>' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Insert the new node
			$this->store();

			// Commit the transaction
			$db->transactionCommit();
		}
		catch (\Exception $e)
		{
			// Roll back the transaction on error
			$db->transactionRollback();

			throw $e;
		}

		return $this;
	}

	/**
	 * Alias for insertAsLastchildOf
	 *
     * @codeCoverageIgnore
	 * @param FOFTableNested $parentNode
	 *
	 * @return $this for chaining
	 *
	 * @throws Exception
	 */
	public function insertAsChildOf(FOFTableNested &$parentNode)
	{
		return $this->insertAsLastChildOf($parentNode);
	}

	/**
	 * Insert the current node to the left of (before) a sibling node
	 *
	 * WARNING: If it's an existing node it will be COPIED, not moved.
	 *
	 * @param FOFTableNested $siblingNode We will be inserted before this node
	 *
	 * @return $this for chaining
	 *
	 * @throws Exception
	 * @throws RuntimeException
	 */
	public function insertLeftOf(FOFTableNested &$siblingNode)
	{
        if($siblingNode->lft >= $siblingNode->rgt)
        {
            throw new RuntimeException('Invalid position values for the sibling node');
        }

		// Get a reference to the database
		$db = $this->getDbo();

		// Get the field names
		$fldRgt = $db->qn($this->getColumnAlias('rgt'));
		$fldLft = $db->qn($this->getColumnAlias('lft'));

        // Nullify the PK, so a new record will be created
        $pk = $this->getKeyName();
        $this->$pk = null;

		// Get the value of the parent node's rgt
		$myLeft = $siblingNode->lft;

		// Update my lft/rgt values
		$this->lft = $myLeft;
		$this->rgt = $myLeft + 1;

		// Update sibling's lft/rgt values
		$siblingNode->lft += 2;
		$siblingNode->rgt += 2;

		$db->transactionStart();

		try
		{
			$db->setQuery(
				$db->getQuery(true)
					->update($db->qn($this->getTableName()))
					->set($fldLft . ' = ' . $fldLft . '+2')
					->where($fldLft . ' >= ' . $db->q($myLeft))
			)->execute();

			$db->setQuery(
				$db->getQuery(true)
					->update($db->qn($this->getTableName()))
					->set($fldRgt . ' = ' . $fldRgt . '+2')
					->where($fldRgt . ' > ' . $db->q($myLeft))
			)->execute();

			$this->store();

            // Commit the transaction
            $db->transactionCommit();
		}
		catch (\Exception $e)
		{
			$db->transactionRollback();

			throw $e;
		}

		return $this;
	}

	/**
	 * Insert the current node to the right of (after) a sibling node
	 *
	 * WARNING: If it's an existing node it will be COPIED, not moved.
	 *
	 * @param FOFTableNested $siblingNode We will be inserted after this node
	 *
	 * @return $this for chaining
	 * @throws Exception
	 * @throws RuntimeException
	 */
	public function insertRightOf(FOFTableNested &$siblingNode)
	{
        if($siblingNode->lft >= $siblingNode->rgt)
        {
            throw new RuntimeException('Invalid position values for the sibling node');
        }

		// Get a reference to the database
		$db = $this->getDbo();

		// Get the field names
		$fldRgt = $db->qn($this->getColumnAlias('rgt'));
		$fldLft = $db->qn($this->getColumnAlias('lft'));

        // Nullify the PK, so a new record will be created
        $pk = $this->getKeyName();
        $this->$pk = null;

		// Get the value of the parent node's lft
		$myRight = $siblingNode->rgt;

		// Update my lft/rgt values
		$this->lft = $myRight + 1;
		$this->rgt = $myRight + 2;

		$db->transactionStart();

		try
		{
			$db->setQuery(
				$db->getQuery(true)
					->update($db->qn($this->getTableName()))
					->set($fldRgt . ' = ' . $fldRgt . '+2')
					->where($fldRgt . ' > ' . $db->q($myRight))
			)->execute();

			$db->setQuery(
				$db->getQuery(true)
					->update($db->qn($this->getTableName()))
					->set($fldLft . ' = ' . $fldLft . '+2')
					->where($fldLft . ' > ' . $db->q($myRight))
			)->execute();

			$this->store();

            // Commit the transaction
            $db->transactionCommit();
		}
		catch (\Exception $e)
		{
			$db->transactionRollback();

			throw $e;
		}

		return $this;
	}

	/**
	 * Alias for insertRightOf
	 *
     * @codeCoverageIgnore
	 * @param FOFTableNested $siblingNode
	 *
	 * @return $this for chaining
	 */
	public function insertAsSiblingOf(FOFTableNested &$siblingNode)
	{
		return $this->insertRightOf($siblingNode);
	}

	/**
	 * Move the current node (and its subtree) one position to the left in the tree, i.e. before its left-hand sibling
	 *
     * @throws  RuntimeException
     *
	 * @return $this
	 */
	public function moveLeft()
	{
        // Sanity checks on current node position
        if($this->lft >= $this->rgt)
        {
            throw new RuntimeException('Invalid position values for the current node');
        }

		// If it is a root node we will not move the node (roots don't participate in tree ordering)
		if ($this->isRoot())
		{
			return $this;
		}

		// Are we already the leftmost node?
		$parentNode = $this->getParent();

		if ($parentNode->lft == ($this->lft - 1))
		{
			return $this;
		}

		// Get the sibling to the left
		$db = $this->getDbo();
		$table = $this->getClone();
		$table->reset();
		$leftSibling = $table->whereRaw($db->qn($this->getColumnAlias('rgt')) . ' = ' . $db->q($this->lft - 1))
			->get(0, 1)->current();

		// Move the node
		if (is_object($leftSibling) && ($leftSibling instanceof FOFTableNested))
		{
			return $this->moveToLeftOf($leftSibling);
		}

		return false;
	}

	/**
	 * Move the current node (and its subtree) one position to the right in the tree, i.e. after its right-hand sibling
     *
     * @throws RuntimeException
	 *
	 * @return $this
	 */
	public function moveRight()
	{
        // Sanity checks on current node position
        if($this->lft >= $this->rgt)
        {
            throw new RuntimeException('Invalid position values for the current node');
        }

		// If it is a root node we will not move the node (roots don't participate in tree ordering)
		if ($this->isRoot())
		{
			return $this;
		}

		// Are we already the rightmost node?
		$parentNode = $this->getParent();

		if ($parentNode->rgt == ($this->rgt + 1))
		{
			return $this;
		}

		// Get the sibling to the right
		$db = $this->getDbo();

		$table = $this->getClone();
		$table->reset();
		$rightSibling = $table->whereRaw($db->qn($this->getColumnAlias('lft')) . ' = ' . $db->q($this->rgt + 1))
			->get(0, 1)->current();

		// Move the node
		if (is_object($rightSibling) && ($rightSibling instanceof FOFTableNested))
		{
			return $this->moveToRightOf($rightSibling);
		}

		return false;
	}

	/**
	 * Moves the current node (and its subtree) to the left of another node. The other node can be in a different
	 * position in the tree or even under a different root.
	 *
	 * @param FOFTableNested $siblingNode
	 *
	 * @return $this for chaining
	 *
	 * @throws Exception
	 * @throws RuntimeException
	 */
	public function moveToLeftOf(FOFTableNested $siblingNode)
	{
        // Sanity checks on current and sibling node position
        if($this->lft >= $this->rgt)
        {
            throw new RuntimeException('Invalid position values for the current node');
        }

        if($siblingNode->lft >= $siblingNode->rgt)
        {
            throw new RuntimeException('Invalid position values for the sibling node');
        }

		$db    = $this->getDbo();
		$left  = $db->qn($this->getColumnAlias('lft'));
		$right = $db->qn($this->getColumnAlias('rgt'));

		// Get node metrics
		$myLeft  = $this->lft;
		$myRight = $this->rgt;
		$myWidth = $myRight - $myLeft + 1;

		// Get sibling metrics
		$sibLeft = $siblingNode->lft;

		// Start the transaction
		$db->transactionStart();

		try
		{
			// Temporary remove subtree being moved
			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set("$left = " . $db->q(0) . " - $left")
				->set("$right = " . $db->q(0) . " - $right")
				->where($left . ' >= ' . $db->q($myLeft))
				->where($right . ' <= ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Close hole left behind
			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($left . ' = ' . $left . ' - ' . $db->q($myWidth))
				->where($left . ' > ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($right . ' = ' . $right . ' - ' . $db->q($myWidth))
				->where($right . ' > ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Make a hole for the new items
			$newSibLeft = ($sibLeft > $myRight) ? $sibLeft - $myWidth : $sibLeft;

			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($right . ' = ' . $right . ' + ' . $db->q($myWidth))
				->where($right . ' >= ' . $db->q($newSibLeft));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($left . ' = ' . $left . ' + ' . $db->q($myWidth))
				->where($left . ' >= ' . $db->q($newSibLeft));
			$db->setQuery($query)->execute();

			// Move node and subnodes
			$moveRight = $newSibLeft - $myLeft;

			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($left . ' = ' . $db->q(0) . ' - ' . $left . ' + ' . $db->q($moveRight))
				->set($right . ' = ' . $db->q(0) . ' - ' . $right . ' + ' . $db->q($moveRight))
				->where($left . ' <= 0 - ' . $db->q($myLeft))
				->where($right . ' >= 0 - ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Commit the transaction
			$db->transactionCommit();
		}
		catch (\Exception $e)
		{
			$db->transactionRollback();

			throw $e;
		}

        // Let's load the record again to fetch the new values for lft and rgt
        $this->load();

		return $this;
	}

	/**
	 * Moves the current node (and its subtree) to the right of another node. The other node can be in a different
	 * position in the tree or even under a different root.
	 *
	 * @param FOFTableNested $siblingNode
	 *
	 * @return $this for chaining
	 *
	 * @throws Exception
	 * @throws RuntimeException
	 */
	public function moveToRightOf(FOFTableNested $siblingNode)
	{
        // Sanity checks on current and sibling node position
        if($this->lft >= $this->rgt)
        {
            throw new RuntimeException('Invalid position values for the current node');
        }

        if($siblingNode->lft >= $siblingNode->rgt)
        {
            throw new RuntimeException('Invalid position values for the sibling node');
        }

		$db    = $this->getDbo();
		$left  = $db->qn($this->getColumnAlias('lft'));
		$right = $db->qn($this->getColumnAlias('rgt'));

		// Get node metrics
		$myLeft  = $this->lft;
		$myRight = $this->rgt;
		$myWidth = $myRight - $myLeft + 1;

		// Get parent metrics
		$sibRight = $siblingNode->rgt;

		// Start the transaction
		$db->transactionStart();

		try
		{
			// Temporary remove subtree being moved
			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set("$left = " . $db->q(0) . " - $left")
				->set("$right = " . $db->q(0) . " - $right")
				->where($left . ' >= ' . $db->q($myLeft))
				->where($right . ' <= ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Close hole left behind
			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($left . ' = ' . $left . ' - ' . $db->q($myWidth))
				->where($left . ' > ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($right . ' = ' . $right . ' - ' . $db->q($myWidth))
				->where($right . ' > ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Make a hole for the new items
			$newSibRight = ($sibRight > $myRight) ? $sibRight - $myWidth : $sibRight;

			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($left . ' = ' . $left . ' + ' . $db->q($myWidth))
				->where($left . ' > ' . $db->q($newSibRight));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($right . ' = ' . $right . ' + ' . $db->q($myWidth))
				->where($right . ' > ' . $db->q($newSibRight));
			$db->setQuery($query)->execute();

			// Move node and subnodes
			$moveRight = ($sibRight > $myRight) ? $sibRight - $myRight : $sibRight - $myRight + $myWidth;

			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($left . ' = ' . $db->q(0) . ' - ' . $left . ' + ' . $db->q($moveRight))
				->set($right . ' = ' . $db->q(0) . ' - ' . $right . ' + ' . $db->q($moveRight))
				->where($left . ' <= 0 - ' . $db->q($myLeft))
				->where($right . ' >= 0 - ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Commit the transaction
			$db->transactionCommit();
		}
		catch (\Exception $e)
		{
			$db->transactionRollback();

			throw $e;
		}

        // Let's load the record again to fetch the new values for lft and rgt
        $this->load();

		return $this;
	}

	/**
	 * Alias for moveToRightOf
	 *
     * @codeCoverageIgnore
	 * @param FOFTableNested $siblingNode
	 *
	 * @return $this for chaining
	 */
	public function makeNextSiblingOf(FOFTableNested $siblingNode)
	{
		return $this->moveToRightOf($siblingNode);
	}

	/**
	 * Alias for makeNextSiblingOf
	 *
     * @codeCoverageIgnore
	 * @param FOFTableNested $siblingNode
	 *
	 * @return $this for chaining
	 */
	public function makeSiblingOf(FOFTableNested $siblingNode)
	{
		return $this->makeNextSiblingOf($siblingNode);
	}

	/**
	 * Alias for moveToLeftOf
	 *
     * @codeCoverageIgnore
	 * @param FOFTableNested $siblingNode
	 *
	 * @return $this for chaining
	 */
	public function makePreviousSiblingOf(FOFTableNested $siblingNode)
	{
		return $this->moveToLeftOf($siblingNode);
	}

	/**
	 * Moves a node and its subtree as a the first (leftmost) child of $parentNode
	 *
	 * @param FOFTableNested $parentNode
	 *
	 * @return $this for chaining
	 *
	 * @throws Exception
	 */
	public function makeFirstChildOf(FOFTableNested $parentNode)
	{
        // Sanity checks on current and sibling node position
        if($this->lft >= $this->rgt)
        {
            throw new RuntimeException('Invalid position values for the current node');
        }

        if($parentNode->lft >= $parentNode->rgt)
        {
            throw new RuntimeException('Invalid position values for the parent node');
        }

		$db    = $this->getDbo();
		$left  = $db->qn($this->getColumnAlias('lft'));
		$right = $db->qn($this->getColumnAlias('rgt'));

		// Get node metrics
		$myLeft  = $this->lft;
		$myRight = $this->rgt;
		$myWidth = $myRight - $myLeft + 1;

		// Get parent metrics
		$parentLeft = $parentNode->lft;

		// Start the transaction
		$db->transactionStart();

		try
		{
			// Temporary remove subtree being moved
			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set("$left = " . $db->q(0) . " - $left")
				->set("$right = " . $db->q(0) . " - $right")
				->where($left . ' >= ' . $db->q($myLeft))
				->where($right . ' <= ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Close hole left behind
			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($left . ' = ' . $left . ' - ' . $db->q($myWidth))
				->where($left . ' > ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($right . ' = ' . $right . ' - ' . $db->q($myWidth))
				->where($right . ' > ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Make a hole for the new items
			$newParentLeft = ($parentLeft > $myRight) ? $parentLeft - $myWidth : $parentLeft;

			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($right . ' = ' . $right . ' + ' . $db->q($myWidth))
				->where($right . ' >= ' . $db->q($newParentLeft));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($left . ' = ' . $left . ' + ' . $db->q($myWidth))
				->where($left . ' > ' . $db->q($newParentLeft));
			$db->setQuery($query)->execute();

			// Move node and subnodes
			$moveRight = $newParentLeft - $myLeft + 1;

			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($left . ' = ' . $db->q(0) . ' - ' . $left . ' + ' . $db->q($moveRight))
				->set($right . ' = ' . $db->q(0) . ' - ' . $right . ' + ' . $db->q($moveRight))
				->where($left . ' <= 0 - ' . $db->q($myLeft))
				->where($right . ' >= 0 - ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Commit the transaction
			$db->transactionCommit();
		}
		catch (\Exception $e)
		{
			$db->transactionRollback();

			throw $e;
		}

        // Let's load the record again to fetch the new values for lft and rgt
        $this->load();

		return $this;
	}

	/**
	 * Moves a node and its subtree as a the last (rightmost) child of $parentNode
	 *
	 * @param FOFTableNested $parentNode
	 *
	 * @return $this for chaining
	 *
	 * @throws Exception
	 * @throws RuntimeException
	 */
	public function makeLastChildOf(FOFTableNested $parentNode)
	{
        // Sanity checks on current and sibling node position
        if($this->lft >= $this->rgt)
        {
            throw new RuntimeException('Invalid position values for the current node');
        }

        if($parentNode->lft >= $parentNode->rgt)
        {
            throw new RuntimeException('Invalid position values for the parent node');
        }

		$db    = $this->getDbo();
		$left  = $db->qn($this->getColumnAlias('lft'));
		$right = $db->qn($this->getColumnAlias('rgt'));

		// Get node metrics
		$myLeft  = $this->lft;
		$myRight = $this->rgt;
		$myWidth = $myRight - $myLeft + 1;

		// Get parent metrics
		$parentRight = $parentNode->rgt;

		// Start the transaction
		$db->transactionStart();

		try
		{
			// Temporary remove subtree being moved
			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set("$left = " . $db->q(0) . " - $left")
				->set("$right = " . $db->q(0) . " - $right")
				->where($left . ' >= ' . $db->q($myLeft))
				->where($right . ' <= ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Close hole left behind
			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($left . ' = ' . $left . ' - ' . $db->q($myWidth))
				->where($left . ' > ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($right . ' = ' . $right . ' - ' . $db->q($myWidth))
				->where($right . ' > ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Make a hole for the new items
			$newLeft = ($parentRight > $myRight) ? $parentRight - $myWidth : $parentRight;

			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($left . ' = ' . $left . ' + ' . $db->q($myWidth))
				->where($left . ' >= ' . $db->q($newLeft));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($right . ' = ' . $right . ' + ' . $db->q($myWidth))
				->where($right . ' >= ' . $db->q($newLeft));
			$db->setQuery($query)->execute();

			// Move node and subnodes
			$moveRight = ($parentRight > $myRight) ? $parentRight - $myRight - 1 : $parentRight - $myRight - 1 + $myWidth;

			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($left . ' = ' . $db->q(0) . ' - ' . $left . ' + ' . $db->q($moveRight))
				->set($right . ' = ' . $db->q(0) . ' - ' . $right . ' + ' . $db->q($moveRight))
				->where($left . ' <= 0 - ' . $db->q($myLeft))
				->where($right . ' >= 0 - ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Commit the transaction
			$db->transactionCommit();
		}
		catch (\Exception $e)
		{
			$db->transactionRollback();

			throw $e;
		}

        // Let's load the record again to fetch the new values for lft and rgt
        $this->load();

		return $this;
	}

	/**
	 * Alias for makeLastChildOf
	 *
     * @codeCoverageIgnore
	 * @param FOFTableNested $parentNode
	 *
	 * @return $this for chaining
	 */
	public function makeChildOf(FOFTableNested $parentNode)
	{
		return $this->makeLastChildOf($parentNode);
	}

	/**
	 * Makes the current node a root (and moving its entire subtree along the way). This is achieved by moving the node
	 * to the right of its root node
	 *
	 * @return  $this  for chaining
	 */
	public function makeRoot()
	{
		// Make sure we are not a root
		if ($this->isRoot())
		{
			return $this;
		}

		// Get a reference to my root
		$myRoot = $this->getRoot();

		// Double check I am not a root
		if ($this->equals($myRoot))
		{
			return $this;
		}

		// Move myself to the right of my root
		$this->moveToRightOf($myRoot);
		$this->treeDepth = 0;

		return $this;
	}

	/**
	 * Gets the level (depth) of this node in the tree. The result is cached in $this->treeDepth for faster retrieval.
	 *
     * @throws  RuntimeException
     *
	 * @return int|mixed
	 */
	public function getLevel()
	{
        // Sanity checks on current node position
        if($this->lft >= $this->rgt)
        {
            throw new RuntimeException('Invalid position values for the current node');
        }

		if (is_null($this->treeDepth))
		{
			$db = $this->getDbo();

			$fldLft = $db->qn($this->getColumnAlias('lft'));
			$fldRgt = $db->qn($this->getColumnAlias('rgt'));

			$query = $db->getQuery(true)
				->select('(COUNT(' . $db->qn('parent') . '.' . $fldLft . ') - 1) AS ' . $db->qn('depth'))
				->from($db->qn($this->getTableName()) . ' AS ' . $db->qn('node'))
				->join('CROSS', $db->qn($this->getTableName()) . ' AS ' . $db->qn('parent'))
				->where($db->qn('node') . '.' . $fldLft . ' >= ' . $db->qn('parent') . '.' . $fldLft)
				->where($db->qn('node') . '.' . $fldLft . ' <= ' . $db->qn('parent') . '.' . $fldRgt)
				->where($db->qn('node') . '.' . $fldLft . ' = ' . $db->q($this->lft))
				->group($db->qn('node') . '.' . $fldLft)
				->order($db->qn('node') . '.' . $fldLft . ' ASC');

			$this->treeDepth = $db->setQuery($query, 0, 1)->loadResult();
		}

		return $this->treeDepth;
	}

	/**
	 * Returns the immediate parent of the current node
	 *
     * @throws RuntimeException
     *
	 * @return FOFTableNested
	 */
	public function getParent()
	{
        // Sanity checks on current node position
        if($this->lft >= $this->rgt)
        {
            throw new RuntimeException('Invalid position values for the current node');
        }

		if ($this->isRoot())
		{
			return $this;
		}

		if (empty($this->treeParent) || !is_object($this->treeParent) || !($this->treeParent instanceof FOFTableNested))
		{
			$db = $this->getDbo();

			$fldLft = $db->qn($this->getColumnAlias('lft'));
			$fldRgt = $db->qn($this->getColumnAlias('rgt'));

			$query = $db->getQuery(true)
				->select($db->qn('parent') . '.' . $fldLft)
				->from($db->qn($this->getTableName()) . ' AS ' . $db->qn('node'))
				->join('CROSS', $db->qn($this->getTableName()) . ' AS ' . $db->qn('parent'))
				->where($db->qn('node') . '.' . $fldLft . ' > ' . $db->qn('parent') . '.' . $fldLft)
				->where($db->qn('node') . '.' . $fldLft . ' <= ' . $db->qn('parent') . '.' . $fldRgt)
				->where($db->qn('node') . '.' . $fldLft . ' = ' . $db->q($this->lft))
				->order($db->qn('parent') . '.' . $fldLft . ' DESC');
			$targetLft = $db->setQuery($query, 0, 1)->loadResult();

			$table = $this->getClone();
			$table->reset();
			$this->treeParent = $table
				->whereRaw($fldLft . ' = ' . $db->q($targetLft))
				->get()->current();
		}

		return $this->treeParent;
	}

	/**
	 * Is this a top-level root node?
	 *
	 * @return bool
	 */
	public function isRoot()
	{
		// If lft=1 it is necessarily a root node
		if ($this->lft == 1)
		{
			return true;
		}

		// Otherwise make sure its level is 0
		return $this->getLevel() == 0;
	}

	/**
	 * Is this a leaf node (a node without children)?
	 *
     * @throws  RuntimeException
     *
	 * @return bool
	 */
	public function isLeaf()
	{
        // Sanity checks on current node position
        if($this->lft >= $this->rgt)
        {
            throw new RuntimeException('Invalid position values for the current node');
        }

		return ($this->rgt - 1) == $this->lft;
	}

	/**
	 * Is this a child node (not root)?
	 *
     * @codeCoverageIgnore
     *
	 * @return bool
	 */
	public function isChild()
	{
		return !$this->isRoot();
	}

	/**
	 * Returns true if we are a descendant of $otherNode
	 *
	 * @param FOFTableNested $otherNode
	 *
     * @throws  RuntimeException
     *
	 * @return bool
	 */
	public function isDescendantOf(FOFTableNested $otherNode)
	{
        // Sanity checks on current node position
        if($this->lft >= $this->rgt)
        {
            throw new RuntimeException('Invalid position values for the current node');
        }

        if($otherNode->lft >= $otherNode->rgt)
        {
            throw new RuntimeException('Invalid position values for the other node');
        }

		return ($otherNode->lft < $this->lft) && ($otherNode->rgt > $this->rgt);
	}

	/**
	 * Returns true if $otherNode is ourselves or if we are a descendant of $otherNode
	 *
	 * @param FOFTableNested $otherNode
	 *
     * @throws  RuntimeException
     *
	 * @return bool
	 */
	public function isSelfOrDescendantOf(FOFTableNested $otherNode)
	{
        // Sanity checks on current node position
        if($this->lft >= $this->rgt)
        {
            throw new RuntimeException('Invalid position values for the current node');
        }

        if($otherNode->lft >= $otherNode->rgt)
        {
            throw new RuntimeException('Invalid position values for the other node');
        }

		return ($otherNode->lft <= $this->lft) && ($otherNode->rgt >= $this->rgt);
	}

	/**
	 * Returns true if we are an ancestor of $otherNode
	 *
     * @codeCoverageIgnore
	 * @param FOFTableNested $otherNode
	 *
	 * @return bool
	 */
	public function isAncestorOf(FOFTableNested $otherNode)
	{
		return $otherNode->isDescendantOf($this);
	}

	/**
	 * Returns true if $otherNode is ourselves or we are an ancestor of $otherNode
	 *
     * @codeCoverageIgnore
	 * @param FOFTableNested $otherNode
	 *
	 * @return bool
	 */
	public function isSelfOrAncestorOf(FOFTableNested $otherNode)
	{
		return $otherNode->isSelfOrDescendantOf($this);
	}

	/**
	 * Is $node this very node?
	 *
	 * @param FOFTableNested $node
	 *
     * @throws  RuntimeException
     *
	 * @return bool
	 */
	public function equals(FOFTableNested &$node)
	{
        // Sanity checks on current node position
        if($this->lft >= $this->rgt)
        {
            throw new RuntimeException('Invalid position values for the current node');
        }

        if($node->lft >= $node->rgt)
        {
            throw new RuntimeException('Invalid position values for the other node');
        }

		return (
			($this->getId() == $node->getId())
			&& ($this->lft == $node->lft)
			&& ($this->rgt == $node->rgt)
		);
	}

	/**
	 * Alias for isDescendantOf
	 *
     * @codeCoverageIgnore
	 * @param FOFTableNested $otherNode
     *
	 * @return bool
	 */
	public function insideSubtree(FOFTableNested $otherNode)
	{
		return $this->isDescendantOf($otherNode);
	}

	/**
	 * Returns true if both this node and $otherNode are root, leaf or child (same tree scope)
	 *
	 * @param FOFTableNested $otherNode
	 *
	 * @return bool
	 */
	public function inSameScope(FOFTableNested $otherNode)
	{
		if ($this->isLeaf())
		{
			return $otherNode->isLeaf();
		}
		elseif ($this->isRoot())
		{
			return $otherNode->isRoot();
		}
		elseif ($this->isChild())
		{
			return $otherNode->isChild();
		}
		else
		{
			return false;
		}
	}

	/**
	 * get() will return all ancestor nodes and ourselves
	 *
	 * @return void
	 */
	protected function scopeAncestorsAndSelf()
	{
		$this->treeNestedGet = true;

		$db = $this->getDbo();

		$fldLft = $db->qn($this->getColumnAlias('lft'));
		$fldRgt = $db->qn($this->getColumnAlias('rgt'));

		$this->whereRaw($db->qn('parent') . '.' . $fldLft . ' >= ' . $db->qn('node') . '.' . $fldLft);
		$this->whereRaw($db->qn('parent') . '.' . $fldLft . ' <= ' . $db->qn('node') . '.' . $fldRgt);
		$this->whereRaw($db->qn('parent') . '.' . $fldLft . ' = ' . $db->q($this->lft));
	}

	/**
	 * get() will return all ancestor nodes but not ourselves
	 *
	 * @return void
	 */
	protected function scopeAncestors()
	{
		$this->treeNestedGet = true;

		$db = $this->getDbo();

		$fldLft = $db->qn($this->getColumnAlias('lft'));
		$fldRgt = $db->qn($this->getColumnAlias('rgt'));

		$this->whereRaw($db->qn('parent') . '.' . $fldLft . ' > ' . $db->qn('node') . '.' . $fldLft);
		$this->whereRaw($db->qn('parent') . '.' . $fldLft . ' < ' . $db->qn('node') . '.' . $fldRgt);
		$this->whereRaw($db->qn('parent') . '.' . $fldLft . ' = ' . $db->q($this->lft));
	}

	/**
	 * get() will return all sibling nodes and ourselves
	 *
	 * @return void
	 */
	protected function scopeSiblingsAndSelf()
	{
		$db = $this->getDbo();

		$fldLft = $db->qn($this->getColumnAlias('lft'));
		$fldRgt = $db->qn($this->getColumnAlias('rgt'));

		$parent = $this->getParent();
		$this->whereRaw($db->qn('node') . '.' . $fldLft . ' > ' . $db->q($parent->lft));
		$this->whereRaw($db->qn('node') . '.' . $fldRgt . ' < ' . $db->q($parent->rgt));
	}

	/**
	 * get() will return all sibling nodes but not ourselves
	 *
     * @codeCoverageIgnore
     *
	 * @return void
	 */
	protected function scopeSiblings()
	{
		$this->scopeSiblingsAndSelf();
		$this->scopeWithoutSelf();
	}

	/**
	 * get() will return only leaf nodes
	 *
	 * @return void
	 */
	protected function scopeLeaves()
	{
		$db = $this->getDbo();

		$fldLft = $db->qn($this->getColumnAlias('lft'));
		$fldRgt = $db->qn($this->getColumnAlias('rgt'));

		$this->whereRaw($db->qn('node') . '.' . $fldLft . ' = ' . $db->qn('node') . '.' . $fldRgt . ' - ' . $db->q(1));
	}

	/**
	 * get() will return all descendants (even subtrees of subtrees!) and ourselves
	 *
	 * @return void
	 */
	protected function scopeDescendantsAndSelf()
	{
		$this->treeNestedGet = true;

		$db = $this->getDbo();

		$fldLft = $db->qn($this->getColumnAlias('lft'));
		$fldRgt = $db->qn($this->getColumnAlias('rgt'));

		$this->whereRaw($db->qn('node') . '.' . $fldLft . ' >= ' . $db->qn('parent') . '.' . $fldLft);
		$this->whereRaw($db->qn('node') . '.' . $fldLft . ' <= ' . $db->qn('parent') . '.' . $fldRgt);
		$this->whereRaw($db->qn('parent') . '.' . $fldLft . ' = ' . $db->q($this->lft));
	}

	/**
	 * get() will return all descendants (even subtrees of subtrees!) but not ourselves
	 *
	 * @return void
	 */
	protected function scopeDescendants()
	{
		$this->treeNestedGet = true;

		$db = $this->getDbo();

		$fldLft = $db->qn($this->getColumnAlias('lft'));
		$fldRgt = $db->qn($this->getColumnAlias('rgt'));

		$this->whereRaw($db->qn('node') . '.' . $fldLft . ' > ' . $db->qn('parent') . '.' . $fldLft);
		$this->whereRaw($db->qn('node') . '.' . $fldLft . ' < ' . $db->qn('parent') . '.' . $fldRgt);
		$this->whereRaw($db->qn('parent') . '.' . $fldLft . ' = ' . $db->q($this->lft));
	}

	/**
	 * get() will only return immediate descendants (first level children) of the current node
	 *
	 * @return void
	 */
	protected function scopeImmediateDescendants()
	{
        // Sanity checks on current node position
        if($this->lft >= $this->rgt)
        {
            throw new RuntimeException('Invalid position values for the current node');
        }

		$db = $this->getDbo();

		$fldLft = $db->qn($this->getColumnAlias('lft'));
		$fldRgt = $db->qn($this->getColumnAlias('rgt'));

		$subQuery = $db->getQuery(true)
			->select(array(
				$db->qn('node') . '.' . $fldLft,
				'(COUNT(*) - 1) AS ' . $db->qn('depth')
			))
			->from($db->qn($this->getTableName()) . ' AS ' . $db->qn('node'))
			->from($db->qn($this->getTableName()) . ' AS ' . $db->qn('parent'))
			->where($db->qn('node') . '.' . $fldLft . ' >= ' . $db->qn('parent') . '.' . $fldLft)
			->where($db->qn('node') . '.' . $fldLft . ' <= ' . $db->qn('parent') . '.' . $fldRgt)
			->where($db->qn('node') . '.' . $fldLft . ' = ' . $db->q($this->lft))
			->group($db->qn('node') . '.' . $fldLft)
			->order($db->qn('node') . '.' . $fldLft . ' ASC');

		$query = $db->getQuery(true)
			->select(array(
				$db->qn('node') . '.' . $fldLft,
				'(COUNT(' . $db->qn('parent') . '.' . $fldLft . ') - (' .
				$db->qn('sub_tree') . '.' . $db->qn('depth') . ' + 1)) AS ' . $db->qn('depth')
			))
			->from($db->qn($this->getTableName()) . ' AS ' . $db->qn('node'))
			->join('CROSS', $db->qn($this->getTableName()) . ' AS ' . $db->qn('parent'))
			->join('CROSS', $db->qn($this->getTableName()) . ' AS ' . $db->qn('sub_parent'))
			->join('CROSS', '(' . $subQuery . ') AS ' . $db->qn('sub_tree'))
			->where($db->qn('node') . '.' . $fldLft . ' >= ' . $db->qn('parent') . '.' . $fldLft)
			->where($db->qn('node') . '.' . $fldLft . ' <= ' . $db->qn('parent') . '.' . $fldRgt)
			->where($db->qn('node') . '.' . $fldLft . ' >= ' . $db->qn('sub_parent') . '.' . $fldLft)
			->where($db->qn('node') . '.' . $fldLft . ' <= ' . $db->qn('sub_parent') . '.' . $fldRgt)
			->where($db->qn('sub_parent') . '.' . $fldLft . ' = ' . $db->qn('sub_tree') . '.' . $fldLft)
			->group($db->qn('node') . '.' . $fldLft)
			->having(array(
				$db->qn('depth') . ' > ' . $db->q(0),
				$db->qn('depth') . ' <= ' . $db->q(1),
			))
			->order($db->qn('node') . '.' . $fldLft . ' ASC');

		$leftValues = $db->setQuery($query)->loadColumn();

		if (empty($leftValues))
		{
			$leftValues = array(0);
		}

		array_walk($leftValues, function (&$item, $key) use (&$db)
		{
			$item = $db->q($item);
		});

		$this->whereRaw($db->qn('node') . '.' . $fldLft . ' IN (' . implode(',', $leftValues) . ')');
	}

	/**
	 * get() will not return the selected node if it's part of the query results
	 *
	 * @param FOFTableNested $node The node to exclude from the results
	 *
	 * @return void
	 */
	public function withoutNode(FOFTableNested $node)
	{
		$db = $this->getDbo();

		$fldLft = $db->qn($this->getColumnAlias('lft'));

		$this->whereRaw('NOT(' . $db->qn('node') . '.' . $fldLft . ' = ' . $db->q($node->lft) . ')');
	}

	/**
	 * get() will not return ourselves if it's part of the query results
	 *
     * @codeCoverageIgnore
     *
	 * @return void
	 */
	protected function scopeWithoutSelf()
	{
		$this->withoutNode($this);
	}

	/**
	 * get() will not return our root if it's part of the query results
	 *
     * @codeCoverageIgnore
     *
	 * @return void
	 */
	protected function scopeWithoutRoot()
	{
		$rootNode = $this->getRoot();
		$this->withoutNode($rootNode);
	}

	/**
	 * Returns the root node of the tree this node belongs to
	 *
	 * @return self
	 *
	 * @throws \RuntimeException
	 */
	public function getRoot()
	{
        // Sanity checks on current node position
        if($this->lft >= $this->rgt)
        {
            throw new RuntimeException('Invalid position values for the current node');
        }

		// If this is a root node return itself (there is no such thing as the root of a root node)
		if ($this->isRoot())
		{
			return $this;
		}

		if (empty($this->treeRoot) || !is_object($this->treeRoot) || !($this->treeRoot instanceof FOFTableNested))
		{
			$this->treeRoot = null;

			// First try to get the record with the minimum ID
			$db = $this->getDbo();

			$fldLft = $db->qn($this->getColumnAlias('lft'));
			$fldRgt = $db->qn($this->getColumnAlias('rgt'));

			$subQuery = $db->getQuery(true)
				->select('MIN(' . $fldLft . ')')
				->from($db->qn($this->getTableName()));

			try
			{
				$table = $this->getClone();
				$table->reset();
				$root = $table
					->whereRaw($fldLft . ' = (' . (string)$subQuery . ')')
					->get(0, 1)->current();

				if ($this->isDescendantOf($root))
				{
					$this->treeRoot = $root;
				}
			}
			catch (\RuntimeException $e)
			{
				// If there is no root found throw an exception. Basically: your table is FUBAR.
				throw new \RuntimeException("No root found for table " . $this->getTableName() . ", node lft=" . $this->lft);
			}

			// If the above method didn't work, get all roots and select the one with the appropriate lft/rgt values
			if (is_null($this->treeRoot))
			{
				// Find the node with depth = 0, lft < our lft and rgt > our right. That's our root node.
				$query = $db->getQuery(true)
					->select(array(
                        $db->qn('node') . '.' . $fldLft,
						'(COUNT(' . $db->qn('parent') . '.' . $fldLft . ') - 1) AS ' . $db->qn('depth')
					))
					->from($db->qn($this->getTableName()) . ' AS ' . $db->qn('node'))
					->join('CROSS', $db->qn($this->getTableName()) . ' AS ' . $db->qn('parent'))
					->where($db->qn('node') . '.' . $fldLft . ' >= ' . $db->qn('parent') . '.' . $fldLft)
					->where($db->qn('node') . '.' . $fldLft . ' <= ' . $db->qn('parent') . '.' . $fldRgt)
					->where($db->qn('node') . '.' . $fldLft . ' < ' . $db->q($this->lft))
					->where($db->qn('node') . '.' . $fldRgt . ' > ' . $db->q($this->rgt))
					->having($db->qn('depth') . ' = ' . $db->q(0))
					->group($db->qn('node') . '.' . $fldLft);

				// Get the lft value
				$targetLeft = $db->setQuery($query)->loadResult();

				if (empty($targetLeft))
				{
					// If there is no root found throw an exception. Basically: your table is FUBAR.
					throw new \RuntimeException("No root found for table " . $this->getTableName() . ", node lft=" . $this->lft);
				}

				try
				{
					$table = $this->getClone();
					$table->reset();
					$this->treeRoot = $table
						->whereRaw($fldLft . ' = ' . $db->q($targetLeft))
						->get(0, 1)->current();
				}
				catch (\RuntimeException $e)
				{
					// If there is no root found throw an exception. Basically: your table is FUBAR.
					throw new \RuntimeException("No root found for table " . $this->getTableName() . ", node lft=" . $this->lft);
				}
			}
		}

		return $this->treeRoot;
	}

	/**
	 * Get all ancestors to this node and the node itself. In other words it gets the full path to the node and the node
	 * itself.
	 *
     * @codeCoverageIgnore
     *
	 * @return FOFDatabaseIterator
	 */
	public function getAncestorsAndSelf()
	{
		$this->scopeAncestorsAndSelf();

		return $this->get();
	}

	/**
	 * Get all ancestors to this node and the node itself, but not the root node. If you want to
	 *
     * @codeCoverageIgnore
     *
	 * @return FOFDatabaseIterator
	 */
	public function getAncestorsAndSelfWithoutRoot()
	{
		$this->scopeAncestorsAndSelf();
		$this->scopeWithoutRoot();

		return $this->get();
	}

	/**
	 * Get all ancestors to this node but not the node itself. In other words it gets the path to the node, without the
	 * node itself.
	 *
     * @codeCoverageIgnore
     *
	 * @return FOFDatabaseIterator
	 */
	public function getAncestors()
	{
		$this->scopeAncestorsAndSelf();
		$this->scopeWithoutSelf();

		return $this->get();
	}

	/**
	 * Get all ancestors to this node but not the node itself and its root.
	 *
     * @codeCoverageIgnore
     *
	 * @return FOFDatabaseIterator
	 */
	public function getAncestorsWithoutRoot()
	{
		$this->scopeAncestors();
		$this->scopeWithoutRoot();

		return $this->get();
	}

	/**
	 * Get all sibling nodes, including ourselves
	 *
     * @codeCoverageIgnore
     *
	 * @return FOFDatabaseIterator
	 */
	public function getSiblingsAndSelf()
	{
		$this->scopeSiblingsAndSelf();

		return $this->get();
	}

	/**
	 * Get all sibling nodes, except ourselves
	 *
     * @codeCoverageIgnore
     *
	 * @return FOFDatabaseIterator
	 */
	public function getSiblings()
	{
		$this->scopeSiblings();

		return $this->get();
	}

	/**
	 * Get all leaf nodes in the tree. You may want to use the scopes to narrow down the search in a specific subtree or
	 * path.
	 *
     * @codeCoverageIgnore
     *
	 * @return FOFDatabaseIterator
	 */
	public function getLeaves()
	{
		$this->scopeLeaves();

		return $this->get();
	}

	/**
	 * Get all descendant (children) nodes and ourselves.
	 *
	 * Note: all descendant nodes, even descendants of our immediate descendants, will be returned.
	 *
     * @codeCoverageIgnore
     *
	 * @return FOFDatabaseIterator
	 */
	public function getDescendantsAndSelf()
	{
		$this->scopeDescendantsAndSelf();

		return $this->get();
	}

	/**
	 * Get only our descendant (children) nodes, not ourselves.
	 *
	 * Note: all descendant nodes, even descendants of our immediate descendants, will be returned.
	 *
     * @codeCoverageIgnore
     *
	 * @return FOFDatabaseIterator
	 */
	public function getDescendants()
	{
		$this->scopeDescendants();

		return $this->get();
	}

	/**
	 * Get the immediate descendants (children). Unlike getDescendants it only goes one level deep into the tree
	 * structure. Descendants of descendant nodes will not be returned.
	 *
     * @codeCoverageIgnore
     *
	 * @return FOFDatabaseIterator
	 */
	public function getImmediateDescendants()
	{
		$this->scopeImmediateDescendants();

		return $this->get();
	}

	/**
	 * Returns a hashed array where each element's key is the value of the $key column (default: the ID column of the
	 * table) and its value is the value of the $column column (default: title). Each nesting level will have the value
	 * of the $column column prefixed by a number of $separator strings, as many as its nesting level (depth).
	 *
	 * This is useful for creating HTML select elements showing the hierarchy in a human readable format.
	 *
	 * @param string $column
	 * @param null   $key
	 * @param string $seperator
	 *
	 * @return array
	 */
	public function getNestedList($column = 'title', $key = null, $seperator = '  ')
	{
		$db = $this->getDbo();

		$fldLft = $db->qn($this->getColumnAlias('lft'));
		$fldRgt = $db->qn($this->getColumnAlias('rgt'));

		if (empty($key) || !$this->hasField($key))
		{
			$key = $this->getKeyName();
		}

		if (empty($column))
		{
			$column = 'title';
		}

		$fldKey = $db->qn($this->getColumnAlias($key));
		$fldColumn = $db->qn($this->getColumnAlias($column));

		$query = $db->getQuery(true)
			->select(array(
				$db->qn('node') . '.' . $fldKey,
				$db->qn('node') . '.' . $fldColumn,
				'(COUNT(' . $db->qn('parent') . '.' . $fldKey . ') - 1) AS ' . $db->qn('depth')
			))
			->from($db->qn($this->getTableName()) . ' AS ' . $db->qn('node'))
			->join('CROSS', $db->qn($this->getTableName()) . ' AS ' . $db->qn('parent'))
			->where($db->qn('node') . '.' . $fldLft . ' >= ' . $db->qn('parent') . '.' . $fldLft)
			->where($db->qn('node') . '.' . $fldLft . ' <= ' . $db->qn('parent') . '.' . $fldRgt)
			->group($db->qn('node') . '.' . $fldLft)
			->order($db->qn('node') . '.' . $fldLft . ' ASC');

		$tempResults = $db->setQuery($query)->loadAssocList();
		$ret = array();

		if (!empty($tempResults))
		{
			foreach ($tempResults as $row)
			{
				$ret[$row[$key]] = str_repeat($seperator, $row['depth']) . $row[$column];
			}
		}

		return $ret;
	}

	/**
	 * Locate a node from a given path, e.g. "/some/other/leaf"
	 *
	 * Notes:
	 * - This will only work when you have a "slug" and a "hash" field in your table.
	 * - If the path starts with "/" we will use the root with lft=1. Otherwise the first component of the path is
	 *   supposed to be the slug of the root node.
	 * - If the root node is not found you'll get null as the return value
	 * - You will also get null if any component of the path is not found
	 *
	 * @param string $path The path to locate
	 *
	 * @return FOFTableNested|null The found node or null if nothing is found
	 */
	public function findByPath($path)
	{
		// @todo
	}

	public function isValid()
	{
		// @todo
	}

	public function rebuild()
	{
		// @todo
	}

	/**
	 * Resets cached values used to speed up querying the tree
	 *
	 * @return  static  for chaining
	 */
	protected function resetTreeCache()
	{
		$this->treeDepth = null;
		$this->treeRoot = null;
		$this->treeParent = null;
		$this->treeNestedGet = false;

		return $this;
	}

	/**
	 * Add custom, pre-compiled WHERE clauses for use in buildQuery. The raw WHERE clause you specify is added as is to
	 * the query generated by buildQuery. You are responsible for quoting and escaping the field names and data found
	 * inside the WHERE clause.
	 *
	 * @param   string $rawWhereClause The raw WHERE clause to add
	 *
	 * @return  $this  For chaining
	 */
	public function whereRaw($rawWhereClause)
	{
		$this->whereClauses[] = $rawWhereClause;

		return $this;
	}

	/**
	 * Builds the query for the get() method
	 *
	 * @return JDatabaseQuery
	 */
	protected function buildQuery()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->qn('node') . '.*')
			->from($db->qn($this->getTableName()) . ' AS ' . $db->qn('node'));

		if ($this->treeNestedGet)
		{
			$query
				->join('CROSS', $db->qn($this->getTableName()) . ' AS ' . $db->qn('parent'));
		}

		// Apply custom WHERE clauses
		if (count($this->whereClauses))
		{
			foreach ($this->whereClauses as $clause)
			{
				$query->where($clause);
			}
		}

		return $query;
	}

	/**
	 * Returns a database iterator to retrieve records. Use the scope methods and the whereRaw method to define what
	 * exactly will be returned.
	 *
	 * @param   integer $limitstart How many items to skip from the start, only when $overrideLimits = true
	 * @param   integer $limit      How many items to return, only when $overrideLimits = true
	 *
	 * @return  FOFDatabaseIterator  The data collection
	 */
	public function get($limitstart = 0, $limit = 0)
	{
		$limitstart = max($limitstart, 0);
		$limit = max($limit, 0);

		$query = $this->buildQuery();
		$db = $this->getDbo();
		$db->setQuery($query, $limitstart, $limit);
		$cursor = $db->execute();

		$dataCollection = FOFDatabaseIterator::getIterator($db->name, $cursor, null, $this->config['_table_class']);

		return $dataCollection;
	}
}
