<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Model;

defined('_JEXEC') || die;

use Exception;
use FOF30\Container\Container;
use FOF30\Model\DataModel\Exception\TreeIncompatibleTable;
use FOF30\Model\DataModel\Exception\TreeInvalidLftRgtCurrent;
use FOF30\Model\DataModel\Exception\TreeInvalidLftRgtOther;
use FOF30\Model\DataModel\Exception\TreeInvalidLftRgtParent;
use FOF30\Model\DataModel\Exception\TreeInvalidLftRgtSibling;
use FOF30\Model\DataModel\Exception\TreeMethodOnlyAllowedInRoot;
use FOF30\Model\DataModel\Exception\TreeRootNotFound;
use FOF30\Model\DataModel\Exception\TreeUnexpectedPrimaryKey;
use FOF30\Model\DataModel\Exception\TreeUnsupportedMethod;
use JDatabaseQuery;
use Joomla\CMS\Application\ApplicationHelper;
use RuntimeException;
use UnexpectedValueException;

/**
 * A DataModel which implements nested trees
 *
 * @property int    $lft  Left value (for nested set implementation)
 * @property int    $rgt  Right value (for nested set implementation)
 * @property string $hash Slug hash (for faster searching)
 */
class TreeModel extends DataModel
{
	/** @var int The level (depth) of this node in the tree */
	protected $treeDepth = null;

	/** @var TreeModel The root node in the tree */
	protected $treeRoot = null;

	/** @var TreeModel The parent node of ourselves */
	protected $treeParent = null;

	/** @var bool Should I perform a nested get (used to query ascendants/descendants) */
	protected $treeNestedGet = false;

	/**
	 * Public constructor. Overrides the parent constructor, making sure there are lft/rgt columns which make it
	 * compatible with nested sets.
	 *
	 * @param   Container  $container  The configuration variables to this model
	 * @param   array      $config     Configuration values for this model
	 *
	 * @throws RuntimeException When lft/rgt columns are not found
	 * @see \FOF30\Model\DataModel::__construct()
	 *
	 */
	public function __construct(Container $container = null, array $config = [])
	{
		parent::__construct($container, $config);

		if (!$this->hasField('lft') || !$this->hasField('rgt'))
		{
			throw new TreeIncompatibleTable($this->tableName);
		}
	}

	/**
	 * Overrides the automated table checks to handle the 'hash' column for faster searching
	 *
	 * @return $this|DataModel
	 */
	public function check()
	{
		// Create a slug if there is a title and an empty slug
		if ($this->hasField('title') && $this->hasField('slug') && !$this->slug)
		{
			$this->slug = ApplicationHelper::stringURLSafe($this->title);
		}

		// Create the SHA-1 hash of the slug for faster searching (make sure the hash column is CHAR(64) to take
		// advantage of MySQL's optimised searching for fixed size CHAR columns)
		if ($this->hasField('hash') && $this->hasField('slug'))
		{
			$this->hash = sha1($this->slug);
		}

		// Reset cached values
		$this->resetTreeCache();

		// Run the parent checks
		parent::check();

		return $this;
	}

	/**
	 * Delete a node, either the currently loaded one or the one specified in $id. If an $id is specified that node
	 * is loaded before trying to delete it. In the end the data model is reset. If the node has any children nodes
	 * they will be removed before the node itself is deleted.
	 *
	 * @param   mixed  $id  Primary key (id field) value
	 *
	 * @return  $this  for chaining
	 * @throws UnexpectedValueException
	 *
	 */
	public function forceDelete($id = null)
	{
		// Load the specified record (if necessary)
		if (!empty($id))
		{
			$this->findOrFail($id);
		}

		$k  = $this->getIdFieldName();
		$pk = (!$id) ? $this->$k : $id;

		// If no primary key is given, return false.
		if (!$pk)
		{
			throw new TreeUnexpectedPrimaryKey;
		}

		// Execute the logic only if I have a primary key, otherwise I could have weird results
		// Perform the checks on the current node *BEFORE* starting to delete the children
		try
		{
			$this->triggerEvent('onBeforeDelete', [&$pk]);
		}
		catch (Exception $e)
		{
			return false;
		}

		$result = true;

		// Recursively delete all children nodes as long as we are not a leaf node
		if (!$this->isLeaf())
		{
			// Get all sub-nodes
			$table = $this->getClone();
			$table->bind($this->getData());
			$subNodes = $table->getDescendants();

			// Delete all subnodes (goes through the model to trigger the observers)
			if (!empty($subNodes))
			{
				/** @var TreeModel $item */
				foreach ($subNodes as $item)
				{
					// We have to pass the id, so we are getting it again from the database.
					// We have to do in this way, since a previous child could have changed our lft and rgt values
					if (!$item->forceDelete($item->$k))
					{
						// A subnode failed or prevents the delete, continue deleting other nodes,
						// but preserve the current node (ie the parent)
						$result = false;
					}
				}

				// Load it again, since while deleting a children we could have updated ourselves, too
				$this->find($pk);
			}
		}

		if ($result)
		{
			$db = $this->getDbo();

			// Delete the row by primary key.
			$query = $db->getQuery(true);
			$query->delete();
			$query->from($this->getTableName());
			$query->where($db->qn($this->getIdFieldName()) . ' = ' . $db->q($pk));

			$db->setQuery($query)->execute();

			$this->triggerEvent('onAfterDelete', [&$pk]);
		}

		return $this;
	}

	/**
	 * Not supported in nested sets
	 *
	 * @param   string  $where  Ignored
	 *
	 * @return  static  Self, for chaining
	 *
	 * @throws  RuntimeException
	 */
	public function reorder($where = '')
	{
		throw new TreeUnsupportedMethod(__METHOD__);
	}

	/**
	 * Not supported in nested sets
	 *
	 * @param   integer  $delta  Ignored
	 * @param   string   $where  Ignored
	 *
	 * @return  static  Self, for chaining
	 *
	 * @throws  RuntimeException
	 */
	public function move($delta, $where = '')
	{
		throw new TreeUnsupportedMethod(__METHOD__);
	}

	/**
	 * Create a new record with the provided data. It is inserted as the last child of the current node's parent
	 *
	 * @param   array  $data  The data to use in the new record
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
			$parentNode = $this->getParent();

			return $newNode->insertAsChildOf($parentNode);
		}
	}

	/**
	 * Makes a copy of the record, inserting it as the last child of the current node's parent.
	 *
	 * @return static
	 *
	 * @codeCoverageIgnore
	 */
	public function copy($data = null)
	{
		$selfData = $this->toArray();

		if (!is_array($data))
		{
			$data = [];
		}

		$data = array_merge($data, $selfData);

		return $this->create($data);
	}

	/**
	 * Reset the record data and the tree cache
	 *
	 * @param   boolean  $useDefaults     Should I use the default values? Default: yes
	 * @param   boolean  $resetRelations  Should I reset the relations too? Default: no
	 *
	 * @return  static  Self, for chaining
	 *
	 * @codeCoverageIgnore
	 */
	public function reset($useDefaults = true, $resetRelations = false)
	{
		$this->resetTreeCache();

		return parent::reset($useDefaults, $resetRelations);
	}

	/**
	 * Insert the current node as a tree root. It is a good idea to never use this method, instead providing a root node
	 * in your schema installation and then sticking to only one root.
	 *
	 * @return static
	 *
	 * @throws  RuntimeException
	 */
	public function insertAsRoot()
	{
		// You can't insert a node that is already saved i.e. the table has an id
		if ($this->getId())
		{
			throw new TreeMethodOnlyAllowedInRoot(__METHOD__);
		}

		// First we need to find the right value of the last parent, a.k.a. the max(rgt) of the table
		$db = $this->getDbo();

		// Get the lft/rgt names
		$fldRgt = $db->qn($this->getFieldAlias('rgt'));

		$query  = $db->getQuery(true)
			->select('MAX(' . $fldRgt . ')')
			->from($db->qn($this->tableName));
		$maxRgt = $db->setQuery($query, 0, 1)->loadResult();

		if (empty($maxRgt))
		{
			$maxRgt = 0;
		}

		$this->lft = ++$maxRgt;
		$this->rgt = ++$maxRgt;

		return $this->save();
	}

	/**
	 * Insert the current node as the first (leftmost) child of a parent node.
	 *
	 * WARNING: If it's an existing node it will be COPIED, not moved.
	 *
	 * @param   TreeModel  $parentNode  The node which will become our parent
	 *
	 * @return $this for chaining
	 * @throws Exception
	 * @throws RuntimeException
	 */
	public function insertAsFirstChildOf(TreeModel &$parentNode)
	{
		if ($parentNode->lft >= $parentNode->rgt)
		{
			throw new TreeInvalidLftRgtParent;
		}

		// Get a reference to the database
		$db = $this->getDbo();

		// Get the field names
		$fldRgt = $db->qn($this->getFieldAlias('rgt'));
		$fldLft = $db->qn($this->getFieldAlias('lft'));

		// Nullify the PK, so a new record will be created
		$this->{$this->idFieldName} = null;

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
				->update($db->qn($this->tableName))
				->set($fldLft . ' = ' . $fldLft . '+2')
				->where($fldLft . ' > ' . $db->q($myLeft));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($fldRgt . ' = ' . $fldRgt . '+ 2')
				->where($fldRgt . '>' . $db->q($myLeft));
			$db->setQuery($query)->execute();

			// Insert the new node
			$this->save();

			// Commit the transaction
			$db->transactionCommit();
		}
		catch (Exception $e)
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
	 * @param   TreeModel  $parentNode  The node which will become our parent
	 *
	 * @return $this for chaining
	 * @throws Exception
	 * @throws RuntimeException
	 */
	public function insertAsLastChildOf(TreeModel &$parentNode)
	{
		if ($parentNode->lft >= $parentNode->rgt)
		{
			throw new TreeInvalidLftRgtParent;
		}

		// Get a reference to the database
		$db = $this->getDbo();

		// Get the field names
		$fldRgt = $db->qn($this->getFieldAlias('rgt'));
		$fldLft = $db->qn($this->getFieldAlias('lft'));

		// Nullify the PK, so a new record will be created
		$this->{$this->idFieldName} = null;

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
				->update($db->qn($this->tableName))
				->set($fldRgt . ' = ' . $fldRgt . '+2')
				->where($fldRgt . '>=' . $db->q($myRight));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($fldLft . ' = ' . $fldLft . '+2')
				->where($fldLft . '>' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Insert the new node
			$this->save();

			// Commit the transaction
			$db->transactionCommit();
		}
		catch (Exception $e)
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
	 *
	 * @param   TreeModel  $parentNode
	 *
	 * @return $this for chaining
	 */
	public function insertAsChildOf(TreeModel &$parentNode)
	{
		return $this->insertAsLastChildOf($parentNode);
	}

	/**
	 * Insert the current node to the left of (before) a sibling node
	 *
	 * WARNING: If it's an existing node it will be COPIED, not moved.
	 *
	 * @param   TreeModel  $siblingNode  We will be inserted before this node
	 *
	 * @return $this for chaining
	 * @throws Exception
	 * @throws RuntimeException
	 */
	public function insertLeftOf(TreeModel &$siblingNode)
	{
		if ($siblingNode->lft >= $siblingNode->rgt)
		{
			throw new TreeInvalidLftRgtSibling;
		}

		// Get a reference to the database
		$db = $this->getDbo();

		// Get the field names
		$fldRgt = $db->qn($this->getFieldAlias('rgt'));
		$fldLft = $db->qn($this->getFieldAlias('lft'));

		// Nullify the PK, so a new record will be created
		$this->{$this->idFieldName} = null;

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
					->update($db->qn($this->tableName))
					->set($fldLft . ' = ' . $fldLft . '+2')
					->where($fldLft . ' >= ' . $db->q($myLeft))
			)->execute();

			$db->setQuery(
				$db->getQuery(true)
					->update($db->qn($this->tableName))
					->set($fldRgt . ' = ' . $fldRgt . '+2')
					->where($fldRgt . ' > ' . $db->q($myLeft))
			)->execute();

			$this->save();

			// Commit the transaction
			$db->transactionCommit();
		}
		catch (Exception $e)
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
	 * @param   TreeModel  $siblingNode  We will be inserted after this node
	 *
	 * @return $this for chaining
	 * @throws Exception
	 * @throws RuntimeException
	 */
	public function insertRightOf(TreeModel &$siblingNode)
	{
		if ($siblingNode->lft >= $siblingNode->rgt)
		{
			throw new TreeInvalidLftRgtSibling;
		}

		// Get a reference to the database
		$db = $this->getDbo();

		// Get the field names
		$fldRgt = $db->qn($this->getFieldAlias('rgt'));
		$fldLft = $db->qn($this->getFieldAlias('lft'));

		// Nullify the PK, so a new record will be created
		$this->{$this->idFieldName} = null;

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
					->update($db->qn($this->tableName))
					->set($fldRgt . ' = ' . $fldRgt . '+2')
					->where($fldRgt . ' > ' . $db->q($myRight))
			)->execute();

			$db->setQuery(
				$db->getQuery(true)
					->update($db->qn($this->tableName))
					->set($fldLft . ' = ' . $fldLft . '+2')
					->where($fldLft . ' > ' . $db->q($myRight))
			)->execute();

			$this->save();

			// Commit the transaction
			$db->transactionCommit();
		}
		catch (Exception $e)
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
	 *
	 * @param   TreeModel  $siblingNode
	 *
	 * @return $this for chaining
	 */
	public function insertAsSiblingOf(TreeModel &$siblingNode)
	{
		return $this->insertRightOf($siblingNode);
	}

	/**
	 * Move the current node (and its subtree) one position to the left in the tree, i.e. before its left-hand sibling
	 *
	 * @return $this
	 * @throws  RuntimeException
	 *
	 */
	public function moveLeft()
	{
		// Sanity checks on current node position
		if ($this->lft >= $this->rgt)
		{
			throw new TreeInvalidLftRgtCurrent;
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
		$db          = $this->getDbo();
		$leftSibling = $this->getClone()->reset()
			->whereRaw($db->qn($this->getFieldAlias('rgt')) . ' = ' . $db->q($this->lft - 1))
			->firstOrFail();

		// Move the node
		return $this->moveToLeftOf($leftSibling);
	}

	/**
	 * Move the current node (and its subtree) one position to the right in the tree, i.e. after its right-hand sibling
	 *
	 * @return $this
	 * @throws RuntimeException
	 *
	 */
	public function moveRight()
	{
		// Sanity checks on current node position
		if ($this->lft >= $this->rgt)
		{
			throw new TreeInvalidLftRgtCurrent;
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

		$rightSibling = $this->getClone()->reset()
			->whereRaw($db->qn($this->getFieldAlias('lft')) . ' = ' . $db->q($this->rgt + 1))
			->firstOrFail();

		// Move the node
		return $this->moveToRightOf($rightSibling);
	}

	/**
	 * Moves the current node (and its subtree) to the left of another node. The other node can be in a different
	 * position in the tree or even under a different root.
	 *
	 * @param   TreeModel  $siblingNode
	 *
	 * @return $this for chaining
	 *
	 * @throws Exception
	 * @throws RuntimeException
	 */
	public function moveToLeftOf(TreeModel $siblingNode)
	{
		// Sanity checks on current and sibling node position
		if ($this->lft >= $this->rgt)
		{
			throw new TreeInvalidLftRgtCurrent;
		}

		if ($siblingNode->lft >= $siblingNode->rgt)
		{
			throw new TreeInvalidLftRgtSibling;
		}

		$db    = $this->getDbo();
		$left  = $db->qn($this->getFieldAlias('lft'));
		$right = $db->qn($this->getFieldAlias('rgt'));

		// Get node metrics
		$myLeft  = $this->lft;
		$myRight = $this->rgt;
		$myWidth = $myRight - $myLeft + 1;

		// Get parent metrics
		$sibLeft = $siblingNode->lft;

		// Start the transaction
		$db->transactionStart();

		try
		{
			// Temporary remove subtree being moved
			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set("$left = " . $db->q(0) . " - $left")
				->set("$right = " . $db->q(0) . " - $right")
				->where($left . ' >= ' . $db->q($myLeft))
				->where($right . ' <= ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Close hole left behind
			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($left . ' = ' . $left . ' - ' . $db->q($myWidth))
				->where($left . ' > ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($right . ' = ' . $right . ' - ' . $db->q($myWidth))
				->where($right . ' > ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Make a hole for the new items
			$newSibLeft = ($sibLeft > $myRight) ? $sibLeft - $myWidth : $sibLeft;

			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($right . ' = ' . $right . ' + ' . $db->q($myWidth))
				->where($right . ' >= ' . $db->q($newSibLeft));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($left . ' = ' . $left . ' + ' . $db->q($myWidth))
				->where($left . ' >= ' . $db->q($newSibLeft));
			$db->setQuery($query)->execute();

			// Move node and subnodes
			$moveRight = $newSibLeft - $myLeft;

			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($left . ' = ' . $db->q(0) . ' - ' . $left . ' + ' . $db->q($moveRight))
				->set($right . ' = ' . $db->q(0) . ' - ' . $right . ' + ' . $db->q($moveRight))
				->where($left . ' <= 0 - ' . $db->q($myLeft))
				->where($right . ' >= 0 - ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Commit the transaction
			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();

			throw $e;
		}

		// Let's load the record again to fetch the new values for lft and rgt
		$this->findOrFail();

		return $this;
	}

	/**
	 * Moves the current node (and its subtree) to the right of another node. The other node can be in a different
	 * position in the tree or even under a different root.
	 *
	 * @param   TreeModel  $siblingNode
	 *
	 * @return $this for chaining
	 *
	 * @throws Exception
	 * @throws RuntimeException
	 */
	public function moveToRightOf(TreeModel $siblingNode)
	{
		// Sanity checks on current and sibling node position
		if ($this->lft >= $this->rgt)
		{
			throw new TreeInvalidLftRgtCurrent;
		}

		if ($siblingNode->lft >= $siblingNode->rgt)
		{
			throw new TreeInvalidLftRgtSibling;
		}

		$db    = $this->getDbo();
		$left  = $db->qn($this->getFieldAlias('lft'));
		$right = $db->qn($this->getFieldAlias('rgt'));

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
				->update($db->qn($this->tableName))
				->set("$left = " . $db->q(0) . " - $left")
				->set("$right = " . $db->q(0) . " - $right")
				->where($left . ' >= ' . $db->q($myLeft))
				->where($right . ' <= ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Close hole left behind
			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($left . ' = ' . $left . ' - ' . $db->q($myWidth))
				->where($left . ' > ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($right . ' = ' . $right . ' - ' . $db->q($myWidth))
				->where($right . ' > ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Make a hole for the new items
			$newSibRight = ($sibRight > $myRight) ? $sibRight - $myWidth : $sibRight;

			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($left . ' = ' . $left . ' + ' . $db->q($myWidth))
				->where($left . ' > ' . $db->q($newSibRight));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($right . ' = ' . $right . ' + ' . $db->q($myWidth))
				->where($right . ' > ' . $db->q($newSibRight));
			$db->setQuery($query)->execute();

			// Move node and subnodes
			$moveRight = ($sibRight > $myRight) ? $sibRight - $myRight : $sibRight - $myRight + $myWidth;

			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($left . ' = ' . $db->q(0) . ' - ' . $left . ' + ' . $db->q($moveRight))
				->set($right . ' = ' . $db->q(0) . ' - ' . $right . ' + ' . $db->q($moveRight))
				->where($left . ' <= 0 - ' . $db->q($myLeft))
				->where($right . ' >= 0 - ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Commit the transaction
			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();

			throw $e;
		}

		// Let's load the record again to fetch the new values for lft and rgt
		$this->findOrFail();

		return $this;
	}

	/**
	 * Alias for moveToRightOf
	 *
	 * @param   TreeModel  $siblingNode
	 *
	 * @return $this for chaining
	 *
	 * @codeCoverageIgnore
	 */
	public function makeNextSiblingOf(TreeModel $siblingNode)
	{
		return $this->moveToRightOf($siblingNode);
	}

	/**
	 * Alias for makeNextSiblingOf
	 *
	 * @param   TreeModel  $siblingNode
	 *
	 * @return $this for chaining
	 *
	 * @codeCoverageIgnore
	 */
	public function makeSiblingOf(TreeModel $siblingNode)
	{
		return $this->makeNextSiblingOf($siblingNode);
	}

	/**
	 * Alias for moveToLeftOf
	 *
	 * @param   TreeModel  $siblingNode
	 *
	 * @return $this for chaining
	 *
	 * @codeCoverageIgnore
	 */
	public function makePreviousSiblingOf(TreeModel $siblingNode)
	{
		return $this->moveToLeftOf($siblingNode);
	}

	/**
	 * Moves a node and its subtree as a the first (leftmost) child of $parentNode
	 *
	 * @param   TreeModel  $parentNode
	 *
	 * @return $this for chaining
	 *
	 * @throws Exception
	 * @throws RuntimeException
	 */
	public function makeFirstChildOf(TreeModel $parentNode)
	{
		// Sanity checks on current and sibling node position
		if ($this->lft >= $this->rgt)
		{
			throw new TreeInvalidLftRgtCurrent;
		}

		if ($parentNode->lft >= $parentNode->rgt)
		{
			throw new TreeInvalidLftRgtParent;
		}

		$db    = $this->getDbo();
		$left  = $db->qn($this->getFieldAlias('lft'));
		$right = $db->qn($this->getFieldAlias('rgt'));

		// Get node metrics
		$myLeft  = $this->lft;
		$myRight = $this->rgt;
		$myWidth = $myRight - $myLeft + 1;

		// Get parent metrics
		$parentRight = $parentNode->rgt;
		$parentLeft  = $parentNode->lft;

		// Start the transaction
		$db->transactionStart();

		try
		{
			// Temporary remove subtree being moved
			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set("$left = " . $db->q(0) . " - $left")
				->set("$right = " . $db->q(0) . " - $right")
				->where($left . ' >= ' . $db->q($myLeft))
				->where($right . ' <= ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Close hole left behind
			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($left . ' = ' . $left . ' - ' . $db->q($myWidth))
				->where($left . ' > ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($right . ' = ' . $right . ' - ' . $db->q($myWidth))
				->where($right . ' > ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Make a hole for the new items
			$newParentLeft  = ($parentLeft > $myRight) ? $parentLeft - $myWidth : $parentLeft;
			$newParentRight = ($parentRight > $myRight) ? $parentRight - $myWidth : $parentRight;

			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($right . ' = ' . $right . ' + ' . $db->q($myWidth))
				->where($right . ' >= ' . $db->q($newParentLeft));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($left . ' = ' . $left . ' + ' . $db->q($myWidth))
				->where($left . ' > ' . $db->q($newParentLeft));
			$db->setQuery($query)->execute();

			// Move node and subnodes
			$moveRight = $newParentLeft - $myLeft + 1;

			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($left . ' = ' . $db->q(0) . ' - ' . $left . ' + ' . $db->q($moveRight))
				->set($right . ' = ' . $db->q(0) . ' - ' . $right . ' + ' . $db->q($moveRight))
				->where($left . ' <= 0 - ' . $db->q($myLeft))
				->where($right . ' >= 0 - ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Commit the transaction
			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();

			throw $e;
		}

		// Let's load the record again to fetch the new values for lft and rgt
		$this->findOrFail();

		return $this;
	}

	/**
	 * Moves a node and its subtree as a the last (rightmost) child of $parentNode
	 *
	 * @param   TreeModel  $parentNode
	 *
	 * @return $this for chaining
	 *
	 * @throws Exception
	 * @throws RuntimeException
	 */
	public function makeLastChildOf(TreeModel $parentNode)
	{
		// Sanity checks on current and sibling node position
		if ($this->lft >= $this->rgt)
		{
			throw new TreeInvalidLftRgtCurrent;
		}

		if ($parentNode->lft >= $parentNode->rgt)
		{
			throw new TreeInvalidLftRgtParent;
		}

		$db    = $this->getDbo();
		$left  = $db->qn($this->getFieldAlias('lft'));
		$right = $db->qn($this->getFieldAlias('rgt'));

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
				->update($db->qn($this->tableName))
				->set("$left = " . $db->q(0) . " - $left")
				->set("$right = " . $db->q(0) . " - $right")
				->where($left . ' >= ' . $db->q($myLeft))
				->where($right . ' <= ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Close hole left behind
			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($left . ' = ' . $left . ' - ' . $db->q($myWidth))
				->where($left . ' > ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($right . ' = ' . $right . ' - ' . $db->q($myWidth))
				->where($right . ' > ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Make a hole for the new items
			$newLeft = ($parentRight > $myRight) ? $parentRight - $myWidth : $parentRight;

			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($left . ' = ' . $left . ' + ' . $db->q($myWidth))
				->where($left . ' >= ' . $db->q($newLeft));
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($right . ' = ' . $right . ' + ' . $db->q($myWidth))
				->where($right . ' >= ' . $db->q($newLeft));
			$db->setQuery($query)->execute();

			// Move node and subnodes
			$moveRight = ($parentRight > $myRight) ? $parentRight - $myRight - 1 : $parentRight - $myRight - 1 + $myWidth;

			$query = $db->getQuery(true)
				->update($db->qn($this->tableName))
				->set($left . ' = ' . $db->q(0) . ' - ' . $left . ' + ' . $db->q($moveRight))
				->set($right . ' = ' . $db->q(0) . ' - ' . $right . ' + ' . $db->q($moveRight))
				->where($left . ' <= 0 - ' . $db->q($myLeft))
				->where($right . ' >= 0 - ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Commit the transaction
			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			$db->transactionRollback();

			throw $e;
		}

		// Let's load the record again to fetch the new values for lft and rgt
		$this->findOrFail();

		return $this;
	}

	/**
	 * Alias for makeLastChildOf
	 *
	 * @param   TreeModel  $parentNode
	 *
	 * @return $this for chaining
	 *
	 * @codeCoverageIgnore
	 */
	public function makeChildOf(TreeModel $parentNode)
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
	 * Gets the level (depth) of this node in the tree. The result is cached in $this->treeDepth for faster fetch.
	 *
	 * @return int|mixed
	 * @throws RuntimeException
	 *
	 */
	public function getLevel()
	{
		// Sanity checks on current node position
		if ($this->lft >= $this->rgt)
		{
			throw new TreeInvalidLftRgtCurrent;
		}

		if (is_null($this->treeDepth))
		{
			$db = $this->getDbo();

			$fldLft = $db->qn($this->getFieldAlias('lft'));
			$fldRgt = $db->qn($this->getFieldAlias('rgt'));

			$query = $db->getQuery(true)
				->select('(COUNT(' . $db->qn('parent') . '.' . $fldLft . ') - 1) AS ' . $db->qn('depth'))
				->from($db->qn($this->tableName) . ' AS ' . $db->qn('node'))
				->join('CROSS', $db->qn($this->tableName) . ' AS ' . $db->qn('parent'))
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
	 * @return static
	 * @throws  RuntimeException
	 *
	 */
	public function getParent()
	{
		// Sanity checks on current node position
		if ($this->lft >= $this->rgt)
		{
			throw new TreeInvalidLftRgtCurrent;
		}

		if ($this->isRoot())
		{
			return $this;
		}

		if (empty($this->treeParent) || !is_object($this->treeParent) || !($this->treeParent instanceof TreeModel))
		{
			$db = $this->getDbo();

			$fldLft = $db->qn($this->getFieldAlias('lft'));
			$fldRgt = $db->qn($this->getFieldAlias('rgt'));

			$query     = $db->getQuery(true)
				->select($db->qn('parent') . '.' . $fldLft)
				->from($db->qn($this->tableName) . ' AS ' . $db->qn('node'))
				->join('CROSS', $db->qn($this->tableName) . ' AS ' . $db->qn('parent'))
				->where($db->qn('node') . '.' . $fldLft . ' >= ' . $db->qn('parent') . '.' . $fldLft)
				->where($db->qn('node') . '.' . $fldLft . ' <= ' . $db->qn('parent') . '.' . $fldRgt)
				->where($db->qn('node') . '.' . $fldLft . ' = ' . $db->q($this->lft))
				->order($db->qn('parent') . '.' . $fldLft . ' DESC');
			$targetLft = $db->setQuery($query, 1, 1)->loadResult();

			$this->treeParent = $this->getClone()->reset()
				->whereRaw($fldLft . ' = ' . $db->q($targetLft))
				->firstOrFail();
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
	 * @return bool
	 * @throws  RuntimeException
	 *
	 */
	public function isLeaf()
	{
		// Sanity checks on current node position
		if ($this->lft >= $this->rgt)
		{
			throw new TreeInvalidLftRgtCurrent;
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
	 * @param   TreeModel  $otherNode
	 *
	 * @return bool
	 * @throws  RuntimeException
	 *
	 */
	public function isDescendantOf(TreeModel $otherNode)
	{
		// Sanity checks on current node position
		if ($this->lft >= $this->rgt)
		{
			throw new TreeInvalidLftRgtCurrent;
		}

		if ($otherNode->lft >= $otherNode->rgt)
		{
			throw new TreeInvalidLftRgtOther;
		}

		return ($otherNode->lft < $this->lft) && ($otherNode->rgt > $this->rgt);
	}

	/**
	 * Returns true if $otherNode is ourselves or if we are a descendant of $otherNode
	 *
	 * @param   TreeModel  $otherNode
	 *
	 * @return bool
	 */
	public function isSelfOrDescendantOf(TreeModel $otherNode)
	{
		// Sanity checks on current node position
		if ($this->lft >= $this->rgt)
		{
			throw new TreeInvalidLftRgtCurrent;
		}

		if ($otherNode->lft >= $otherNode->rgt)
		{
			throw new TreeInvalidLftRgtOther;
		}

		return ($otherNode->lft <= $this->lft) && ($otherNode->rgt >= $this->rgt);
	}

	/**
	 * Returns true if we are an ancestor of $otherNode
	 *
	 * @codeCoverageIgnore
	 *
	 * @param   TreeModel  $otherNode
	 *
	 * @return bool
	 */
	public function isAncestorOf(TreeModel $otherNode)
	{
		return $otherNode->isDescendantOf($this);
	}

	/**
	 * Returns true if $otherNode is ourselves or we are an ancestor of $otherNode
	 *
	 * @codeCoverageIgnore
	 *
	 * @param   TreeModel  $otherNode
	 *
	 * @return bool
	 */
	public function isSelfOrAncestorOf(TreeModel $otherNode)
	{
		return $otherNode->isSelfOrDescendantOf($this);
	}

	/**
	 * Is $node this very node?
	 *
	 * @param   TreeModel  $node
	 *
	 * @return bool
	 * @throws  RuntimeException
	 *
	 */
	public function equals(TreeModel &$node)
	{
		// Sanity checks on current node position
		if ($this->lft >= $this->rgt)
		{
			throw new TreeInvalidLftRgtCurrent;
		}

		if ($node->lft >= $node->rgt)
		{
			throw new TreeInvalidLftRgtOther;
		}

		return (
			($this->getId() == $node->getId())
			&& ($this->lft == $node->lft)
			&& ($this->rgt == $node->rgt)
		);
	}

	/**
	 * Checks if our node is inside the subtree of $otherNode. This is a fast check as only lft and rgt values have to
	 * be compared.
	 *
	 * @param   TreeModel  $otherNode
	 *
	 * @return bool
	 * @throws  RuntimeException
	 *
	 */
	public function insideSubtree(TreeModel $otherNode)
	{
		// Sanity checks on current node position
		if ($this->lft >= $this->rgt)
		{
			throw new TreeInvalidLftRgtCurrent;
		}

		if ($otherNode->lft >= $otherNode->rgt)
		{
			throw new TreeInvalidLftRgtOther;
		}

		return ($this->lft > $otherNode->lft) && ($this->rgt < $otherNode->rgt);
	}

	/**
	 * Returns true if both this node and $otherNode are root, leaf or child (same tree scope)
	 *
	 * @param   TreeModel  $otherNode
	 *
	 * @return bool
	 */
	public function inSameScope(TreeModel $otherNode)
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
	 * get() will not return the selected node if it's part of the query results
	 *
	 * @param   TreeModel  $node  The node to exclude from the results
	 *
	 * @return void
	 */
	public function withoutNode(TreeModel $node)
	{
		$db = $this->getDbo();

		$fldLft = $db->qn($this->getFieldAlias('lft'));

		$this->whereRaw('NOT(' . $db->qn('node') . '.' . $fldLft . ' = ' . $db->q($node->lft) . ')');
	}

	/**
	 * Returns the root node of the tree this node belongs to
	 *
	 * @return static
	 *
	 * @throws RuntimeException
	 */
	public function getRoot()
	{
		// Empty node, let's try to get the first available root, ie lft=1
		if (!$this->getId())
		{
			$this->load(['lft' => 1]);
		}

		// Sanity checks on current node position
		if ($this->lft >= $this->rgt)
		{
			throw new TreeInvalidLftRgtCurrent;
		}

		// If this is a root node return itself (there is no such thing as the root of a root node)
		if ($this->isRoot())
		{
			return $this;
		}

		if (empty($this->treeRoot) || !is_object($this->treeRoot) || !($this->treeRoot instanceof TreeModel))
		{
			$this->treeRoot = null;

			// First try to get the record with the minimum ID
			$db = $this->getDbo();

			$fldLft = $db->qn($this->getFieldAlias('lft'));
			$fldRgt = $db->qn($this->getFieldAlias('rgt'));

			$subQuery = $db->getQuery(true)
				->select('MIN(' . $fldLft . ')')
				->from($db->qn($this->tableName));

			try
			{
				$root = $this->getClone()->reset()
					->whereRaw($fldLft . ' = (' . (string) $subQuery . ')')
					->firstOrFail();

				if ($this->isDescendantOf($root))
				{
					$this->treeRoot = $root;
				}
			}
			catch (RuntimeException $e)
			{
				// If there is no root found throw an exception. Basically: your table is FUBAR.
				throw new TreeRootNotFound($this->tableName, $this->lft);
			}

			// If the above method didn't work, get all roots and select the one with the appropriate lft/rgt values
			if (is_null($this->treeRoot))
			{
				// Find the node with depth = 0, lft < our lft and rgt > our right. That's our root node.
				$query = $db->getQuery(true)
					->select([
						$db->qn('node') . '.' . $fldLft,
						'(COUNT(' . $db->qn('parent') . '.' . $fldLft . ') - 1) AS ' . $db->qn('depth'),
					])
					->from($db->qn($this->tableName) . ' AS ' . $db->qn('node'))
					->join('CROSS', $db->qn($this->tableName) . ' AS ' . $db->qn('parent'))
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
					throw new TreeRootNotFound($this->tableName, $this->lft);
				}

				try
				{
					$this->treeRoot = $this->getClone()->reset()
						->whereRaw($fldLft . ' = ' . $db->q($targetLeft))
						->firstOrFail();
				}
				catch (RuntimeException $e)
				{
					// If there is no root found throw an exception. Basically: your table is FUBAR.
					throw new TreeRootNotFound($this->tableName, $this->lft);
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
	 * @return DataModel\Collection
	 */
	public function getAncestorsAndSelf()
	{
		$this->scopeAncestorsAndSelf();

		return $this->get(true);
	}

	/**
	 * Get all ancestors to this node and the node itself, but not the root node. If you want to
	 *
	 * @codeCoverageIgnore
	 *
	 * @return DataModel\Collection
	 */
	public function getAncestorsAndSelfWithoutRoot()
	{
		$this->scopeAncestorsAndSelf();
		$this->scopeWithoutRoot();

		return $this->get(true);
	}

	/**
	 * Get all ancestors to this node but not the node itself. In other words it gets the path to the node, without the
	 * node itself.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return DataModel\Collection
	 */
	public function getAncestors()
	{
		$this->scopeAncestorsAndSelf();
		$this->scopeWithoutSelf();

		return $this->get(true);
	}

	/**
	 * Get all ancestors to this node but not the node itself and its root.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return DataModel\Collection
	 */
	public function getAncestorsWithoutRoot()
	{
		$this->scopeAncestors();
		$this->scopeWithoutRoot();

		return $this->get(true);
	}

	/**
	 * Get all sibling nodes, including ourselves
	 *
	 * @codeCoverageIgnore
	 *
	 * @return DataModel\Collection
	 */
	public function getSiblingsAndSelf()
	{
		$this->scopeSiblingsAndSelf();

		return $this->get(true);
	}

	/**
	 * Get all sibling nodes, except ourselves
	 *
	 * @codeCoverageIgnore
	 *
	 * @return DataModel\Collection
	 */
	public function getSiblings()
	{
		$this->scopeSiblings();

		return $this->get(true);
	}

	/**
	 * Get all leaf nodes in the tree. You may want to use the scopes to narrow down the search in a specific subtree or
	 * path.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return DataModel\Collection
	 */
	public function getLeaves()
	{
		$this->scopeLeaves();

		return $this->get(true);
	}

	/**
	 * Get all descendant (children) nodes and ourselves.
	 *
	 * Note: all descendant nodes, even descendants of our immediate descendants, will be returned.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return DataModel\Collection
	 */
	public function getDescendantsAndSelf()
	{
		$this->scopeDescendantsAndSelf();

		return $this->get(true);
	}

	/**
	 * Get only our descendant (children) nodes, not ourselves.
	 *
	 * Note: all descendant nodes, even descendants of our immediate descendants, will be returned.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return DataModel\Collection
	 */
	public function getDescendants()
	{
		$this->scopeDescendants();

		return $this->get(true);
	}

	/**
	 * Get the immediate descendants (children). Unlike getDescendants it only goes one level deep into the tree
	 * structure. Descendants of descendant nodes will not be returned.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return DataModel\Collection
	 */
	public function getImmediateDescendants()
	{
		$this->scopeImmediateDescendants();

		return $this->get(true);
	}

	/**
	 * Returns a hashed array where each element's key is the value of the $key column (default: the ID column of the
	 * table) and its value is the value of the $column column (default: title). Each nesting level will have the value
	 * of the $column column prefixed by a number of $separator strings, as many as its nesting level (depth).
	 *
	 * This is useful for creating HTML select elements showing the hierarchy in a human readable format.
	 *
	 * @param   string  $column
	 * @param   null    $key
	 * @param   string  $seperator
	 *
	 * @return array
	 */
	public function getNestedList($column = 'title', $key = null, $seperator = '  ')
	{
		$db = $this->getDbo();

		$fldLft = $db->qn($this->getFieldAlias('lft'));
		$fldRgt = $db->qn($this->getFieldAlias('rgt'));

		if (empty($key) || !$this->hasField($key))
		{
			$key = $this->getIdFieldName();
		}

		if (empty($column))
		{
			$column = 'title';
		}

		$fldKey    = $db->qn($this->getFieldAlias($key));
		$fldColumn = $db->qn($this->getFieldAlias($column));

		$query = $db->getQuery(true)
			->select([
				$db->qn('node') . '.' . $fldKey,
				$db->qn('node') . '.' . $fldColumn,
				'(COUNT(' . $db->qn('parent') . '.' . $fldKey . ') - 1) AS ' . $db->qn('depth'),
			])
			->from($db->qn($this->tableName) . ' AS ' . $db->qn('node'))
			->join('CROSS', $db->qn($this->tableName) . ' AS ' . $db->qn('parent'))
			->where($db->qn('node') . '.' . $fldLft . ' >= ' . $db->qn('parent') . '.' . $fldLft)
			->where($db->qn('node') . '.' . $fldLft . ' <= ' . $db->qn('parent') . '.' . $fldRgt)
			->group($db->qn('node') . '.' . $fldLft)
			->order($db->qn('node') . '.' . $fldLft . ' ASC');

		$tempResults = $db->setQuery($query)->loadAssocList();
		$ret         = [];

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
	 * @param   string  $path  The path to locate
	 *
	 * @return TreeModel|null The found node or null if nothing is found
	 */
	public function findByPath($path)
	{
		// No path? No node.
		if (empty($path))
		{
			return null;
		}

		// Extract the path parts
		$pathParts = explode('/', $path);

		$firstElement = array_shift($pathParts);

		if (!empty($firstElement))
		{
			array_unshift($pathParts, $firstElement);
		}

		// Just a slash? Return the root
		if (empty($pathParts[0]))
		{
			return $this->getRoot();
		}

		// Get the quoted field names
		$db = $this->getDbo();

		$fldLeft  = $db->qn($this->getFieldAlias('lft'));
		$fldRight = $db->qn($this->getFieldAlias('rgt'));
		$fldHash  = $db->qn($this->getFieldAlias('hash'));

		// Get the quoted hashes of the slugs
		$pathHashesQuoted = [];

		foreach ($pathParts as $part)
		{
			$pathHashesQuoted[] = $db->q(sha1($part));
		}

		// Get all nodes with slugs matching our path
		$query        = $db->getQuery(true)
			->select([
				$db->qn('node') . '.*',
				'(COUNT(' . $db->qn('parent') . '.' . $db->qn($this->getFieldAlias('lft')) . ') - 1) AS ' . $db->qn('depth'),
			])->from($db->qn($this->tableName) . ' AS ' . $db->qn('node'))
			->join('CROSS', $db->qn($this->tableName) . ' AS ' . $db->qn('parent'))
			->where($db->qn('node') . '.' . $fldLeft . ' >= ' . $db->qn('parent') . '.' . $fldLeft)
			->where($db->qn('node') . '.' . $fldLeft . ' <= ' . $db->qn('parent') . '.' . $fldRight)
			->where($db->qn('node') . '.' . $fldHash . ' IN (' . implode(',', $pathHashesQuoted) . ')')
			->group($db->qn('node') . '.' . $fldLeft)
			->order([
				$db->qn('depth') . ' ASC',
				$db->qn('node') . '.' . $fldLeft . ' ASC',
			]);
		$queryResults = $db->setQuery($query)->loadAssocList();

		$pathComponents = [];

		// Handle paths with (no root slug provided) and without (root slug provided) a leading slash
		$currentLevel = (substr($path, 0, 1) == '/') ? 0 : -1;
		$maxLevel     = count($pathParts) + $currentLevel;

		// Initialise the path results array
		$i = $currentLevel;

		foreach ($pathParts as $part)
		{
			$i++;
			$pathComponents[$i] = [
				'slug' => $part,
				'id'   => null,
				'lft'  => null,
				'rgt'  => null,
			];
		}

		// Search for the best matching nodes
		$colSlug = $this->getFieldAlias('slug');
		$colLft  = $this->getFieldAlias('lft');
		$colRgt  = $this->getFieldAlias('rgt');
		$colId   = $this->getIdFieldName();

		foreach ($queryResults as $row)
		{
			if ($row['depth'] == $currentLevel + 1)
			{
				if ($row[$colSlug] != $pathComponents[$currentLevel + 1]['slug'])
				{
					continue;
				}

				if ($currentLevel > 0)
				{
					if ($row[$colLft] < $pathComponents[$currentLevel]['lft'])
					{
						continue;
					}

					if ($row[$colRgt] > $pathComponents[$currentLevel]['rgt'])
					{
						continue;
					}
				}

				$currentLevel++;
				$pathComponents[$currentLevel]['id']  = $row[$colId];
				$pathComponents[$currentLevel]['lft'] = $row[$colLft];
				$pathComponents[$currentLevel]['rgt'] = $row[$colRgt];
			}

			if ($currentLevel == $maxLevel)
			{
				break;
			}
		}

		// Get the last found node
		$lastNode = array_pop($pathComponents);

		// If the node exists, return it...
		if (!empty($lastNode['lft']))
		{
			return $this->getClone()->reset()->where($colLft, '=', $lastNode['lft'])->firstOrFail();
		}

		// ...otherwise return null
		return null;
	}

	/**
	 * Overrides the DataModel's buildQuery to allow nested set searches using the provided scopes
	 *
	 * @param   bool  $overrideLimits
	 *
	 * @return JDatabaseQuery
	 */
	public function buildQuery($overrideLimits = false)
	{
		$db = $this->getDbo();

		$query = parent::buildQuery($overrideLimits);

		// Wipe out select and from sections
		$query->clear('select');
		$query->clear('from');

		$query
			->select($db->qn('node') . '.*')
			->from($db->qn($this->tableName) . ' AS ' . $db->qn('node'));

		if ($this->treeNestedGet)
		{
			$query
				->join('CROSS', $db->qn($this->tableName) . ' AS ' . $db->qn('parent'));
		}

		return $query;
	}

	protected function onAfterDelete($oid)
	{
		$db = $this->getDbo();

		$myLeft  = $this->lft;
		$myRight = $this->rgt;

		$fldLft = $db->qn($this->getFieldAlias('lft'));
		$fldRgt = $db->qn($this->getFieldAlias('rgt'));

		// Move all siblings to the left
		$width = $this->rgt - $this->lft + 1;

		// Wrap everything in a transaction
		$db->transactionStart();

		try
		{
			// Shrink lft values
			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($fldLft . ' = ' . $fldLft . ' - ' . $width)
				->where($fldLft . ' > ' . $db->q($myLeft));
			$db->setQuery($query)->execute();

			// Shrink rgt values
			$query = $db->getQuery(true)
				->update($db->qn($this->getTableName()))
				->set($fldRgt . ' = ' . $fldRgt . ' - ' . $width)
				->where($fldRgt . ' > ' . $db->q($myRight));
			$db->setQuery($query)->execute();

			// Commit the transaction
			$db->transactionCommit();
		}
		catch (Exception $e)
		{
			// Roll back the transaction on error
			$db->transactionRollback();

			throw $e;
		}

		return $this;
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

		$fldLft = $db->qn($this->getFieldAlias('lft'));
		$fldRgt = $db->qn($this->getFieldAlias('rgt'));

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

		$fldLft = $db->qn($this->getFieldAlias('lft'));
		$fldRgt = $db->qn($this->getFieldAlias('rgt'));

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

		$fldLft = $db->qn($this->getFieldAlias('lft'));
		$fldRgt = $db->qn($this->getFieldAlias('rgt'));

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

		$fldLft = $db->qn($this->getFieldAlias('lft'));
		$fldRgt = $db->qn($this->getFieldAlias('rgt'));

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

		$fldLft = $db->qn($this->getFieldAlias('lft'));
		$fldRgt = $db->qn($this->getFieldAlias('rgt'));

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

		$fldLft = $db->qn($this->getFieldAlias('lft'));
		$fldRgt = $db->qn($this->getFieldAlias('rgt'));

		$this->whereRaw($db->qn('node') . '.' . $fldLft . ' > ' . $db->qn('parent') . '.' . $fldLft);
		$this->whereRaw($db->qn('node') . '.' . $fldLft . ' < ' . $db->qn('parent') . '.' . $fldRgt);
		$this->whereRaw($db->qn('parent') . '.' . $fldLft . ' = ' . $db->q($this->lft));
	}

	/**
	 * get() will only return immediate descendants (first level children) of the current node
	 *
	 * @return void
	 * @throws RuntimeException
	 *
	 */
	protected function scopeImmediateDescendants()
	{
		// Sanity checks on current node position
		if ($this->lft >= $this->rgt)
		{
			throw new TreeInvalidLftRgtCurrent;
		}

		$db = $this->getDbo();

		$fldLft = $db->qn($this->getFieldAlias('lft'));
		$fldRgt = $db->qn($this->getFieldAlias('rgt'));

		$subQuery = $db->getQuery(true)
			->select([
				$db->qn('node') . '.' . $fldLft,
				'(COUNT(*) - 1) AS ' . $db->qn('depth'),
			])
			->from($db->qn($this->tableName) . ' AS ' . $db->qn('node'))
			->from($db->qn($this->tableName) . ' AS ' . $db->qn('parent'))
			->where($db->qn('node') . '.' . $fldLft . ' >= ' . $db->qn('parent') . '.' . $fldLft)
			->where($db->qn('node') . '.' . $fldLft . ' <= ' . $db->qn('parent') . '.' . $fldRgt)
			->where($db->qn('node') . '.' . $fldLft . ' = ' . $db->q($this->lft))
			->group($db->qn('node') . '.' . $fldLft)
			->order($db->qn('node') . '.' . $fldLft . ' ASC');

		$query = $db->getQuery(true)
			->select([
				$db->qn('node') . '.' . $fldLft,
				'(COUNT(' . $db->qn('parent') . '.' . $fldLft . ') - (' .
				$db->qn('sub_tree') . '.' . $db->qn('depth') . ' + 1)) AS ' . $db->qn('depth'),
			])
			->from($db->qn($this->tableName) . ' AS ' . $db->qn('node'))
			->join('CROSS', $db->qn($this->tableName) . ' AS ' . $db->qn('parent'))
			->join('CROSS', $db->qn($this->tableName) . ' AS ' . $db->qn('sub_parent'))
			->join('CROSS', '(' . $subQuery . ') AS ' . $db->qn('sub_tree'))
			->where($db->qn('node') . '.' . $fldLft . ' >= ' . $db->qn('parent') . '.' . $fldLft)
			->where($db->qn('node') . '.' . $fldLft . ' <= ' . $db->qn('parent') . '.' . $fldRgt)
			->where($db->qn('node') . '.' . $fldLft . ' >= ' . $db->qn('sub_parent') . '.' . $fldLft)
			->where($db->qn('node') . '.' . $fldLft . ' <= ' . $db->qn('sub_parent') . '.' . $fldRgt)
			->where($db->qn('sub_parent') . '.' . $fldLft . ' = ' . $db->qn('sub_tree') . '.' . $fldLft)
			->group($db->qn('node') . '.' . $fldLft)
			->having([
				$db->qn('depth') . ' > ' . $db->q(0),
				$db->qn('depth') . ' <= ' . $db->q(1),
			])
			->order($db->qn('node') . '.' . $fldLft . ' ASC');

		$leftValues = $db->setQuery($query)->loadColumn();

		if (empty($leftValues))
		{
			$leftValues = [0];
		}

		array_walk($leftValues, function (&$item, $key) use (&$db) {
			$item = $db->q($item);
		});

		$this->whereRaw($db->qn('node') . '.' . $fldLft . ' IN (' . implode(',', $leftValues) . ')');
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
	 * Resets cached values used to speed up querying the tree
	 *
	 * @return  static  for chaining
	 */
	protected function resetTreeCache()
	{
		$this->treeDepth     = null;
		$this->treeRoot      = null;
		$this->treeParent    = null;
		$this->treeNestedGet = false;

		return $this;
	}
}
