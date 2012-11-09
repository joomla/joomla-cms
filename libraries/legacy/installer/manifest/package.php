<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.installer.extension');

/**
 * Joomla! Package Manifest File
 *
 * @package     Joomla.Platform
 * @subpackage  Installer
 * @since       11.1
 * @deprecated  13.3
 */
class JInstallerManifestPackage extends JInstallerManifest
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
	 * @var string scriptfile Scriptfile for the package
	 */
	public $scriptfile = '';

	/**
	 * Apply manifest data from a SimpleXMLElement to the object.
	 *
	 * @param   SimpleXMLElement  $xml  Data to load
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function loadManifestFromData(SimpleXmlElement $xml)
	{
		$this->name        = (string) $xml->name;
		$this->packagename = (string) $xml->packagename;
		$this->update      = (string) $xml->update;
		$this->authorurl   = (string) $xml->authorUrl;
		$this->author      = (string) $xml->author;
		$this->authoremail = (string) $xml->authorEmail;
		$this->description = (string) $xml->description;
		$this->packager    = (string) $xml->packager;
		$this->packagerurl = (string) $xml->packagerurl;
		$this->scriptfile  = (string) $xml->scriptfile;
		$this->version     = (string) $xml->version;

		if (isset($xml->files->file) && count($xml->files->file))
		{
			foreach ($xml->files->file as $file)
			{
				// NOTE: JExtension doesn't expect a string.
				// DO NOT CAST $file
				$this->filelist[] = new JExtension($file);
			}
		}
	}
}
