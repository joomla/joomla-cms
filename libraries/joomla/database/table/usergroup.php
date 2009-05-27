<?php
/**
 * @version		$Id: usergroup.php 298 2009-05-27 05:52:18Z andrew.eddie $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
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
	 * @var int unsigned
	 */
	var $id;

	/**
	 * @var int unsigned
	 */
	var $parent_id;

	/**
	 * @var int unsigned
	 */
	var $left_id;

	/**
	 * @var int unsigned
	 */
	var $right_id;

	/**
	 * @var varchar
	 */
	var $title;

	/**
	 * @var int unsigned
	 */
	var $section_id;

	/**
	 * @var varchar
	 */
	var $section;

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	object	Database object
	 * @return	void
	 * @since	1.0
	 */
	function __construct(&$db)
	{
		parent::__construct('#__usergroups', 'id', $db);
	}

	/**
	 * Method to check the current record to save
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.0
	 */
	function check()
	{
		// Validate the title.
		if ((trim($this->title)) == '') {
			$this->setError(JText::_('Usergroup must have a title'));
			return false;
		}

		// Validate the section.
		if (empty($this->section_id)) {
			$this->setError(JText::_('Usergroup must have a section'));
			return false;
		}

		return true;
	}

	/**
	 * Method to recursively rebuild the nested set tree.
	 *
	 * @access	public
	 * @param	integer	The root of the tree to rebuild.
	 * @param	integer	The left id to start with in building the tree.
	 * @return	boolean	True on success
	 * @since	1.0
	 */
	function rebuild($parent_id = 0, $left = 0)
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
			' SET left_id='. (int)$left .', right_id='. (int)$right .
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
	 * @access	public
	 * @param	boolean		If false, null object variables are not updated
	 * @return	boolean 	True successful, false otherwise and an internal error message is set`
	 */
	function store($updateNulls = false)
	{
		if ($result = parent::store($updateNulls))
		{
			// Rebuild the nested set tree.
			$this->rebuild();
		}

		return $result;
	}

	/**
	 * Delete this object and it's dependancies
	 */
	function delete($oid = null)
	{
		$k = $this->_tbl_key;

		if ($oid) {
			$this->load($oid);
		}
		if ($this->id == 0) {
			return new JException(JText::_('Category not found'));
		}
		if ($this->parent_id == 0) {
			return new JException(JText::_('Root categories cannot be deleted'));
		}
		if ($this->left_id == 0 or $this->right_id == 0) {
			return new JException(JText::_('Left-Right data inconsistency. Cannot delete category.'));
		}

		$db = &$this->getDbo();

		// Select the category ID and it's children
		$db->setQuery(
			'SELECT c.id' .
			' FROM `'.$this->_tbl.'` AS c' .
			' WHERE c.left_id >= '.(int) $this->left_id.' AND c.right_id <= '.$this->right_id
		);
		$ids = $db->loadResultArray();
		if (empty($ids)) {
			return new JException(JText::_('Left-Right data inconsistency. Cannot delete category.'));
		}
		$ids = implode(',', $ids);

		// Delete the category dependancies
		// @todo Remove all related threads, posts and subscriptions

		// Delete the category and it's children
		$db->setQuery(
			'DELETE FROM `'.$this->_tbl.'`' .
			' WHERE id IN ('.$ids.')'
		);
		if (!$db->query()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Delete the user to usergroup mappings for the group(s) from the database.
		$db->setQuery(
			'DELETE FROM `#__user_usergroup_map`' .
			' WHERE `group_id` IN ('.$ids.')'
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
