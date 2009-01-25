<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__).DS.'_aclobject.php';

/**
 * Table object for ACOs.
 *
 * @package		Joomla.Framework
 * @subpackage	Table
 * @since		1.6
 */
class JTableAco extends JTable_AclObject
{
	/** @var int */
	protected $acl_type = null;
	/** @var int */
	public $note = null;
	/**
	 * @var	string The object type
	 */
	protected $_type = 'aco';

	/**
	 * Deletes referenced data
	 *
	 * This method can only operate on a previously loaded object.
	 * Deletes the mapped groups and then hands off to the parent class
	 *
	 * @return	boolean
	 * @access	protected
	 */
	function _deleteReferences()
	{
		if (empty($this->id) || empty($this->section_value) || empty($this->value)) {
			$this->setError(JText::_('Error Acl Table Invalid properties to find references'));
			return false;
		}

		// Load the references for the object
		$references = $this->findReferences();
		if ($references === false) {
			return false;
		}

		$aclIds = $references->getAcls();

		if (!empty($aclIds))
		{
			// See if the ACL is orphaned, if so delete it
			$this->_db->setQuery(
				'SELECT acl_id'.
				' FROM '.$this->_db->nameQuote('#__core_acl_aco_map').
				' WHERE acl_id IN ('.implode(',', $aclIds).')'
			);
			$keepAclIds = $this->_db->loadResultArray();
			if ($this->_db->getErrorNum()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			// Now we diff the arrays to see which one are orphaned
			$orphanAclIds = array_diff($aclIds, $keepAclIds);
			if (!empty($orphanAclIds))
			{
				$aclTable = &JTable::getInstance('Acl');
				foreach ($orphanAclIds AS $aclId)
				{
					if (!$aclTable->delete($aclId, true)) {
						$this->setError($this->_db->getErrorMsg());
						return false;
					}
				}
			}
		}

		return parent::_deleteReferences();
	}
}
