<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Language  installer
 *
 * @author		Louis Landry <louis@webimagery.net>
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.1
 */
class JInstallerLanguage extends JInstaller {

	/**
	 * Custom install method
	 *
	 * @access public
	 * @param string $p_fromdir Directory from which to install the language
	 * @return boolean True on success
	 * @since 1.1
	 */
	function install( $p_fromdir ) {

		/*
		 * First lets set the installation directory, find and check the installation file and verify
		 * that it is the proper installation type
		 */
		if (!$this->preInstallCheck( $p_fromdir, 'language' )) {
			return false;
		}

		// Get the root node of the XML document
		$root =& $this->i_xmldoc->documentElement;

		/*
		 * Get the client application target
		 */
		if ($client = $root->getAttribute('client')) {

			// Attempt to map the client to a base path
			$clientVals = $this->_mapClient($client);
			if ($clientVals === false) {
				JError::raiseWarning( 1, 'JInstallerLanguage::install: ' . JText::_('Unknown client type').' ['.$client.']');
				return false;
			}
			$basePath = $clientVals['path'];
			$clientId = $clientVals['id'];
		} else {
			/*
			 * No client attribute was found so we assume the site as the client
			 */
			$client = 'site';
			$basePath = JPATH_SITE;
			$clientId = 0;	
		}

		/*
		 * Get the language name
		 */
		$e =& $root->getElementsByPath( 'name', 1);
		$this->i_extensionName = $e->getText();

		/*
		 * Get the Language tag [ISO tag, eg. en_GB]
		 */
		$e =& $root->getElementsByPath( 'metadata/tag', 1);
		$folder = $e->getText();
		
		/*
		 * Set the language installation path
		 */
		$this->i_extensionDir = JPath::clean( $basePath . DS ."language". DS .$folder );

		/*
		 * If the language directory does not exist, lets create it
		 */
		if (!file_exists($this->i_extensionDir) && !$created = JFolder::create($this->i_extensionDir)) {
			JError::raiseWarning( 1, 'JInstallerLanguage::install: ' . JText::_('Failed to create directory').' "'.$this->i_extensionDir.'"');
			return false;
		}

		/*
		 * If we created the language directory and will want to remove it if we
		 * have to roll back the installation, lets add it to the installation
		 * step stack
		 */
		if ($created) {
			$this->i_stepStack[] = array('type' => 'folder', 'path' => $this->i_extensionDir);
		}

		/*
		 * Copy all the necessary files
		 */
		if ($this->_parseFiles( 'files', 'language' ) === false) {

		 	// Install failed, rollback changes
		 	$this->_rollback();
			return false;
		}

		/*
		 * Copy all the necessary font files to the common pdf_fonts directory
		 */
		$holdExtDir = $this->i_extensionDir;
		$this->i_extensionDir = JPath::clean( $basePath . DS ."language". DS .'pdf_fonts' );
		$this->i_allowOverwrite = true;
		if ($this->_parseFiles( 'fonts', 'language') === false) {

		 	// Install failed, rollback changes
		 	$this->_rollback();
			return false;
		}
		$this->i_extensionDir = $holdExtDir;
		$this->i_allowOverwrite = false;

		/*
		 * Get the language description
		 */
		$e =& $root->getElementsByPath( 'description', 1 );
		if (!is_null($e)) {
			$this->i_description = $this->i_extensionName.'<p>'.$e->getText().'</p>';
		} else {
			$this->i_description = $this->i_extensionName;
		}

		/*
		 * Lastly, we will copy the setup file to its appropriate place.
		 */
		 if (!$this->_copyInstallFile(0)) {
			JError::raiseWarning( 1, 'JInstallerLanguage::install: ' . JText::_( 'Could not copy setup file' ));

		 	// Install failed, rollback changes
		 	$this->_rollback();
		 	return false;
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
	 * @since 1.1
	 */
	function uninstall( $id, $client = 'site' ) {
		
		/*
		 * For a language the id will be an ISO tag, eg. en_GB which represents the 
		 * subfolder of the languages folder that the language resides in.
		 */
		$id = trim( $id );
		if (!$id) {
			JError::raiseWarning( 'SOME_ERROR_CODE', 'JInstallerLanguage::uninstall: ' . JText::_('Language id is empty, cannot uninstall files') );
			return false;
		}
		
		/*
		 * Set the language path
		 */
		$clientVals = $this->_mapClient($client);
		if ($clientVals === false) {
			JError::raiseWarning( 1, 'JInstallerModule::install: ' . JText::_('Unknown client type').' ['.$root->getAttribute('client').']');
			return false;
		}
		$path = JPath :: clean($clientVals['path'] . DS . 'language' . DS . $id);

		/*
		 * Set some internal paths for smooth operation :)
		 */		
		$this->i_installDir = $path;
		$this->i_extensionDir = $path;

		/*
		 * See if there is an xml install file
		 */
		if (!$this->_findInstallFile()) {
			JError::raiseWarning( 'SOME_ERROR_CODE', 'JInstallerLanguage::uninstall: ' . JText::_( 'Could not find xml install file' ) );
			return false;
		}

		/*
		 * Remove all installed files (leaving files in this directory that
		 * might have already been there -- belong to another extension)
		 */
		if (!$this->_removeFiles('files')) {
			JError::raiseWarning( 'SOME_ERROR_CODE', 'JInstallerLanguage::uninstall: ' . JText::_( 'Could not remove all files' ) );
			return false;
		}

		/*
		 * If the folder is empty, let's delete it
		 */
		$files = JFolder::files($this->i_installDir);
		if (!count($files)) {
			JFolder::delete($this->i_installDir);
		}
		
		return true;
	}
}
?>