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
jimport('joomla.installer.installer');

/**
 * Installer Manage Model
 *
 * @package		Joomla.Administator
 * @subpackage	com_installer
 * @since		1.6
 */
class InstallerModelDiscover extends InstallerModel
{
	protected $_context = 'com_installer.discover';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();
		$this->setState('message',$app->getUserState('com_installer.message'));
		$this->setState('extension_message',$app->getUserState('com_installer.extension_message'));
		$app->setUserState('com_installer.message','');
		$app->setUserState('com_installer.extension_message','');
		parent::populateState('name','asc');
	}

	/**
	 * Method to get the database query.
	 *
	 * @return	JDatabaseQuery the database query
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		$db		= JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__extensions');
		$query->where('state=-1');
		return $query;
	}

	/**
	 * Discover extensions.
	 *
	 * Finds uninstalled extensions
	 *
	 * @since	1.6
	 */
	function discover()
	{
		$installer	= JInstaller::getInstance();
		$results	= $installer->discover();

		// Get all templates, including discovered ones
		$query = 'SELECT *,'
				.' CASE WHEN CHAR_LENGTH(folder) THEN CONCAT_WS(":", folder, element) ELSE element END as elementkey'
				.' FROM #__extensions';
		$dbo = JFactory::getDBO();
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
	 * Installs a discovered extension.
	 *
	 * @since	1.6
	 */
	function discover_install()
	{
		$app = JFactory::getApplication();
		$installer = JInstaller::getInstance();
		$eid = JRequest::getVar('cid',0);
		if (is_array($eid) || $eid) {
			if (!is_array($eid)) {
				$eid = Array($eid);
			}
			JArrayHelper::toInteger($eid);
			$app = JFactory::getApplication();
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
			if (!$failed) {
				$app->enqueueMessage(JText::_('COM_INSTALLER_MSG_DISCOVER_INSTALLSUCCESSFUL'));
			}
		} else {
			$app->enqueueMessage(JText::_('COM_INSTALLER_MSG_DISCOVER_NOEXTENSIONSELECTED'));
		}
	}

	/**
	 * Cleans out the list of discovered extensions.
	 *
	 * @since	1.6
	 */
	function purge()
	{
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
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