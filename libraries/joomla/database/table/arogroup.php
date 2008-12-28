<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

require_once dirname(__FILE__).DS.'_aclgroup.php';

/**
 * Table object for ARO (User) Groups.
 *
 * @package		Joomla.Framework
 * @subpackage	Table
 * @since		1.6
 */
class JTableAroGroup extends JTable_AclGroup
{
	/**
	 * @var	string The group type
	 */
	protected $_type = 'aro';

	/**
	 * Find the references to this AXO
	 *
	 * This method can only operate on a previously loaded object.
	 *
	 * @return	JAclReferences
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
					' FROM #__core_acl_groups_aro_map AS m'.
					' LEFT JOIN #__core_acl_aro AS a ON a.id = m.aro_id'.
					' WHERE group_id = '.(int) $this->id
				);
				$values = $this->_db->loadObjectList();
				if (!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
				if (!empty($result)) {
					foreach ($result as $aro) {
						$this->_references->addAro($aro->section_value, $aro->id);
					}
				}
			}
		}

		return $this->_references;
	}
}
