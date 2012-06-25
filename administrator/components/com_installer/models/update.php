<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Import library dependencies
jimport('joomla.application.component.modellist');
jimport('joomla.updater.update');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @since		1.6
 */
class InstallerModelUpdate extends JModelList
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
				'type',
				'folder',
				'extension_id',
				'update_id',
				'update_site_id',
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
		$app = JFactory::getApplication('administrator');
		$this->setState('message', $app->getUserState('com_installer.message'));
		$this->setState('extension_message', $app->getUserState('com_installer.extension_message'));
		$app->setUserState('com_installer.message', '');
		$app->setUserState('com_installer.extension_message', '');
		parent::populateState('name', 'asc');
	}

	/**
	 * Method to get the database query
	 *
	 * @return	JDatabaseQuery	The database query
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		// grab updates ignoring new installs
		$query->select('*')->from('#__updates')->where('extension_id != 0');
		$query->order($this->getState('list.ordering').' '.$this->getState('list.direction'));

		// Filter by extension_id
		if ($eid = $this->getState('filter.extension_id')) {
			$query->where($db->nq('extension_id') . ' = ' . $db->q((int) $eid));
		} else {
			$query->where($db->nq('extension_id').' != '.$db->q(0));
			$query->where($db->nq('extension_id').' != '.$db->q(700));
		}

		return $query;
	}

	/**
	 * Finds updates for an extension.
	 *
	 * @param	int		Extension identifier to look for
	 * @return	boolean Result
	 * @since	1.6
	 */
	public function findUpdates($eid=0, $cache_timeout = 0)
	{
		$updater = JUpdater::getInstance();
		$results = $updater->findUpdates($eid, $cache_timeout);
		return true;
	}

	/**
	 * Removes all of the updates from the table.
	 *
	 * @return	boolean result of operation
	 * @since	1.6
	 */
	public function purge()
	{
		$db = JFactory::getDBO();
		// Note: TRUNCATE is a DDL operation
		// This may or may not mean depending on your database
		$db->setQuery('TRUNCATE TABLE #__updates');
		if ($db->Query()) {
			// Reset the last update check timestamp
			$query = $db->getQuery(true);
			$query->update($db->nq('#__update_sites'));
			$query->set($db->nq('last_check_timestamp').' = '.$db->q(0));
			$db->setQuery($query);
			$db->query();

			$this->_message = JText::_('COM_INSTALLER_PURGED_UPDATES');
			return true;
		} else {
			$this->_message = JText::_('COM_INSTALLER_FAILED_TO_PURGE_UPDATES');
			return false;
		}
	}

	/**
	 * Enables any disabled rows in #__update_sites table
	 *
	 * @return	boolean result of operation
	 * @since	1.6
	 */
	public function enableSites()
	{
		$db = JFactory::getDBO();
		$db->setQuery('UPDATE #__update_sites SET enabled = 1 WHERE enabled = 0');
		if ($db->Query()) {
			if ($rows = $db->getAffectedRows()) {
				$this->_message .= JText::plural('COM_INSTALLER_ENABLED_UPDATES', $rows);
			}
			return true;
		} else {
			$this->_message .= JText::_('COM_INSTALLER_FAILED_TO_ENABLE_UPDATES');
			return false;
		}
	}

	/**
	 * Update function.
	 *
	 * Sets the "result" state with the result of the operation.
	 *
	 * @param	Array[int] List of updates to apply
	 * @since	1.6
	 */
	public function update($uids)
	{
		$result = true;
		foreach($uids as $uid) {
			$update = new JUpdate;
			$instance = JTable::getInstance('update');
			$instance->load($uid);
			$update->loadFromXML($instance->detailsurl);
			// install sets state and enqueues messages
			$res = $this->install($update);

			if ($res) {
				$instance->delete($uid);
			}

			$result = $res & $result;
		}

		// Set the final state
		$this->setState('result', $result);
	}

	/**
	 * Handles the actual update installation.
	 *
	 * @param	JUpdate	An update definition
	 * @return	boolean	Result of install
	 * @since	1.6
	 */
	private function install($update)
	{
		$app = JFactory::getApplication();
		if (isset($update->get('downloadurl')->_data)) {
			$url = $update->downloadurl->_data;
		} else {
			JError::raiseWarning('', JText::_('COM_INSTALLER_INVALID_EXTENSION_UPDATE'));
			return false;
		}

		$p_file = JInstallerHelper::downloadPackage($url);

		// Was the package downloaded?
		if (!$p_file) {
			JError::raiseWarning('', JText::sprintf('COM_INSTALLER_PACKAGE_DOWNLOAD_FAILED', $url));
			return false;
		}

		$config		= JFactory::getConfig();
		$tmp_dest	= $config->get('tmp_path');

		// Unpack the downloaded package file
		$package	= JInstallerHelper::unpack($tmp_dest . '/' . $p_file);

		// Get an installer instance
		$installer	= JInstaller::getInstance();
		$update->set('type', $package['type']);

		// Install the package
		if (!$installer->update($package['dir'])) {
			// There was an error updating the package
			$msg = JText::sprintf('COM_INSTALLER_MSG_UPDATE_ERROR', JText::_('COM_INSTALLER_TYPE_TYPE_'.strtoupper($package['type'])));
			$result = false;
		} else {
			// Package updated successfully
			$msg = JText::sprintf('COM_INSTALLER_MSG_UPDATE_SUCCESS', JText::_('COM_INSTALLER_TYPE_TYPE_'.strtoupper($package['type'])));
			$result = true;
		}

		// Quick change
		$this->type = $package['type'];

		// Set some model state values
		$app->enqueueMessage($msg);

		// TODO: Reconfigure this code when you have more battery life left
		$this->setState('name', $installer->get('name'));
		$this->setState('result', $result);
		$app->setUserState('com_installer.message', $installer->message);
		$app->setUserState('com_installer.extension_message', $installer->get('extension_message'));

		// Cleanup the install files
		if (!is_file($package['packagefile'])) {
			$config = JFactory::getConfig();
			$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

		return $result;
	}
}
