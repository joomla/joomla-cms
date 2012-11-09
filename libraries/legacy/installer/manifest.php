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

/**
 * Joomla! Package Manifest File
 *
 * @package     Joomla.Platform
 * @subpackage  Installer
 * @since       12.2
 * @deprecated  13.3
 */
abstract class JInstallerManifest
{
	/**
	 * @var string manifest_file Path to the manifest file
	 */
	public $manifest_file = '';

	/**
	 * @var string name Name of the library/package
	 */
	public $name = '';

	/**
	 * @var string version Version of the library/package
	 */
	public $version = '';

	/**
	 * @var string description Description of the library
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
	 * @var string update Update site for the package
	 */
	public $update = '';

	/**
	 * @var array filelist List of files in this library/package
	 */
	public $filelist = array();

	/**
	 * Constructor
	 *
	 * @param   string  $xmlpath  Path to XML manifest file.
	 *
	 * @since   12.2
	 */
	public function __construct($xmlpath = '')
	{
		if (strlen($xmlpath))
		{
			$this->loadManifestFromXML($xmlpath);
		}
	}

	/**
	 * Load a manifest from a file
	 *
	 * @param   string  $xmlfile  Path to file to load
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	public function loadManifestFromXML($xmlfile)
	{
		$this->manifest_file = basename($xmlfile, '.xml');

		$xml = simplexml_load_file($xmlfile);

		if (!$xml)
		{
			$this->_errors[] = JText::sprintf('JLIB_INSTALLER_ERROR_LOAD_XML', $xmlfile);

			return false;
		}
		else
		{
			$this->loadManifestFromData($xml);

			return true;
		}
	}

	/**
	 * Apply manifest data from a SimpleXMLElement to the object.
	 *
	 * @param   SimpleXMLElement  $xml  Data to load
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	abstract protected function loadManifestFromData(SimpleXmlElement $xml);
}
