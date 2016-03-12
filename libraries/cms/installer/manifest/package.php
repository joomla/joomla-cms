<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla! Package Manifest File
 *
 * @since  3.1
 */
class JInstallerManifestPackage extends JInstallerManifest
{
	/**
	 * Unique name of the package
	 *
	 * @var    string
	 * @since  3.1
	 */
	public $packagename = '';

	/**
	 * Website for the package
	 *
	 * @var    string
	 * @since  3.1
	 */
	public $url = '';

	/**
	 * Scriptfile for the package
	 *
	 * @var    string
	 * @since  3.1
	 */
	public $scriptfile = '';

	/**
	 * Apply manifest data from a SimpleXMLElement to the object.
	 *
	 * @param   SimpleXMLElement  $xml  Data to load
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function loadManifestFromData(SimpleXMLElement $xml)
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
				// NOTE: JInstallerExtension doesn't expect a string.
				// DO NOT CAST $file
				$this->filelist[] = new JInstallerExtension($file);
			}
		}

		// Handle cases where package contains folders
		if (isset($xml->files->folder) && count($xml->files->folder))
		{
			foreach ($xml->files->folder as $folder)
			{
				// NOTE: JInstallerExtension doesn't expect a string.
				// DO NOT CAST $folder
				$this->filelist[] = new JInstallerExtension($folder);
			}
		}
	}
}
