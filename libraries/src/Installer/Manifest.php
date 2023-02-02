<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installer;

use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Package Manifest File
 *
 * @since  3.1
 */
abstract class Manifest
{
    /**
     * Path to the manifest file
     *
     * @var    string
     * @since  3.1
     */
    public $manifest_file = '';

    /**
     * Name of the extension
     *
     * @var    string
     * @since  3.1
     */
    public $name = '';

    /**
     * Version of the extension
     *
     * @var    string
     * @since  3.1
     */
    public $version = '';

    /**
     * Description of the extension
     *
     * @var    string
     * @since  3.1
     */
    public $description = '';

    /**
     * Packager of the extension
     *
     * @var    string
     * @since  3.1
     */
    public $packager = '';

    /**
     * Packager's URL of the extension
     *
     * @var    string
     * @since  3.1
     */
    public $packagerurl = '';

    /**
     * Update site for the extension
     *
     * @var    string
     * @since  3.1
     */
    public $update = '';

    /**
     * List of files in the extension
     *
     * @var    array
     * @since  3.1
     */
    public $filelist = [];

    /**
     * Constructor
     *
     * @param   string  $xmlpath  Path to XML manifest file.
     *
     * @since   3.1
     */
    public function __construct($xmlpath = '')
    {
        if ($xmlpath !== '') {
            $this->loadManifestFromXml($xmlpath);
        }
    }

    /**
     * Load a manifest from a file
     *
     * @param   string  $xmlfile  Path to file to load
     *
     * @return  boolean
     *
     * @since   3.1
     */
    public function loadManifestFromXml($xmlfile)
    {
        $this->manifest_file = basename($xmlfile, '.xml');

        $xml = simplexml_load_file($xmlfile);

        if (!$xml) {
            $this->_errors[] = Text::sprintf('JLIB_INSTALLER_ERROR_LOAD_XML', $xmlfile);

            return false;
        } else {
            $this->loadManifestFromData($xml);

            return true;
        }
    }

    /**
     * Apply manifest data from a \SimpleXMLElement to the object.
     *
     * @param   \SimpleXMLElement  $xml  Data to load
     *
     * @return  void
     *
     * @since   3.1
     */
    abstract protected function loadManifestFromData(\SimpleXMLElement $xml);
}
