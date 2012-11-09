<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla! Library Manifest File
 *
 * @package     Joomla.Platform
 * @subpackage  Installer
 * @since       11.1
 * @deprecated  13.3
 */
class JInstallerManifestLibrary extends JInstallerManifest
{
	/**
	 * @var string libraryname File system name of the library
	 */
	public $libraryname = '';

	/**
	 * @var date creationDate Creation Date of the extension
	 */
	public $creationDate = '';

	/**
	 * @var string copyright Copyright notice for the extension
	 */
	public $copyright = '';

	/**
	 * @var string license License for the extension
	 */
	public $license = '';

	/**
	 * @var string author Author for the extension
	 */
	public $author = '';

	/**
	 * @var string authoremail Author email for the extension
	 */
	public $authoremail = '';

	/**
	 * @var string authorurl Author url for the extension
	 */
	public $authorurl = '';

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
		$this->name         = (string) $xml->name;
		$this->libraryname  = (string) $xml->libraryname;
		$this->version      = (string) $xml->version;
		$this->description  = (string) $xml->description;
		$this->creationdate = (string) $xml->creationDate;
		$this->author       = (string) $xml->author;
		$this->authoremail  = (string) $xml->authorEmail;
		$this->authorurl    = (string) $xml->authorUrl;
		$this->packager     = (string) $xml->packager;
		$this->packagerurl  = (string) $xml->packagerurl;
		$this->update       = (string) $xml->update;

		if (isset($xml->files) && isset($xml->files->file) && count($xml->files->file))
		{
			foreach ($xml->files->file as $file)
			{
				$this->filelist[] = (string) $file;
			}
		}
	}
}
