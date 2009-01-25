<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__).DS.'_aclgroup.php';

/**
 * Table object for ARO (User) Groups.
 *
 * @package		Joomla.Framework
 * @subpackage	Table
 * @version		1.0
 */
class JTableAxoGroup extends JTable_AclGroup
{
	/**
	 * @var	string The group type
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
					'SELET a.id, a.section_value'.
					' FROM '.$this->_db->nameQuote('#__core_acl_groups_'.$this->_type.'_map').' AS m'.
					' LEFT JOIN #__core_acl_axo AS a ON a.id = m.axo_id'.
					' WHERE group_id = '.(int) $this->id
				);
				$values = $this->_db->loadObjectList();
				if (!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
				if (!empty($result)) {
					foreach ($result as $axo) {
						$this->_references->addAxo($axo->section_value, $axo->id);
					}
				}
			}
		}

		return $this->_references;
	}
}
