<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Table object for Acl Groups.
 *
 * @package		Joomla.Framework
 * @subpackage	Table
 * @since		1.6
 */
abstract class JTable_AclGroup extends JTable
{
	/**
	 * @var int Primary key
	 */
	public $id = null;
	/**
	 * @var varchar
	 */
	public $name = null;
	/** @var varchar */
	public $value = null;
	/** @var int */
	public $parent_id = null;
	/** @var int */
	public $lft = null;
	/** @var int */
	public $rgt = null;
	/** @var int */
	public $section_id = null;

	/**
	 * @var	string The section type
	 * @protected
	 */
	protected $_type = null;

	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	JDatabase	$db
	 * @param	string		Optional set the type by hand
	 * @return	void
	 * @since	1.0
	 */
	function __construct(&$db, $type = '')
	{
		if ($type) {
			$this->_type = $type;
		}
		if (empty($this->_type)) {
			// Fatal Error
			JError::raiseError(500, 'Error Acl Group Table Invalid type');
		}
		parent::__construct('#__core_acl_'.$this->_type.'_groups', 'id', $db);
	}

	/**
	 * Load an object by mathcing the `name` field
	 *
	 * @param	string $name	The name of the group
	 * @param	int $sectionId	The section id of the group
	 *
	 * @return	boolean			True if successful, false if not found
	 */
	function loadByName($name, $sectionId)
	{
		$this->_db->setQuery(
			'SELECT id'
			.' FROM '.$this->_tbl
			.' WHERE `name` = '.$this->_db->quote($name)
			.($sectionId ? ' AND section_id = '.(int) $sectionId : '')
		);
		if ($id = $this->_db->loadResult()) {
			$this->load($id);
			return true;
		}
		return false;
	}


	/**
	 * Load an object by mathcing the `value` field
	 *
	 * @param	string $value
	 *
	 * @return	boolean			True if successful, false if not found
	 */
	function loadByValue($value)
	{
		$this->_db->setQuery(
			'SELECT id'
			.' FROM '.$this->_tbl
			.' WHERE `value` = '.$this->_db->quote($value)
		);
		if ($id = $this->_db->loadResult()) {
			return $this->load($id);
		}
		return false;
	}

	/**
	 * Validate the internal data
	 *
	 * @return	boolean
	 */
	function check()
	{
		// Sanitize and validate group name.
		if (empty($this->name)) {
			$this->setError(JText::_('Error Acl Group Table Invalid name'));
			return false;
		}

		// Sanitize and validate group value.
		if ($this->value === null || $this->value === '') {
			$this->setError(JText::_('Error Acl Group Table Invalid value'));
			return false;
		}

		return true;
	}

	/**
	 * Inserts a new row if id is zero or updates an existing row in the database table
	 *
	 * Can be overloaded/supplemented by the child class
	 *
	 * @access public
	 * @param boolean If false, null object variables are not updated
	 * @return null|string null if successful otherwise returns and error message
	 */
	function store($updateNulls = false)
	{
		if ($result = parent::store($updateNulls)) {
			$result = $this->rebuild();
		}
		return $result;
	}

	/**
	 * Recursive method to rebuild the nested set right and left values.
	 *
	 * @access	public
	 * @param	int	parent id
	 * @param	int	Left value
	 * @return	int	Right value + 1
	 * @since	1.0
	 */
	function rebuild($parentId = 0, $left = 1)
	{
		// get the database object
		$db = &$this->_db;

		// get all children of this node
		$db->setQuery(
			'SELECT id FROM '. $this->_tbl .
			' WHERE parent_id='. (int) $parentId .
			' ORDER BY parent_id, name'
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
			' SET lft='. (int) $left .', rgt='. (int) $right .
			' WHERE id='. (int) $parentId
		);
		// if there is an update failure, return false to break out of the recursion
		if (!$db->query()) {
			return false;
		}

		// return the right value of this node + 1
		return $right + 1;
	}

	/**
	 * Delete a record from the database table
	 *
	 * @param	int $id			An optional ID column value. If not supplied, the internal property is used
	 *
	 * @return	boolean			True if successful, false if an error occured
	 *
	 * @access	public
	 */
	function delete($id = null)
	{
		if (empty($id)) {
			if (empty($this->id)) {
				$this->setError(JText::_('Error Acl Group Table Invalid '.$this->_type.' Id'));
				return false;
			}
			else {
				$id = $this->id;
			}
		}

		// Load the existing data
		if (!$this->load($id)) {
			return false;
		}

		// Check for children
		$this->_db->setQuery(
			'SELECT COUNT(id)'.
			' FROM '.$this->_db->nameQuote('#__core_acl_'.$this->_type.'_groups').
			' WHERE parent_id = '.(int) $this->id
		);
		if ($count = $this->_db->loadResult()) {
			$this->setError(JText::_('Error Acl Group Table '.$this->_type.' group has children'));
			return false;
		}
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// If we survived all that, delete the actual object
		return parent::delete($id);
	}

	/**
	 * Find the references to this object
	 *
	 * This method can only operate on a previously loaded object.
	 *
	 * @return	JAclReferences
	 * @access	public
	 */
	function &findReferences()
	{
		if (empty($this->id) || empty($this->section_value) || empty($this->value)) {
			$this->setError(JText::_('Error Acl Group Table Invalid properties to find references'));
			return false;
		}

		$false = false;

		if (empty($this->_references))
		{
			require_once JPATH_LIBRARIES.DS.'joomla'.DS.'acl'.DS.'aclreferences.php';

			$this->_references = new JAclReferences;

			// Find the references to ACLs
			$this->_db->setQuery(
				'SELET a.id, a.section_value'.
				' FROM '.$this->_db->nameQuote('#__core_acl_'.$this->_type.'_groups_map').' AS m'.
				' LEFT JOIN #__core_acl_acl AS a ON a.id = m.acl_id'.
				' WHERE group_id = '.(int) $this->id
			);
			$result = $this->_db->loadObjectList();
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return $false;
			}

			if (!empty($result)) {
				foreach ($result as $acl) {
					$this->_references->addAcl($acl->section_value, $acl->id);
				}
			}
		}

		return $this->_references;
	}

	/**
	 * Deletes referenced data
	 *
	 * This method can only operate on a previously loaded object.
	 * Delete the usage of the group in ACLs and also removes objects from the group
	 *
	 * @return	boolean
	 * @access	protected
	 */
	function _deleteReferences()
	{
		if (empty($this->id) || empty($this->section_value) || empty($this->value)) {
			$this->setError(JText::_('Error Acl Group Table Invalid properties to find references'));
			return false;
		}

		// Erase all acl_{type}_groups_map references (these are ACLs using this object)
		$this->_db->setQuery(
			'DELETE FROM '.$this->_db->nameQuote('#__core_acl_'.$this->_type.'_groups_map').
			' WHERE group_id = '.(int) $this->id
		);
		if (!$this->_db->query()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Erase all acl_groups_{type}_map references (these are objects in this object)
		$this->_db->setQuery(
			'DELETE FROM '.$this->_db->nameQuote('#__core_acl_groups_'.$this->_type.'_map').
			' WHERE group_id = '.(int) $this->id
		);
		if (!$this->_db->query()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}
}
