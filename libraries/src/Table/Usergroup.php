<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Usergroup table class.
 *
 * @since  1.7.0
 */
class Usergroup extends Table
{
    /**
     * Constructor
     *
     * @param   DatabaseInterface     $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since   1.7.0
     */
    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__usergroups', 'id', $db, $dispatcher);
    }

    /**
     * Method to check the current record to save
     *
     * @return  boolean  True on success
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

        // Validate the title.
        if ((trim($this->title)) == '') {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_USERGROUP_TITLE'));

            return false;
        }

        // The parent_id can not be equal to the current id
        if ($this->id === (int) $this->parent_id) {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_USERGROUP_PARENT_ID_NOT_VALID'));

            return false;
        }

        // Check for a duplicate parent_id, title.
        // There is a unique index on the (parent_id, title) field in the table.
        $db       = $this->_db;
        $parentId = (int) $this->parent_id;
        $title    = trim($this->title);
        $id       = (int) $this->id;
        $query    = $db->getQuery(true)
            ->select('COUNT(title)')
            ->from($this->_tbl)
            ->where($db->quoteName('title') . ' = :title')
            ->where($db->quoteName('parent_id') . ' = :parentid')
            ->where($db->quoteName('id') . ' <> :id')
            ->bind(':title', $title)
            ->bind(':parentid', $parentId, ParameterType::INTEGER)
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);

        if ($db->loadResult() > 0) {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_USERGROUP_TITLE_EXISTS'));

            return false;
        }

        // We do not allow to move non public to root and public to non-root
        if (!empty($this->id)) {
            $table = new self($this->getDbo(), $this->getDispatcher());

            $table->load($this->id);

            if ((!$table->parent_id && $this->parent_id) || ($table->parent_id && !$this->parent_id)) {
                $this->setError(Text::_('JLIB_DATABASE_ERROR_USERGROUP_PARENT_ID_NOT_VALID'));

                return false;
            }
        } elseif (!$this->parent_id) {
            // New entry should always be greater 0
            $this->setError(Text::_('JLIB_DATABASE_ERROR_USERGROUP_PARENT_ID_NOT_VALID'));

            return false;
        }

        // The new parent_id has to be a valid group
        if ($this->parent_id) {
            $table = new self($this->getDbo(), $this->getDispatcher());
            $table->load($this->parent_id);

            if ($table->id != $this->parent_id) {
                $this->setError(Text::_('JLIB_DATABASE_ERROR_USERGROUP_PARENT_ID_NOT_VALID'));

                return false;
            }
        }

        return true;
    }

    /**
     * Method to recursively rebuild the nested set tree.
     *
     * @param   integer  $parentId  The root of the tree to rebuild.
     * @param   integer  $left      The left id to start with in building the tree.
     *
     * @return  boolean  True on success
     *
     * @since   1.7.0
     */
    public function rebuild($parentId = 0, $left = 0)
    {
        // Get the database object
        $db       = $this->_db;
        $query    = $db->getQuery(true);
        $parentId = (int) $parentId;

        // Get all children of this node
        $query->clear()
            ->select($db->quoteName('id'))
            ->from($db->quoteName($this->_tbl))
            ->where($db->quoteName('parent_id') . ' = :parentid')
            ->bind(':parentid', $parentId, ParameterType::INTEGER)
            ->order([$db->quoteName('parent_id'), $db->quoteName('title')]);

        $db->setQuery($query);
        $children = $db->loadColumn();

        // The right value of this node is the left value + 1
        $right = $left + 1;

        // Execute this function recursively over all children
        foreach ($children as $child) {
            // $right is the current right value, which is incremented on recursion return
            $right = $this->rebuild($child, $right);

            // If there is an update failure, return false to break out of the recursion
            if ($right === false) {
                return false;
            }
        }

        $left  = (int) $left;
        $right = (int) $right;

        // We've got the left value, and now that we've processed
        // the children of this node we also know the right value
        $query->clear()
            ->update($db->quoteName($this->_tbl))
            ->set($db->quoteName('lft') . ' = :lft')
            ->set($db->quoteName('rgt') . ' = :rgt')
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':lft', $left, ParameterType::INTEGER)
            ->bind(':rgt', $right, ParameterType::INTEGER)
            ->bind(':id', $parentId, ParameterType::INTEGER);
        $db->setQuery($query);

        // If there is an update failure, return false to break out of the recursion
        try {
            $db->execute();
        } catch (ExecutionFailureException) {
            return false;
        }

        // Return the right value of this node + 1
        return $right + 1;
    }

    /**
     * Inserts a new row if id is zero or updates an existing row in the database table
     *
     * @param   boolean  $updateNulls  If false, null object variables are not updated
     *
     * @return  boolean  True if successful, false otherwise and an internal error message is set
     *
     * @since   1.7.0
     */
    public function store($updateNulls = false)
    {
        if ($result = parent::store($updateNulls)) {
            // Rebuild the nested set tree.
            $this->rebuild();
        }

        return $result;
    }

    /**
     * Delete this object and its dependencies
     *
     * @param   integer  $oid  The primary key of the user group to delete.
     *
     * @return  mixed  Boolean or Exception.
     *
     * @since   1.7.0
     * @throws  \RuntimeException on database error.
     * @throws  \UnexpectedValueException on data error.
     */
    public function delete($oid = null)
    {
        if ($oid) {
            $this->load($oid);
        }

        if ($this->id == 0) {
            throw new \UnexpectedValueException('Usergroup not found');
        }

        if ($this->parent_id == 0) {
            throw new \UnexpectedValueException('Root usergroup cannot be deleted.');
        }

        if ($this->lft == 0 || $this->rgt == 0) {
            throw new \UnexpectedValueException('Left-Right data inconsistency. Cannot delete usergroup.');
        }

        $db = $this->_db;

        $lft = (int) $this->lft;
        $rgt = (int) $this->rgt;

        // Select the usergroup ID and its children
        $query = $db->getQuery(true)
            ->select($db->quoteName('c.id'))
            ->from($db->quoteName($this->_tbl, 'c'))
            ->where($db->quoteName('c.lft') . ' >= :lft')
            ->where($db->quoteName('c.rgt') . ' <= :rgt')
            ->bind(':lft', $lft, ParameterType::INTEGER)
            ->bind(':rgt', $rgt, ParameterType::INTEGER);
        $db->setQuery($query);
        $ids = $db->loadColumn();

        if (empty($ids)) {
            throw new \UnexpectedValueException('Left-Right data inconsistency. Cannot delete usergroup.');
        }

        // Delete the usergroup and its children
        $query->clear()
            ->delete($db->quoteName($this->_tbl))
            ->whereIn($db->quoteName('id'), $ids);
        $db->setQuery($query);
        $db->execute();

        // Rebuild the nested set tree.
        $this->rebuild();

        // Delete the usergroup in view levels
        $replace = [];

        foreach ($ids as $id) {
            $replace[] = ',' . $db->quote("[$id,") . ',' . $db->quote('[');
            $replace[] = ',' . $db->quote(",$id,") . ',' . $db->quote(',');
            $replace[] = ',' . $db->quote(",$id]") . ',' . $db->quote(']');
            $replace[] = ',' . $db->quote("[$id]") . ',' . $db->quote('[]');
        }

        $query->clear()
            ->select(
                [
                    $db->quoteName('id'),
                    $db->quoteName('rules'),
                ]
            )
            ->from($db->quoteName('#__viewlevels'));
        $db->setQuery($query);
        $rules = $db->loadObjectList();

        $matchIds = [];

        foreach ($rules as $rule) {
            foreach ($ids as $id) {
                if (strstr($rule->rules, '[' . $id) || strstr($rule->rules, ',' . $id) || strstr($rule->rules, $id . ']')) {
                    $matchIds[] = $rule->id;
                }
            }
        }

        if (!empty($matchIds)) {
            $query->clear()
                ->update($db->quoteName('#__viewlevels'))
                ->set($db->quoteName('rules') . ' = ' . str_repeat('REPLACE(', 4 * \count($ids)) . $db->quoteName('rules') . implode(')', $replace) . ')')
                ->whereIn($db->quoteName('id'), $matchIds);
            $db->setQuery($query);
            $db->execute();
        }

        // Delete the user to usergroup mappings for the group(s) from the database.
        $query->clear()
            ->delete($db->quoteName('#__user_usergroup_map'))
            ->whereIn($db->quoteName('group_id'), $ids);
        $db->setQuery($query);
        $db->execute();

        return true;
    }
}
