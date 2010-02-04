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
jimport('joomla.updater.updater');
jimport('joomla.updater.update');

/**
 * @package		Joomla.Administrator
 *
 * @package		Joomla
 * @subpackage	com_installer
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
	 * Current update list
	 */
	protected function _loadItems()
	{
		jimport('joomla.filesystem.folder');

		/* Get a database connector */
		$db =& JFactory::getDBO();

		$query = 'SELECT *' .
				' FROM #__updates' .
		//' WHERE extension_id != 0' . // we only want actual updates
				' ORDER BY type, client_id, folder, name';
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$apps =& JApplicationHelper::getClientInfo();

		$numRows = count($rows);
		for($i=0;$i < $numRows; $i++)
		{
			$row =& $rows[$i];
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
	 * Finds updates for an extension
	 * @param int Extension identifier to look for
	 * @return boolean Result
	 */
	public function findUpdates($eid=0)
	{
		$updater =& JUpdater::getInstance();
		$results = $updater->findUpdates($eid);
		return true;
	}

	/**
	 * Removes all of the updates from the table
	 * @return boolean result of operation
	 */
	public function purge()
	{
		$db =& JFactory::getDBO();
		// Note: TRUNCATE is a DDL operation
		// This may or may not mean depending on your database
		$db->setQuery('TRUNCATE TABLE #__updates');
		if ($db->Query())
		{
			$this->_message = JText::_('PURGED_UPDATES');
			return true;
		}
		else
		{
			$this->_message = JText::_('FAILED_TO_PURGE_UPDATES');
			return false;
		}
	}

	/**
	 * Update function
	 * Sets the "result" state with the result of the operation
	 * @param Array[int] List of updates to apply
	 */
	public function update($uids)
	{
		$result = true;
		foreach($uids as $uid)
		{
			$update = new JUpdate();
			$instance =& JTable::getInstance('update');
			$instance->load($uid);
			$update->loadFromXML($instance->detailsurl);
			// install sets state and enqueues messages
			$res = $this->_install($update);
			$result = $res & $result;
		}

		// Set the final state
		$this->setState('result', $result);
	}

	/**
	 * Handles the actual update installation
	 * @param JUpdate an update definition
	 * @return boolean Result of install
	 */
	private function _install($update)
	{
		$app = &JFactory::getApplication();
		if(isset($update->get('downloadurl')->_data)) {
			$url = $update->downloadurl->_data;
		} else
		{
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('Invalid extension update'));
			return false;
		}

		jimport('joomla.installer.helper');
		$p_file = JInstallerHelper::downloadPackage($url);

		// Was the package downloaded?
		if (!$p_file)
		{
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('Package download failed').': '. $url);
			return false;
		}

		$config =& JFactory::getConfig();
		$tmp_dest	= $config->getValue('config.tmp_path');

		// Unpack the downloaded package file
		$package = JInstallerHelper::unpack($tmp_dest.DS.$p_file);

		// Get an installer instance
		$installer =& JInstaller::getInstance();
		$update->set('type', $package['type']);

		// Install the package
		if (!$installer->install($package['dir']))
		{
			// There was an error installing the package
			$msg = JText::sprintf('INSTALLEXT', JText::_($package['type']), JText::_('Error'));
			$result = false;
		}
		else
		{
			// Package installed sucessfully
			$msg = JText::sprintf('INSTALLEXT', JText::_($package['type']), JText::_('Success'));
			$result = true;
		}

		// Quick change
		$this->type = $package['type'];

		// Set some model state values
		$app->enqueueMessage($msg);

		// TODO: Reconfigure this code when you have more battery life left
		$this->setState('name', $installer->get('name'));
		$this->setState('result', $result);
		$this->setState('message', $installer->message);
		$this->setState('extension.message', $installer->get('extension.message'));

		// Cleanup the install files
		if (!is_file($package['packagefile']))
		{
			$config =& JFactory::getConfig();
			$package['packagefile'] = $config->getValue('config.tmp_path').DS.$package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

		return $result;
	}
}