<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die('Restricted access');

/**
 * @package		Joomla.Framework
 * @subpackage	Table
 */
class JTable_GroupMap extends JTable
{
	var $group_id = null;

	/**
	 * @var	string The section type
	 * @protected
	 */
	var $_type = null;

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
			JError::raiseError(500, 'Error Acl Invalid Map Type');
		}
		$this->_type = strtolower($this->_type);

		parent::__construct('#__core_acl_groups_'.$this->_type.'_map', '', $db);
	}

	function store($groupIds, $mapId = null)
	{
		if (empty($mapId)) {
			$k = $this->_type.'_id';
			if (empty($this->$k)) {
				$this->setError(JText::_('Error Acl Invalid map id'));
				return false;
			}
			else {
				$mapId = $this->$k;
			}
		}
		$mapId = (int) $mapId;

		if (!is_array($groupIds)) {
			$groupIds = array($groupIds);
		}
		else if (empty($groupIds)) {
			$this->setError(JText::_('Error Acl Invalid group ids'));
		}
		JArrayHelper::toInteger($groupIds);

		// Cleanup the group mappings for the 'object'
		$this->_db->setQuery(
			'DELETE FROM '.$this->_tbl
			.' WHERE '.$this->_type.'_id = '.$mapId
		);
		if (!$this->_db->query()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Now remap
		$tuples = array();
		foreach ($groupIds as $id) {
			$tuples[] = '('.$mapId.', '.$id.')';
		}
		$this->_db->setQuery(
			'INSERT INTO '.$this->_tbl.' ('.$this->_type.'_id, group_id)'
			.' VALUES '.implode(',', $tuples)
		);
		if (!$this->_db->query()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}
}
