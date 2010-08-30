<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.database.table');

/**
 * Usergroup table class.
 *
 * @package		Joomla.Framework
 * @subpackage	Database
 * @version		1.0
 */
class JTableUsergroup extends JTable
{
	/**
	 * Constructor
	 *
	 * @param	object	Database object
	 * @return	void
	 * @since	1.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__usergroups', 'id', $db);
	}

	/**
	 * Method to check the current record to save
	 *
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	public function check()
	{
		// Validate the title.
		if ((trim($this->title)) == '') {
			$this->setError(JText::_('JLIB_DATABASE_ERROR_USERGROUP_TITLE'));
			return false;
		}

		// Check for a duplicate title.
		// There is a unique index on the title field in the table.
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(title)')
			->from($this->_tbl)
			->where('title = '.$db->quote(trim($this->title)));
		$db->setQuery($query);

		if ($db->loadResult() > 0) {
			$this->setError(JText::_('JLIB_DATABASE_ERROR_USERGROUP_TITLE_EXISTS'));
			return false;
		}

		return true;
	}

	/**
	 * Method to recursively rebuild the nested set tree.
	 *
	 * @param	integer	The root of the tree to rebuild.
	 * @param	integer	The left id to start with in building the tree.
	 *
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	public function rebuild($parent_id = 0, $left = 0)
	{
		// get the database object
		$db = &$this->_db;

		// get all children of this node
		$db->setQuery(
			'SELECT id FROM '. $this->_tbl .
			' WHERE parent_id='. (int)$parent_id .
			' ORDER BY parent_id, title'
		);
		$children = $db->loadResultArray();

		// the right value of this node is the left value + 1
		$right = $left + 1;

		// execute this function recursively over all children
		for ($i=0,$n=count($children); $i < $n; $i++)
		{
			// $right is the current right value, which is incremented on recursion return
			$right = $this->rebuild($children[$i], $right);

			// if there is an update failure, return false to break out of the recursion
			if ($right === false) {
				return false;
			}
		}

		// we've got the left value, and now that we've processed
		// the children of this node we also know the right value
		$db->setQuery(
			'UPDATE '. $this->_tbl .
			' SET lft='. (int)$left .', rgt='. (int)$right .
			' WHERE id='. (int)$parent_id
		);
		// if there is an update failure, return false to break out of the recursion
		if (!$db->query()) {
			return false;
		}

		// return the right value of this node + 1
		return $right + 1;
	}

	/**
	 * Inserts a new row if id is zero or updates an existing row in the database table
	 *
	 * @param	boolean		$updateNulls	If false, null object variables are not updated
	 *
	 * @return	boolean		True successful, false otherwise and an internal error message is set
	 * @since	1.6
	 */
	function store($updateNulls = false)
	{
		if ($result = parent::store($updateNulls)) {
			// Rebuild the nested set tree.
			$this->rebuild();
		}

		return $result;
	}

	/**
	 * Delete this object and it's dependancies
	 *
	 * @param	int		$oid	The primary key of the user group to delete.
	 *
	 * @return	mixed	Boolean or Exception.
	 * @since	1.6
	 */
	function delete($oid = null)
	{
		$k = $this->_tbl_key;

		if ($oid) {
			$this->load($oid);
		}
		if ($this->id == 0) {
			return new JException(JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}
		if ($this->parent_id == 0) {
			return new JException(JText::_('JLIB_DATABASE_ERROR_DELETE_ROOT_CATEGORIES'));
		}
		if ($this->lft == 0 or $this->rgt == 0) {
			return new JException(JText::_('JLIB_DATABASE_ERROR_DELETE_CATEGORY'));
		}

		$db = $this->getDbo();

		// Select the category ID and it's children
		$db->setQuery(
			'SELECT c.id' .
			' FROM `'.$this->_tbl.'` AS c' .
			' WHERE c.lft >= '.(int) $this->lft.' AND c.rgt <= '.$this->rgt
		);
		$ids = $db->loadResultArray();
		if (empty($ids)) {
			return new JException(JText::_('JLIB_DATABASE_ERROR_DELETE_CATEGORY'));
		}

		// Delete the category dependancies
		// @todo Remove all related threads, posts and subscriptions

		// Delete the category and it's children
		$db->setQuery(
			'DELETE FROM `'.$this->_tbl.'`' .
			' WHERE id IN ('.implode(',', $ids).')'
		);
		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Delete the usergroup in view levels
		$replace = array();
		foreach ($ids as $id)
		{
			$replace []= ','.$db->quote("[$id,").','.$db->quote("[").')';
			$replace []= ','.$db->quote(",$id,").','.$db->quote(",").')';
			$replace []= ','.$db->quote(",$id]").','.$db->quote("]").')';
			$replace []= ','.$db->quote("[$id]").','.$db->quote("[]").')';
		}

		$query = $db->getQuery(true);
		$query->set('rules='.str_repeat('replace(',4*count($ids)).'rules'.implode('',$replace));
		$query->update('#__viewlevels');
		$query->where('rules REGEXP "(,|\\\\[)('.implode('|', $ids).')(,|\\\\])"');
		$db->setQuery($query);
		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Delete the user to usergroup mappings for the group(s) from the database.
		$db->setQuery(
			'DELETE FROM `#__user_usergroup_map`' .
			' WHERE `group_id` IN ('.implode(',', $ids).')'
		);
		$db->query();

		// Check for a database error.
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		return true;
	}
}