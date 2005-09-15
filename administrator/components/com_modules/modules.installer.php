<?php
/**
* @version $Id: modules.installer.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Mambots
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* Component installer
* @package Joomla
* @subpackage Installer
*/
class mosModuleInstaller extends mosInstaller {
	/** @var string The element type */
	var $elementType = 'module';
	/** @var int */
	var $elementId = null;

	/**
	 * @return string The base folder for the element
	 */
	function getBasePath() {
		return mosModule::getBasePath( $this->elementClient() );
	}

	// --- INSTALLER METHODS ---

	/**
	 * Checks before installing
	 * @return boolean True if the base path was created
	 */
	function _installCheck() {
		return $this->setElementDir( $this->getbasePath() );
	}

	/**
	 * Installs the files for the element
	 * @protected
	 * @return boolean
	 */
	function _installFiles() {
		global $_LANG;
		$this->log( $_LANG->_( 'COPY FILES' ) );

		$xmlDoc =& $this->xmlDoc();
		$root =& $xmlDoc->documentElement;

		$files = $root->getElementsByTagName( 'filename' );

		$basePath = $this->elementDir();

		$toCopy = array();
		$n = $files->getLength();
		for ($i = 0; $i < $n; $i++) {
			$file =& $files->item( $i );
			$fileName = $file->getText();

			if ($file->getAttribute( 'module' )) {
				$this->elementSpecial( $file->getAttribute( 'module' ) );
			}

			$parent =& $file->parentNode;

			// determine the base path for these files
			$destFile = $basePath . $fileName;

			$srcFile = $this->installDir();
			if ($folder = $parent->getAttribute( 'folder' )) {
				$srcFile .= $folder . DIRECTORY_SEPARATOR;
			}
			$srcFile .= $fileName;

			$toCopy[] = array( $srcFile, $destFile );
		}

		if ($this->elementSpecial() == '') {
			$this->setError( 1, $_LANG->_( 'No file is marked as module file' ) );
			return false;
		}

		if ($this->copyFiles( $toCopy ) === false) {
			return false;
		}

		$toCopy = array();
		// install file
		$toCopy[] = array( $this->installXML(), $basePath . basename( $this->installXML() ) );

		if ($this->copyFiles( $toCopy ) === false) {
			return false;
		}

		return true;
	}

	/**
	 * Routines before data processing
	 * @protected
	 * @return boolean
	 */
	function _installPreData() {
		return true;
	}

	/**
	 * Installs the data for the element
	 * @protected
	 * @return boolean
	 */
	function _installData() {
		return true;
	}

	/**
	 * Routines after data processing
	 * @protected
	 * @return boolean
	 */
	function _installPostData() {
		global $_LANG;

		$query = 'SELECT id' .
				' FROM #__modules' .
				' WHERE module = ' . $this->_db->Quote( $this->elementSpecial() ) .
				' AND client_id = ' . $this->_db->Quote( $this->elementClient() )
				;
		$this->_db->setQuery( $query );
		if (!$this->_db->query()) {
			$this->setError( 1, $_LANG->_( 'SQL error' ) .': ' . $this->_db->stderr( true ) );
			return false;
		}

		$id = $this->_db->loadResult();

		if ($id && !$this->allowOverwrite()) {
			$this->setError( 1, 'Module "' . $this->elementName() . '" '. $_LANG->_( 'already exists' ) );
			return false;
		} else {
			$row = new mosModule( $this->_db );
			$row->title		= $this->elementName();
			$row->ordering = 0;
			$row->position = 'left';
			$row->showtitle = 1;
			$row->iscore = 0;
			$row->access = ($this->elementClient() == '1' ? 99 : 0);
			$row->client_id = $this->elementClient();
			$row->module = $this->elementSpecial();

			if (!$row->store()) {
				$this->setError( 1, $_LANG->_( 'SQL error' ) .': ' . $row->getError() );
				return false;
			}

			$query = 'INSERT INTO #__modules_menu VALUES (' . $this->_db->Quote( $row->id ) . ', 0)';
			$this->_db->setQuery( $query );
			if(!$this->_db->query()) {
				$this->setError( 1, $_LANG->_( 'SQL error' ) .': ' . $this->_db->getError( true ) );
				return false;
			}
		}

		$xmlDoc =& $this->xmlDoc();
		$root =& $xmlDoc->documentElement;

		if ($e = &$root->getElementsByPath( 'description', 1 )) {
			$desc = $e->getText();
		}
		$this->setError( 0, $desc );

		return true;
	}

	// --- UNINSTALLER METHODS ---

	/**
	 * Checks before uninstalling
	 */
	function _uninstallCheck() {
		global $mainframe, $_LANG;

		$this->log( $_LANG->_( 'CHECKING' ) );
		$id = intval( $this->elementName() );

		$obj = new mosModule( $this->_db );
		if (!$obj->load( $id )) {
			$this->setError( 1, $_LANG->_( 'errorModuleNotFound' ) );
			return false;
		}
		$this->elementId = $id;

		mosFS::load( '@domit' );
		$this->elementDir( $this->getBasePath() );
		$file = $this->installXML( $this->elementDir() . $obj->module . '.xml' );

		if (!file_exists( $file )) {
			$this->setError( 1, $_LANG->_( 'errorXMLNotFound' ) );
			return true;
		}

		$xmlDoc = null;
		if (!$this->isPackageFile( $file, $xmlDoc )) {
			$this->setError( 1, $_LANG->_( 'errorXMLNotFound' ) );
			return false;
		}

		$this->installXML( $file );

		$this->xmlDoc( $xmlDoc );
		$root =& $xmlDoc->documentElement;

		if ($obj->iscore) {
			$this->setError( 1, $_LANG->_( 'errorElementIsCore' ) );
			return false;
		}

		$this->log( '...' . $_LANG->_( 'Done' ), true );
		return true;
	}

	/**
	 * Uninstalls the files for the element
	 * @protected
	 * @return boolean True if successful, false otherwise and an error is set
	 */
	function _uninstallFiles() {
		global $_LANG;

		if (is_null( $this->xmlDoc() )) {
			return true;
		}

		$this->log( $_LANG->_( 'DELETE FILES' ) );
		$xmlDoc =& $this->xmlDoc();
		if (is_null( $xmlDoc )) {
			return true;
		}

		$root =& $xmlDoc->documentElement;

		$basePath = $this->elementDir();
		$eList = $root->getElementsByPath( 'files/filename' );

		$folders = array();
		$n = $eList->getLength();
		for ($i = 0; $i < $n; $i++) {
			$file = $eList->item( $i );
			$name = $file->getText();

			$dir = dirname( $name );
			if ($dir <> '' && $dir <> '.' && $dir <> '..') {
				if (!in_array( $dir, $folders )) {
					$this->log( $basePath . $dir );
					if (!mosFS::deleteFolder( $basePath . $dir )) {
						$this->log( '...' . $_LANG->_( 'Failed' ), true );
					}
					$folders[] = $dir;
				}
			} else {
				$this->log( $basePath . $name );
				if (!mosFS::deleteFile( $basePath . $name )) {
						$this->log( '...' . $_LANG->_( 'Failed' ), true );
				}
			}
		}
		$this->log( $this->installXML() );
		if (!mosFS::deleteFile( $this->installXML() )) {
			$this->log( '...' . $_LANG->_( 'Failed' ), true );
		}

		return true;
	}

	/**
	 * Uninstalls the data for the element
	 * @protected
	 * @return boolean True if successful, false otherwise and an error is set
	 */
	function _uninstallData() {
		global $_LANG;

		$this->log( $_LANG->_( 'DELETE MODULES' ) );

		$obj = new mosModule( $this->_db );
		if (!$obj->delete( $this->elementId )) {
			$this->error( $this->_db->stderr() );
			return false;
		}

		$query = "SELECT id FROM #__modules WHERE module = '$obj->module'";
		$this->_db->setQuery( $query );
		$modules = $this->_db->loadResultArray();

		if (count( $modules )) {
			$query = "DELETE FROM #__modules_menu"
			. "\n WHERE moduleid IN ('".implode( "','", $modules ) ."')"
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->setError( 1, $_LANG->_( 'SQL error' ) .': ' . $this->_db->getError( true ) );
			}
		}

		$query = "DELETE FROM #__modules WHERE module = '$obj->module'";
		$this->_db->setQuery( $query );
		if (!$this->_db->query()) {
			$this->setError( 1, $_LANG->_( 'SQL error' ) .': ' . $this->_db->getError( true ) );
		}

		$this->log( '...' . $_LANG->_( 'Done' ), true );
		return true;
	}
}
?>