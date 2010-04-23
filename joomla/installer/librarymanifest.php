<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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

		$xml =JFactory::getXML($xmlfile);
		if( ! $xml)
		{
			$this->_errors[] = JText::sprintf('JLIB_INSTALLER_ERROR_LOAD_XML', $xmlfile);
			return false;
		}
		else
		{
			$this->name = (string)$xml->name;
			$this->libraryname = (string)$xml->libraryname;
			$this->version = (string)$xml->version;
			$this->description = (string)$xml->description;
			$this->creationdate = (string)$xml->creationdate;
			$this->author = (string)$xml->author;
			$this->authoremail = (string)$xml->authorEmail;
			$this->authorurl = (string)$xml->authorUrl;
			$this->packager = (string)$xml->packager;
			$this->packagerurl = (string)$xml->packagerurl;
			$this->update = (string)$xml->update;

			if(isset($xml->files) && isset($xml->files->file) && count($xml->files->file))
			{
				foreach ($xml->files->file as $file) {
					$this->filelist[] = (string)$file;
				}
			}
			return true;
		}
	}
}
