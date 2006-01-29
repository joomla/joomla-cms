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
 * Template installer
 *
 * @package 	Joomla.Framework
 * @subpackage 	Installer
 * @since 1.1
 */
class JInstallerTemplate extends JInstaller {

	/**
	 * Custom install method
	 *
	 * @access public
	 * @param string $p_fromdir Directory from which to install the template
	 * @return boolean True on success
	 * @since 1.1
	 */
	function install($p_fromdir) {

		/*
		 * First lets set the installation directory, find and check the installation file and verify
		 * that it is the proper installation type
		 */
		if (!$this->preInstallCheck($p_fromdir, 'template')) {
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
				JError::raiseWarning( 1, 'JInstallerTemplate::install: ' . JText::_('Unknown client type').' ['.$client.']');
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
		 * Set the template name
		 */
		$e = & $root->getElementsByPath('name', 1);
		$this->i_extensionName = $e->getText();
		
		/*
		 * Set the template installation directory
		 */
		$this->i_extensionDir = JPath :: clean($basePath.DS.'templates'.DS.strtolower(str_replace(" ", "_", $this->i_extensionName)));

		/*
		 * If the template directory does not exists, lets create it
		 */
		if (!file_exists($this->extensionDir) && !JFolder::create($this->extensionDir)) {
			JError::raiseWarning( 1, 'JInstallerTemplate::install: ' . JText::_('Failed to create directory').' "'.$this->extensionDir.'"');
			return false;
		}

		/*
		 * Since we created the template directory and will want to remove it if we have to roll back
		 * the installation, lets add it to the installation step stack
		 */
		$this->i_stepStack[] = array('type' => 'folder', 'path' => $this->i_extensionDir);

		/*
		 * Copy all necessary files
		 */
		if ($this->_parseFiles('files') === false) {
			JError::raiseWarning( 1, 'JInstallerTemplate::install: ' . JText::_('Failed to create directory').' "'.$this->extensionDir.'"');

			// Install failed, rollback any changes
			$this->_rollback();
			return false;
		}
		if ($this->_parseFiles('images') === false) {
			JError::raiseWarning( 1, 'JInstallerTemplate::install: ' . JText::_('Failed to create directory').' "'.$this->extensionDir.'"');

			// Install failed, rollback any changes
			$this->_rollback();
			return false;
		}
		if ($this->_parseFiles('css') === false) {
			JError::raiseWarning( 1, 'JInstallerTemplate::install: ' . JText::_('Failed to create directory').' "'.$this->extensionDir.'"');

			// Install failed, rollback any changes
			$this->_rollback();
			return false;
		}
		if ($this->_parseFiles('media') === false) {
			JError::raiseWarning( 1, 'JInstallerTemplate::install: ' . JText::_('Failed to create directory').' "'.$this->extensionDir.'"');

			// Install failed, rollback any changes
			$this->_rollback();
			return false;
		}

		/*
		 * Get the template description
		 */
		$e =& $root->getElementsByPath( 'description', 1 );
		if (!is_null($e)) {
			$this->i_description = $this->i_extensionName.'<p>'.$e->getText().'</p>';
		} else {
			$this->i_description = $this->i_extensionName;
		}

		/*
		 * Now, lets create the necessary template positions
		 */
		$templatePositions = & $root->getElementsByPath('install/positions', 1);
		if (!is_null($templatePositions)) {
			$positions = $templatePositions->childNodes;
			foreach ($positions as $position) {
				
				$id = $this->_createTemplatePosition($position->getText());

				if ($id) {
					/*
					 * Since we have created a template positions item, we add it to the installation step stack
					 * so that if we have to rollback the changes we can undo it.
					 */
					$step = 
					$this->i_stepStack[] = array ('type' => 'position', 'id' => $id);
				}
			}
		}

		/*
		 * Lastly, we will copy the setup file to its appropriate place.
		 */
		 if (!$this->_copyInstallFile(0)) {
			JError::raiseWarning( 1, 'JInstallerTemplate::install: ' . JText::_( 'Could not copy setup file' ));

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
	 * @param int $id The id of the template to uninstall
	 * @param int $client The client id
	 * @return boolean True on success
	 * @since 1.1
	 */
	function uninstall($id, $client) {

		// Initialize variables
		$retval = true;
		
		/*
		 * For a template the id will be the template name which represents the 
		 * subfolder of the templates folder that the template resides in.
		 */
		$id = trim( $id );
		if (!$id) {
			JError::raiseWarning( 'SOME_ERROR_CODE', 'JInstallerTemplate::uninstall: ' . JText::_('Template id is empty, cannot uninstall files') );
			return false;
		}
		
		/*
		 * Set the template path
		 */
		$clientVals = $this->_mapClient($client);
		if ($clientVals === false) {
			JError::raiseWarning( 1, 'JInstallerTemplate::uninstall: ' . JText::_('Unknown client type').' ['.$root->getAttribute('client').']');
			return false;
		}
		$path = JPath :: clean($clientVals['path'] . DS . 'templates' . DS . $id);

		/*
		 * Set some internal paths for smooth operation :)
		 */		
		$this->i_installDir = $path;
		$this->i_extensionDir = $path;

		/*
		 * See if there is an xml install file
		 */
		if (!$this->_findInstallFile()) {
			JError::raiseWarning( 'SOME_ERROR_CODE', 'JInstallerTemplate::uninstall: ' . JText::_( 'Could not find xml install file' ) );
			return false;
		}
		
		/*
		 * Remove any files added to the Joomla images/ folder
		 */
		if ($this->_removeFiles('media') === false) {
			JError::raiseWarning( 'SOME_ERROR_CODE', 'JInstallerTemplate::uninstall: ' . JText::_('Unable to remove media files') );
			$retval = false;
		}
		
		/*
		 * Delete the template directory
		 */
		if (JFolder::exists($path)) {
			$retval = JFolder::delete(JPath::clean($path));
		} else {
			JError::raiseWarning( 'SOME_ERROR_CODE', 'JInstallerTemplate::uninstall: ' . JText::_('Directory does not exist, cannot remove files') );
			$retval = false;
		}

		return $retval;
	}

	/**
	 * Creates a new template position if it doesn't exist already
	 * 
	 * @access private
	 * @param string $position Template position to create
	 * @return mixed Template position id (int) if a position was inserted or boolean false otherwise
	 * @since 1.1
	 */
	function _createTemplatePosition($position) {

		/*
		 * Get the global database connector object
		 */
		$db = $this->i_db;

		// Initialize variable
		$retval = false;

		if ($position) {
			$db->setQuery(	"SELECT id " .
							"\nFROM #__template_positions " .
							"\nWHERE position = '$position'");
			$db->Query();
			if (!$db->getNumRows()) {
				$db->setQuery("INSERT INTO #__template_positions "."\nVALUES (0,'$position','')");
				if ($db->Query() !== false) {
					$retval = $db->insertid();	
				}
			}
		}
		return $retval;
	}

	/**
	 * Custom rollback method
	 * 	- Roll back the template position item
	 *
	 * @access private
	 * @param array $arg Installation step to rollback
	 * @return boolean True on success
	 * @since 1.1
	 */
	function _rollback_position($arg) {

		// Get database connector object
		$db =& $this->i_db;

		/*
		 * Remove the entry from the #__template_positions table
		 */
		$query = 	"DELETE " .
					"\nFROM `#__template_positions` " .
					"\nWHERE id='".$arg['id']."'";

		$db->setQuery( $query );
		
		return ($db->query() !== false);
	}
}
?>
