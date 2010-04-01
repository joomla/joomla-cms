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

/**
 * Installer Manage Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @since		1.5
 */
class InstallerModelManage extends JModelList {
	protected $_context = 'com_installer.manage';

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 */
	protected function _populateState() {
		$app = JFactory::getApplication('administrator');
		$this->setState('message',$app->getUserState('com_installer.message'));
		$this->setState('extension_message',$app->getUserState('com_installer.extension_message'));
		$app->setUserState('com_installer.message','');
		$app->setUserState('com_installer.extension_message','');
		$data = JRequest::getVar('filters');
		if (empty($data)) {
			$data = $app->getUserState('com_installer.manage.data');
		}
		else {
			$app->setUserState('com_installer.manage.data', $data);
		}
		$this->setState('filter.search', isset($data['search']['expr']) ? $data['search']['expr'] : '');
		$this->setState('filter.hideprotected', isset($data['search']['hideprotected']) ? $data['search']['hideprotected'] : 0);
		$this->setState('filter.type', isset($data['select']['type']) ? $data['select']['type'] : '');
		$this->setState('filter.group', isset($data['select']['group']) ? $data['select']['group'] : '');
		$this->setState('filter.client', isset($data['select']['client']) ? $data['select']['client'] : '');
		parent::_populateState('name', 'asc');
	}

	/**
	 * Enable/Disable an extension
	 *
	 * @static
	 * @return boolean True on success
	 * @since 1.0
	 */
	function publish($eid = array(), $value = 1) {

		// Initialise variables.
		$user = JFactory::getUser();
		if ($user->authorise('core.edit.state', 'com_installer')) {
			$result = true;

			/*
			* Ensure eid is an array of extension ids
			* TODO: If it isn't an array do we want to set an error and fail?
			*/
			if (!is_array($eid)) {
				$eid = array($eid);
			}

			// Get a database connector
			$db = & JFactory::getDBO();

			// Get a table object for the extension type
			$table = & JTable::getInstance('Extension');

			// Enable the extension in the table and store it in the database
			foreach($eid as $id) {
				$table->load($id);
				$table->enabled = $value || $table->protected;
				if (!$table->store()) {
					$this->setError($table->getError());
					$result = false;
				}
			}
		} else {
			$result = false;
			JError::raiseWarning(403, JText::_('JERROR_CORE_EDIT_STATE_NOT_PERMITTED'));
		}
		return $result;
	}

	/**
	 * Refreshes the cached manifest information for an extension
	 * @param int extension identifier (key in #__extensions)
	 * @return boolean result of refresh
	 * @since 1.6
	 */
	function refresh($eid) {
		if (!is_array($eid)) {
			$eid = array($eid => 0);
		}

		// Get a database connector
		$db = & JFactory::getDBO();

		// Get an installer object for the extension type
		jimport('joomla.installer.installer');
		$installer = & JInstaller::getInstance();
		$row = & JTable::getInstance('extension');
		$result = 0;

		// Uninstall the chosen extensions
		foreach($eid as $id) {
			$result|= $installer->refreshManifestCache($id);
		}
		return $result;
	}

	/**
	 * Remove (uninstall) an extension
	 *
	 * @static
	 * @param	array	An array of identifiers
	 * @return	boolean	True on success
	 * @since 1.0
	 */
	function remove($eid = array()) {
		// Initialise variables.
		$user = JFactory::getUser();
		if ($user->authorise('core.delete', 'com_installer')) {

			// Initialise variables.
			$failed = array();

			/*
			* Ensure eid is an array of extension ids in the form id => client_id
			* TODO: If it isn't an array do we want to set an error and fail?
			*/
			if (!is_array($eid)) {
				$eid = array($eid => 0);
			}

			// Get a database connector
			$db = & JFactory::getDBO();

			// Get an installer object for the extension type
			jimport('joomla.installer.installer');
			$installer = & JInstaller::getInstance();
			$row = & JTable::getInstance('extension');

			// Uninstall the chosen extensions
			foreach($eid as $id) {
				$id = trim($id);
				$row->load($id);
				if ($row->type) {
					$result = $installer->uninstall($row->type, $id);

					// Build an array of extensions that failed to uninstall
					if ($result === false) {
						$failed[] = $id;
					}
				}
				else {
					$failed[] = $id;
				}
			}
			if (count($failed)) {

				// There was an error in uninstalling the package
				$msg = JText::sprintf('COM_INSTALLER_UNINSTALL_ERROR', $row->type);
				$result = false;
			}
			else {

				// Package uninstalled sucessfully
				$msg = JText::sprintf('COM_INSTALLER_UNINSTALL_SUCCESS', $row->type);
				$result = true;
			}
			$app = & JFactory::getApplication();
			$app->enqueueMessage($msg);
			$this->setState('action', 'remove');
			$this->setState('name', $installer->get('name'));
			$app->setUserState('com_installer.message', $installer->message);
			$app->setUserState('com_installer.extension_message', $installer->get('extension_message'));
			return $result;
		} else {
			$result = false;
			JError::raiseWarning(403, JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
		}
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
		$search = $this->getState('filter.search');
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
			$row->description = JText::_(@$row->description);
			$row->author_info = @$row->authorEmail .'<br />'. @$row->authorUrl;
			$row->client = $row->client_id ? JText::_('JADMINISTRATOR') : JText::_('JSITE');
			if ($search && !preg_match("/$search/i", $row->name)) {
				unset($result[$i]);
				continue;
			}
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
		$type = $this->getState('filter.type');
		$client = $this->getState('filter.client');
		$group = $this->getState('filter.group');
		$hideprotected = $this->getState('filter.hideprotected');
		$query = new JDatabaseQuery;
		$query->select('*');
		$query->from('#__extensions');
		$query->where('state=0');
		$query->order('protected');
		$query->order('type');
		$query->order('client_id');
		$query->order('folder');
		$query->order('name');
		if ($hideprotected) {
			$query->where('protected!=1');
		}
		if ($type) {
			$query->where('type=' . $this->_db->Quote($type));
		}
		if ($client != '') {
			$query->where('client_id=' . intval($client));
		}
		if ($group != '' && in_array($type, array('plugin', 'library', ''))) {
			
			$query->where('folder=' . $this->_db->Quote($group == '*' ? '' : $group));
		}
		return $query;
	}

	/**
	 * Method to get the row form.
	 *
	 * @return	mixed	JForm object on success, false on failure.
	 */
	public function getForm() {

		// Initialise variables.
		$app = & JFactory::getApplication();

		// Get the form.
		jimport('joomla.form.form');
		JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		$form = & JForm::getInstance('com_installer.manage', 'manage', array('control' => 'filters', 'event' => 'onPrepareForm'));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_installer.manage.data', array());
		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}
		return $form;
	}
}