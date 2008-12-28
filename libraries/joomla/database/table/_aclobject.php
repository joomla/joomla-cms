<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Table object for access control objects: ACO, ARO and AXO.
 *
 * @package		Joomla.Framework
 * @subpackage	Table
 * @since		1.6
 */
abstract class JTable_AclObject extends JTable
{
	/** @var int Primary key */
	public $id = null;
	/** @var int */
	public $section_value = null;
	/** @var varchar */
	public $value = null;
	/** @var int */
	public $order_value = null;
	/** @var varchar */
	public $name = null;
	/** @var int */
	public $hidden = null;
	/**
	 * @var	string The section type
	 * @protected
	 */
	protected $_type = null;
	/**
	 * @var	JAclReferences Any references to the current object
	 * @protected
	 */
	protected $_references = null;

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
			JError::raiseError(500, 'Error Acl Object Table Invalid type');
		}
		parent::__construct('#__core_acl_'.$this->_type, 'id', $db);
	}

	/**
	 * Load an object by mathcing the `name` field
	 *
	 * @param	string $name
	 * @param	string $section	Optional section to match
	 *
	 * @return	boolean			True if successful, false if not found
	 */
	function loadByName($name, $section = null)
	{
		if (empty($name)) {
			$this->setError('Error Acl Invalid object name');
			return false;
		}

		$this->_db->setQuery(
			'SELECT id FROM '.$this->_db->nameQuote($this->_tbl)
			.' WHERE `name` = '.$this->_db->quote($name)
			.($section ? ' AND `section_value` = '.$this->_db->quote($section) : '')
		);
		if ($id = $this->_db->loadResult()) {
			return $this->load($id);
		}
		return false;
 	}

	/**
	 * Load an object by mathcing the `value` field
	 *
	 * @param	string $value
	 * @param	string $section	Optional section to match
	 *
	 * @return	boolean			True if successful, false if not found
	 */
	function loadByValue($value, $section = null)
	{
		$this->_db->setQuery(
			'SELECT id FROM '.$this->_db->nameQuote($this->_tbl)
			.' WHERE `value` = '.$this->_db->quote($value)
			.($section ? ' AND `section_value` = '.$this->_db->quote($section) : '')
		);
		if ($id = $this->_db->loadResult()) {
			return $this->load($id);
		}
		return false;
 	}

	/**
	 * Clears the properties
	 *
	 * @return	void
	 */
 	function clear()
 	{
		foreach ($this->getProperties() as $name => $value)
		{
			$this->$name = null;
		}
 		$this->_references = null;
 		$this->_errors = array();
 	}

	/**
	 * Validate the internal data
	 *
	 * @return	boolean
	 */
	function check()
	{
		// Sanitize and validate group name.
		if (empty($this->section_value)) {
			$this->setError(JText::_('Error Acl Object Table invalid '.strtoupper($this->_type).' section value'));
			return false;
		}

		if (empty($this->name)) {
			$this->setError(JText::_('Error Acl Object Table invalid '.strtoupper($this->_type).' name'));
			return false;
		}

		// Sanitize and validate group value.
		if ($this->value === null || $this->value === '') {
			$this->setError(JText::_('Error Acl Object Table invalid '.strtoupper($this->_type).' value'));
			return false;
		}

		// Check that section exists
		$this->_db->setQuery(
			'SELECT id FROM #__core_acl_'.$this->_type.'_sections WHERE value = '.$this->_db->quote($this->section_value)
		);
		$id = $this->_db->loadResult();
		if (empty($id)) {
			$this->setError(JText::_('Error Acl Object Table %s section %s not found', strtoupper($this->_type), $this->section_value));
			return false;
		}

		// Check for duplicate Object value
		$this->_db->setQuery(
			'SELECT id FROM '.$this->_db->nameQuote($this->_tbl)
			.' WHERE value = '.$this->_db->quote($this->value)
			.'  AND section_value = '.$this->_db->quote($this->section_value)
		);
		$id = $this->_db->loadResult();
		if (!empty($id) && $id != $this->id) {
			$this->setError(JText::sprintf('Error Acl Object Table %s value %s:%s already used', strtoupper($this->_type), $this->section_value, $this->value));
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
				'SELECT CONCAT_WS("/", section_value, value)'.
				' FROM '.$this->_db->nameQuote($this->_tbl).
				' WHERE id = '.(int) $this->id
			);
			$existing = $this->_db->loadResult();
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			if ($this->section_value.'/'.$this->value != $existing) {
				$reSync = true;
			}
		}

		if ($result = parent::store($updateNulls))
		{
			// Syncronise the section_value and value with foreign keys
			if ($reSync)
			{
				$parts = explode('/', $existing);

				// Update the acl_{type}_map table
				$this->_db->setQuery(
					'UPDATE '.$this->_db->nameQuote('#__core_acl_'.$this->_type.'_map').
					' SET section_value = '.$this->_db->quote($this->section_value).
					' , value ='.$this->_db->quote($this->value).
					' WHERE section_value = '.$this->_db->quote($parts[0]).
					'  AND value ='.$this->_db->quote($parts[1])
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
				$this->setError(JText::_('Error Acl Object Table invalid '.$this->_type.' Id'));
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

		// Load the references for the object
		$references = $this->findReferences();
		if ($references === false) {
			return false;
		}

		if ($erase)
		{
			// Erase all referenced data
			if (!$this->_deleteReferences()) {
				return false;
			}
		}
		else {
			// Check for group references
			if (!$references->isEmpty()) {
				$this->setError(JText::_('Error Acl Object Table '.$this->_type.' used in group'));
				return false;
			}
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
			$this->setError(JText::_('Error Acl Object Table Invalid properties to find references'));
			return false;
		}

		$false = false;

		if (empty($this->_references))
		{
			require_once JPATH_LIBRARIES.DS.'joomla'.DS.'acl'.DS.'aclreferences.php';

			$this->_references = new JAclReferences;

			// Find the references to ACLs
			$this->_db->setQuery(
				'SELET acl_id'.
				' FROM '.$this->_db->nameQuote('#__core_acl_'.$this->_type.'_map').
				' WHERE section_value = '.$this->_db->quote($this->section_value).
				'  AND value = '.$this->_db->quote($this->value)
			);
			$result = $this->_db->loadResultArray();
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return $false;
			}

			$this->_references->addAcls($this->section_value, $result);
		}

		return $this->_references;
	}

	/**
	 * Deletes referenced data
	 *
	 * This method can only operate on a previously loaded object.
	 * Grouped object must extend this method to also clean up groups
	 *
	 * @return	boolean
	 * @access	protected
	 */
	protected function _deleteReferences()
	{
		if (empty($this->id) || empty($this->section_value) || empty($this->value)) {
			$this->setError(JText::_('Error Acl Object Table Invalid properties to find references'));
			return false;
		}

		// Erase all acl_{type}_map references (these are ACLs using this object)
		$this->_db->setQuery(
			'DELETE FROM '.$this->_db->nameQuote('#__core_acl_'.$this->_type.'_map').
			' WHERE section_value = '.$this->_db->quote($this->section_value).
			'  AND value = '.$this->_db->quote($this->value)
		);
		if (!$this->_db->query()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}
}
