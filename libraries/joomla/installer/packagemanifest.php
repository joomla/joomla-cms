<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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

		$xml = JFactory::getXML($xmlfile);

		if( ! $xml)
		{
			$this->_errors[] = JText::sprintf('JLIB_INSTALLER_ERROR_LOAD_XML', $xmlfile);
			return false;
		}
		else
		{
			$xml = $xml->document;
			$this->name = (string)$xml->name;
			$this->packagename = (string)$xml->packagename;
			$this->update = (string)$xml->update;
			$this->authorurl = (string)$xml->authorUrl;
			$this->author = (string)$xml->author;
			$this->authoremail = (string)$xml->authorEmail;
			$this->description = (string)$xml->description;
			$this->packager = (string)$xml->packager;
			$this->packagerurl = (string)$xml->packagerurl;
			$this->version = (string)$xml->version;
			if (isset($xml->files->file) && count($xml->files->file))
			{
				foreach ($xml->files->file as $file) {
					$this->filelist[] = new JExtension((string)$file);
				}
			}
			return true;
		}
	}
}
