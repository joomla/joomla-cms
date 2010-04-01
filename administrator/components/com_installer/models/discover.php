<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

// Import library dependencies
jimport('joomla.application.component.modellist');
jimport('joomla.installer.installer');

/**
 * Installer Manage Model
 *
 * @package		Joomla.Administator
 * @subpackage	com_installer
 * @since		1.5
 */
class InstallerModelDiscover extends JModelList
{
	protected $_context = 'com_installer.discover';

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 */
	protected function _populateState() {
		$app = JFactory::getApplication();
		$this->setState('message',$app->getUserState('com_installer.message'));
		$this->setState('extension_message',$app->getUserState('com_installer.extension_message'));
		$app->setUserState('com_installer.message','');
		$app->setUserState('com_installer.extension_message','');
		parent::_populateState('name','asc');
	}

	/**
	 * Returns an object list
	 *
	 * @param	string The query
	 * @param	int Offset
	 * @param	int The number of records
	 * @return	array
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0) {
		$this->_db->setQuery($query);
		$result = $this->_db->loadObjectList();
		$lang = JFactory::getLanguage();
		foreach($result as $i => $row) {
			if (strlen($row->manifest_cache)) {
				$data = unserialize($row->manifest_cache);
				if ($data) {
					foreach($data as $key => $value) {
						if ($key == 'type') {
							// ignore the type field
							continue;
						}
						$row->$key = $value;
					}
				}
			}
			$path = $row->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE;
			switch ($row->type) {
				case 'component':
					$extension = $row->element;
					$source = JPATH_ADMINISTRATOR . '/components/' . $row->name;
						$lang->load("$extension.sys", JPATH_ADMINISTRATOR, null, false, false)
					||	$lang->load("$extension.sys", $source, null, false, false)
					||	$lang->load("$extension.sys", JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
					||	$lang->load("$extension.sys", $source, $lang->getDefault(), false, false);
				break;
				case 'library':
					$extension = 'lib_' . $row->element;
						$lang->load("$extension.sys", JPATH_SITE, null, false, false)
					||	$lang->load("$extension.sys", JPATH_SITE, $lang->getDefault(), false, false);
				break;
				case 'module':
					$extension = $row->element;
					$source = $path . '/modules/' . $row->name;
						$lang->load("$extension.sys", $path, null, false, false)
					||	$lang->load("$extension.sys", $source, null, false, false)
					||	$lang->load("$extension.sys", $path, $lang->getDefault(), false, false)
					||	$lang->load("$extension.sys", $source, $lang->getDefault(), false, false);
				break;
				case 'package':
					$extension = 'pkg_' . $row->element;
						$lang->load("$extension.sys", JPATH_SITE, null, false, false)
					||	$lang->load("$extension.sys", JPATH_SITE, $lang->getDefault(), false, false);
				break;
				case 'plugin':
					$extension = 'plg_' . $row->folder . '_' . $row->element;
					$source = JPATH_PLUGINS . '/' . $row->folder . '/' . $row->element;
						$lang->load("$extension.sys", JPATH_ADMINISTRATOR, null, false, false)
					||	$lang->load("$extension.sys", $source, null, false, false)
					||	$lang->load("$extension.sys", JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
					||	$lang->load("$extension.sys", $source, $lang->getDefault(), false, false);
				break;
				case 'template':
					$extension = 'tpl_' . $row->name;
					$source = $path . '/templates/' . $row->name;
						$lang->load("$extension.sys", $path, null, false, false)
					||	$lang->load("$extension.sys", $source, null, false, false)
					||	$lang->load("$extension.sys", $path, $lang->getDefault(), false, false)
					||	$lang->load("$extension.sys", $source, $lang->getDefault(), false, false);
				break;
			}
			$row->name = JText::_($row->name);
			$row->description = JText::_($row->description);
			$row->author_info = @$row->authorEmail .'<br />'. @$row->authorUrl;
			$row->client = $row->client_id ? JText::_('JADMINISTRATOR') : JText::_('JSITE');
		}
		JArrayHelper::sortObjects($result, $this->getState('list.ordering'), $this->getState('list.direction') == 'desc' ? -1 : 1);
		$total = count($result);
		$store = $this->_getStoreId('getTotal');
		$this->_cache[$store] = $total;
		if ($total < $limitstart) {
			$limitstart = 0;
			$this->setState('list.start', 0);
		}
		if ($limit > 0) {
			$result = array_slice($result, $limitstart, $limit);
		}
		return $result;
	}
	/**
	 * Method to get the database query
	 *
	 * @return JDatabaseQuery the database query
	 */
	protected function _getListQuery() {
		$query = new JDatabaseQuery;
		$query->select('*');
		$query->from('#__extensions');
		$query->where('state=-1');
		$query->order('protected');
		$query->order('type');
		$query->order('client_id');
		$query->order('folder');
		$query->order('name');
		return $query;
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
		$app = JFactory::getApplication();
		$installer =& JInstaller::getInstance();
		$eid = JRequest::getVar('cid',0);
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
					$app->enqueueMessage(JText::_('COM_INSTALLER_MSG_DISCOVER_INSTALLFAILED').': '. $id);
				}
			}
			$this->setState('action', 'remove');
			$this->setState('name', $installer->get('name'));
			$app->setUserState('com_installer.message', $installer->message);
			$app->setUserState('com_installer.extension_message', $installer->get('extension_message'));
			if (!$failed) $app->enqueueMessage(JText::_('COM_INSTALLER_MSG_DISCOVER_INSTALLSUCCESSFUL'));
		} else {
			$app->enqueueMessage(JText::_('COM_INSTALLER_MSG_DISCOVER_NOEXTENSIONSELECTED'));
		}
	}

	/**
	 * Cleans out the list of discovered extensions
	 */
	function purge() {
		$db =& JFactory::getDBO();
		$query = new JDatabaseQuery;
		$query->delete();
		$query->from('#__extensions');
		$query->where('state = -1');
		$db->setQuery((string)$query);
		if ($db->Query()) {
			$this->_message = JText::_('COM_INSTALLER_MSG_DISCOVER_PURGEDDISCOVEREDEXTENSIONS');
			return true;
		} else {
			$this->_message = JText::_('COM_INSTALLER_MSG_DISCOVER_FAILEDTOPURGEEXTENSIONS');
			return false;
		}
	}
}