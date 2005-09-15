<?php
/**
* @version $Id: templates.installer.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Templates
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* Template installer
* @package Joomla
* @subpackage Installer
*/
class mosTemplateInstaller extends mosInstaller {
	/** @var string The element type */
	var $elementType = 'template';

	/**
	 * @return string The base folder for the element
	 */
	function getBasePath() {
		return mosTemplate::getBasePath( $this->elementClient );
	}

	// --- INSTALLER METHODS ---

	/**
	 * Checks before installing
	 * @return boolean
	 */
	function _installCheck() {
		// set (which creates) the install path
		$path = $this->getBasePath() . strtolower( str_replace( ' ', '_', $this->elementName() ) );

		return $this->setElementDir( $path );
	}

	/**
	 * Installs the files for the element
	 * @protected
	 * @return boolean
	 */
	function _installFiles() {
		$xmlDoc =& $this->xmlDoc();
		$mosinstall =& $xmlDoc->documentElement;

		$files = $mosinstall->getElementsByTagName( 'filename' );

		$toCopy = array();
		$n = $files->getLength();
		for ($i = 0; $i < $n; $i++) {
			$basePath = $this->elementDir();
			$file =& $files->item( $i );
			$fileName = $file->getText();

			$parent =& $file->parentNode;
			if ($folder = $parent->getAttribute( 'folder' )) {
				$basePath .= $folder . DIRECTORY_SEPARATOR;
			}

			switch ($parent->getTagName()) {
				case 'files':
				case 'images':
				case 'css':
				default:
					$destFile = $basePath . $fileName;
					break;
			}
			$srcFile = $this->installDir() . $fileName;
			$toCopy[] = array( $srcFile, $destFile );
		}

		if ($this->copyFiles( $toCopy ) === false) {
			return false;
		}

		return $this->copySetupFile();
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
		// no data to install
		return true;
	}

	/**
	 * Routines after data processing
	 * @protected
	 * @return boolean
	 */
	function _installPostData() {
		$xmlDoc =& $this->xmlDoc();
		$mosinstall =& $xmlDoc->documentElement;

		if ($e = &$mosinstall->getElementsByPath( 'description', 1 )) {
			$this->setError( 0, $e->getText() );
		}
		return true;
	}

	// --- UNINSTALLER METHODS ---

	/**
	 * Checks before uninstalling
	 */
	function _uninstallCheck() {
		// check that we are deleting the active template
		$tMenuPos = new mosTemplatesMenu( $this->_db );
		$cur_template = $tMenuPos->getCurrent( $this->elementClient(), 0 );

		if ($cur_template == $this->elementName()) {
			$this->error( $_LANG->_( 'errorTemplateInUse' ) );
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Uninstalls the files for the element
	 * @protected
	 * @return boolean True if successful, false otherwise and an error is set
	 */
	function _uninstallFiles() {
		$path = $this->getBasePath() . $this->elementName();
		return $this->_deleteFolder( $path );
	}

	/**
	 * Uninstalls the data for the element
	 * @protected
	 * @return boolean True if successful, false otherwise and an error is set
	 */
	function _uninstallData() {
		// remove references from the templates_menu table
		$template = $this->_db->Quote( $this->elementName() );
		$client_id = $this->_db->Quote( $this->elementClient() );

		$query = 'DELETE FROM #__templates_menu
			WHERE template=' . $template . ' AND client_id=' . $client_id . ' AND menuid <> \'0\'';
		$this->_db->setQuery( $query );

		if ($this->_db->query()) {
			return true;
		} else {
			$this->error( $this->_db->getErrorMsg() );
		}

		return false;
	}
}
?>