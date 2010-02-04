<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');
jimport('joomla.installer.installer');
jimport('joomla.installer.helper');

// Import library dependencies
require_once dirname(__FILE__).DS.'extension.php';

/**
 * Extension Manager Install Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @since		1.5
 */
class InstallerModelInstall extends InstallerModel
{
	/**
	 * @var object JTable object
	 */
	protected $_table = null;

	/**
	 * @var object JTable object
	 */
	protected $_url = null;

	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_installer.install';

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @since	1.6
	 */
	protected function _populateState()
	{
		// Initialise variables.
		$app = &JFactory::getApplication('administrator');

		// Remember the 'Install from Directory' path.
		$path = $app->getUserStateFromRequest($this->_context.'.install_directory', 'install_directory', $app->getCfg('config.tmp_path'));
		$this->setState('install.directory', $path);
		parent::_populateState();
	}

	/**
	 * Install an extension from either folder, url or upload
	 * @return boolean result of install
	 */
	function install()
	{
		$this->setState('action', 'install');

		switch(JRequest::getWord('installtype'))
		{
			case 'folder':
				$package = $this->_getPackageFromFolder();
				break;

			case 'upload':
				$package = $this->_getPackageFromUpload();
				break;

			case 'url':
				$package = $this->_getPackageFromUrl();
				break;

			default:
				$this->setState('message', 'JNo_Install_Type_Found');
				return false;
				break;
		}

		// Was the package unpacked?
		if (!$package) {
			$this->setState('message', 'UNABLE_TO_FIND_INSTALL_PACKAGE');
			return false;
		}

		// Get a database connector
		//$db = & JFactory::getDbo();

		// Get an installer instance
		$installer = &JInstaller::getInstance();

		// Install the package
		if (!$installer->install($package['dir'])) {
			// There was an error installing the package
			$msg = JText::sprintf('INSTALLEXT', JText::_($package['type']), JText::_('Error'));
			$result = false;
		} else {
			// Package installed sucessfully
			$msg = JText::sprintf('INSTALLEXT', JText::_($package['type']), JText::_('Success'));
			$result = true;
		}

		// Set some model state values
		$app	= &JFactory::getApplication();
		$app->enqueueMessage($msg);
		$this->setState('name', $installer->get('name'));
		$this->setState('result', $result);
		$this->setState('message', $installer->message);
		$this->setState('extension_message', $installer->get('extension_message'));

		// Cleanup the install files
		if (!is_file($package['packagefile'])) {
			$config = &JFactory::getConfig();
			$package['packagefile'] = $config->getValue('config.tmp_path').DS.$package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

		return $result;
	}

	/**
	 * Works out an installation package from a HTTP upload
	 * @return package definition or false on failure
	 */
	function _getPackageFromUpload()
	{
		// Get the uploaded file information
		$userfile = JRequest::getVar('install_package', null, 'files', 'array');

		// Make sure that file uploads are enabled in php
		if (!(bool) ini_get('file_uploads')) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('WARNINSTALLFILE'));
			return false;
		}

		// Make sure that zlib is loaded so that the package can be unpacked
		if (!extension_loaded('zlib')) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('WARNINSTALLZLIB'));
			return false;
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($userfile)) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('JNo_file_selected'));
			return false;
		}

		// Check if there was a problem uploading the file.
		if ($userfile['error'] || $userfile['size'] < 1)
		{
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('WARNINSTALLUPLOADERROR'));
			return false;
		}

		// Build the appropriate paths
		$config = &JFactory::getConfig();
		$tmp_dest	= $config->getValue('config.tmp_path').DS.$userfile['name'];
		$tmp_src	= $userfile['tmp_name'];

		// Move uploaded file
		jimport('joomla.filesystem.file');
		$uploaded = JFile::upload($tmp_src, $tmp_dest);

		// Unpack the downloaded package file
		$package = JInstallerHelper::unpack($tmp_dest);

		return $package;
	}

	/**
	 * Install an extension from a directory
	 *
	 * @static
	 * @return Package details or false on failure
	 * @since 1.0
	 */
	function _getPackageFromFolder()
	{
		// Get the path to the package to install
		$p_dir = JRequest::getString('install_directory');
		$p_dir = JPath::clean($p_dir);

		// Did you give us a valid directory?
		if (!is_dir($p_dir)) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('PLEASE_ENTER_A_PACKAGE_DIRECTORY'));
			return false;
		}

		// Detect the package type
		$type = JInstallerHelper::detectType($p_dir);

		// Did you give us a valid package?
		if (!$type) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('PATH_DOES_NOT_HAVE_A_VALID_PACKAGE'));
			return false;
		}

		$package['packagefile'] = null;
		$package['extractdir'] = null;
		$package['dir'] = $p_dir;
		$package['type'] = $type;

		return $package;
	}

	/**
	 * Install an extension from a URL
	 *
	 * @static
	 * @return Package details or false on failure
	 * @since 1.5
	 */
	function _getPackageFromUrl()
	{
		// Get a database connector
		$db = & JFactory::getDbo();

		// Get the URL of the package to install
		$url = JRequest::getString('install_url');

		// Did you give us a URL?
		if (!$url) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('PLEASE_ENTER_A_URL'));
			return false;
		}

		// Download the package at the URL given
		$p_file = JInstallerHelper::downloadPackage($url);

		// Was the package downloaded?
		if (!$p_file) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('INVALID_URL'));
			return false;
		}

		$config = &JFactory::getConfig();
		$tmp_dest	= $config->getValue('config.tmp_path');

		// Unpack the downloaded package file
		$package = JInstallerHelper::unpack($tmp_dest.DS.$p_file);

		return $package;
	}
}