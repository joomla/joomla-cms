<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Language installer
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.5
 */
class JInstallerLanguage extends JInstaller
{

	/**
	 * Core language pack flag
	 * 
	 * @access	private
	 * @var		boolean
	 */
	var $_corePack = false;

	/**
	 * Custom install method
	 *
	 * @access public
	 * @param string $p_fromdir Directory from which to install the language
	 * @return boolean True on success
	 * @since 1.5
	 */
	function install($p_fromdir)
	{
		/*
		 * First lets set the installation directory, find and check the installation file and verify
		 * that it is the proper installation type
		 */
		if (!$this->preInstallCheck($p_fromdir, 'language')) {
			return false;
		}

		// Get the root node of the XML document
		$root = & $this->_xmldoc->documentElement;

		// Get the client application target
		if ($client = $root->getAttribute('client')) {
			// Attempt to map the client to a base path
			$clientVals = JApplicationHelper::getClientInfo($client, true);
			if ($clientVals === false) {
				JError::raiseWarning(1, 'JInstallerLanguage::install: '.JText::_('Unknown client type').' ['.$client.']');
				return false;
			}
			$basePath = $clientVals->path;
			$clientId = $clientVals->id;
		} else {
			// No client attribute was found so we assume the site as the client
			$client = 'site';
			$basePath = JPATH_SITE;
			$clientId = 0;
		}

		// Get the language name
		$e = & $root->getElementsByPath('name', 1);
		$this->_extensionName = $e->getText();

		// Get the Language tag [ISO tag, eg. en-GB]
		$e = & $root->getElementsByPath('tag', 1);
		$folder = $e->getText();

		// Set the language installation path
		$this->_extensionDir = JPath::clean($basePath.DS."language".DS.$folder);

		/*
		 * Do we have a meta file in the file list?  In other words... is this a
		 * core language pack?
		 */
		$e = & $root->getElementsByPath('files', 1);
		if (!is_null($e) && $e->hasChildNodes()) {
			$files = & $e->childNodes;
			foreach ($files as $file) {
				if ($file->hasAttribute('file')) {
					if ($file->getAttribute('file') == "meta") {
						$this->_corePack = true;
						break;
					}
				}
			}
		}

		/*
		 * Either we are installing a core pack or a core pack must exist for
		 * the language we are installing.
		 */
		if (!$this->_corePack) {
			if (!JFile::exists($this->_extensionDir.DS.$folder.'.xml')) {
				JError::raiseWarning( 'SOME_ERROR_CODE', 'JInstallerLanguage::install: '.JText::_('No core pack exists for the language').' :'.$folder);
				return false;
			}
		}

		// If the language directory does not exist, lets create it
		if (!file_exists($this->_extensionDir)) {
			if (!$created = JFolder::create($this->_extensionDir)) {
				JError::raiseWarning(1, 'JInstallerLanguage::install: '.JText::_('Failed to create directory').' "'.$this->_extensionDir.'"');
				return false;
			}
		}

		/*
		 * If we created the language directory and will want to remove it if we
		 * have to roll back the installation, lets add it to the installation
		 * step stack
		 */
		if ($created) {
			$this->_stepStack[] = array ('type' => 'folder', 'path' => $this->_extensionDir);
		}

		// Copy all the necessary files
		if ($this->_parseFiles('files') === false) {
			// Install failed, rollback changes
			$this->_rollback();
			return false;
		}

		// Copy all the necessary font files to the common pdf_fonts directory
		$holdExtDir = $this->_extensionDir;
		$this->_extensionDir = JPath::clean($basePath.DS."language".DS.'pdf_fonts');
		$this->_allowOverwrite = true;
		if ($this->_parseFiles('fonts', 'language') === false) {
			// Install failed, rollback changes
			$this->_rollback();
			return false;
		}
		$this->_extensionDir = $holdExtDir;
		$this->_allowOverwrite = false;

		// Get the language description
		$e = & $root->getElementsByPath('description', 1);
		if (!is_null($e)) {
			$this->description = $this->_extensionName.'<p>'.$e->getText().'</p>';
		} else {
			$this->description = $this->_extensionName;
		}
		return true;
	}

	/**
	 * Custom uninstall method
	 *
	 * @access public
	 * @param int $id The id of the language to uninstall [ISO Tag]
	 * @param int $client The client id
	 * @return boolean True on success
	 * @since 1.5
	 */
	function uninstall($id, $client = 0)
	{
		/*
		 * For a language the id will be an RFC 3066 code, eg. en-GB which represents the 
		 * subfolder of the languages folder that the language resides in.
		 */
		$id = trim($id);
		if (!$id) {
			JError::raiseWarning('SOME_ERROR_CODE', 'JInstallerLanguage::uninstall: '.JText::_('Language id is empty, cannot uninstall files'));
			return false;
		}

		// Get some information about the client
		$clientVals = JApplicationHelper::getClientInfo($client, false);
		if ($clientVals === false) {
			JError::raiseWarning(1, 'JInstallerModule::uninstall: '.JText::_('Unknown client type').' ['.$client.']');
			return false;
		}

		// Create the full path to languages for the given client and remove the folder
		$path = JPath::clean($clientVals->path.DS.'language'.DS.$id);
		if (!JFolder::delete($path)) {
			JError::raiseWarning( 'SOME_ERROR_CODE', 'JInstallerLanguage::uninstall: '.JText::_('Unable to remove language directory'));
			return false;
		}
		return true;
	}
}
?>