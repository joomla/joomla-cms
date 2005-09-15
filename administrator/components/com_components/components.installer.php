<?php
/**
* @version $Id: components.installer.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Components
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
* @package Mambo
* @subpackage Installer
*/
class mosComponentInstaller extends mosInstaller {
	/** @var string The element type */
	var $elementType = 'component';
	/** @var int */
	var $componentId = null;
	/** @var string */
	var $comName = null;
	/** @var string */
	var $i_installfile = null;

	/**
	 * @return string The base folder for the element
	 */
	function getBasePath( $client=0 ) {
		return mosComponent::getBasePath( $client );
	}

	function installFile( $p_installfile = null ) {
		return $this->setVar( 'i_installfile', $p_installfile );
	}

	// --- INSTALLER METHODS ---

	/**
	 * Checks before installing
	 * @return boolean
	 */

	function _installCheck() {
		// replace spaces in name with nothing
		$this->comName = 'com_' . strtolower( str_replace( ' ', '', $this->elementName() ) );

		$xmlDoc =& $this->xmlDoc();
		$root =& $xmlDoc->documentElement;

		// if has an admin component, copy the xml file there
		if ($root->getElementsByTagName( 'administration' )) {
			$path = $this->getbasePath( 1 ) . $this->comName;
		} else {
			$path = $this->getbasePath( 0 ) . $this->comName;
		}

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

		$basePathSite = $this->getbasePath( 0 ) . $this->comName . DIRECTORY_SEPARATOR;
		$basePathAdmin = $this->getbasePath( 1 ) . $this->comName . DIRECTORY_SEPARATOR;

		$hasAdmin = false;
		$toCopy = array();
		$n = $files->getLength();
		for ($i = 0; $i < $n; $i++) {
			$file =& $files->item( $i );
			$fileName = $file->getText();

			$parent =& $file->parentNode;
			$gparent =& $parent->parentNode;

			// determine the base path for these files
			if ($gparent->getTagName() == 'administration') {
				$basePath = $basePathAdmin;
				$hasAdmin = true;
			} else {
				$basePath = $basePathSite;
			}

			switch ($parent->getTagName()) {
				case 'images':	// legacy
				case 'files':
				default:
					$destFile = $basePath . $fileName;
					break;
			}

			$srcFile = $this->installDir();
			if ($folder = $parent->getAttribute( 'folder' )) {
				$srcFile .= $folder . DIRECTORY_SEPARATOR;
			}
			$srcFile .= $fileName;

			$toCopy[] = array( $srcFile, $destFile );
		}

		if ($this->copyFiles( $toCopy ) === false) {
			return false;
		}

		$toCopy = array();
		// is there an installfile
		$file = $root->getElementsByPath( 'installfile', 1 );
		if ($file) {
			$fileName = $file->getText();
			$destFile = $basePathAdmin . $fileName;
			if (!mosFS::file_exists( $destFile )) {
				$srcFile = $this->installDir() . $fileName;
				$toCopy[] = array( $srcFile, $destFile );
			}
			$this->installFile( $destFile );
		}

		// is there an uninstallfile
		$file = $root->getElementsByPath( 'uninstallfile', 1 );
		if ($file) {
			$fileName = $file->getText();
			$destFile = $basePathAdmin . $fileName;
			if (!mosFS::file_exists( $destFile )) {
				$srcFile = $this->installDir() . $fileName;
				$toCopy[] = array( $srcFile, $destFile );
			}
		}

		// install file
		if ($hasAdmin) {
			$toCopy[] = array( $this->installXML(), $basePathAdmin . basename( $this->installXML() ) );
		} else {
			$toCopy[] = array( $this->installXML(), $basePathAdmin . basename( $this->installXML() ) );
		}

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
		global $_LANG;
		$this->log( $_LANG->_( 'INSTALL QUERIES' ) );

		$xmlDoc =& $this->xmlDoc();
		$root =& $xmlDoc->documentElement;

		$e = &$root->getElementsbyPath( 'install/queries', 1 );
		if ($e) {
			$queries = $e->childNodes;
			$n = $e->childCount;
			for ($i = 0; $i < $n; $i++) {
				$sql = $queries[$i]->getText();
				$this->_db->setQuery( $sql );

				if ($this->_db->query()) {
					$this->log( $_LANG->_( 'Success' ) );
				} else {
					$this->log( $_LANG->_( 'Error' ) . $this->_db->getErrorMsg() );
					//$this->error( $this->_db->getErrorMsg() );
					//return false;
				}
			}
		} else {
		    $this->log( $_LANG->_( 'None found' ) );
		}

		return true;
	}

	/**
	 * Routines after data processing
	 * @protected
	 * @return boolean
	 */
	function _installPostData() {
		global $_LANG;

		$xmlDoc =& $this->xmlDoc();
		$root =& $xmlDoc->documentElement;

		if ($this->_installMenus( $root )) {
			if ($e = &$root->getElementsByPath( 'description', 1 )) {
				$desc = $e->getText();
			}

			if ($this->installFile()) {
				if (is_file( $this->installFile() )) {
					$this->log( $_LANG->_( 'INSTALL FILE' ) );
					ob_start();
					require_once( $this->installFile() );
					ob_end_clean();
					$ret = com_install();
					if ($ret != '') {
						$desc .= $ret;
					}
				}
			}

			$this->setError( 0, $desc );

			return true;
		}

		return false;
	}

	/**
	 * Installs the admin menu items
	 * @param object Root xml element
	 * @return boolean
	 */
	function _installMenus( &$root ) {
		global $_LANG;

		$this->log( $_LANG->_( 'INSTALLING MENUS' ) );
		$admin = $root->getElementsByPath( 'administration', 1 );
		if (!$admin) {
			return true;
		}
		$menus = $admin->getElementsByTagName( 'menu' );

		$parentId = 0;
		$subMenuOrdering = 0;
		$n = $menus->getLength();
		for ($i = 0; $i < $n; $i++) {
			$menu =& $menus->item( $i );
			$menuName = $menu->getText();
			$parent =& $menu->parentNode;

			$com = new mosComponent( $this->_db );
			$com->name		= $menuName;
			$com->menuid	= 0;
			$com->iscore	= 0;
			$com->option	= $this->comName;
			$com->admin_menu_alt = $menuName;

			// menu image (new in 4.5.3)
			if ($a = $menu->getAttribute( 'image' )) {
				$com->admin_menu_img	= $a;
			} else {
				$com->admin_menu_img	= 'js/ThemeOffice/component.png';
			}

			// determine the link
			if ($a = $menu->getAttribute( 'task' )) {
				$com->admin_menu_link = 'option=' . $this->comName . '&task=' . $a;
			} else if ($a = $menu->getAttribute( 'act' )) {
				$com->admin_menu_link = 'option=' . $this->comName . '&act=' . $a;
			} else if ($a = $menu->getAttribute( 'link' )) {
				$com->admin_menu_link = $a;
			} else {
				$com->admin_menu_link = 'option=' . $this->comName;
			}

			switch ($parent->getTagName()) {
				case 'submenu':
					if ($parentId > 0) {
						$com->link		= '';
						$com->parent	= $parentId;
						$com->ordering	= $subMenuOrdering++;
					} else {
						$this->log( $_LANG->sprintf( 'errorMenuNotCreated', $menuName ) );
					}

					if ($this->_menuExists( $com )) {
						$this->log( $_LANG->sprintf( 'subMenuExists', $menuName ) );
					} else {
						if (!$com->store()) {
							$this->setError( 1, $this->_db->getErrorMsg() );
							return false;
						}
						$this->log( '....' . $menuName );
					}
					break;

				default:
					$com->link				= 'option=' . $this->comName;
					$com->parent			= 0;
					$com->ordering			= 0;

					if ($id = $this->_menuExists( $com )) {
						$this->log( $_LANG->sprintf( 'mainMenuExists', $menuName ) );
						$com->id = $id;
					} else {
						if (!$com->store()) {
							$this->setError( 1, $this->_db->getErrorMsg() );
							return false;
						}
						$this->log( '..' . $menuName );
					}
					$parentId = $com->id;
					break;
			}
		}

		return true;
	}

	/**
	 * Checks if menu exists
	 * @param object
	 * @return int The number of matching menus found
	 */
	function _menuExists( &$com ) {
		if ($com->parent > 0) {
			$sql = 'SELECT id' .
					' FROM #__components' .
					' WHERE (admin_menu_link = ' . $this->_db->Quote( $com->admin_menu_link ) . ' AND parent > 0)';
		} else {
			$sql = 'SELECT id' .
					' FROM #__components' .
					' WHERE (' . $this->_db->NameQuote( 'option' ) . ' = ' . $this->_db->Quote( $com->option ) . ' AND parent = 0)';
		}
		$this->_db->setQuery( $sql );
		$result = $this->_db->loadResult();
		return $result;
	}

	// --- UNINSTALLER METHODS ---

	/**
	 * Checks before uninstalling
	 */
	function _uninstallCheck() {
		global $mainframe, $_LANG;

		$id = intval( $this->elementName() );

		$obj = new mosComponent( $this->_db );
		if (!$obj->load( $id )) {
			$this->error( $_LANG->_( 'errorElementNotFound' ) );
			return false;
		}
		$this->componentId = $id;
		$this->elementName( $obj->option );

		mosFS::load( '@domit' );
		$file = $mainframe->getPath( 'com_xml', $obj->option );

		$xmlDoc = null;
		if (!$this->isPackageFile( $file, $xmlDoc )) {
			$this->error( $_LANG->_( 'errorXMLNotFound' ) );
			return false;
		}

		$this->installXML( $file );

		$this->xmlDoc( $xmlDoc );
		$root =& $xmlDoc->documentElement;

		if ($obj->iscore) {
			$this->error( $_LANG->_( 'errorElementIsCore' ) );
			return false;
		}

		// include the uninstall file
		if ($e = $root->getElementsByPath( 'uninstallfile', 1 )) {
			if ($file = trim( $e->getText() )) {
				$path = $this->getBasePath( 1 ) . '/' . $file;
				if (is_file( $path )) {
					require_once( $path );
					$ret = com_uninstall();
					if ($ret != '') {
						$this->setError( 0, $ret );
						return false;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Uninstalls the files for the element
	 * @protected
	 * @return boolean True if successful, false otherwise and an error is set
	 */
	function _uninstallFiles() {
		$xmlDoc =& $this->xmlDoc();
		$root =& $xmlDoc->documentElement;

		$e = $root->getElementsByPath( 'administration/files', 1 );
		$hasAdmin = ($e && $e->childCount > 0);

		$e = $root->getElementsByPath( 'files', 1 );
		$hasSite = ($e && $e->childCount > 0);

		if ($hasSite) {
			$path = $this->getBasePath( 0 ) . $this->elementName();
			if (!$this->_deleteFolder( $path )) {
				return false;
			}
 		}
		if ($hasAdmin) {
			$path = $this->getBasePath( 1 ) . $this->elementName();
			if (!$this->_deleteFolder( $path )) {
				return false;
			}
 		}

		return true;
	}

	/**
	 * Uninstalls the data for the element
	 * @protected
	 * @return boolean True if successful, false otherwise and an error is set
	 */
	function _uninstallData() {
		// remove references from the templates_menu table
		$id = $this->componentId;
		if ($id == 0) {
			$this->error( 'errorComponentNotSet' );
		}

		// Delete menu items
		$sql = 'DELETE FROM #__components' .
				' WHERE id = ' . $this->_db->Quote( $id ) .
				' OR parent= ' . $this->_db->Quote( $id );
		$this->_db->setQuery( $sql );

		if (!$this->_db->query()) {
			$this->error( $this->_db->getErrorMsg() );
			return false;
		}

		$xmlDoc =& $this->xmlDoc();
		$root =& $xmlDoc->documentElement;

		$e = &$root->getElementsbyPath( 'uninstall/queries', 1 );
		if ($e) {
			$queries = $e->childNodes;
			$n = $e->childCount;
			for ($i = 0; $i < $n; $i++) {
				$sql = $queries[$i]->getText();
				$this->_db->setQuery( $sql );

				if (!$this->_db->query()) {
					$this->error( $this->_db->getErrorMsg() );
					return false;
				}
			}
		}

		return true;
	}
}
?>
