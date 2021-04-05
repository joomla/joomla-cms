<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installer\Manifest;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Installer\Manifest;

/**
 * Joomla! Library Manifest File
 *
 * @since  3.1
 */
class LibraryManifest extends Manifest
{
	/**
	 * File system name of the library
	 *
	 * @var    string
	 * @since  3.1
	 */
	public $libraryname = '';

	/**
	 * Creation Date of the library
	 *
	 * @var    string
	 * @since  3.1
	 */
	public $creationDate = '';

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
	 * Author for the library
	 *
	 * @var    string
	 * @since  3.1
	 */
	public $author = '';

	/**
	 * Author email for the library
	 *
	 * @var    string
	 * @since  3.1
	 */
	public $authoremail = '';

	/**
	 * Author URL for the library
	 *
	 * @var    string
	 * @since  3.1
	 */
	public $authorurl = '';

	/**
	 * Apply manifest data from a \SimpleXMLElement to the object.
	 *
	 * @param   \SimpleXMLElement  $xml  Data to load
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function loadManifestFromData(\SimpleXMLElement $xml)
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

		if (isset($xml->files) && isset($xml->files->file) && \count($xml->files->file))
		{
			foreach ($xml->files->file as $file)
			{
				$this->filelist[] = (string) $file;
			}
		}
	}
}
