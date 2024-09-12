<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Table class supporting modified pre-order tree traversal behavior.
 *
 * @since  1.7.0
 */
class Asset extends Nested
{
    /**
     * The primary key of the asset.
     *
     * @var    integer
     * @since  1.7.0
     */
    public $id = null;

    /**
     * The unique name of the asset.
     *
     * @var    string
     * @since  1.7.0
     */
    public $name = null;

    /**
     * The human readable title of the asset.
     *
     * @var    string
     * @since  1.7.0
     */
    public $title = null;

    /**
     * The rules for the asset stored in a JSON string
     *
     * @var    string
     * @since  1.7.0
     */
    public $rules = null;

    /**
     * Constructor
     *
     * @param   DatabaseDriver        $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since   1.7.0
     */
    public function __construct(DatabaseDriver $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__assets', 'id', $db, $dispatcher);
    }

    /**
     * Method to load an asset by its name.
     *
     * @param   string  $name  The name of the asset.
     *
     * @return  integer
     *
     * @since   1.7.0
     */
    public function loadByName($name)
    {
        return $this->load(['name' => $name]);
    }

    /**
     * Assert that the nested set data is valid.
     *
     * @return  boolean  True if the instance is sane and able to be stored in the database.
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

        if (empty($this->rules)) {
            $this->rules = '{}';
        }

        // Nested does not allow parent_id = 0, override this.
        if ($this->parent_id > 0) {
            // Get the DatabaseQuery object
            $query = $this->_db->getQuery(true)
                ->select('1')
                ->from($this->_db->quoteName($this->_tbl))
                ->where($this->_db->quoteName('id') . ' = ' . $this->parent_id);

            $query->setLimit(1);

            if ($this->_db->setQuery($query)->loadResult()) {
                return true;
            }

            $this->setError(Text::_('JLIB_DATABASE_ERROR_INVALID_PARENT_ID'));

            return false;
        }

        return true;
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
     * @since   3.5
     * @throws  \RuntimeException on database error.
     */
    public function rebuild($parentId = null, $leftId = 0, $level = 0, $path = null)
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
                ->select($this->_tbl_key)
                ->from($this->_tbl)
                ->where('parent_id = %d');

            // If the table has an ordering field, use that for ordering.
            if ($this->hasField('ordering')) {
                $query->order('parent_id, ordering, lft');
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
            $rightId = $this->rebuild($node->{$this->_tbl_key}, $rightId, $level + 1);

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
            ->where($this->_tbl_key . ' = ' . (int) $parentId);
        $this->_db->setQuery($query)->execute();

        // Return the right value of this node + 1.
        return $rightId + 1;
    }
}
