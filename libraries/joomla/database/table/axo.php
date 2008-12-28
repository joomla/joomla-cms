<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__).DS.'_aclobject.php';

/**
 * Table object for AXOs.
 *
 * @package 	Joomla.Framework
 * @subpackage	Table
 * @since		1.0
 */
class JTableAxo extends JTable_AclObject
{
	/**
	 * @var	string The object type
	 * @protected
	 */
	protected $_type = 'axo';

	/**
	 * Find the references to this AXO
	 *
	 * This method can only operate on a previously loaded object.
	 *
	 * @return	JAclReferences
	 * @access	public
	 */
	public function &findReferences()
	{
		if (empty($this->_references))
		{
			// Allow the parent method to run first, do validation checks and set up the reference object
			if (parent::findReferences() !== false)
			{
				$this->_db->setQuery(
					'SELECT group_id'.
					' FROM '.$this->_db->nameQuote('#__core_acl_groups_'.$this->_type.'_map').
					' WHERE '.$this->_db->nameQuote($this->_type.'_id').' = '.(int) $this->id
				);
				$values = $this->_db->loadResultArray();
				if (!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
				if (!empty($values)) {
					$this->_references->addAroGroup($values);
				}
			}
		}

		return $this->_references;
	}

	/**
	 * Deletes referenced data
	 *
	 * This method can only operate on a previously loaded object.
	 * Deletes the mapped groups and then hands off to the parent class
	 *
	 * @return	boolean
	 */
	protected function _deleteReferences()
	{
		if (empty($this->id) || empty($this->section_value) || empty($this->value)) {
			$this->setError(JText::_('Error Acl Table Invalid properties to find references'));
			return false;
		}

		// Remove from the acl_groups_{type}_map table
		$this->_db->setQuery(
			'DELETE FROM '.$this->_db->nameQuote('#__core_acl_groups_'.$this->_type.'_map').
			' WHERE '.$this->_db->nameQuote($this->_type.'_id').' = '.(int) $this->id
		);
		if (!$this->_db->query()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return parent::_deleteReferences();
	}
}
