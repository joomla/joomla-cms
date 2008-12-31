<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Table object for ACL sections.
 *
 * @package		Joomla.Framework
 * @subpackage	Table
 * @since		1.6
 */
abstract class JTable_AclSection extends JTable
{
	/** @var int Primary key */
	public $id = null;
	/** @var varchar */
	public $value = null;
	/** @var int */
	public $order_value = null;
	/** @var varchar */
	public $name = null;
	/** @var string */
	public $hidden = null;
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
			JError::raiseError(500, 'Error Acl Section Table Invalid type');
		}
		$this->_type = strtolower($this->_type);
		parent::__construct('#__core_acl_'.$this->_type.'_sections', 'id', $db);
	}

	/**
	 * Loads row data by the name field
	 *
	 * @param	string
	 *
	 * @return	boolean
	 */
	function loadByName($name)
	{
		if (empty($name)) {
			$this->setError('Error Acl Section Table Invalid name');
			return false;
		}

		$this->_db->setQuery(
			'SELECT id FROM '.$this->_db->nameQuote($this->_tbl).' WHERE name = '.$this->_db->quote($name)
		);
		if ($id = $this->_db->loadResult()) {
			return $this->load($id);
		}
		return false;
 	}

	/**
	 * Loads row data by the name field
	 *
	 * @param	string
	 *
	 * @return	boolean
	 */
	function loadByValue($value)
	{
		if (empty($value)) {
			$this->setError('Error Acl Section Table Invalid value');
			return false;
		}

		$this->_db->setQuery(
			'SELECT id FROM '.$this->_db->nameQuote($this->_tbl).' WHERE `value` = '.$this->_db->quote($value)
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
			$this->setError(JText::_('Error Acl Section Table Invalid name'));
			return false;
		}

		// Sanitize and validate group value.
		if ($this->value === null || $this->value === '') {
			$this->setError(JText::_('Error Acl Section Table Invalid value'));
			return false;
		}

		// Check for duplicate section value
		$this->_db->setQuery(
			'SELECT id FROM '.$this->_db->nameQuote($this->_tbl).' WHERE value = '.$this->_db->quote($this->value)
		);
		$id = $this->_db->loadResult();
		if (!empty($id) && $id != $this->id) {
			$this->setError(JText::sprintf('Error Acl Section Table Value %s already used', $this->value));
			return false;
		}

		return true;
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
		// Flag if this is a new record
		$isNew	= empty($this->id);
		$reSync	= false;

		if (!$isNew)
		{
			// Load the existing section_value and value to check for a change
			$this->_db->setQuery(
				'SELECT value'.
				' FROM '.$this->_db->nameQuote($this->_tbl).
				' WHERE id = '.(int) $this->id
			);
			$existing = $this->_db->loadResult();
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			if ($this->value != $existing) {
				$reSync = true;
			}
		}

		if ($result = parent::store($updateNulls))
		{
			// Syncronise the section_value and value with foreign keys
			if ($reSync)
			{
				// Update the acl_{type} table
				$this->_db->setQuery(
					'UPDATE '.$this->_db->nameQuote('#__core_acl_'.$this->_type).
					' SET section_value = '.$this->_db->quote($this->value).
					' WHERE section_value = '.$this->_db->quote($existing)
				);
				if (!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}

				// Update the acl_{type}_map table
				$this->_db->setQuery(
					'UPDATE '.$this->_db->nameQuote('#__core_acl_'.$this->_type.'_map').
					' SET section_value = '.$this->_db->quote($this->value).
					' WHERE section_value = '.$this->_db->quote($existing)
				);
				if (!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}

		return $result;
	}

	/**
	 * Delete a record from the database table
	 *
	 * @param	int $id			An optional ID column value. If not supplied, the internal property is used
	 * @param	boolean $erase	True removes all referencing elements to the section
	 *
	 * @return	boolean			True if successful otherwise returns and error message
	 *
	 * @access	public
	 */
	function delete($id = null, $erase = false)
	{
		if (empty($id)) {
			if (empty($this->id)) {
				$this->setError(JText::_('Error Acl Section Table Invalid section Id'));
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

		// Get all object ID's in the section
		$this->_db->setQuery(
			'SELECT id'.
			' FROM '.$this->_db->nameQuote('#__core_acl_'.$this->_type).
			' WHERE section_value = '.$this->_db->quote($this->value)
		);
		$objectIds = $this->_db->loadResultArray();
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if ($erase)
		{
			// Erase all referencing objects

			if ($this->_type == 'acl') {
				$table = &JTable::getInstance('Acl');
			}
			else if ($this->_type == 'aco') {
				$table = &JTable::getInstance('Aco');
			}
			else if ($this->_type == 'aro') {
				$table = &JTable::getInstance('Aro');
			}
			else if ($this->_type == 'axo') {
				$table = &JTable::getInstance('Axo');
			}
			else {
				$this->setError(JText::_('Error Acl Section Table Invalid type'));
				return false;
			}

			foreach ($objectIds as $oId) {
				if (!$table->delete($oId)) {
					$this->setError($table->getError());
					return false;
				}
			}
		}
		else if (!empty($objectIds))
		{
			// Not erasing and there are objects using this section

			$this->setError(JText::_('Error Acl Section Table Section is in use'));
			return false;
		}

		return parent::delete($id);
	}
}
