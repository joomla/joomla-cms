<?php
/**
* @version $Id: mambots.installer.php 137 2005-09-12 10:21:17Z eddieajau $
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
class mosMambotInstaller extends mosInstaller {
	/** @var string The element type */
	var $elementType = 'mambot';
	/** @var int */
	var $mambotId = null;
	/** @var string */
	var $_botGroup = null;

	/**
	 * @return string The base folder for the element
	 */
	function getBasePath( $client=0 ) {
		global $mosConfig_absolute_path;
		return mosFS::getNativePath( $mosConfig_absolute_path . '/mambots' );
	}

	function botGroup( $val = null ) {
		return $this->setVar( '_botGroup', $val );
	}

	// --- INSTALLER METHODS ---

	/**
	 * Checks before installing
	 * @return boolean
	 */
	function _installCheck() {
		$xmlDoc =& $this->xmlDoc();
		$root =& $xmlDoc->documentElement;

		// mambots installed in a 'group' folder
		$folder = $root->getAttribute( 'group' );
		$this->botGroup( $folder );
		$path = $this->getbasePath( 1 ) . $folder;

		return $this->setElementDir( $path );
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

			if ($file->getAttribute( 'mambot' )) {
				$this->elementSpecial( $file->getAttribute( 'mambot' ) );
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
			$this->setError( 1, $_LANG->_( 'No file is marked as mambot file' ) );
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

		$this->_db->setQuery( "SELECT id FROM #__mambots WHERE element = '" . $this->elementName() . "'" );
		if (!$this->_db->query()) {
			$this->setError( 1, $_LANG->_( 'SQL error' ) .': ' . $database->stderr( true ) );
			return false;
		}

		$id = $this->_db->loadResult();

		if ($id && !$this->allowOverwrite()) {
			$this->setError( 1, 'Mambot "' . $this->elementName() . '" '. $_LANG->_( 'already exists' ) );
			return false;
		} else {
			$row = new mosMambot( $this->_db );
			$row->name		= $this->elementName();
			$row->ordering	= 0;
			$row->folder	= $this->botGroup();
			$row->iscore	= 0;
			$row->access	= 0;
			$row->client_id	= 0;
			$row->element	= $this->elementSpecial();

			if (!$row->store()) {
				$this->setError( 1, $_LANG->_( 'SQL error' ) .': ' . $row->getError() );
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

		$obj = new mosMambot( $this->_db );
		if (!$obj->load( $id )) {
			$this->setError( 1, $_LANG->_( 'errorMambotNotFound' ) );
			return false;
		}
		$this->elementId = $id;

		mosFS::load( '@domit' );
		$this->elementDir( mosFS::getNativePath( $this->getBasePath() . $obj->folder  ) );
		$file = $this->installXML( $this->elementDir() . $obj->element . '.xml' );

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

		$this->log( $_LANG->_( 'DELETE MAMBOT' ) );
		$obj = new mosMambot( $this->_db );
		if (!$obj->delete( $this->elementId )) {
			$this->log( '...' . $_LANG->_( 'Failed' ), true );
			$this->error( $this->_db->stderr() );
			return false;
		}

		$this->log( '...' . $_LANG->_( 'Done' ), true );
		return true;
	}
}
?>