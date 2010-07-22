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
	/** @var string name Name of the package */
	var $name = '';
	/** @var string packagename Unique name of the package */
	var $packagename = '';
	/** @var string url Website for the package */
	var $url = '';
	/** @var string description Description for the package */
	var $description = '';
	/** @var string packager Packager of the package */
	var $packager = '';
	/** @var string packagerurl Packager's URL of the package */
	var $packagerurl = '';
	/** @var string update Update site for the package */
	var $update = '';
	/** @var string version Version of the package */
	var $version = '';
	/** @var JExtension[] filelist List of files in this package */
	var $filelist = Array();
	/** @var string manifest_file Path to the manifest file */
	var $manifest_file = '';

	/**
	 * Constructor
	 * @param string $xmlpath Path to XML manifest file
	 */
	function __construct($xmlpath='')
	{
		if (strlen($xmlpath)) {
			$this->loadManifestFromXML($xmlpath);
		}
	}

	/**
	 * Load a manifest from an XML file
	 * @param string $xmlpath Path to XML manifest file
	 * @return boolean result of load
	 */
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
					// contrary to idiotic belief, JExtension doesn't expect a string
					// so please for the love of god don't type cast this into a string
					// when it shouldn't be...don't touch what you don't understand!
					$this->filelist[] = new JExtension($file);
				}
			}
			return true;
		}
	}
}
