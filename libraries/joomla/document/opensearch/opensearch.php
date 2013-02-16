<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.methods');
jimport('joomla.environment.uri');

/**
 * OpenSearch class, provides an easy interface to display an OpenSearch document
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @see         http://www.opensearch.org/
 * @since       11.1
 */
class JDocumentOpensearch extends JDocument
{
	/**
	 * ShortName element
	 *
	 * required
	 *
	 * @var    string
	 * @since  11.1
	 */
	private $_shortName = "";

	/**
	 * Images collection
	 *
	 * optional
	 *
	 * @var    object
	 * @since  11.1
	 */
	private $_images = array();

	/**
	 * The url collection
	 *
	 * @var    array
	 * @since  11.1
	 */
	private $_urls = array();

	/**
	 * Class constructor
	 *
	 * @param   array  $options  Associative array of options
	 *
	 * @since  11.1
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		// Set document type
		$this->_type = 'opensearch';

		// Set mime type
		$this->_mime = 'application/opensearchdescription+xml';

		// Add the URL for self updating
		$update = new JOpenSearchUrl;
		$update->type = 'application/opensearchdescription+xml';
		$update->rel = 'self';
		$update->template = JRoute::_(JFactory::getURI());
		$this->addUrl($update);

		// Add the favicon as the default image
		// Try to find a favicon by checking the template and root folder
		$app = JFactory::getApplication();
		$dirs = array(JPATH_THEMES . '/' . $app->getTemplate(), JPATH_BASE);

		foreach ($dirs as $dir)
		{
			if (file_exists($dir . '/favicon.ico'))
			{

				$path = str_replace(JPATH_BASE . '/', '', $dir);
				$path = str_replace('\\', '/', $path);

				$favicon = new JOpenSearchImage;
				$favicon->data = JURI::base() . $path . '/favicon.ico';
				$favicon->height = '16';
				$favicon->width = '16';
				$favicon->type = 'image/vnd.microsoft.icon';

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
	 * @return  The rendered data
	 *
	 * @since   11.1
	 */
	public function render($cache = false, $params = array())
	{
		$xml = new DOMDocument('1.0', 'utf-8');
		$xml->formatOutput = true;

		// The OpenSearch Namespace
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

		foreach ($this->_images as $image)
		{
			$elImage = $xml->createElementNS($osns, 'Image');
			$elImage->setAttribute('type', $image->type);
			$elImage->setAttribute('width', $image->width);
			$elImage->setAttribute('height', $image->height);
			$elImage->appendChild($xml->createTextNode(htmlspecialchars($image->data)));
			$elOs->appendChild($elImage);
		}

		foreach ($this->_urls as $url)
		{
			$elUrl = $xml->createElementNS($osns, 'Url');
			$elUrl->setAttribute('type', $url->type);
			// Results is the defualt value so we don't need to add it
			if ($url->rel != 'results')
			{
				$elUrl->setAttribute('rel', $url->rel);
			}
			$elUrl->setAttribute('template', $url->template);
			$elOs->appendChild($elUrl);
		}

		$xml->appendChild($elOs);
		parent::render();
		return $xml->saveXML();
	}

	/**
	 * Sets the short name
	 *
	 * @param   string  $name  The name.
	 *
	 * @return  JDocumentOpensearch instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function setShortName($name)
	{
		$this->_shortName = $name;

		return $this;
	}

	/**
	 * Adds an URL to the OpenSearch description.
	 *
	 * @param   JOpenSearchUrl  &$url  The url to add to the description.
	 *
	 * @return  JDocumentOpensearch instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function addUrl(&$url)
	{
		$this->_urls[] = $url;

		return $this;
	}

	/**
	 * Adds an image to the OpenSearch description.
	 *
	 * @param   JOpenSearchImage  &$image  The image to add to the description.
	 *
	 * @return  JDocumentOpensearch instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function addImage(&$image)
	{
		$this->_images[] = $image;

		return $this;
	}
}

/**
 * JOpenSearchUrl is an internal class that stores the search URLs for the OpenSearch description
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @since       11.1
 */
class JOpenSearchUrl extends JObject
{

	/**
	 * Type item element
	 *
	 * required
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'text/html';

	/**
	 * Rel item element
	 *
	 * required
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $rel = 'results';

	/**
	 * Template item element. Has to contain the {searchTerms} parameter to work.
	 *
	 * required
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $template;
}

/**
 * JOpenSearchImage is an internal class that stores Images for the OpenSearch Description
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @since       11.1
 */
class JOpenSearchImage extends JObject
{

	/**
	 * The images MIME type
	 *
	 * required
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = "";

	/**
	 * URL of the image or the image as base64 encoded value
	 *
	 * required
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $data = "";

	/**
	 * The image's width
	 *
	 * required
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $width;

	/**
	 * The image's height
	 *
	 * required
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $height;
}
