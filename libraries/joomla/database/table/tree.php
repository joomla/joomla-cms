<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

/**
 * @package		Joomla.Framework
 * @subpackage	Table
 */
abstract class JTableTree extends JTable
{
	/**
	 * @var integer
	 */
	protected $parent_id = null;
	/**
	 * @var integer
	 */
	protected $lft = null;
	/**
	 * @var integer
	 */
	protected $rgt = null;

	/**
	 * Inserts a new row if id is zero or updates an existing row in the database table
	 *
	 * Can be overloaded/supplemented by the child class
	 *
	 * @access public
	 * @param boolean If false, null object variables are not updated
	 * @return null|string null if successful otherwise returns and error message
	 */
	public function store($updateNulls = false)
	{
		if ($result = parent::store($updateNulls)) {
			$result = $this->rebuild();
		}
		return $result;
	}

	/**
	 * Recursive method
	 * @param	int	parent id
	 * @param	int	Left value
	 */
	public function rebuild($parent_id = 0, $left = 1)
	{
		$db = &$this->_db;

		$parent_id = (int) $parent_id;

		// get all children of this node
		$query = 'SELECT id FROM '. $this->_tbl .' WHERE parent_id='. $parent_id;

		$db->setQuery($query);
		try {
			$children = $db->loadResultArray();
		} catch(JException $e) {
			$this->setError($e->getMessage());
			return false;
		}

		// the right value of this node is the left value + 1
		$right = $left + 1;

		$n = count($children);
		foreach ($children as $id) {
			// recursive execution of this function for each
			// child of this node
			// $right is the current right value, which is
			// incremented by the rebuild_tree function
			$right = $this->rebuild($id, $right);

			if ($right === FALSE) {
				return FALSE;
			}
		}

		// we've got the left value, and now that we've processed
		// the children of this node we also know the right value
		$query  = 'UPDATE '. $this->_tbl .' SET lft='. $left .', rgt='. $right .' WHERE id='. $parent_id;

		$db->setQuery($query);
		try {
			$db->query();
		} catch(JException $e) {
			$this->setError($e->getMessage());
			return false;
		}

		// return the right value of this node + 1
		return $right + 1;
	}
}
