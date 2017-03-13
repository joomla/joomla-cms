<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Table;

defined('JPATH_PLATFORM') or die;

/**
 * Table class supporting modified pre-order tree traversal behavior.
 *
 * @since  11.1
 */
class Asset extends Nested
{
	/**
	 * The primary key of the asset.
	 *
	 * @var    integer
	 * @since  11.1
	 */
	public $id = null;

	/**
	 * The unique name of the asset.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $name = null;

	/**
	 * The human readable title of the asset.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $title = null;

	/**
	 * The rules for the asset stored in a JSON string
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $rules = null;

	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver  $db  Database driver object.
	 *
	 * @since   11.1
	 */
	public function __construct(\JDatabaseDriver $db)
	{
		parent::__construct('#__assets', 'id', $db);
	}

	/**
	 * Method to load an asset by its name.
	 *
	 * @param   string  $name  The name of the asset.
	 *
	 * @return  integer
	 *
	 * @since   11.1
	 */
	public function loadByName($name)
	{
		$query = $this->_db->getQuery(true)
			->select($this->_db->quoteName('id'))
			->from($this->_db->quoteName('#__assets'))
			->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote($name));
		$this->_db->setQuery($query);
		$assetId = (int) $this->_db->loadResult();

		if (empty($assetId))
		{
			return false;
		}

		return $this->load($assetId);
	}

	/**
	 * Assert that the nested set data is valid.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @since   11.1
	 */
	public function check()
	{
		try
		{
			parent::check();
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$this->parent_id = (int) $this->parent_id;

		if (empty($this->rules))
		{
			$this->rules = '{}';
		}
		// Nested does not allow parent_id = 0, override this.
		if ($this->parent_id > 0)
		{
			// Get the \JDatabaseQuery object
			$query = $this->_db->getQuery(true)
				->select('COUNT(id)')
				->from($this->_db->quoteName($this->_tbl))
				->where($this->_db->quoteName('id') . ' = ' . $this->parent_id);
			$this->_db->setQuery($query);

			if ($this->_db->loadResult())
			{
				return true;
			}
			else
			{
				$this->setError('Invalid Parent ID');

				return false;

			}
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
				->select($this->_tbl_key)
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
			$rightId = $this->rebuild($node->{$this->_tbl_key}, $rightId, $level + 1);

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
			->where($this->_tbl_key . ' = ' . (int) $parentId);
		$this->_db->setQuery($query)->execute();

		// Return the right value of this node + 1.
		return $rightId + 1;
	}

	/**
	 * Method to load a row from the database by primary key and bind the fields
	 * to the JTable instance properties.
	 *
	 * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.  If not
	 *                           set the instance property value is used.
	 * @param   boolean  $reset  True to reset the default values before loading the new row.
	 *
	 * @return  boolean  True if successful. False if row not found.
	 *
	 * @link    https://docs.joomla.org/JTable/load
	 * @since   11.1
	 * @throws  \InvalidArgumentException
	 * @throws  \RuntimeException
	 * @throws  \UnexpectedValueException
	 */

	public function load($keys = null, $reset = true)
	{
		$ret = parent::load($keys, $reset);

		if ($ret !== false && is_string($this->rules))
		{
			$this->rules = json_decode($this->rules, true);
		}

		return $ret;
	}

	/**
	 * Method to bind an associative array or object to the JTable instance.This
	 * method only binds properties that are publicly accessible and optionally
	 * takes an array of properties to ignore when binding.
	 *
	 * @param   mixed  $src     An associative array or object to bind to the JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1
	 * @throws  \InvalidArgumentException
	 */
	public function bind($src, $ignore = '')
	{
		$query = $this->_db->getQuery(true);
		$query->select('*');
		$query->from('#__permissions');
		$query->where('`assetid` = ' . (int) $src['id']);
		$query->order('permission');
		$this->_db->setQuery($query);

		$permissions = $this->_db->loadObjectList();

		$rules = array();

		foreach ($permissions AS $permission)
		{
			if (!isset($rules[$permission->permission]))
			{
				$rules[$permission->permission] = array();
			}

			$rules[$permission->permission][$permission->group] = $permission->value;
		}

		if (isset($rules) && is_array($rules) && !empty($rules))
		{
			$src['rules'] = $rules;
		}

		if (empty($src['rules']))
		{
			$src['rules'] = array();
		}

		if (is_string($src['rules']))
		{
			$src['rules'] = json_decode($src['rules'], true);
		}

		return parent::bind($src, $ignore);
	}

	/**
	 * Method to store a node in the database table.
	 *
	 * @param   boolean  $updateNulls  True to update null values as well.
	 *
	 * @return  boolean  True on success.
	 *
	 * @link    https://docs.joomla.org/JTableNested/store
	 * @since   11.1
	 */
	public function store($updateNulls = false)
	{
		$rules       = $this->rules;
		$this->rules = '{}';

		try
		{
			parent::store($updateNulls);
		}
		catch (\Exception $e)
		{
			// Check for a database error.
			if ($e->getMessage())
			{
				throw new \UnexpectedValueException(sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), $e->getMessage()));
			}
		}

		// Reset groups to the local object.
		$this->rules = $rules;
		unset($rules);

		if (is_string($this->rules))
		{
			$this->rules = json_decode($this->rules, true);
		}

		$key = $this->_tbl_key;

		if (empty($this->$key))
		{
			// Get the last id created by parent::store
			$query = $this->_db->getQuery(true);
			$query->select('id');
			$query->from($this->_tbl);
			$query->where('name = ' . $this->_db->quote($this->name));
			$this->_db->setQuery($query);

			try
			{
				$assetId = $this->_db->loadResult();
			}
			catch (\Exception $e)
			{
				// Check for a database error.
				if ($e->getMessage())
				{
					$this->_unlock();
					throw new \UnexpectedValueException(sprintf('JLIB_DATABASE_QUERY_FAILED', $e->getMessage(), get_class($this)));
				}
			}
		}
		else
		{
			$assetId = $this->$key;
		}

		// Store the rules data if parent data was saved.
		if (!is_array($this->rules) || count($this->rules) == 0)
		{
			// We have nothing to store, we are not storing empty values
			return true;
		}

		// Only delete on update
		if (!empty($this->$key))
		{
			try
			{    // Delete the old permissions.
				$query = $this->_db->getQuery(true);

				$query->delete('#__permissions');
				$query->where('assetid = ' . (int) $assetId);
				$this->_db->setQuery($query);
				$this->_db->execute();
			}
			catch (\Exception $e)
			{
				// Check for a database error.
				if ($e->getMessage())
				{
					$this->_unlock();
					throw new \UnexpectedValueException(sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', get_class($this), $e->getMessage()));
				}
			}
		}

		try
		{
			// Insert new permissions
			foreach ($this->rules AS $perName => $groups)
			{
				if (!empty($groups))
				{
					foreach ($groups AS $perGroup => $value)
					{
						$query->clear();
						$query->insert('#__permissions');
						$query->set('`permission` = ' . $this->_db->quote($perName));
						$query->set('`value` = ' . $this->_db->quote($value));
						$query->set('`group` = ' . $perGroup);
						$query->set('`assetid` = ' . (int) $this->id);
						$this->_db->setQuery($query);
						$this->_db->execute();
					}
				}
			}
		}
		catch (\Exception $e)
		{
			// Check for a database error.
			if ($e->getMessage())
			{
				$this->_unlock();
				throw new \UnexpectedValueException(sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', get_class($this), $e->getMessage()));
			}
		}

		return true;
	}
}
