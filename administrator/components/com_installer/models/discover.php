<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

// Import library dependencies
require_once dirname(__FILE__).DS.'extension.php';
jimport('joomla.installer.installer');

/**
 * Installer Manage Model
 *
 * @package		Joomla.Administator
 * @subpackage	com_installer
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
	 * Current discovered extension list
	 */
	function _loadItems()
	{
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
			if (strlen($row->manifest_cache)) {
				$data = unserialize($row->manifest_cache);
				if ($data) {
					foreach($data as $key => $value) {
						$row->$key = $value;
					}
				}
			}
			$row->jname = JString::strtolower(str_replace(" ", "_", $row->name));
			if (isset($apps[$row->client_id])) {
				$row->client = ucfirst($apps[$row->client_id]->name);
			} else {
				$row->client = $row->client_id;
			}
		}
		$this->setState('pagination.total', $numRows);
		if ($this->_state->get('pagination.limit') > 0) {
			$this->_items = array_slice($rows, $this->_state->get('pagination.offset'), $this->_state->get('pagination.limit'));
		} else {
			$this->_items = $rows;
		}
	}

	/**
	 * Discover extensions
	 * Finds uninstalled extensions
	 */
	function discover() {
		$installer =& JInstaller::getInstance();
		$results = $installer->discover();
		// Get all templates, including discovered ones
		$query = 'SELECT *,'
				.' CASE WHEN CHAR_LENGTH(folder) THEN CONCAT_WS(":", folder, element) ELSE element END as elementkey'
				.' FROM #__extensions';
		$dbo =& JFactory::getDBO();
		$dbo->setQuery($query);
		$installed = $dbo->loadObjectList('elementkey');
		foreach($results as $result) {
			// check if we have a match on the element
			if ($result->get('type') != 'plugin' && !array_key_exists($result->get('element'), $installed)) {
				// since the element doesn't exist, its definitely new
				$result->store(); // put it into the table
			} elseif($result->get('type') == 'plugin' && !array_key_exists($result->get('folder').':'.$result->get('element'), $installed)) {
				// since the element doesn't exist, its definitely new
				$result->store(); // put it into the table
			} else {
				// TODO: Add extra checks here to add entries that have conflicting elements
				// an element exists that matches this
			}
		}
	}

	/**
	 * Installs a discovered extension
	 */
	function discover_install() {
		$installer =& JInstaller::getInstance();
		$eid = JRequest::getVar('eid',0);
		if (is_array($eid) || $eid) {
			if (!is_array($eid)) {
				$eid = Array($eid);
			}
			JArrayHelper::toInteger($eid);
			$app =& JFactory::getApplication();
			$failed = false;
			foreach($eid as $id) {
				$result = $installer->discover_install($id);
				if (!$result) {
					$failed = true;
					$app->enqueueMessage(JText::_('DISCOVER_INSTALL_FAILED').': '. $id);
				}
			}
			$this->setState('action', 'remove');
			$this->setState('name', $installer->get('name'));
			$this->setState('message', $installer->message);
			$this->setState('extension_message', $installer->get('extension_message'));
			if (!$failed) $app->enqueueMessage(JText::_('DISCOVER_INSTALL_SUCCESSFUL'));
		} else {
			$app =& JFactory::getApplication();
			$app->enqueueMessage(JText::_('No_extension_selected'));
		}
	}

	/**
	 * Cleans out the list of discovered extensions
	 */
	function purge() {
		$db =& JFactory::getDBO();
		$db->setQuery('DELETE FROM #__extensions WHERE state = -1');
		if ($db->Query()) {
			$this->_message = JText::_('PURGED_DISCOVERED_EXTENSIONS');
			return true;
		} else {
			$this->_message = JText::_('FAILED_TO_PURGE_EXTENSIONS');
			return false;
		}
	}
}