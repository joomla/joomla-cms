<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Import library dependencies
require_once(dirname(__FILE__).DS.'extension.php');
jimport('joomla.installer.installer');

/**
 * Installer Manage Model
 *
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class InstallerModelDiscover extends InstallerModel
{
	/**
	 * Extension Type
	 * @var	string
	 */
	var $_type = 'discover';

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
				' FROM #__extensions' .
				' WHERE state = -1' .
				' ORDER BY type, client_id, folder, name';
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$apps =& JApplicationHelper::getClientInfo();

		$numRows = count($rows);
		for($i=0;$i < $numRows; $i++)
		{
			$row =& $rows[$i];
			if(strlen($row->manifest_cache)) {
				$data = unserialize($row->manifest_cache);
				if($data) {
					foreach($data as $key => $value) {
						$row->$key = $value;
					}
				}
			}
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

	function discover() {
		$installer =& JInstaller::getInstance();
		$results = $installer->discover();
		// Get all templates, including discovered ones
		$query = 'SELECT * FROM #__extensions';
		$dbo =& JFactory::getDBO();
		$dbo->setQuery($query);
		$installed = $dbo->loadObjectList('element');
		foreach($results as $result) {
			// check if we have a match on the element
			if(!array_key_exists($result->element, $installed)) {
				// since the element doesn't exist, its definitely new
				$result->store(); // put it into the table
				//echo '<p>Added: <pre>'.print_r($result,1).'</pre></p>';
			} else {
				// TODO: Add extra checks here to add entries that have conflicting elements
				// an element exists that matches this
				//echo '<p>Ignored: '. $result->name .'</p>';
			}
		}
	}

	function discover_install() {
		$installer =& JInstaller::getInstance();
		$eid = JRequest::getVar('eid',0);
		if(is_array($eid) || $eid) {
			if(!is_array($eid)) {
				$eid = Array($eid);
			}
			JArrayHelper::toInteger($eid);
			$app =& JFactory::getApplication();
			$failed = false;
			foreach($eid as $id) {
				$result = $installer->discover_install($id);
				if(!$result) {
					$failed = true;
					$app->enqueueMessage(JText::_('Discover install failed').': '. $id);
				}
			}
			$this->setState('action', 'remove');
			$this->setState('name', $installer->get('name'));
			$this->setState('message', $installer->message);
			$this->setState('extension.message', $installer->get('extension.message'));
			if(!$failed) $app->enqueueMessage(JText::_('Discover install successful'));
		} else {
			$app =& JFactory::getApplication();
			$app->enqueueMessage(JText::_('No extension selected'));
		}
	}

	function purge() {
		$db =& JFactory::getDBO();
		$db->setQuery('DELETE FROM #__extensions WHERE state = -1');
		if($db->Query()) {
			$this->_message = JText::_('Purged discovered extensions');
			return true;
		} else {
			$this->_message = JText::_('Failed to purge extensions');
			return false;
		}
	}
}