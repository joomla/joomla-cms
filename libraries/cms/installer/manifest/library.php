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
 * Joomla! Library Manifest File
 *
 * @since  3.1
 */
class JInstallerManifestLibrary extends JInstallerManifest
{
	/**
	 * File system name of the library (alias)
	 *
	 * @var    string
	 * @since  3.1
	 *
	 * @deprecated  4.0  Use $libraryName instead
	 */
	public $libraryname = '';

	/**
	 * File system name of the library
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $libraryName = '';

	/**
	 * Creation Date of the library
	 *
	 * @var    string
	 * @since  3.1
	 */
	public $creationDate = '';

	/**
	 * Creation Date of the library
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 *
	 * @deprecated  4.0  Use $creationDate instead
	 */
	public $creationdate = '';

	/**
	 * Copyright notice for the library
	 *
	 * @var    string
	 * @since  3.1
	 */
	public $copyright = '';

	/**
	 * License for the library
	 *
	 * @var    string
	 * @since  3.1
	 */
	public $license = '';

	/**
	 * JInstallerManifestLibrary constructor.
	 *
	 * @param   string  $xmlPath  Path to XML manifest file.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct($xmlPath = '')
	{
		// Old and new variables are referenced for B/C
		$this->creationDate = &$this->creationdate;
		$this->libraryName  = &$this->libraryname;

		parent::__construct($xmlPath);
	}

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
		$this->name         = (string) $xml->name;
		$this->libraryName  = (string) $xml->libraryName ?: (string) $xml->libraryname;
		$this->version      = (string) $xml->version;
		$this->description  = (string) $xml->description;
		$this->creationDate = (string) $xml->creationDate;
		$this->author       = (string) $xml->author;
		$this->authorEmail  = (string) $xml->authorEmail;
		$this->authorURL    = (string) $xml->authorURL ?: (string) $xml->authorUrl;
		$this->packager     = (string) $xml->packager;
		$this->packagerURL  = (string) $xml->packagerURL ?: (string) $xml->packagerurl;
		$this->update       = (string) $xml->update;

		if (isset($xml->files) && isset($xml->files->file) && count($xml->files->file))
		{
			foreach ($xml->files->file as $file)
			{
				$this->fileList[] = (string) $file;
			}
		}
	}
}
