<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\Event\Dispatcher;
use Joomla\Event\Event;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Table class supporting modified pre-order tree traversal behavior.
 *
 * @since  1.7.0
 */
class Nested extends Table
{
    /**
     * Object property holding the primary key of the parent node.  Provides adjacency list data for nodes.
     *
     * @var    integer
     * @since  1.7.0
     */
    public $parent_id;

    /**
     * Object property holding the depth level of the node in the tree.
     *
     * @var    integer
     * @since  1.7.0
     */
    public $level;

    /**
     * Object property holding the left value of the node for managing its placement in the nested sets tree.
     *
     * @var    integer
     * @since  1.7.0
     */
    public $lft;

    /**
     * Object property holding the right value of the node for managing its placement in the nested sets tree.
     *
     * @var    integer
     * @since  1.7.0
     */
    public $rgt;

    /**
     * Object property holding the alias of this node used to construct the full text path, forward-slash delimited.
     *
     * @var    string
     * @since  1.7.0
     */
    public $alias;

    /**
     * Object property to hold the location type to use when storing the row.
     *
     * @var    string
     * @since  1.7.0
     * @see    Nested::$_validLocations
     */
    protected $_location;

    /**
     * Object property to hold the primary key of the location reference node to use when storing the row.
     *
     * A combination of location type and reference node describes where to store the current node in the tree.
     *
     * @var    integer
     * @since  1.7.0
     */
    protected $_location_id;

    /**
     * An array to cache values in recursive processes.
     *
     * @var    array
     * @since  1.7.0
     */
    protected $_cache = [];

    /**
     * Debug level
     *
     * @var    integer
     * @since  1.7.0
     */
    protected $_debug = 0;

    /**
     * Cache for the root ID
     *
     * @var    integer
     * @since  3.3
     */
    protected static $root_id = 0;

    /**
     * Array declaring the valid location values for moving a node
     *
     * @var    array
     * @since  3.7.0
     */
    private $_validLocations = ['before', 'after', 'first-child', 'last-child'];

    /**
     * Sets the debug level on or off
     *
     * @param   integer  $level  0 = off, 1 = on
     *
     * @return  void
     *
     * @since   1.7.0
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
     * @since   1.7.0
     * @throws  \RuntimeException on database error
     */
    public function getPath($pk = null, $diagnostic = false)
    {
        $k  = $this->_tbl_key;
        $pk = (\is_null($pk)) ? $this->$k : $pk;

        // Get the path from the node to the root.
        $select = ($diagnostic) ? 'p.' . $k . ', p.parent_id, p.level, p.lft, p.rgt' : 'p.*';
        $query  = $this->_db->getQuery(true)
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
     * @since   1.7.0
     * @throws  \RuntimeException on database error.
     */
    public function getTree($pk = null, $diagnostic = false)
    {
        $k  = $this->_tbl_key;
        $pk = (\is_null($pk)) ? $this->$k : $pk;

        // Get the node and children as a tree.
        $select = ($diagnostic) ? 'n.' . $k . ', n.parent_id, n.level, n.lft, n.rgt' : 'n.*';
        $query  = $this->_db->getQuery(true)
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
     * @note    Since 3.0.0 this method returns null if the node does not exist.
     * @since   1.7.0
     * @throws  \RuntimeException on database error.
     */
    public function isLeaf($pk = null)
    {
        $k    = $this->_tbl_key;
        $pk   = (\is_null($pk)) ? $this->$k : $pk;
        $node = $this->_getNode($pk);

        // Get the node by primary key.
        if (empty($node)) {
            // Error message set in getNode method.
            return;
        }

        // The node is a leaf node.
        return ($node->rgt - $node->lft) == 1;
    }

    /**
     * Method to set the location of a node in the tree object.  This method does not
     * save the new location to the database, but will set it in the object so
     * that when the node is stored it will be stored in the new location.
     *
     * @param   integer  $referenceId  The primary key of the node to reference new location by.
     * @param   string   $position     Location type string.
     *
     * @return  void
     *
     * @note    Since 3.0.0 this method returns void and throws an \InvalidArgumentException when an invalid position is passed.
     * @see     Nested::$_validLocations
     * @since   1.7.0
     * @throws  \InvalidArgumentException
     */
    public function setLocation($referenceId, $position = 'after')
    {
        // Make sure the location is valid.
        if (!\in_array($position, $this->_validLocations)) {
            throw new \InvalidArgumentException(
                \sprintf('Invalid location "%1$s" given, valid values are %2$s', $position, implode(', ', $this->_validLocations))
            );
        }

        // Set the location properties.
        $this->_location    = $position;
        $this->_location_id = $referenceId;
    }

    /**
     * Method to move a row in the ordering sequence of a group of rows defined by an SQL WHERE clause.
     * Negative numbers move the row up in the sequence and positive numbers move it down.
     *
     * @param   integer  $delta  The direction and magnitude to move the row in the ordering sequence.
     * @param   string   $where  WHERE clause to use for limiting the selection of rows to compact the
     *                           ordering values.
     *
     * @return  mixed    Boolean true on success.
     *
     * @since   1.7.0
     */
    public function move($delta, $where = '')
    {
        $k  = $this->_tbl_key;
        $pk = $this->$k;

        $query = $this->_db->getQuery(true)
            ->select($k)
            ->from($this->_tbl)
            ->where('parent_id = ' . $this->parent_id);

        if ($where) {
            $query->where($where);
        }

        if ($delta > 0) {
            $query->where('rgt > ' . $this->rgt)
                ->order('rgt ASC');
            $position = 'after';
        } else {
            $query->where('lft < ' . $this->lft)
                ->order('lft DESC');
            $position = 'before';
        }

        $this->_db->setQuery($query);
        $referenceId = $this->_db->loadResult();

        if ($referenceId) {
            return $this->moveByReference($referenceId, $position, $pk);
        }

        return false;
    }

    /**
     * Method to move a node and its children to a new location in the tree.
     *
     * @param   integer  $referenceId      The primary key of the node to reference new location by.
     * @param   string   $position         Location type string. ['before', 'after', 'first-child', 'last-child']
     * @param   integer  $pk               The primary key of the node to move.
     * @param   boolean  $recursiveUpdate  Flag indicate that method recursiveUpdatePublishedColumn should be call.
     *
     * @return  boolean  True on success.
     *
     * @since   1.7.0
     * @throws  \RuntimeException on database error.
     */
    public function moveByReference($referenceId, $position = 'after', $pk = null, $recursiveUpdate = true)
    {
        if ($this->_debug) {
            echo "\nMoving ReferenceId:$referenceId, Position:$position, PK:$pk";
        }

        $k  = $this->_tbl_key;
        $pk = (\is_null($pk)) ? $this->$k : $pk;

        // Get the node by id.
        if (!$node = $this->_getNode($pk)) {
            // Error message set in getNode method.
            return false;
        }

        // Get the ids of child nodes.
        $query = $this->_db->getQuery(true)
            ->select($k)
            ->from($this->_tbl)
            ->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt);

        $children = $this->_db->setQuery($query)->loadColumn();

        if ($this->_debug) {
            $this->_logtable(false);
        }

        // Cannot move the node to be a child of itself.
        if (\in_array($referenceId, $children)) {
            $this->setError(
                new \UnexpectedValueException(
                    \sprintf('%1$s::moveByReference() is trying to make record ID %2$d a child of itself.', \get_class($this), $pk)
                )
            );

            return false;
        }

        // Lock the table for writing.
        if (!$this->_lock()) {
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
        if ($referenceId) {
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
        } else {
            // We are moving the tree to be the last child of the root node
            // Get the last root node as the reference node.
            $query->clear()
                ->select($this->_tbl_key . ', parent_id, level, lft, rgt')
                ->from($this->_tbl)
                ->where('parent_id = 0')
                ->order('lft DESC');

            $query->setLimit(1);
            $this->_db->setQuery($query);
            $reference = $this->_db->loadObject();

            if ($this->_debug) {
                $this->_logtable(false);
            }

            // Get the reposition data for re-inserting the node after the found root.
            if (!$repositionData = $this->_getTreeRepositionData($reference, $node->width, 'last-child')) {
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
        $offset      = $repositionData->new_lft - $node->lft;
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
        if ($node->parent_id != $repositionData->new_parent_id) {
            $query = $this->_db->getQuery(true)
                ->update($this->_tbl);

            // Update the title and alias fields if they exist for the table.
            $fields = $this->getFields();

            if ($this->hasField('title') && $this->title !== null) {
                $query->set('title = ' . $this->_db->quote($this->title));
            }

            if (\array_key_exists('alias', $fields) && $this->alias !== null) {
                $query->set('alias = ' . $this->_db->quote($this->alias));
            }

            $query->set('parent_id = ' . (int) $repositionData->new_parent_id)
                ->where($this->_tbl_key . ' = ' . (int) $node->$k);
            $this->_db->setQuery($query);

            $this->_runQuery($query, 'JLIB_DATABASE_ERROR_MOVE_FAILED');
        }

        // Unlock the table for writing.
        $this->_unlock();

        if ($this->hasField('published') && $recursiveUpdate) {
            $this->recursiveUpdatePublishedColumn($node->$k);
        }

        // Set the object values.
        $this->parent_id = $repositionData->new_parent_id;
        $this->level     = $repositionData->new_level;
        $this->lft       = $repositionData->new_lft;
        $this->rgt       = $repositionData->new_rgt;

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
     * @since   1.7.0
     */
    public function delete($pk = null, $children = true)
    {
        $k  = $this->_tbl_key;
        $pk = (\is_null($pk)) ? $this->$k : $pk;

        // Pre-processing by observers
        $event = new Event(
            'onBeforeDelete',
            [
                'pk' => $pk,
            ]
        );
        $this->getDispatcher()->dispatch('onBeforeDelete', $event);

        // If tracking assets, remove the asset first.
        if ($this->_trackAssets) {
            $name  = $this->_getAssetName();
            $asset = new Asset($this->getDbo(), $this->getDispatcher());

            if ($asset->loadByName($name)) {
                // Delete the node in assets table.
                if (!$asset->delete(null, $children)) {
                    $this->setError($asset->getError());

                    return false;
                }
            } else {
                $this->setError($asset->getError());

                return false;
            }
        }

        // Lock the table for writing.
        if (!$this->_lock()) {
            // Error message set in lock method.
            return false;
        }

        // Get the node by id.
        $node = $this->_getNode($pk);

        if (empty($node)) {
            // Error message set in getNode method.
            $this->_unlock();

            return false;
        }

        $query = $this->_db->getQuery(true);

        // Should we delete all children along with the node?
        if ($children) {
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
        } else {
            // Leave the children and move them up a level.
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

        // Post-processing by observers
        $event = new Event(
            'onAfterDelete',
            [
                'pk' => $pk,
            ]
        );
        $this->getDispatcher()->dispatch('onAfterDelete', $event);

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
     * @since   1.7.0
     */
    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        $this->parent_id = (int) $this->parent_id;

        // Set up a mini exception handler.
        try {
            // Check that the parent_id field is valid.
            if ($this->parent_id == 0) {
                throw new \UnexpectedValueException(\sprintf('Invalid `parent_id` [%1$d] in %2$s::check()', $this->parent_id, \get_class($this)));
            }

            $query = $this->_db->getQuery(true)
                ->select('1')
                ->from($this->_tbl)
                ->where($this->_tbl_key . ' = ' . $this->parent_id);

            if (!$this->_db->setQuery($query)->loadResult()) {
                throw new \UnexpectedValueException(\sprintf('Invalid `parent_id` [%1$d] in %2$s::check()', $this->parent_id, \get_class($this)));
            }
        } catch (\UnexpectedValueException $e) {
            // Validation error - record it and return false.
            $this->setError($e);

            return false;
        }

        return true;
    }

    /**
     * Method to store a node in the database table.
     *
     * @param   boolean  $updateNulls  True to update null values as well.
     *
     * @return  boolean  True on success.
     *
     * @since   1.7.0
     */
    public function store($updateNulls = false)
    {
        $k = $this->_tbl_key;

        // Pre-processing by observers
        $event = AbstractEvent::create(
            'onTableBeforeStore',
            [
                'subject'     => $this,
                'updateNulls' => $updateNulls,
                'k'           => $k,
            ]
        );
        $this->getDispatcher()->dispatch('onTableBeforeStore', $event);

        if ($this->_debug) {
            echo "\n" . \get_class($this) . "::store\n";
            $this->_logtable(true, false);
        }

        /*
         * If the primary key is empty, then we assume we are inserting a new node into the
         * tree.  From this point we would need to determine where in the tree to insert it.
         */
        if (empty($this->$k)) {
            /*
             * We are inserting a node somewhere in the tree with a known reference
             * node.  We have to make room for the new node and set the left and right
             * values before we insert the row.
             */
            if ($this->_location_id >= 0) {
                // Lock the table for writing.
                if (!$this->_lock()) {
                    // Error message set in lock method.
                    return false;
                }

                // We are inserting a node relative to the last root node.
                if ($this->_location_id == 0) {
                    // Get the last root node as the reference node.
                    $query = $this->_db->getQuery(true)
                        ->select($this->_tbl_key . ', parent_id, level, lft, rgt')
                        ->from($this->_tbl)
                        ->where('parent_id = 0')
                        ->order('lft DESC');

                    $query->setLimit(1);
                    $this->_db->setQuery($query);
                    $reference = $this->_db->loadObject();

                    if ($this->_debug) {
                        $this->_logtable(false);
                    }
                } else {
                    // We have a real node set as a location reference.
                    // Get the reference node by primary key.
                    if (!$reference = $this->_getNode($this->_location_id)) {
                        // Error message set in getNode method.
                        $this->_unlock();

                        return false;
                    }
                }

                // Get the reposition data for shifting the tree and re-inserting the node.
                if (!($repositionData = $this->_getTreeRepositionData($reference, 2, $this->_location))) {
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
                $this->level     = $repositionData->new_level;
                $this->lft       = $repositionData->new_lft;
                $this->rgt       = $repositionData->new_rgt;
            } else {
                // Negative parent ids are invalid
                $e = new \UnexpectedValueException(\sprintf('%s::store() used a negative _location_id', \get_class($this)));
                $this->setError($e);

                return false;
            }
        } else {
            /**
             * If we have a given primary key then we assume we are simply updating this
             * node in the tree.  We should assess whether or not we are moving the node
             * or just updating its data fields.
             */
            // If the location has been set, move the node to its new location.
            if ($this->_location_id > 0) {
                // Skip recursiveUpdatePublishedColumn method, it will be called later.
                if (!$this->moveByReference($this->_location_id, $this->_location, $this->$k, false)) {
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

        // We do not want parent::store to update observers since tables are locked and we are updating it from this
        // level of store():

        $oldDispatcher   = clone $this->getDispatcher();
        $blankDispatcher = new Dispatcher();
        $this->setDispatcher($blankDispatcher);

        $result = parent::store($updateNulls);

        // Restore previous callable dispatcher state:
        $this->setDispatcher($oldDispatcher);

        if ($result) {
            if ($this->_debug) {
                $this->_logtable();
            }
        }

        // Unlock the table for writing.
        $this->_unlock();

        if ($result && $this->hasField('published')) {
            $this->recursiveUpdatePublishedColumn($this->$k);
        }

        // Post-processing by observers
        $event = AbstractEvent::create(
            'onTableAfterStore',
            [
                'subject' => $this,
                'result'  => &$result,
            ]
        );
        $this->getDispatcher()->dispatch('onTableAfterStore', $event);

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
     * @since   1.7.0
     * @throws  \UnexpectedValueException
     */
    public function publish($pks = null, $state = 1, $userId = 0)
    {
        $k = $this->_tbl_key;

        $query      = $this->_db->getQuery(true);
        $table      = $this->_db->quoteName($this->_tbl);
        $published  = $this->_db->quoteName($this->getColumnAlias('published'));
        $checkedOut = $this->_db->quoteName($this->getColumnAlias('checked_out'));
        $key        = $this->_db->quoteName($k);

        // Sanitize input.
        $pks    = ArrayHelper::toInteger($pks);
        $userId = (int) $userId;
        $state  = (int) $state;

        // If $state > 1, then we allow state changes even if an ancestor has lower state
        // (for example, can change a child state to Archived (2) if an ancestor is Published (1)
        $compareState = ($state > 1) ? 1 : $state;

        // If there are no primary keys set check to see if the instance key is set.
        if (empty($pks)) {
            if ($this->$k) {
                $pks = explode(',', $this->$k);
            } else {
                // Nothing to set publishing state on, return false.
                $e = new \UnexpectedValueException(\sprintf('%s::publish(%s, %d, %d) empty.', \get_class($this), implode(',', $pks), $state, $userId));
                $this->setError($e);

                return false;
            }
        }

        // Determine if there is checkout support for the table.
        $checkoutSupport = ($this->hasField('checked_out') || $this->hasField('checked_out_time'));

        // Iterate over the primary keys to execute the publish action if possible.
        foreach ($pks as $pk) {
            // Get the node by primary key.
            if (!$node = $this->_getNode($pk)) {
                // Error message set in getNode method.
                return false;
            }

            // If the table has checkout support, verify no children are checked out.
            if ($checkoutSupport) {
                // Ensure that children are not checked out.
                $query->clear()
                    ->select('COUNT(' . $k . ')')
                    ->from($this->_tbl)
                    ->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt)
                    ->where('(' . $checkedOut . ' <> 0 AND ' . $checkedOut . ' <> ' . (int) $userId . ')');
                $this->_db->setQuery($query);

                // Check for checked out children.
                if ($this->_db->loadResult()) {
                    // @todo Convert to a conflict exception when available.
                    $e = new \RuntimeException(\sprintf('%s::publish(%s, %d, %d) checked-out conflict.', \get_class($this), $pks[0], $state, $userId));

                    $this->setError($e);

                    return false;
                }
            }

            // If any parent nodes have lower published state values, we cannot continue.
            if ($node->parent_id) {
                // Get any ancestor nodes that have a lower publishing state.
                $query->clear()
                    ->select('1')
                    ->from($table)
                    ->where('lft < ' . (int) $node->lft)
                    ->where('rgt > ' . (int) $node->rgt)
                    ->where('parent_id > 0')
                    ->where($published . ' < ' . (int) $compareState);

                // Just fetch one row (one is one too many).
                $query->setLimit(1);
                $this->_db->setQuery($query);

                if ($this->_db->loadResult()) {
                    $e = new \UnexpectedValueException(
                        \sprintf('%s::publish(%s, %d, %d) ancestors have lower state.', \get_class($this), $pks[0], $state, $userId)
                    );
                    $this->setError($e);

                    return false;
                }
            }

            $this->recursiveUpdatePublishedColumn($pk, $state);

            // If checkout support exists for the object, check the row in.
            if ($checkoutSupport) {
                $this->checkIn($pk);
            }
        }

        // If the Table instance value is in the list of primary keys that were set, set the instance.
        if (\in_array($this->$k, $pks)) {
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
     * @since   1.7.0
     * @throws  \RuntimeException on database error.
     */
    public function orderUp($pk)
    {
        $k  = $this->_tbl_key;
        $pk = (\is_null($pk)) ? $this->$k : $pk;

        // Lock the table for writing.
        if (!$this->_lock()) {
            // Error message set in lock method.
            return false;
        }

        // Get the node by primary key.
        $node = $this->_getNode($pk);

        if (empty($node)) {
            // Error message set in getNode method.
            $this->_unlock();

            return false;
        }

        // Get the left sibling node.
        $sibling = $this->_getNode($node->lft - 1, 'right');

        if (empty($sibling)) {
            // Error message set in getNode method.
            $this->_unlock();

            return false;
        }

        try {
            // Get the primary keys of child nodes.
            $query = $this->_db->getQuery(true)
                ->select($this->_tbl_key)
                ->from($this->_tbl)
                ->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt);

            $children = $this->_db->setQuery($query)->loadColumn();

            // Shift left and right values for the node and its children.
            $query->clear()
                ->update($this->_tbl)
                ->set('lft = lft - ' . (int) $sibling->width)
                ->set('rgt = rgt - ' . (int) $sibling->width)
                ->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt);
            $this->_db->setQuery($query)->execute();

            // Shift left and right values for the sibling and its children.
            $query->clear()
                ->update($this->_tbl)
                ->set('lft = lft + ' . (int) $node->width)
                ->set('rgt = rgt + ' . (int) $node->width)
                ->where('lft BETWEEN ' . (int) $sibling->lft . ' AND ' . (int) $sibling->rgt)
                ->where($this->_tbl_key . ' NOT IN (' . implode(',', $children) . ')');
            $this->_db->setQuery($query)->execute();
        } catch (\RuntimeException $e) {
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
     * @since   1.7.0
     * @throws  \RuntimeException on database error.
     */
    public function orderDown($pk)
    {
        $k  = $this->_tbl_key;
        $pk = (\is_null($pk)) ? $this->$k : $pk;

        // Lock the table for writing.
        if (!$this->_lock()) {
            // Error message set in lock method.
            return false;
        }

        // Get the node by primary key.
        $node = $this->_getNode($pk);

        if (empty($node)) {
            // Error message set in getNode method.
            $this->_unlock();

            return false;
        }

        $query = $this->_db->getQuery(true);

        // Get the right sibling node.
        $sibling = $this->_getNode($node->rgt + 1, 'left');

        if (empty($sibling)) {
            // Error message set in getNode method.
            $this->_unlock();

            return false;
        }

        try {
            // Get the primary keys of child nodes.
            $query->clear()
                ->select($this->_tbl_key)
                ->from($this->_tbl)
                ->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt);
            $this->_db->setQuery($query);
            $children = $this->_db->loadColumn();

            // Shift left and right values for the node and its children.
            $query->clear()
                ->update($this->_tbl)
                ->set('lft = lft + ' . (int) $sibling->width)
                ->set('rgt = rgt + ' . (int) $sibling->width)
                ->where('lft BETWEEN ' . (int) $node->lft . ' AND ' . (int) $node->rgt);
            $this->_db->setQuery($query)->execute();

            // Shift left and right values for the sibling and its children.
            $query->clear()
                ->update($this->_tbl)
                ->set('lft = lft - ' . (int) $node->width)
                ->set('rgt = rgt - ' . (int) $node->width)
                ->where('lft BETWEEN ' . (int) $sibling->lft . ' AND ' . (int) $sibling->rgt)
                ->where($this->_tbl_key . ' NOT IN (' . implode(',', $children) . ')');
            $this->_db->setQuery($query)->execute();
        } catch (\RuntimeException $e) {
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
     * @since   1.7.0
     */
    public function getRootId()
    {
        if ((int) self::$root_id > 0) {
            return self::$root_id;
        }

        // Get the root item.
        $k = $this->_tbl_key;

        // Test for a unique record with parent_id = 0
        $query = $this->_db->getQuery(true)
            ->select($k)
            ->from($this->_tbl)
            ->where('parent_id = 0');

        $result = $this->_db->setQuery($query)->loadColumn();

        if (\count($result) == 1) {
            self::$root_id = $result[0];

            return self::$root_id;
        }

        // Test for a unique record with lft = 0
        $query->clear()
            ->select($k)
            ->from($this->_tbl)
            ->where('lft = 0');

        $result = $this->_db->setQuery($query)->loadColumn();

        if (\count($result) == 1) {
            self::$root_id = $result[0];

            return self::$root_id;
        }

        $fields = $this->getFields();

        if (\array_key_exists('alias', $fields)) {
            // Test for a unique record alias = root
            $query->clear()
                ->select($k)
                ->from($this->_tbl)
                ->where('alias = ' . $this->_db->quote('root'));

            $result = $this->_db->setQuery($query)->loadColumn();

            if (\count($result) == 1) {
                self::$root_id = $result[0];

                return self::$root_id;
            }
        }

        $e = new \UnexpectedValueException(\sprintf('%s::getRootId', \get_class($this)));
        $this->setError($e);
        self::$root_id = false;

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
     * @since   1.7.0
     * @throws  \RuntimeException on database error.
     */
    public function rebuild($parentId = null, $leftId = 0, $level = 0, $path = '')
    {
        // If no parent is provided, try to find it.
        if ($parentId === null) {
            // Get the root item.
            $parentId = $this->getRootId();

            if ($parentId === false) {
                return false;
            }
        }

        $query = $this->_db->getQuery(true);

        // Build the structure of the recursive query.
        if (!isset($this->_cache['rebuild.sql'])) {
            $query->clear()
                ->select($this->_tbl_key . ', alias')
                ->from($this->_tbl)
                ->where('parent_id = %d');

            // If the table has an ordering field, use that for ordering.
            if ($this->hasField('ordering')) {
                $query->order('parent_id, ' . $this->_db->quoteName($this->getColumnAlias('ordering')) . ', lft');
            } else {
                $query->order('parent_id, lft');
            }

            $this->_cache['rebuild.sql'] = (string) $query;
        }

        // Make a shortcut to database object.

        // Assemble the query to find all children of this node.
        $this->_db->setQuery(\sprintf($this->_cache['rebuild.sql'], (int) $parentId));

        $children = $this->_db->loadObjectList();

        // The right value of this node is the left value + 1
        $rightId = $leftId + 1;

        // Execute this function recursively over all children
        foreach ($children as $node) {
            /*
             * $rightId is the current right value, which is incremented on recursion return.
             * Increment the level for the children.
             * Add this item's alias to the path (but avoid a leading /)
             */
            $rightId = $this->rebuild($node->{$this->_tbl_key}, $rightId, $level + 1, $path . (empty($path) ? '' : '/') . $node->alias);

            // If there is an update failure, return false to break out of the recursion.
            if ($rightId === false) {
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
     * Method to rebuild the node's path field from the alias values of the nodes from the current node to the root node of the tree.
     *
     * @param   integer  $pk  Primary key of the node for which to get the path.
     *
     * @return  boolean  True on success.
     *
     * @since   1.7.0
     */
    public function rebuildPath($pk = null)
    {
        $fields = $this->getFields();

        // If there is no alias or path field, just return true.
        if (!\array_key_exists('alias', $fields) || !\array_key_exists('path', $fields)) {
            return true;
        }

        $k  = $this->_tbl_key;
        $pk = (\is_null($pk)) ? $this->$k : $pk;

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
        if ($segments[0] === 'root') {
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
     * Method to reset class properties to the defaults set in the class
     * definition. It will ignore the primary key as well as any private class
     * properties (except $_errors).
     *
     * @return  void
     *
     * @since   3.2.1
     */
    public function reset()
    {
        parent::reset();

        // Reset the location properties.
        $this->setLocation(0);
    }

    /**
     * Method to update order of table rows
     *
     * @param   array  $idArray   id numbers of rows to be reordered.
     * @param   array  $lftArray  lft values of rows to be reordered.
     *
     * @return  integer|boolean  1 + value of root rgt on success, false on failure.
     *
     * @since   1.7.0
     * @throws  \Exception on database error.
     */
    public function saveorder($idArray = null, $lftArray = null)
    {
        try {
            $query = $this->_db->getQuery(true);

            // Validate arguments
            if (\is_array($idArray) && \is_array($lftArray) && \count($idArray) == \count($lftArray)) {
                for ($i = 0, $count = \count($idArray); $i < $count; $i++) {
                    // Do an update to change the lft values in the table for each id
                    $query->clear()
                        ->update($this->_tbl)
                        ->where($this->_tbl_key . ' = ' . (int) $idArray[$i])
                        ->set('lft = ' . (int) $lftArray[$i]);

                    $this->_db->setQuery($query)->execute();

                    if ($this->_debug) {
                        $this->_logtable();
                    }
                }

                return $this->rebuild();
            }

            return false;
        } catch (\Exception $e) {
            $this->_unlock();
            throw $e;
        }
    }

    /**
     * Method to recursive update published column for children rows.
     *
     * @param   integer  $pk        Id number of row which published column was changed.
     * @param   integer  $newState  An optional value for published column of row identified by $pk.
     *
     * @return  boolean  True on success.
     *
     * @since   3.7.0
     * @throws  \RuntimeException on database error.
     */
    protected function recursiveUpdatePublishedColumn($pk, $newState = null)
    {
        $query     = $this->_db->getQuery(true);
        $table     = $this->_db->quoteName($this->_tbl);
        $key       = $this->_db->quoteName($this->_tbl_key);
        $published = $this->_db->quoteName($this->getColumnAlias('published'));

        if ($newState !== null) {
            // Use a new published state in changed row.
            $newState = "(CASE WHEN p2.$key = " . (int) $pk . " THEN " . (int) $newState . " ELSE p2.$published END)";
        } else {
            $newState = "p2.$published";
        }

        /**
         * We have to calculate the correct value for c2.published
         * based on p2.published and own c2.published column,
         * where (p2) is parent category is and (c2) current category
         *
         * p2.published <= c2.published AND p2.published > 0 THEN c2.published
         *            2 <=  2 THEN  2 (If archived in archived then archived)
         *            1 <=  2 THEN  2 (If archived in published then archived)
         *            1 <=  1 THEN  1 (If published in published then published)
         *
         * p2.published >  c2.published AND c2.published > 0 THEN p2.published
         *            2 >   1 THEN  2 (If published in archived then archived)
         *
         * p2.published >  c2.published THEN c2.published ELSE p2.published
         *            2 >  -2 THEN -2 (If trashed in archived then trashed)
         *            2 >   0 THEN  0 (If unpublished in archived then unpublished)
         *            1 >   0 THEN  0 (If unpublished in published then unpublished)
         *            0 >  -2 THEN -2 (If trashed in unpublished then trashed)
         * ELSE
         *            0 <=  2 THEN  0 (If archived in unpublished then unpublished)
         *            0 <=  1 THEN  0 (If published in unpublished then unpublished)
         *            0 <=  0 THEN  0 (If unpublished in unpublished then unpublished)
         *           -2 <= -2 THEN -2 (If trashed in trashed then trashed)
         *           -2 <=  0 THEN -2 (If unpublished in trashed then trashed)
         *           -2 <=  1 THEN -2 (If published in trashed then trashed)
         *           -2 <=  2 THEN -2 (If archived in trashed then trashed)
         */

        // Find node and all children keys
        $query->select("c.$key")
            ->from("$table AS node")
            ->leftJoin("$table AS c ON node.lft <= c.lft AND c.rgt <= node.rgt")
            ->where("node.$key = " . (int) $pk);

        $pks = $this->_db->setQuery($query)->loadColumn();

        // Prepare a list of correct published states.
        $subquery = (string) $query->clear()
            ->select("c2.$key AS newId")
            ->select("CASE WHEN MIN($newState) > 0 THEN MAX($newState) ELSE MIN($newState) END AS newPublished")
            ->from("$table AS c2")
            ->innerJoin("$table AS p2 ON p2.lft <= c2.lft AND c2.rgt <= p2.rgt")
            ->where("c2.$key IN (" . implode(',', $pks) . ")")
            ->group("c2.$key");

        // Update and cascade the publishing state.
        $query->clear()
            ->update($table)
            ->innerJoin("($subquery) AS c2")
            ->set("$published = " . $this->_db->quoteName("c2.newpublished"))
            ->where("$key = c2.newId")
            ->where("$key IN (" . implode(',', $pks) . ")");

        $this->_runQuery($query, 'JLIB_DATABASE_ERROR_STORE_FAILED');

        return true;
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
     * @since   1.7.0
     * @throws  \RuntimeException on database error.
     */
    protected function _getNode($id, $key = null)
    {
        // Determine which key to get the node base on.
        switch ($key) {
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

        $query->setLimit(1);
        $row = $this->_db->setQuery($query)->loadObject();

        // Check for no $row returned
        if (empty($row)) {
            $e = new \UnexpectedValueException(\sprintf('%s::_getNode(%d, %s) failed.', \get_class($this), $id, $k));
            $this->setError($e);

            return false;
        }

        // Do some simple calculations.
        $row->numChildren = (int) ($row->rgt - $row->lft - 1) / 2;
        $row->width       = (int) $row->rgt - $row->lft + 1;

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
     *                                   should be made.
     *
     * @return  mixed    Boolean false on failure or data object on success.
     *
     * @since   1.7.0
     */
    protected function _getTreeRepositionData($referenceNode, $nodeWidth, $position = 'before')
    {
        // Make sure the reference an object with a left and right id.
        if (!\is_object($referenceNode) || !(isset($referenceNode->lft) && isset($referenceNode->rgt))) {
            return false;
        }

        // A valid node cannot have a width less than 2.
        if ($nodeWidth < 2) {
            return false;
        }

        $k    = $this->_tbl_key;
        $data = new \stdClass();

        // Run the calculations and build the data object by reference position.
        switch ($position) {
            case 'first-child':
                $data->left_where  = 'lft > ' . $referenceNode->lft;
                $data->right_where = 'rgt >= ' . $referenceNode->lft;

                $data->new_lft       = $referenceNode->lft + 1;
                $data->new_rgt       = $referenceNode->lft + $nodeWidth;
                $data->new_parent_id = $referenceNode->$k;
                $data->new_level     = $referenceNode->level + 1;
                break;

            case 'last-child':
                $data->left_where  = 'lft > ' . ($referenceNode->rgt);
                $data->right_where = 'rgt >= ' . ($referenceNode->rgt);

                $data->new_lft       = $referenceNode->rgt;
                $data->new_rgt       = $referenceNode->rgt + $nodeWidth - 1;
                $data->new_parent_id = $referenceNode->$k;
                $data->new_level     = $referenceNode->level + 1;
                break;

            case 'before':
                $data->left_where  = 'lft >= ' . $referenceNode->lft;
                $data->right_where = 'rgt >= ' . $referenceNode->lft;

                $data->new_lft       = $referenceNode->lft;
                $data->new_rgt       = $referenceNode->lft + $nodeWidth - 1;
                $data->new_parent_id = $referenceNode->parent_id;
                $data->new_level     = $referenceNode->level;
                break;

            default:
            case 'after':
                $data->left_where  = 'lft > ' . $referenceNode->rgt;
                $data->right_where = 'rgt > ' . $referenceNode->rgt;

                $data->new_lft       = $referenceNode->rgt + 1;
                $data->new_rgt       = $referenceNode->rgt + $nodeWidth;
                $data->new_parent_id = $referenceNode->parent_id;
                $data->new_level     = $referenceNode->level;
                break;
        }

        if ($this->_debug) {
            echo "\nRepositioning Data for $position\n-----------------------------------\nLeft Where:    $data->left_where"
                . "\nRight Where:   $data->right_where\nNew Lft:       $data->new_lft\nNew Rgt:       $data->new_rgt"
                . "\nNew Parent ID: $data->new_parent_id\nNew Level:     $data->new_level\n";
        }

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
     * @since   1.7.0
     */
    protected function _logtable($showData = true, $showQuery = true)
    {
        $sep    = "\n" . str_pad('', 40, '-');
        $buffer = '';

        if ($showQuery) {
            $buffer .= "\n" . htmlspecialchars($this->_db->getQuery(), ENT_QUOTES, 'UTF-8') . $sep;
        }

        if ($showData) {
            $query = $this->_db->getQuery(true)
                ->select($this->_tbl_key . ', parent_id, lft, rgt, level')
                ->from($this->_tbl)
                ->order($this->_tbl_key);
            $this->_db->setQuery($query);

            $rows = $this->_db->loadRowList();
            $buffer .= \sprintf("\n| %4s | %4s | %4s | %4s |", $this->_tbl_key, 'par', 'lft', 'rgt');
            $buffer .= $sep;

            foreach ($rows as $row) {
                $buffer .= \sprintf("\n| %4s | %4s | %4s | %4s |", $row[0], $row[1], $row[2], $row[3]);
            }

            $buffer .= $sep;
        }

        echo $buffer;
    }

    /**
     * Runs a query and unlocks the database on an error.
     *
     * @param   mixed   $query         A string or DatabaseQuery object.
     * @param   string  $errorMessage  Unused.
     *
     * @return  void
     *
     * @note    Since 3.0.0 this method returns void and will rethrow the database exception.
     * @since   1.7.0
     * @throws  \Exception on database error.
     */
    protected function _runQuery($query, $errorMessage)
    {
        // Prepare to catch an exception.
        try {
            $this->_db->setQuery($query)->execute();

            if ($this->_debug) {
                $this->_logtable();
            }
        } catch (\Exception $e) {
            // Unlock the tables and rethrow.
            $this->_unlock();

            throw $e;
        }
    }
}
