<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.file');
jimport('joomla.installer.extension');

/**
 * Joomla! Package Manifest File
 *
 * @package     Joomla.Platform
 * @subpackage  Installer
 * @since       11.1
 */
class JPackageManifest extends JObject
{
	/**
	 * @var string name Name of the package
	 */
	public $name = '';

	/**
	 * @var string packagename Unique name of the package
	 */
	public $packagename = '';

	/**
	 * @var string url Website for the package
	 */
	public $url = '';

	/**
	 * @var string description Description for the package
	 */
	public $description = '';

	/**
	 * @var string packager Packager of the package
	 */
	public $packager = '';

	/**
	 * @var string packagerurl Packager's URL of the package
	 */
	public $packagerurl = '';

	/**
	 * @var string scriptfile Scriptfile for the package
	 */
	public $scriptfile = '';

	/**
	 * @var string update Update site for the package
	 */
	public $update = '';

	/**
	 * @var string version Version of the package
	 */
	public $version = '';

	/**
	 * @var array filelist List of files in this package
	 */
	public $filelist = array();

	/**
	 * @var string manifest_file Path to the manifest file
	 */
	public $manifest_file = '';

	/**
	 * Constructor
	 *
	 * @param   string  $xmlpath  Path to XML manifest file.
	 *
	 * @since
	 */
	public function __construct($xmlpath = '')
	{
		if (strlen($xmlpath))
		{
			$this->loadManifestFromXML($xmlpath);
		}
	}

	/**
	 * Load a manifest from an XML file
	 *
	 * @param   string  $xmlfile  Path to XML manifest file
	 *
	 * @return  boolean	Result of load
	 *
	 * @since   11.1
	 */
	public function loadManifestFromXML($xmlfile)
	{
		$this->manifest_file = JFile::stripExt(basename($xmlfile));

		$xml = simplexml_load_file($xmlfile);

		if (!$xml)
		{
			$this->_errors[] = JText::sprintf('JLIB_INSTALLER_ERROR_LOAD_XML', $xmlfile);

			return false;
		}
		else
		{
			$this->name = (string) $xml->name;
			$this->packagename = (string) $xml->packagename;
			$this->update = (string) $xml->update;
			$this->authorurl = (string) $xml->authorUrl;
			$this->author = (string) $xml->author;
			$this->authoremail = (string) $xml->authorEmail;
			$this->description = (string) $xml->description;
			$this->packager = (string) $xml->packager;
			$this->packagerurl = (string) $xml->packagerurl;
			$this->scriptfile = (string) $xml->scriptfile;
			$this->version = (string) $xml->version;

			if (isset($xml->files->file) && count($xml->files->file))
			{
				foreach ($xml->files->file as $file)
				{
					// NOTE: JExtension doesn't expect a string.
					// DO NOT CAST $file
					$this->filelist[] = new JExtension($file);
				}
			}

			return true;
		}
	}
}
