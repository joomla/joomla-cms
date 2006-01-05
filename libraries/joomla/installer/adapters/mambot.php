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
 * Mambot installer
 *
 * @package 	Joomla.Framework
 * @subpackage 	Installer
 * @since 1.1
 */
class JInstallerMambot extends JInstaller {

	/**
	 * Custom install method
	 *
	 * @access public
	 * @param string $p_fromdir Directory from which to install the mambot
	 * @return boolean True on success
	 * @since 1.1
	 */
	function install($p_fromdir) {

		// Get database connector object
		$db =& $this->_db;

		/*
		 * First lets set the installation directory, find and check the installation file and verify
		 * that it is the proper installation type
		 */
		if (!$this->preInstallCheck( $p_fromdir, 'mambot' )) {
			return false;
		}

		// Get the root node of the XML document
		$root =& $this->i_xmldoc->documentElement;

		/*
		 * Set the component name
		 */
		$e = &$root->getElementsByPath('name', 1);
		$this->i_extensionName = $e->getText();

		/*
		 * Set the mambot path
		 */
		$folder =& $root->getAttribute('group');
		$this->i_extensionDir = JPath::clean(JPATH_SITE.DS.'mambots'.DS.$folder);

		/*
		 * If the mambot directory does not exist, lets create it
		 */
		if (!file_exists($this->extensionDir) && !JFolder::create($this->extensionDir)) {
			JError::raiseWarning( 1, 'JInstallerMambot::install: ' . JText::_('Failed to create directory').' "'.$this->extensionDir.'"');
			return false;
		}

		/*
		 * Copy all the necessary files
		 */
		if ($this->_parseFiles('files', 'mambot', JText::_('No file is marked as module file')) === false) {
			JError::raiseWarning( 1, 'JInstallerMambot::install: ' . JText::_('Failed to copy files to').' "'.$this->i_extensionDir.'"');

			// Install failed, roll back changes
			$this->_rollback();
			return false;
		}

		/*
		 * Copy extra files
		 */
		$this->_parseFiles( 'media' );
		$this->_parseFiles( 'languages' );
		$this->_parseFiles( 'administration/languages' );

		/*
		 * Check to make sure a mambot by the same name is not already installed
		 */
		$query = 	"SELECT `id` " .
					"\nFROM `#__mambots` " .
					"\nWHERE element = '".$this->i_extensionName."'";

		$db->setQuery($query);
		if (!$db->query()) {
			JError::raiseWarning( 1, 'JInstallerMambot::install: ' . JText::_('SQL error').': '.$db->stderr(true));
			return false;
		}

		// If value is loaded then a mambot with the same name DOES exist
		$id = $db->loadResult();

		if (!$id) {
			$row = new JMambotModel($db);
			$row->name = $this->i_extensionName;
			$row->ordering = 0;
			$row->folder = $folder;
			$row->iscore = 0;
			$row->access = 0;
			$row->client_id = 0;
			$row->element = $this->i_extensionSpecial;

			if ($folder == 'editors') {
				$row->published = 1;
			}

			if (!$row->store()) {
			JError::raiseWarning( 1, 'JInstallerMambot::install: ' . JText::_('SQL error').': '.$db->stderr(true));

				// Install failed, rollback any changes
				$this->_rollback();
				return false;
			}

			/*
			 * Since we have created a mambot item, we add it to the installation step stack
			 * so that if we have to rollback the changes we can undo it.
			 */
			$this->i_stepStack[] = array ('type' => 'mambot', 'id' => $row->_db->insertid());

		} else {
			JError::raiseWarning( 1, 'JInstallerMambot::install: ' . JText::_('Mambot').' "'.$this->i_extensionName.'" '.JText::_('already exists!'));

			// Install failed, rollback any changes
			$this->_rollback();
			return false;
		}

		/*
		 * Get the mambot description
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
			JError::raiseWarning( 1, 'JInstallerMambot::install: ' . JText::_( 'Could not copy setup file' ));

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
	 * @param int $id The id of the mambot to uninstall
	 * @param int $client The client id
	 * @return boolean True on success
	 * @since 1.1
	 */
	function uninstall($id, $client = 0) {

		// Initialize variables
		$row = null;
		$retval = true;

		// Get database connector object
		$db = & $this->_db;

		/*
		 * First order of business will be to load the mambot object model from the database.
		 * This should give us the necessary information to proceed.
		 */
		$row = new JMambotModel($db);
		$row->load($id);

		/*
		 * Is the component we are trying to uninstall a core one?
		 * Because that is not a good idea...
		 */
		if ($row->iscore) {
            JError::raiseWarning( 'SOME_ERROR_CODE', 'JInstallerMambot::uninstall: '. sprintf( JText::_( 'WARNCORECOMPONENT' ), $row->name ) ."<br />". JText::_( 'WARNCORECOMPONENT2' ));
			return false;
		}

		/*
		 * Get the mambot folder so we can properly build the mambot path
		 */
		if (trim($row->folder) == '') {
            JError::raiseWarning( 'SOME_ERROR_CODE', 'JInstallerMambot::uninstall: '. JText::_('Folder field empty, cannot remove files'));
			return false;
		}

		/*
		 * Use the client id to determine which mambot path to use for the xml install file
		 */
		if (!$row->client_id) {
			$basepath = JPATH_SITE.DS.'mambots'.DS.$row->folder.DS;
		} else {
			$basepath = JPATH_ADMINISTRATOR.DS.'mambots'.DS.$row->folder.DS;
		}
		$this->i_extensionDir = $basepath;
		$xmlfile = $basepath.$row->element.'.xml';
		$folder = $row->folder;

		/*
		 * Now we will no longer need the mambot object, so lets delete it
		 */
		$row->delete($row->id);
		unset($row);
		
		/*
		 * Now is time to process the xml install file stuff...
		 */
		if (file_exists($xmlfile)) {
			$this->i_xmldoc = & JFactory::getXMLParser();
			$this->i_xmldoc->resolveErrors(true);

			if ($this->i_xmldoc->loadXML($xmlfile, false, true)) {

				/*
				 * Let's remove the files for the mambot
				 */		
				if ($this->_removeFiles( 'files' ) === false) {
					JError::raiseWarning( 1, 'JInstallerMambot::uninstall: '.JText::_( 'Unable to remove all files' ));
					$retval = false;
				}

				/*
				 * Remove other files
				 */
				$this->_removeFiles('images');
				$this->_removeFiles( 'media' );
				$this->_removeFiles( 'languages' );
				$this->_removeFiles( 'administration/languages' );

				/*
				 * Let's run the uninstall queries for the module
				 */		
				if ($this->_parseQueries( 'uninstall/queries' ) === false) {
					JError::raiseWarning( 1, 'JInstallerMambot::uninstall: '.$db->stderr(true));
					$retval = false;
				}

			} else {
				JError::raiseWarning( 1, 'JInstallerMambot::uninstall: '.JText::_( 'Could not load XML file' ) .' '. $xmlfile);
				$retval = false;
			}
		} else {
			JError::raiseWarning( 1, 'JInstallerMambot::uninstall: '.JText::_( 'File does not exist' ) .' '. $xmlfile);
			$retval = false;
		}

		/*
		 * If the folder is empty, let's delete it
		 */
		$files = JFolder::files($this->i_extensionDir);
		if (!count($files)) {
			JFolder::delete($this->i_extensionDir);
		}
		
		return $retval;
	}

	/**
	 * Custom rollback method
	 * 	- Roll back the mambot item
	 *
	 * @access private
	 * @param array $arg Installation step to rollback
	 * @return boolean True on success
	 * @since 1.1
	 */
	function _rollback_mambot($arg) {

		// Get database connector object
		$db =& $this->_db;

		/*
		 * Remove the entry from the #__mambot table
		 */
		$query = 	"DELETE " .
					"\nFROM `#__mambot` " .
					"\nWHERE id='".$arg['id']."'";

		$db->setQuery( $query );
		
		return ($db->query() !== false);
	}
}
?>
