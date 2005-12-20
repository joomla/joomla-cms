<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Installer
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Load the base line installer class
jimport('joomla.installers.installer');


class JInstallerFactory {
	var $_class 	= null;		// The class
	var $_result 	= null; 	// The last result
	var $_type 	= null; 	// Its type e.g. component

	function &JInstallerFactory($name=null) {
		if($name) {
			return $this->createClass($name);
		}
		return false;
	}

	function &getClass() {
		return $this->_class;
	}

	function getType() {
		return $this->_type;
	}

	function &createClass($name) {
		$success = false;
		$this->_type = $name;
		switch($name) {
			case 'component':
				jimport('joomla.installers.component');
		                $this->_class = new mosInstallerComponent();
				$success = true;
				break;
			case 'module':
				jimport('joomla.installers.module');
				$this->_class = new mosInstallerModule();
				$success = true;
				break;
			case 'mambot':
				jimport('joomla.installers.mambot');
				$this->_class = new mosInstallerMambot();
				$success = true;
				break;
			case 'template':
				jimport('joomla.installers.template');
				$this->_class = new mosInstallerTemplate();
				$success = true;
				break;
			case 'language':
				jimport('joomla.installers.language');
				$this->_class = new mosInstallerLanguage();
				$success = true;
				break;
			default:
				die("<p>Attempt to create a '$name' installer failed</p>");
				break;
		}
		$this->_result = $success;
		return $this->_class;
	}

	function autoInstall($method,$data) {
		// Provide an auto install system. Generic should work for all cases,
		// but a switch statement is provided for future possibilities
		switch($this->_type) {
			default: return $this->autoInstallGeneric($method, $data); break;
		}
	}



	function &webInstall($url) {
		$processor = new mosInstaller();
		$location = $processor->downloadPackage($url);
		if(!$location) {
			return $processor;
		}
		JPath::setPermissions($location);
		$processor->extractArchive();
		$type = $this->detectType($processor->unpackDir());
		$this->createClass($type);
		//$this->_class->allowOverwrite(1);
		return $this->autoInstallGeneric('directory',$processor->unpackDir());
	}

	function detectType( $location ) {
		$found = false;
		// Search the install dir for an xml file
		$files = JFolder::files( $location, '\.xml$', true, true );

		if (count( $files ) > 0) {

			foreach ($files as $file) {
				$xmlDoc =& JFactory::getXMLParser();
				$xmlDoc->resolveErrors( true );

				if (!$xmlDoc->loadXML( $file, false, true )) {
					return false;
				}
				$root = &$xmlDoc->documentElement;

				if ($root->getTagName() != "mosinstall") {
					continue;
				}
//				echo "<p>Looking at file $file, I consider it to be a valid installer file.</p>";
				return $root->getAttribute( 'type' );

			}
//			$this->setError( 1, JText::_( 'ERRORNOTFINDMAMBOXMLSETUPFILE' ) );
			return false;
		} else {
//			$this->setError( 1, JText::_( 'ERRORNOTFINDXMLSETUPFILE' ) );
			return false;
		}
		return false;
	}

	function &autoInstallGeneric($method=null,$data=null,$type=null) {
		$msg = "SUCCESS";
		if($type) {
			$this->createClass($type);
		}
		$installer = $this->_class; // Class should have been set already by initializer or done manually
		switch($method) {
			case 'upload':
				$userfile = mosGetParam( $_FILES, 'userfile', null );
				if (!$installer->uploadArchive( $userfile )) {
					$msg = $installer->error();
				}
				if (!$installer->extractArchive()) {
					$msg = $installer->error();
				}
				break;
			default:
				$extractdir = $data;
				$installer->installDir( $extractdir );
                		// Try to find the correct install dir. in case that the package have subdirs
                		// Save the install dir for later cleanup
                		$filesindir = mosReadDirectory( $installer->installDir(), '' );

                		if (count( $filesindir ) == 1) {
                		        if (is_dir( $extractdir . $filesindir[0] )) {
                               			$installer->installDir( mosPathName( $extractdir . $filesindir[0] ) );
                        		}
                		}
				break;
                }
                if (!$installer->install()) {
		//	$installer->cleanupInstall();
			$msg = $installer->error();
                }
		$installer->msg = $msg;
		return $installer;
	}
}

