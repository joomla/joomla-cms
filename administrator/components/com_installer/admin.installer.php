<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installer
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.installer.installer');
require_once ($mainframe->getPath('admin_html'));

/*
 * Make sure the user is authorized to view this page
 */
$user = & JFactory::getUser();
if (!$user->authorize('com_installer', 'installer')) {
	$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

/*
 * We need to load the appropriate tmpl file
 */
$extension	= JRequest::getVar('extension');
if ($extension != '') {
	$path = dirname(__FILE__).DS.'views'.DS.$extension.DS.$extension.'.php';

	if (file_exists($path)) {
		require_once ($path);
	}
}

/*
 * Get the task variable from the page request variables
 */

switch (JRequest::getVar('task')) 
{
	case 'uploadpackage' :
		JInstallerController::uploadpackage();
		break;
	case 'installfromdirectory' :
		JInstallerController::installFromDirectory();
		break;
	case 'installfromurl' :
		JInstallerController::installfromurl();
		break;
	case 'enable' :
		JInstallerController::enableextension();
		break;
	case 'disable' :
		JInstallerController::disableextension();
		break;
	case 'remove' :
	case 'removeextension' :
		JInstallerController::removeextension();
		break;
	default :
		JInstallerController::installer();
		break;
}

class JInstallerController
{
	/**
	 * @param string The class name for the installer
	 */
	function uploadpackage()
	{
		// Get a database connector
		$db = & JFactory::getDBO();

		// Get the uploaded file information
		$userfile = JRequest::getVar('userfile', '', 'files', 'array' );

		// Make sure that file uploads are enabled in php
		if (!(bool) ini_get('file_uploads')) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('WARNINSTALLFILE'));
			JInstallerScreens::showInstallForm();
			return false;
		}

		// Make sure that zlib is loaded so that the package can be unpacked
		if (!extension_loaded('zlib')) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('WARNINSTALLZLIB'));
			JInstallerScreens::showInstallForm();
			return false;
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($userfile) || $userfile['size'] < 1) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('No file selected'));
			JInstallerScreens::showInstallForm();
			return false;
		}

		// Build the appropriate paths
		$tmp_dest 	= JPATH_ROOT.DS.'tmp'.DS.$userfile['name'];
		$tmp_src	= $userfile['tmp_name'];

		// Move uploaded file
		jimport('joomla.filesystem.file');
		$uploaded = JFile::upload($tmp_src, $tmp_dest);

		// Unpack the downloaded package file
		$package = JInstallerHelper::unpack($tmp_dest);

		// Was the package unpacked?
		if (!$package) {
			JInstallerScreens::showInstallForm();
			return false;
		}

		// Get an installer instance
		$installer = & JInstaller::getInstance($db, $package['type']);

		// Install the package
		if (!$installer->install($package['dir']))
		{
			// There was an error in installing the package
			$msg = sprintf(JText::_('INSTALLEXT'), $package['type'], JText::_('Error'));
			JInstallerScreens::showInstallMessage($msg, $installer->description, $installer->message);
			// Cleanup the install files
			JInstallerHelper::cleanupInstall(JPATH_ROOT.DS.'tmp'.DS.$userfile['name'], $package['extractdir']);
			JInstallerScreens::showInstallForm();
		}
		else
		{
			// Package installed sucessfully
			$msg = sprintf(JText::_('INSTALLEXT'), $package['type'], JText::_('Success'));
			JInstallerScreens::showInstallMessage($msg, $installer->description, $installer->message);
			// Cleanup the install files
			JInstallerHelper::cleanupInstall(JPATH_ROOT.DS.'tmp'.DS.$userfile['name'], $package['extractdir']);
			JInstallerScreens::showInstallForm();
		}
	}

	/**
	 * Install an extension from a directory
	 *
	 * @static
	 * @return boolean True on success
	 * @since 1.0
	 */
	function installFromDirectory()
	{
		global $mainframe;

		// Get a database connector
		$db = & JFactory::getDBO();

		// Get the path to the package to install
		$p_dir = JRequest::getVar('userfile');

		// Did you give us a valid directory?
		if (!is_dir($p_dir)) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('Please enter a package directory'));
			JInstallerScreens::showInstallForm();
			return false;
		}

		// Detect the package type
		$type = JInstallerHelper::detectType($p_dir);

		// Did you give us a valid package?
		if (!$type) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('Path does not have a valid package'));
			JInstallerScreens::showInstallForm();
			return false;
		}

		// Get an installer instance
		$installer = & JInstaller::getInstance($db, $type);

		// Install the package
		if (!$installer->install($p_dir))
		{
			// There was an error in installing the package
			$msg = sprintf(JText::_('INSTALLEXT'), $type, JText::_('Error'));
			JInstallerScreens::showInstallMessage($msg, $installer->description, $installer->message);
			JInstallerScreens::showInstallForm();
		}
		else
		{
			// Package installed sucessfully
			$msg = sprintf(JText::_('INSTALLEXT'), $type, JText::_('Success'));
			JInstallerScreens::showInstallMessage($msg, $installer->description, $installer->message);
			JInstallerScreens::showInstallForm();
		}
	}

	/**
	 * Install an extension from a URL
	 *
	 * @static
	 * @return boolean True on success
	 * @since 1.5
	 */
	function installFromUrl()
	{
		// Get a database connector
		$db = & JFactory::getDBO();

		// Get the URL of the package to install
		$url = JRequest::getVar('userfile');

		// Did you give us a URL?
		if (!$url) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('Please enter a URL'));
			JInstallerScreens::showInstallForm();
			return false;
		}

		// Download the package at the URL given
		$p_file = JInstallerHelper::downloadPackage($url);

		// Was the package downloaded?
		if (!$p_file) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('Invalid URL'));
			JInstallerScreens::showInstallForm();
			return false;
		}

		// Unpack the downloaded package file
		$package = JInstallerHelper::unpack($p_file);

		// Was the package unpacked?
		if (!$package) {
			JInstallerScreens::showInstallForm();
			return false;
		}

		// Get an installer instance
		$installer = & JInstaller::getInstance($db, $package['type']);

		// Install the package
		if (!$installer->install($package['dir']))
		{
			// There was an error in installing the package
			$msg = sprintf(JText::_('INSTALLEXT'), $package['type'], JText::_('Error'));
			JInstallerScreens::showInstallMessage($msg, $installer->description, $installer->message);
			// Cleanup the install files
			JInstallerHelper::cleanupInstall($p_file, $package['extractdir']);
			JInstallerScreens::showInstallForm();
		}
		else
		{
			// Package installed sucessfully
			$msg = sprintf(JText::_('INSTALLEXT'), $package['type'], JText::_('Success'));
			JInstallerScreens::showInstallMessage($msg, $installer->description, $installer->message);
			// Cleanup the install files
			JInstallerHelper::cleanupInstall($p_file, $package['extractdir']);
			JInstallerScreens::showInstallForm();
		}
	}

	/**
	 * Enable an extension
	 *
	 * @static
	 * @return boolean True on success
	 * @since 1.0
	 */
	function enableextension()
	{
		// Get a database connector
		$db = & JFactory::getDBO();

		// Initialize variables
		$eid			= JRequest::getVar('eid', array (0), '', 'array');
		$extension	= JRequest::getVar('extension');
		$result		= false;

//		/*
//		 * Get the extension client
//		 *
//		 * Defaults to 'site'
//		 * Set 'admin' to 'administrator'
//		 */
//		$client = JRequest::getVar('client', 'site');
//		if ($client == '') {
//			$client = 'site';
//		}
//		if ($client == 'admin') {
//			$client = 'administrator';
//		}

		/*
		 * Ensure eid is an array of extension ids
		 * TODO: If it isn't an array do we want to set an error and fail?
		 */
		if (!is_array($eid)) {
			$eid = array ($eid);
		}

		// Get a table object for the extension type
		$table = & JTable::getInstance($extension, $db);

		// Disable the extension in the table and store it in the database
		foreach ($eid as $id) {
			$table->load($id);
			$table->enabled = '1';
			$table->store();
		}

		// Display the extension management screen
		JInstallerExtensionTasks::showInstalled();
	}

	/**
	 * Disable an extension
	 *
	 * @static
	 * @return boolean True on success
	 * @since 1.5
	 */
	function disableextension()
	{
		// Get a database connector
		$db =& JFactory::getDBO();

		// Initialize variables
		$eid		= JRequest::getVar('eid', array (0), '', 'array');
		$extension	= JRequest::getVar('extension');
		$result		= false;

//		/*
//		 * Get the extension client
//		 *
//		 * Defaults to 'site'
//		 * Set 'admin' to 'administrator'
//		 */
//		$client = JRequest::getVar('client', 'site');
//		if ($client == '') {
//			$client = 'site';
//		}
//		if ($client == 'admin') {
//			$client = 'administrator';
//		}

		/*
		 * Ensure eid is an array of extension ids
		 * TODO: If it isn't an array do we want to set an error and fail?
		 */
		if (!is_array($eid)) {
			$eid = array ($eid);
		}

		// Get a table object for the extension type
		$table = & JTable::getInstance($extension, $db);

		// Disable the extension in the table and store it in the database
		foreach ($eid as $id) {

			$table->load($id);
			$table->enabled = '0';
			$table->store();
		}

		// Display the extension management screen
		JInstallerExtensionTasks::showInstalled();
	}

	/**
	 * Remove (uninstall) an extension
	 *
	 * @static
	 * @return boolean True on success
	 * @since 1.0
	 */
	function removeextension()
	{
		// Get a database connector
		$db =& JFactory::getDBO();

		// Initialize variables
		$eid		= JRequest::getVar('eid', array (0));
		$eclient	= JRequest::getVar('eclient', array (0));
		$extension	= JRequest::getVar('extension', '');
		$result		= false;
		$failed		= array ();
		
		echo var_dump($eid);
		echo var_dump($eclient);
		die();

//		/*
//		 * Get the extension client
//		 *
//		 * Defaults to 'site'
//		 * Set 'admin' to 'administrator'
//		 */
//		$client = JRequest::getVar('client', 'site');
//		if ($client == '') {
//			$client = 'site';
//		}
//		if ($client == 'admin') {
//			$client = 'administrator';
//		}

		/*
		 * Ensure eid is an array of extension ids
		 * TODO: If it isn't an array do we want to set an error and fail?
		 */
		if (!is_array($eid)) {
			$eid = array ($eid);
		}

		/*
		 * Ensure eclient is an array of extension clients
		 * TODO: If it isn't an array do we want to set an error and fail?
		 */
		if (!is_array($eclient)) {
			$eclient = array ($eclient);
		}

		// Get an installer object for the extension type
		$installer = & JInstaller::getInstance($db, $extension);

		// Uninstall the chosen extensions
		$i = 0;
		foreach ($eid as $id)
		{
			$result = $installer->uninstall($id, $eclient[$i++]);

			// Build an array of extensions that failed to uninstall
			if ($result === false) {
				$failed[] = $id;
			}
		}

		if (count($failed))
		{
			// There was an error in uninstalling the package
			$msg = sprintf(JText::_('UNINSTALLEXT'), $extension, JText::_('Error'));
			JInstallerScreens::showInstallMessage($msg, $installer->description, $installer->message);

			// Display the extension management screen
			JInstallerExtensionTasks::showInstalled();
		}
		else
		{
			// Package uninstalled sucessfully
			$msg = sprintf(JText::_('UNINSTALLEXT'), $extension, JText::_('Success'));
			JInstallerScreens::showInstallMessage($msg, $installer->description, $installer->message);

			// Display the extension management screen
			JInstallerExtensionTasks::showInstalled();
		}
	}

	/**
	 * Unified intaller
	 */
	function installer()
	{
		// Initialize variables
		$extension = JRequest::getVar('extension');

		if (!empty($extension)) {
			JInstallerExtensionTasks::showInstalled();
		} else {
			JInstallerScreens::showInstallForm();
		}
	}
}
?>