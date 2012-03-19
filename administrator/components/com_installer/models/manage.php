<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
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
	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'name',
				'client_id',
				'status',
				'type',
				'folder',
				'extension_id',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication();
		$filters = JRequest::getVar('filters');
		if (empty($filters)) {
			$data = $app->getUserState($this->context.'.data');
			$filters = $data['filters'];
		}
		else {
			$app->setUserState($this->context.'.data', array('filters'=>$filters));
		}

		$this->setState('message', $app->getUserState('com_installer.message'));
		$this->setState('extension_message', $app->getUserState('com_installer.extension_message'));
		$app->setUserState('com_installer.message', '');
		$app->setUserState('com_installer.extension_message', '');

		$this->setState('filter.search', isset($filters['search']) ? $filters['search'] : '');
		$this->setState('filter.status', isset($filters['status']) ? $filters['status'] : '');
		$this->setState('filter.type', isset($filters['type']) ? $filters['type'] : '');
		$this->setState('filter.group', isset($filters['group']) ? $filters['group'] : '');
		$this->setState('filter.client_id', isset($filters['client_id']) ? $filters['client_id'] : '');
		parent::populateState('name', 'asc');
	}

	/**
	 * Enable/Disable an extension.
	 *
	 * @return	boolean True on success
	 * @since	1.5
	 */
	function publish(&$eid = array(), $value = 1)
	{
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
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_templates/tables');
			// Enable the extension in the table and store it in the database
			foreach($eid as $i=>$id) {
				$table->load($id);
				if ($table->type == 'template') {
					$style = JTable::getInstance('Style', 'TemplatesTable');
					if ($style->load(array('template' => $table->element, 'client_id' => $table->client_id, 'home'=>1))) {
						JError::raiseNotice(403, JText::_('COM_INSTALLER_ERROR_DISABLE_DEFAULT_TEMPLATE_NOT_PERMITTED'));
						unset($eid[$i]);
						continue;
					}
				}
				if ($table->protected == 1) {
					$result = false;
					JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
				} else {
					$table->enabled = $value;
				}
				if (!$table->store()) {
					$this->setError($table->getError());
					$result = false;
				}
			}
		} else {
			$result = false;
			JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
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
			$db = JFactory::getDBO();

			// Get an installer object for the extension type
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

			$langstring = 'COM_INSTALLER_TYPE_TYPE_'. strtoupper($row->type);
			$rowtype = JText::_($langstring);
			if(strpos($rowtype, $langstring) !== false) {
				$rowtype = $row->type;
			}

			if (count($failed)) {

				// There was an error in uninstalling the package
				$msg = JText::sprintf('COM_INSTALLER_UNINSTALL_ERROR', $rowtype);
				$result = false;
			} else {

				// Package uninstalled sucessfully
				$msg = JText::sprintf('COM_INSTALLER_UNINSTALL_SUCCESS', $rowtype);
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
		$status = $this->getState('filter.status');
		$type = $this->getState('filter.type');
		$client = $this->getState('filter.client_id');
		$group = $this->getState('filter.group');
		$query = JFactory::getDBO()->getQuery(true);
		$query->select('*');
		$query->select('2*protected+(1-protected)*enabled as status');
		$query->from('#__extensions');
		$query->where('state=0');
		if ($status != '') {
			if ($status == '2')
			{
				$query->where('protected = 1');
			}
			else
			{
				$query->where('protected = 0');
				$query->where('enabled=' . intval($status));
			}
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
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$app = JFactory::getApplication();
		JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		$form = JForm::getInstance('com_installer.manage', 'manage', array('load_data' => $loadData));

		// Check for an error.
		if ($form == false) {
			$this->setError($form->getMessage());
			return false;
		}
		// Check the session for previously entered form data.
		$data = $this->loadFormData();

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_installer.manage.data', array());

		return $data;
	}
}
