<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Import library dependencies
require_once(dirname(__FILE__).DS.'extension.php');
jimport('joomla.installer.installer');
jimport('joomla.updater.updater');
jimport('joomla.updater.update');

/**
 * Installer Manage Model
 *
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class InstallerModelUpdate extends InstallerModel
{
	/**
	 * Extension Type
	 * @var	string
	 */
	var $_type = 'update';
	
	var $_message = '';
	
	/**
	 * Current extension list
	 */

	function _loadItems()
	{
		global $mainframe, $option;

		jimport('joomla.filesystem.folder');

		/* Get a database connector */
		$db =& JFactory::getDBO();

		$query = 'SELECT *' .
				' FROM #__updates' .
				//' WHERE extensionid != 0' . // we only want actual updates
				' ORDER BY type, client_id, folder, name';
		$db->setQuery($query);
		try {
			$rows = $db->loadObjectList();
		} catch (JException $e) {
			JError::raiseWarning(-1, $e->getMessage());
			return false;
		}
		
		$apps =& JApplicationHelper::getClientInfo();
		
		$numRows = count($rows);
		for($i=0;$i < $numRows; $i++)
		{
			$row =& $rows[$i];
			$row->jname = JString::strtolower(str_replace(" ", "_", $row->name));
			if(isset($apps[$row->client_id])) {
				$row->client = ucfirst($apps[$row->client_id]->name);
			} else {
				$row->client = $row->client_id;
			}
		}
		$this->setState('pagination.total', $numRows);
		if($this->_state->get('pagination.limit') > 0) {
			$this->_items = array_slice( $rows, $this->_state->get('pagination.offset'), $this->_state->get('pagination.limit') );
		} else {
			$this->_items = $rows;
		}
	}
	
	function findUpdates($eid=0) {
		$updater =& JUpdater::getInstance();
		$results = $updater->findUpdates($eid);
		return true;
	}
	
	function purge() {
		$db =& JFactory::getDBO();
		$db->setQuery('TRUNCATE TABLE #__updates');
		if($db->Query()) {
			$this->_message = JText::_('Purged updates');
			return true;
		} else {
			$this->_message = JText::_('Failed to purge updates');
			return false;
		}
	}
	
	function update($uids) {
		foreach($uids as $uid) {
			$update = new JUpdate();
			$instance =& JTable::getInstance('update');
			$instance->load($uid);
			$update->loadFromXML($instance->detailsurl);
			$update->install();
		}
	}
}