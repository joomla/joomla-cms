<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installer;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Object\CMSObject;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Extension object
 *
 * @since  3.1
 */
class InstallerExtension extends CMSObject
{
    /**
     * Client ID of the extension
     *
     * @var    int
     * @since  4.2.9
     */
    public $client_id;

    /**
     * Filename of the extension
     *
     * @var    string
     * @since  3.1
     */
    public $filename = '';

    /**
     * Type of the extension
     *
     * @var    string
     * @since  3.1
     */
    public $type = '';

    /**
     * Unique Identifier for the extension
     *
     * @var    string
     * @since  3.1
     */
    public $id = '';

    /**
     * The status of the extension
     *
     * @var    boolean
     * @since  3.1
     */
    public $published = false;

    /**
     * String representation of client. Valid for modules, templates and languages.
     * Set by default to site.
     *
     * @var    string
     * @since  3.1
     */
    public $client = 'site';

    /**
     * The group name of the plugin. Not used for other known extension types (only plugins)
     *
     * @var string
     * @since  3.1
     */
    public $group = '';

    /**
     * An object representation of the manifest file stored metadata
     *
     * @var object
     * @since  3.1
     */
    public $manifest_cache = null;

    /**
     * An object representation of the extension params
     *
     * @var    object
     * @since  3.1
     */
    public $params = null;

    /**
     * The namespace of the extension
     *
     * @var    string
     * @since  4.0.0
     */
    public $namespace = null;

    /**
     * Constructor
     *
     * @param   \SimpleXMLElement  $element  A SimpleXMLElement from which to load data from
     *
     * @since  3.1
     */
    public function __construct(\SimpleXMLElement $element = null)
    {
        if ($element) {
            $this->type = (string) $element->attributes()->type;
            $this->id = (string) $element->attributes()->id;

            switch ($this->type) {
                case 'component':
                    // By default a component doesn't have anything
                    break;

                case 'module':
                case 'template':
                case 'language':
                    $this->client = (string) $element->attributes()->client;
                    $tmp_client_id = ApplicationHelper::getClientInfo($this->client, 1);

                    if ($tmp_client_id == null) {
                        Log::add(Text::_('JLIB_INSTALLER_ERROR_EXTENSION_INVALID_CLIENT_IDENTIFIER'), Log::WARNING, 'jerror');
                    } else {
                        $this->client_id = $tmp_client_id->id;
                    }
                    break;

                case 'plugin':
                    $this->group = (string) $element->attributes()->group;
                    break;

                default:
                    // Catch all
                    // Get and set client and group if we don't recognise the extension
                    if ($element->attributes()->client) {
                        $this->client_id = ApplicationHelper::getClientInfo($this->client, 1);
                        $this->client_id = $this->client_id->id;
                    }

                    if ($element->attributes()->group) {
                        $this->group = (string) $element->attributes()->group;
                    }
                    break;
            }

            $this->filename = (string) $element;
        }
    }
}
