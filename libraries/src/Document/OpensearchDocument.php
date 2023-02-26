<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

use Joomla\CMS\Document\Opensearch\OpensearchImage;
use Joomla\CMS\Document\Opensearch\OpensearchUrl;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Opensearch class, provides an easy interface to display an Opensearch document
 *
 * @link   http://www.opensearch.org/
 * @since  1.7.0
 */
class OpensearchDocument extends Document
{
    /**
     * ShortName element
     *
     * required
     *
     * @var    string
     * @since  1.7.0
     */
    private $_shortName = '';

    /**
     * Images collection
     *
     * optional
     *
     * @var    object
     * @since  1.7.0
     */
    private $_images = [];

    /**
     * The url collection
     *
     * @var    array
     * @since  1.7.0
     */
    private $_urls = [];

    /**
     * Class constructor
     *
     * @param   array  $options  Associative array of options
     *
     * @since  1.7.0
     */
    public function __construct($options = [])
    {
        parent::__construct($options);

        // Set document type
        $this->_type = 'opensearch';

        // Set mime type
        $this->_mime = 'application/opensearchdescription+xml';

        // Add the URL for self updating
        $update           = new OpensearchUrl();
        $update->type     = 'application/opensearchdescription+xml';
        $update->rel      = 'self';
        $update->template = Route::_(Uri::getInstance());
        $this->addUrl($update);

        // Add the favicon as the default image
        // Try to find a favicon by checking the template and root folder
        $app  = Factory::getApplication();
        $dirs = [JPATH_THEMES . '/' . $app->getTemplate(), JPATH_BASE];

        foreach ($dirs as $dir) {
            if (is_file($dir . '/favicon.ico')) {
                $path    = str_replace(JPATH_BASE, '', $dir);
                $path    = str_replace('\\', '/', $path);
                $favicon = new OpensearchImage();

                if ($path == '') {
                    $favicon->data = Uri::base() . 'favicon.ico';
                } else {
                    if ($path[0] == '/') {
                        $path = substr($path, 1);
                    }

                    $favicon->data = Uri::base() . $path . '/favicon.ico';
                }

                $favicon->height = '16';
                $favicon->width  = '16';
                $favicon->type   = 'image/vnd.microsoft.icon';

                $this->addImage($favicon);

                break;
            }
        }
    }

    /**
     * Render the document
     *
     * @param   boolean  $cache   If true, cache the output
     * @param   array    $params  Associative array of attributes
     *
     * @return  string  The rendered data
     *
     * @since   1.7.0
     */
    public function render($cache = false, $params = [])
    {
        $xml = new \DOMDocument('1.0', 'utf-8');

        if (\defined('JDEBUG') && JDEBUG) {
            $xml->formatOutput = true;
        }

        // The Opensearch Namespace
        $osns = 'http://a9.com/-/spec/opensearch/1.1/';

        // Create the root element
        $elOs = $xml->createElementNS($osns, 'OpenSearchDescription');

        $elShortName = $xml->createElementNS($osns, 'ShortName');
        $elShortName->appendChild($xml->createTextNode(htmlspecialchars($this->_shortName)));
        $elOs->appendChild($elShortName);

        $elDescription = $xml->createElementNS($osns, 'Description');
        $elDescription->appendChild($xml->createTextNode(htmlspecialchars($this->description)));
        $elOs->appendChild($elDescription);

        // Always set the accepted input encoding to UTF-8
        $elInputEncoding = $xml->createElementNS($osns, 'InputEncoding');
        $elInputEncoding->appendChild($xml->createTextNode('UTF-8'));
        $elOs->appendChild($elInputEncoding);

        foreach ($this->_images as $image) {
            $elImage = $xml->createElementNS($osns, 'Image');
            $elImage->setAttribute('type', $image->type);
            $elImage->setAttribute('width', $image->width);
            $elImage->setAttribute('height', $image->height);
            $elImage->appendChild($xml->createTextNode(htmlspecialchars($image->data)));
            $elOs->appendChild($elImage);
        }

        foreach ($this->_urls as $url) {
            $elUrl = $xml->createElementNS($osns, 'Url');
            $elUrl->setAttribute('type', $url->type);

            // Results is the default value so we don't need to add it
            if ($url->rel !== 'results') {
                $elUrl->setAttribute('rel', $url->rel);
            }

            $elUrl->setAttribute('template', $url->template);
            $elOs->appendChild($elUrl);
        }

        $xml->appendChild($elOs);
        parent::render($cache, $params);

        return $xml->saveXML();
    }

    /**
     * Sets the short name
     *
     * @param   string  $name  The name.
     *
     * @return  OpensearchDocument instance of $this to allow chaining
     *
     * @since   1.7.0
     */
    public function setShortName($name)
    {
        $this->_shortName = $name;

        return $this;
    }

    /**
     * Adds a URL to the Opensearch description.
     *
     * @param   OpensearchUrl  $url  The url to add to the description.
     *
     * @return  OpensearchDocument instance of $this to allow chaining
     *
     * @since   1.7.0
     */
    public function addUrl(OpensearchUrl $url)
    {
        $this->_urls[] = $url;

        return $this;
    }

    /**
     * Adds an image to the Opensearch description.
     *
     * @param   OpensearchImage  $image  The image to add to the description.
     *
     * @return  OpensearchDocument instance of $this to allow chaining
     *
     * @since   1.7.0
     */
    public function addImage(OpensearchImage $image)
    {
        $this->_images[] = $image;

        return $this;
    }
}
