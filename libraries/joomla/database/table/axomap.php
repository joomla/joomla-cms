<?php
/**
 * @version		$Id:article.php 1840 2007-09-21 01:09:04Z masterchief $
 * @package		Magazine
 * @copyright	2008 JXtended LLC.  All rights reserved.
 * @license		GNU General Public License
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Abstract table used for object that map to the access control system
 *
 * @since	1.6
 */
abstract class JTableAxoMap extends JTable
{
	/**
	 * Required property.  Maps to a value in the AXO Groups Table.
	 *
	 * @var		int
	 */
	protected $access = 0;

	/**
	 * Abstract method to return the title of the object to insert into the AXO table
	 *
	 * @return	string
	 */
	protected abstract function getAxoSection();

	/**
	 * Abstract method to return the section of the object to insert into the AXO table
	 *
	 * @return	string
	 */
	protected abstract function getAxoTitle();

	/**
	 * Stores the record, adds/updates the AXO Table and maps it to the appropriate AXO Group
	 *
	 * @param	boolean		Update null values in the object
	 *
	 * @return	boolean
	 */
	function store($updateNulls = false)
	{
		// Control ACL Mode - update the AXO
		$acl		= &JFactory::getACL();
		$k		= $this->_tbl_key;
		$key		= $this->$k;
		$groupId	= $acl->get_group_id($this->access, '', 'AXO');
		$axoTitle	= $this->getAxoTitle();
		$axoSection	= $this->getAxoSection();
	
		try {
			if ($key)
			{
				// existing record
				if ($ret = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_key, $updateNulls)) {
					// consistency check
					$axoId	= $acl->get_object_id($axoSection, $key, 'AXO');
					if (!$axoId) {
						$axoId	= $acl->add_object($axoSection, $axoTitle, $key, null, null, 'AXO');
					} else {
						// update the AXO object
						$ret	= $acl->edit_object($axoId, $axoSection, $axoTitle, $key, 0, 0, 'AXO');
					}
	
					// syncronise ACL - single group handled at the moment
					if ($groups = $acl->get_object_groups($axoId, 'AXO')) {
						$acl->del_group_object($groups[0], $axoSection, $key, 'AXO');
					}
					$acl->add_group_object($groupId, $axoSection, $key, 'AXO');
				}
			}
			else {
				// new record
				if ($ret = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_key)) {
					$key = $this->$k;
					// syncronise ACL
					$acl->add_object($axoSection, $axoTitle, $key, null, null, 'AXO');
					$acl->add_group_object($groupId, $axoSection, $key, 'AXO');
				}
			}
		} catch(JException $e) {
			$this->setError(get_class($this).'::'. JText::_('store failed') .'<br />' . $e->getMessage());
			return false;
		}
		return true;
	}
}
