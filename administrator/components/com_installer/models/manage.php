<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Import library dependencies
require_once dirname(__FILE__) . '/extension.php';

/**
 * Installer Manage Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @since		1.5
 */
class InstallerModelManage extends InstallerModel
{
	protected $_context = 'com_installer.manage';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('administrator');
		$this->setState($this->_context.'.message',$app->getUserState('com_installer.message'));
		$this->setState($this->_context.'.extension_message',$app->getUserState('com_installer.extension_message'));
		$app->setUserState('com_installer.message','');
		$app->setUserState('com_installer.extension_message','');
		$data = JRequest::getVar('filters');
		if (empty($data)) {
			$data = $app->getUserState('com_installer.manage.data', array());
		} else {
			$app->setUserState('com_installer.manage.data', $data);
		}
		$this->setState('filter.search', isset($data['search']['expr']) ? $data['search']['expr'] : '');
		$this->setState('filter.hideprotected', isset($data['search']['hideprotected']) ? $data['search']['hideprotected'] : 0);
		$this->setState('filter.type', isset($data['select']['type']) ? $data['select']['type'] : '');
		$this->setState('filter.group', isset($data['select']['group']) ? $data['select']['group'] : '');
		$this->setState('filter.client', isset($data['select']['client']) ? $data['select']['client'] : '');
		parent::populateState('name', 'asc');
	}

	/**
	 * Enable/Disable an extension.
	 *
	 * @return	boolean True on success
	 * @since	1.5
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
			$db = JFactory::getDBO();

			// Get a table object for the extension type
			$table = JTable::getInstance('Extension');

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
	 * Refreshes the cached manifest information for an extension.
	 *
	 * @param	int		extension identifier (key in #__extensions)
	 * @return	boolean	result of refresh
	 * @since	1.6
	 */
	function refresh($eid)
	{
		if (!is_array($eid)) {
			$eid = array($eid => 0);
		}

		// Get a database connector
		$db = JFactory::getDBO();

		// Get an installer object for the extension type
		jimport('joomla.installer.installer');
		$installer = JInstaller::getInstance();
		$row = JTable::getInstance('extension');
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
	 * @param	array	An array of identifiers
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function remove($eid = array())
	{
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
			$installer = JInstaller::getInstance();
			$row = JTable::getInstance('extension');

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
			$app = JFactory::getApplication();
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
	 * Method to get the database query
	 *
	 * @return	JDatabaseQuery	The database query
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		$type = $this->getState('filter.type');
		$client = $this->getState('filter.client');
		$group = $this->getState('filter.group');
		$hideprotected = $this->getState('filter.hideprotected');
		$query = new JDatabaseQuery;
		$query->select('*');
		$query->from('#__extensions');
		$query->where('state=0');
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

		// Filter by search in id
		$search = $this->getState('filter.search');
		if (!empty($search) && stripos($search, 'id:') === 0) {
			$query->where('extension_id = '.(int) substr($search, 3));
		}

		return $query;
	}

	/**
	 * Method to get the row form.
	 *
	 * @return	mixed	JForm object on success, false on failure.
	 * @since	1.6
	 */
	public function getForm() {

		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		jimport('joomla.form.form');
		JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		$form = JForm::getInstance('com_installer.manage', 'manage', array('control' => 'filters', 'event' => 'onPrepareForm'));

		// Check for an error.
		if ($form == false) {
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