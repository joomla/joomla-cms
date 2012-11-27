<?php

/**
 * @author      Jeremy Wilken - Gnome on the run
 * @link        www.gnomeontherun.com
 * @copyright   Copyright 2011 Gnome on the run. All Rights Reserved.
 * @category    Administrator
 * @package     com_installer
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

// Import library dependencies
jimport('joomla.application.component.modellist');
jimport('joomla.installer.installer');
jimport('joomla.installer.helper');
jimport('joomla.updater.updater');
jimport('joomla.updater.update');

/**
 * @package		Administrator
 * @subpackage	com_installer
 * @since		2.5
 */
class InstallerModelSite extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	2.5
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
	 * @since	2.5
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('administrator');
		$this->setState('message',$app->getUserState('com_installer.message'));
		$this->setState('extension_message',$app->getUserState('com_installer.extension_message'));
		$app->setUserState('com_installer.message','');
		$app->setUserState('com_installer.extension_message','');
		parent::populateState('name', 'asc');
	}

	/**
	 * Method to get the database query
	 *
	 * @return	JDatabaseQuery	The database query
	 * @since	2.5
	 */
	protected function getListQuery()
	{
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
        $id = JRequest::getVar('id', 0, '', 'int');
		// grab updates ignoring new installs
		$query->select('*')->from('#__updates')->where('extension_id = 0')->where('update_site_id = '.$id);
		$query->order($this->getState('list.ordering').' '.$this->getState('list.direction'));

		return $query;
	}

	/**
	 * Finds updates for an extension.
	 *
	 * @param	int		Extension identifier to look for
	 * @return	boolean Result
	 * @since	2.5
	 */
	public function findUpdates($cid=0)
	{
		$updater = JUpdater::getInstance();
		$results = $updater->findUpdates($cid);
		return true;
	}

	/**
	 * Removes all of the updates from the table.
	 *
	 * @return	boolean result of operation
	 * @since	2.5
	 */
	public function purge()
	{
		$db = JFactory::getDBO();
		// Note: TRUNCATE is a DDL operation
		// This may or may not mean depending on your database
		$db->setQuery('TRUNCATE TABLE #__updates');
		if ($db->Query()) {
			$this->_message = JText::_('COM_INSTALLER_PURGED_UPDATES');
			return true;
		} else {
			$this->_message = JText::_('COM_INSTALLER_FAILED_TO_PURGE_UPDATES');
			return false;
		}
	}
    
    /**
     * Get current extensions
     * 
     * @since   2.5
     * @return  object
     */
    public function getUpdates()
    {
        $db = JFactory::getDBO();
        $db->setQuery('SELECT * FROM #__updates');
        if ($updates = $db->loadObjectList())
        {
            return $updates;
        }
        return false;
    }
    
    public function getDistro($id)
    {
        $db = JFactory::getDBO();
        $db->setQuery('SELECT * FROM #__update_sites WHERE update_site_id = '.$id);
        if ($row = $db->loadObject())
        {
            return $row;
        }
        return false;
    }
    
    /**
     * Get current extensions
     * 
     * @since   2.5
     * @return  object
     */
    public function getExtensions()
    {
        $db = JFactory::getDBO();
        $db->setQuery('SELECT * FROM #__extensions');
        if ($extensions = $db->loadObjectList())
        {
            return $extensions;
        }
        return false;
    }
    
    /**
	 * Install function.
	 *
	 * Sets the "result" state with the result of the operation.
	 *
	 * @param	Array[int] List of updates to apply
	 * @since	2.5
	 */
	public function install($cids)
	{
		$result = true;
		foreach($cids as $cid) {
			$update = new JUpdate();
			$instance = JTable::getInstance('update');
			$instance->load($cid);
			$update->loadFromXML($instance->detailsurl);
			// install sets state and enqueues messages
			$res = $this->install_install($update->get('downloadurl')->_data);

            // Disabling the purging of the update list, instead deleting specific row
			if ($res) {
				$instance->delete($cid);
			}

			$result = $res & $result;
		}

		// Set the final state
		$this->setState('result', $result);
	}
    
    /**
     * Installs the extension
     * 
     * @param   string  $url
     * @return  boolean 
     * @since   2.5
     */
    function install_install($url)
	{
		jimport('joomla.client.helper');
		$this->setState('action', 'install');

		// Set FTP credentials, if given.
		JClientHelper::setCredentialsFromRequest('ftp');
		$app = JFactory::getApplication();
        
        $package = $this->_getPackageFromUrl($url);

		// Was the package unpacked?
		if (!$package) {
			$app->setUserState('com_installer.message', JText::_('COM_INSTALLER_UNABLE_TO_FIND_INSTALL_PACKAGE'));
			return false;
		}

		// Get an installer instance
		$installer = JInstaller::getInstance();

		// Install the package
		if (!$installer->install($package['dir'])) {
			// There was an error installing the package
			$msg = JText::sprintf('COM_INSTALLER_INSTALL_ERROR', JText::_('COM_INSTALLER_TYPE_TYPE_'.strtoupper($package['type'])));
			$result = false;
		} else {
			// Package installed sucessfully
			$msg = JText::sprintf('COM_INSTALLER_INSTALL_SUCCESS', JText::_('COM_INSTALLER_TYPE_TYPE_'.strtoupper($package['type'])));
			$result = true;
		}

		// Set some model state values
		$app	= JFactory::getApplication();
		$app->enqueueMessage($msg);
		$this->setState('name', $installer->get('name'));
		$this->setState('result', $result);
		$app->setUserState('com_installer.message', $installer->message);
		$app->setUserState('com_installer.extension_message', $installer->get('extension_message'));
		$app->setUserState('com_installer.redirect_url', $installer->get('redirect_url'));

		// Cleanup the install files
		if (!is_file($package['packagefile'])) {
			$config = JFactory::getConfig();
			$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);


		return $result;
	}
    
    /**
	 * Install an extension from a URL
	 *
	 * @return	Package details or false on failure
	 * @since	2.5
	 */
	protected function _getPackageFromUrl($url)
	{
		// Get a database connector
		$db = JFactory::getDbo();

		// Did you give us a URL?
		if (!$url) {
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL'));
			return false;
		}

		// Download the package at the URL given
		$p_file = JInstallerHelper::downloadPackage($url);

		// Was the package downloaded?
		if (!$p_file) {
			JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_INVALID_URL'));
			return false;
		}

		$config		= JFactory::getConfig();
		$tmp_dest	= $config->get('tmp_path');

		// Unpack the downloaded package file
		$package = JInstallerHelper::unpack($tmp_dest . '/' . $p_file);

		return $package;
	}
}
