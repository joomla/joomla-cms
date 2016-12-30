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
	 *
	 * @deprecated  4.0  Use $packageName instead
	 */
	public $packagename = '';

	/**
	 * Unique name of the package
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $packageName = '';

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
	 *
	 * @deprecated  4.0  Use $scriptFile instead
	 */
	public $scriptfile = '';

	/**
	 * Scriptfile for the package
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $scriptFile = '';

	/**
	 * Flag if the package blocks individual child extensions from being uninstalled
	 *
	 * @var    boolean
	 * @since  3.7.0
	 */
	public $blockChildUninstall = false;

	/**
	 * JInstallerManifestPackage constructor.
	 *
	 * @param   string $xmlPath Path to XML manifest file.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct($xmlPath = '')
	{
		// Old and new variables are referenced for B/C
		$this->packageName = &$this->packagename;
		$this->scriptFile  = &$this->scriptfile;

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
		$this->name        = (string) $xml->name;
		$this->packageName = (string) $xml->packageName ?: (string) $xml->packagename;
		$this->version     = (string) $xml->version;
		$this->description = (string) $xml->description;
		$this->scriptFile  = (string) $xml->scriptfile;
		$this->author      = (string) $xml->author;
		$this->authorEmail = (string) $xml->authorEmail;
		$this->authorURL   = (string) $xml->authorURL ?: (string) $xml->authorUrl;
		$this->packager    = (string) $xml->packager;
		$this->packagerURL = (string) $xml->packagerURL ?: (string) $xml->packagerurl;
		$this->update      = (string) $xml->update;

		if (isset($xml->blockChildUninstall))
		{
			$value = (string) $xml->blockChildUninstall;

			if ($value === '1' || $value === 'true')
			{
				$this->blockChildUninstall = true;
			}
		}

		if (isset($xml->files->file) && count($xml->files->file))
		{
			foreach ($xml->files->file as $file)
			{
				// NOTE: JInstallerExtension doesn't expect a string.
				// DO NOT CAST $file
				$this->fileList[] = new JInstallerExtension($file);
			}
		}

		// Handle cases where package contains folders
		if (isset($xml->files->folder) && count($xml->files->folder))
		{
			foreach ($xml->files->folder as $folder)
			{
				// NOTE: JInstallerExtension doesn't expect a string.
				// DO NOT CAST $folder
				$this->fileList[] = new JInstallerExtension($folder);
			}
		}
	}
}
