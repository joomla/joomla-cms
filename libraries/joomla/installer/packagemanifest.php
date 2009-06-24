<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.filesystem.file');
jimport('joomla.installer.extension');

/**
 * Joomla! Package Manifest File
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.6
 */
class JPackageManifest extends JObject
{
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

	function __construct($xmlpath='')
	{
		if (strlen($xmlpath)) {
			$this->loadManifestFromXML($xmlpath);
		}
	}

	function loadManifestFromXML($xmlfile)
	{
		$this->manifest_file = JFile::stripExt(basename($xmlfile));
		$xml = JFactory::getXMLParser('Simple');

		if (!$xml->loadFile($xmlfile))
		{
			$this->_errors[] = 'Failed to load XML File: ' . $xmlfile;
			return false;
		}
		else
		{
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
			if (isset($xml->files[0]->file) && count($xml->files[0]->file))
			{
				foreach ($xml->files[0]->file as $file) {
					$this->filelist[] = new JExtension($file);
				}
			}
			return true;
		}
	}
}
