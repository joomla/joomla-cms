<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.filesystem.file');

/**
 * Joomla! Library Manifest File
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.6
 */
class JLibraryManifest extends JObject
{
	/** @var string name Name of variable */
	var $name = '';
	var $libraryname = '';
	var $version = '';
	var $description = '';
	var $creationDate = '';
	var $copyright = '';
	var $license = '';
	var $author = '';
	var $authoremail = '';
	var $authorurl = '';
	var $packager = '';
	var $packagerurl = '';
	var $update = '';
	var $filelist = Array();
	var $manifest_file = '';

	function __construct($xmlpath='')
	{
		if (strlen($xmlpath)) $this->loadManifestFromXML($xmlpath);
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
			$this->libraryname = isset($xml->libraryname[0]) ? $xml->libraryname[0]->data() : '';
			$this->version = isset($xml->version[0]) ? $xml->version[0]->data() : '';
			$this->description = isset($xml->description[0]) ? $xml->description[0]->data() : '';
			$this->creationdate = isset($xml->creationdate) ? $xml->creationDate[0]->data() : '';
			$this->author = isset($xml->author[0]) ? $xml->author[0]->data() : '';
			$this->authoremail = isset($xml->authorEmail[0]) ? $xml->authorEmail[0]->data() : '';
			$this->authorurl = isset($xml->authorUrl[0]) ? $xml->authorUrl[0]->data() : '';
			$this->packager = isset($xml->packager[0]) ? $xml->packager[0]->data() : '';
			$this->packagerurl = isset($xml->packagerurl[0]) ? $xml->packagerurl[0]->data() : '';
			$this->update = isset($xml->update[0]) ? $xml->update[0]->data() : '';

			if (isset($xml->files[0]) && isset($xml->files[0]->file) && count($xml->files[0]->file))
			{
				foreach ($xml->files[0]->file as $file) {
					$this->filelist[] = $file->data();
				}
			}
			return true;
		}
	}
}
