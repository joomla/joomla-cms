<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

//Register the renderer class with the loader
JLoader::register('JDocumentRenderer', dirname(__FILE__).DS.'renderer.php');

/**
 * Document class, provides an easy interface to parse and display a document
 *
 * @abstract
 * @package		Joomla.Platform
 * @subpackage	Document
 * @since		11.1
 */
class JDocument extends JObject
{
	/**
	 * Document title
	 *
	 * @var	string
	 */
	public $title = '';

	/**
	 * Document description
	 *
	 * @var	string
	 */
	public $description = '';

	/**
	 * Document full URL
	 *
	 * @var	string
	 */
	public $link = '';

	/**
	 * Document base URL
	 *
	 * @var	string
	 */
	public $base = '';

	/**
	 * Contains the document language setting
	 *
	 * @var	string
	 */
	public $language = 'en-gb';

	/**
	 * Contains the document direction setting
	 *
	 * @var	string
	 */
	public $direction = 'ltr';

	/**
	 * Document generator
	 *
	 * @var		string
	 */
	public $_generator = 'Joomla! 1.6 - Open Source Content Management';

	/**
	 * Document modified date
	 *
	 * @var		string
	 */
	public $_mdate = '';

	/**
	 * Tab string
	 *
	 * @var		string
	 */
	private $_tab = "\11";

	/**
	 * Contains the line end string
	 *
	 * @var		string
	 */
	private $_lineEnd = "\12";

	/**
	 * Contains the character encoding string
	 *
	 * @var	string
	 */
	private $_charset = 'utf-8';

	/**
	 * Document mime type
	 *
	 * @var		string
	 */
	private $_mime = '';

	/**
	 * Document namespace
	 *
	 * @var		string
	 */
	private $_namespace = '';

	/**
	 * Document profile
	 *
	 * @var		string
	 */
	private $_profile = '';

	/**
	 * Array of linked scripts
	 *
	 * @var		array
	 */
	private $_scripts = array();

	/**
	 * Array of scripts placed in the header
	 *
	 * @var  array
	 */
	private $_script = array();

	/**
	 * Array of linked style sheets
	 *
	 * @var	array
	 */
	private $_styleSheets = array();

	/**
	 * Array of included style declarations
	 *
	 * @var	array
	 */
	private $_style = array();

	/**
	 * Array of meta tags
	 *
	 * @var	array
	 */
	private $_metaTags = array();

	/**
	 * The rendering engine
	 *
	 * @var	object
	 */
	private $_engine = null;

	/**
	 * The document type
	 *
	 * @var	string
	 */
	private $_type = null;

	/**
	 * Array of buffered output
	 *
	 * @var		mixed (depends on the renderer)
	 */
	protected static $_buffer = null;

	/**
	 * Class constructor.
	 *
	 * @param   array  $options  Associative array of options
	 *
	 * @return  JDocument
	 *
	 * @since   11.1
	 */
	public function __construct($options = array())
	{
		parent::__construct();

		if (array_key_exists('lineend', $options)) {
			$this->setLineEnd($options['lineend']);
		}

		if (array_key_exists('charset', $options)) {
			$this->setCharset($options['charset']);
		}

		if (array_key_exists('language', $options)) {
			$this->setLanguage($options['language']);
		}

		if (array_key_exists('direction', $options)) {
			$this->setDirection($options['direction']);
		}

		if (array_key_exists('tab', $options)) {
			$this->setTab($options['tab']);
		}

		if (array_key_exists('link', $options)) {
			$this->setLink($options['link']);
		}

		if (array_key_exists('base', $options)) {
			$this->setBase($options['base']);
		}
	}

	/**
	 * Returns the global JDocument object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param type $type The document type to instantiate
	 *
	 * @return object  The document object.
	 */
	public static function getInstance($type = 'html', $attributes = array())
	{
		static $instances;

		if (!isset($instances)) {
			$instances = array();
		}

		$signature = serialize(array($type, $attributes));

		if (empty($instances[$signature])) {
			$type	= preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
			$path	= dirname(__FILE__).DS.$type.DS.$type.'.php';
			$ntype	= null;

			// Check if the document type exists
			if (!file_exists($path)) {
				// Default to the raw format
				$ntype	= $type;
				$type	= 'raw';
			}

			// Determine the path and class
			$class = 'JDocument'.$type;
			if (!class_exists($class)) {
				$path	= dirname(__FILE__).DS.$type.DS.$type.'.php';
				if (file_exists($path)) {
					require_once $path;
				}
				else {
					JError::raiseError(500,JText::_('JLIB_DOCUMENT_ERROR_UNABLE_LOAD_DOC_CLASS'));
				}
			}

			$instance	= new $class($attributes);
			$instances[$signature] = &$instance;

			if (!is_null($ntype)) {
				// Set the type to the Document type originally requested
				$instance->setType($ntype);
			}
		}

		return $instances[$signature];
	}

	/**
	 * Set the document type
	 *
	 * @param	string $type
	 */
	public function setType($type)
	{
		$this->_type = $type;
	}

	/**
	 * Returns the document type
	 *
	 * @return	string
	 */
	public function getType()
	{
		return $this->_type;
	}

	/**
	 * Get the document head data
	 *
	 * @return	array	The document head data in array form
	 */
	public function getHeadData()
	{
		// Impelemented in child classes
	}

	/**
	 * Set the document head data
	 *
	 * @param	array	$data	The document head data in array form
	 */
	public function setHeadData($data)
	{
		// Impelemented in child classes
	}

	/**
	 * Set the document head data
	 *
	 * @param	array	$data	The document head data in array form
	 */
	public function mergeHeadData($data)
	{
		// Impelemented in child classes
	}

	/**
	 * Get the contents of the document buffer
	 *
	 * @return	The contents of the document buffer
	 */
	public function getBuffer()
	{
		return self::$_buffer;
	}

	/**
	 * Set the contents of the document buffer
	 *
	 * @param	string	$content	The content to be set in the buffer.
	 * @param	array	$options	Array of optional elements.
	 */
	public function setBuffer($content, $options = array())
	{
		self::$_buffer = $content;
	}

	/**
	 * Gets a meta tag.
	 *
	 * @param	string	$name			Value of name or http-equiv tag
	 * @param	bool	$http_equiv	META type "http-equiv" defaults to null
	 *
	 * @return	string
	 */
	public function getMetaData($name, $http_equiv = false)
	{
		$result = '';
		$name = strtolower($name);
		if ($name == 'generator') {
			$result = $this->getGenerator();
		}
		else if ($name == 'description') {
			$result = $this->getDescription();
		}
		else {
			if ($http_equiv == true) {
				$result = @$this->_metaTags['http-equiv'][$name];
			}
			else {
				$result = @$this->_metaTags['standard'][$name];
			}
		}

		return $result;
	}

	/**
	 * Sets or alters a meta tag.
	 *
	 * @param string	$name			Value of name or http-equiv tag
	 * @param string	$content		Value of the content tag
	 * @param bool		$http_equiv		META type "http-equiv" defaults to null
	 * @param bool		$sync			Should http-equiv="content-type" by synced with HTTP-header?
	 *
	 * @return void
	 */
	public function setMetaData($name, $content, $http_equiv = false, $sync = true)
	{
		$name = strtolower($name);

		if ($name == 'generator') {
			$this->setGenerator($content);
		}
		else if ($name == 'description') {
			$this->setDescription($content);
		}
		else {
			if ($http_equiv == true) {
				$this->_metaTags['http-equiv'][$name] = $content;

				// Syncing with HTTP-header
				if($sync && strtolower($name) == 'content-type') {
					$this->setMimeEncoding($content, false);
				}
			}
			else {
				$this->_metaTags['standard'][$name] = $content;
			}
		}
	}

	/**
	 * Adds a linked script to the page
	 *
	 * @param	string  $url		URL to the linked script
	 * @param	string  $type		Type of script. Defaults to 'text/javascript'
	 * @param	bool	$defer		Adds the defer attribute.
	 * @param	bool	$async		Adds the async attribute.
	 */
	public function addScript($url, $type = "text/javascript", $defer = false, $async = false)
	{
		$this->_scripts[$url]['mime'] = $type;
		$this->_scripts[$url]['defer'] = $defer;
		$this->_scripts[$url]['async'] = $async;
	}

	/**
	 * Adds a script to the page
	 *
	 * @param	string  $content	Script
	 * @param	string  $type	Scripting mime (defaults to 'text/javascript')
	 * @return	void
	 */
	public function addScriptDeclaration($content, $type = 'text/javascript')
	{
		if (!isset($this->_script[strtolower($type)])) {
			$this->_script[strtolower($type)] = $content;
		}
		else {
			$this->_script[strtolower($type)] .= chr(13).$content;
		}
	}

	/**
	 * Adds a linked stylesheet to the page
	 *
	 * @param	string  $url	URL to the linked style sheet
	 * @param	string  $type	Mime encoding type
	 * @param	string  $media  Media type that this stylesheet applies to
	 */
	public function addStyleSheet($url, $type = 'text/css', $media = null, $attribs = array())
	{
		$this->_styleSheets[$url]['mime']		= $type;
		$this->_styleSheets[$url]['media']		= $media;
		$this->_styleSheets[$url]['attribs']	= $attribs;
	}

	/**
	 * Adds a stylesheet declaration to the page
	 *
	 * @param	string  $content	Style declarations
	 * @param	string  $type		Type of stylesheet (defaults to 'text/css')
	 *
	 * @return	void
	 */
	public function addStyleDeclaration($content, $type = 'text/css')
	{
		if (!isset($this->_style[strtolower($type)])) {
			$this->_style[strtolower($type)] = $content;
		}
		else {
			$this->_style[strtolower($type)] .= chr(13).$content;
		}
	}

	/**
	 * Sets the document charset
	 *
	 * @param	string	$type  Charset encoding string
	 *
	 * @return  void
	 */
	public function setCharset($type = 'utf-8')
	{
		$this->_charset = $type;
	}

	/**
	 * Returns the document charset encoding.
	 *
	 * @return string
	 */
	public function getCharset()
	{
		return $this->_charset;
	}

	/**
	 * Sets the global document language declaration. Default is English (en-gb).
	 *
	 * @param	string	$lang
	 */
	public function setLanguage($lang = "en-gb")
	{
		$this->language = strtolower($lang);
	}

	/**
	 * Returns the document language.
	 *
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * Sets the global document direction declaration. Default is left-to-right (ltr).
	 *
	 * @param	string	$lang
	 */
	public function setDirection($dir = "ltr")
	{
		$this->direction = strtolower($dir);
	}

	/**
	 * Returns the document direction declaration.
	 *
	 * @return string
	 *
	 */
	public function getDirection()
	{
		return $this->direction;
	}

	/**
	 * Sets the title of the document
	 *
	 * @param	string	$title
	 *
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * Return the title of the document.
	 *
	 * @return	string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Sets the base URI of the document
	 *
	 * @param	string	$base
	 *
	 */
	public function setBase($base)
	{
		$this->base = $base;
	}

	/**
	 * Return the base URI of the document.
	 *
	 * @return	string
	 *
	 */
	public function getBase()
	{
		return $this->base;
	}

	/**
	 * Sets the description of the document
	 *
	 * @param	string	$title
	 *
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * Return the title of the page.
	 *
	 * @return	string
	 *
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Sets the document link
	 *
	 * @param	string	$url  A url
	 *
	 * @return  void
	 */
	public function setLink($url)
	{
		$this->link = $url;
	}

	/**
	 * Returns the document base url
	 *
	 * @return string
	 */
	public function getLink()
	{
		return $this->link;
	}

	/**
	 * Sets the document generator
	 *
	 * @param	string
	 * @return  void
	 */
	public function setGenerator($generator)
	{
		$this->_generator = $generator;
	}

	/**
	 * Returns the document generator
	 *
	 * @return string
	 */
	public function getGenerator()
	{
		return $this->_generator;
	}

	/**
	 * Sets the document modified date
	 *
	 * @param	string
	 *
	 * @return  void
	 */
	public function setModifiedDate($date)
	{
		$this->_mdate = $date;
	}

	/**
	 * Returns the document modified date
	 *
	 * @return string
	 */
	public function getModifiedDate()
	{
		return $this->_mdate;
	}

	/**
	 * Sets the document MIME encoding that is sent to the browser.
	 *
	 * This usually will be text/html because most browsers cannot yet
	 * accept the proper mime settings for XHTML: application/xhtml+xml
	 * and to a lesser extent application/xml and text/xml. See the W3C note
	 * ({@link http://www.w3.org/TR/xhtml-media-types/
	 * http://www.w3.org/TR/xhtml-media-types/}) for more details.
	 *
	 * @param	string		$type
	 * @param	boolean		Should the type be synced with HTML?
	 * @return	void
	 */
	public function setMimeEncoding($type = 'text/html', $sync = true)
	{
		$this->_mime = strtolower($type);

		// Syncing with meta-data
		if ($sync) {
			$this->setMetaData('content-type', $type, true, false);
		}
	}

	/**
	 * Return the document MIME encoding that is sent to the browser.
	 *
	 * @return	string
	 */
	public function getMimeEncoding()
	{
		return $this->_mime;
	}

	/**
	 * Sets the line end style to Windows, Mac, Unix or a custom string.
	 *
	 * @param	string  $style  "win", "mac", "unix" or custom string.
	 * @return  void
	 */
	public function setLineEnd($style)
	{
		switch ($style)
		{
			case 'win':
				$this->_lineEnd = "\15\12";
				break;
			case 'unix':
				$this->_lineEnd = "\12";
				break;
			case 'mac':
				$this->_lineEnd = "\15";
				break;
			default:
				$this->_lineEnd = $style;
		}
	}

	/**
	 * Returns the lineEnd
	 *
	 * @return	string
	 */
	private function _getLineEnd()
	{
		return $this->_lineEnd;
	}

	/**
	 * Sets the string used to indent HTML
	 *
	 * @param	string	$string	String used to indent ("\11", "\t", '  ', etc.).
	 *
	 * @return	void
	 */
	public function setTab($string)
	{
		$this->_tab = $string;
	}

	/**
	 * Returns a string containing the unit for indenting HTML
	 *
	 * @return	string
	 */
	private function _getTab()
	{
		return $this->_tab;
	}

	/**
	* Load a renderer
	*
	* @param	string	The renderer type
	* @return	object
	* @since   11.1
	*/
	public function loadRenderer($type)
	{
		$class	= 'JDocumentRenderer'.$type;

		if (!class_exists($class)) {
			$path = dirname(__FILE__).DS.$this->_type.DS.'renderer'.DS.$type.'.php';

			if (file_exists($path)) {
				require_once $path;
			}
			else {
				JError::raiseError(500,JText::_('Unable to load renderer class'));
			}
		}

		if (!class_exists($class)) {
			return null;
		}

		$instance = new $class($this);

		return $instance;
	}

	/**
	 * Parses the document and prepares the buffers
	 *
	 * @return null
	 */
	public function parse($params = array())
	{
		return null;
	}

	/**
	 * Outputs the document
	 *
	 * @param boolean	$cache		If true, cache the output
	 * @param boolean	$compress	If true, compress the output
	 * @param array		$params		Associative array of attributes
	 *
	 * @return	The rendered data
	 */
	public function render($cache = false, $params = array())
	{
		if ($mdate = $this->getModifiedDate()) {
			JResponse::setHeader('Last-Modified', $mdate /* gmdate('D, d M Y H:i:s', time() + 900) . ' GMT' */);
		}

		JResponse::setHeader('Content-Type', $this->_mime .  '; charset=' . $this->_charset);
	}
}
