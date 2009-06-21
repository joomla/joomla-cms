<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
 
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport( 'joomla.filesystem.file' );
jimport( 'joomla.installer.extension' );

/**
 * Joomla! Package Manifest File
 *
 * @author 		Sam Moffatt <pasamio@gmail.com> 
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.6
 */
class JPackageManifest extends JObject {
	var $name = '';		
	var $packagename = '';
	var $url = '';
	var $description = '';
	var $packager = '';
	var $packagerurl = '';
	var $update = '';
	var $version = '';
	var $filelist = Array();
	var $manifest_file = '';
	
	function __construct($xmlpath='') {
		if(strlen($xmlpath)) $this->loadManifestFromXML($xmlpath);
	}
	
	function loadManifestFromXML($xmlfile) {
		$this->manifest_file = JFile::stripExt(basename($xmlfile));
		$xml = JFactory::getXMLParser('Simple');
		if(!$xml->loadFile($xmlfile)) {
			$this->_errors[] = 'Failed to load XML File: ' . $xmlfile;
			return false;
		} else {
			$xml = $xml->document;
			$this->name = isset($xml->name[0]) ? $xml->name[0]->data() : '';
			$this->packagename = isset($xml->packagename[0]) ? $xml->packagename[0]->data() : '';
			$this->update = isset($xml->update[0]) ? $xml->update[0]->data() : '';
			$this->authorurl = isset($xml->authorUrl[0]) ? $xml->authorUrl[0]->data() : '';
			$this->author = isset($xml->author[0]) ? $xml->author[0]->data() : '';
			$this->authoremail = isset($xml->authorEmail[0]) ? $xml->authorEmail[0]->data() : '';
			$this->description = isset($xml->description[0]) ? $xml->description[0]->data() : '';
			$this->packager = isset($xml->packager[0]) ? $xml->packager[0]->data() : '';
			$this->packagerurl = isset($xml->packagerurl[0]) ? $xml->packagerurl[0]->data() : '';
			$this->version = isset($xml->version[0]) ? $xml->version[0]->data() : '';
			if(isset($xml->files[0]->file) && count($xml->files[0]->file)) {
				foreach($xml->files[0]->file as $file) {
					$this->filelist[] = new JExtension($file);
				}
			}
			return true;
		}
	}
}
