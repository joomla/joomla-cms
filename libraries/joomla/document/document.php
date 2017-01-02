<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Document class, provides an easy interface to parse and display a document
 *
 * @since  11.1
 */
class JDocument
{
	/**
	 * Document title
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $title = '';

	/**
	 * Document description
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $description = '';

	/**
	 * Document full URL
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $link = '';

	/**
	 * Document base URL
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $base = '';

	/**
	 * Contains the document language setting
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $language = 'en-gb';

	/**
	 * Contains the document direction setting
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $direction = 'ltr';

	/**
	 * Document generator
	 *
	 * @var    string
	 */
	public $_generator = 'Joomla! - Open Source Content Management';

	/**
	 * Document modified date
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $_mdate = '';

	/**
	 * Tab string
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $_tab = "\11";

	/**
	 * Contains the line end string
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $_lineEnd = "\12";

	/**
	 * Contains the character encoding string
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $_charset = 'utf-8';

	/**
	 * Document mime type
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $_mime = '';

	/**
	 * Document namespace
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $_namespace = '';

	/**
	 * Document profile
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $_profile = '';

	/**
	 * Array of linked scripts
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $_scripts = array();

	/**
	 * Array of scripts placed in the header
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $_script = array();

	/**
	 * Array of scripts options
	 *
	 *  @var    array
	 */
	protected $scriptOptions = array();

	/**
	 * Array of linked style sheets
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $_styleSheets = array();

	/**
	 * Array of included style declarations
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $_style = array();

	/**
	 * Array of meta tags
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $_metaTags = array();

	/**
	 * The rendering engine
	 *
	 * @var    object
	 * @since  11.1
	 */
	public $_engine = null;

	/**
	 * The document type
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $_type = null;

	/**
	 * Array of buffered output
	 *
	 * @var    mixed (depends on the renderer)
	 * @since  11.1
	 */
	public static $_buffer = null;

	/**
	 * JDocument instances container.
	 *
	 * @var    array
	 * @since  11.3
	 */
	protected static $instances = array();

	/**
	 * Media version added to assets
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $mediaVersion = null;

	/**
	 * Class constructor.
	 *
	 * @param   array  $options  Associative array of options
	 *
	 * @since   11.1
	 */
	public function __construct($options = array())
	{
		if (array_key_exists('lineend', $options))
		{
			$this->setLineEnd($options['lineend']);
		}

		if (array_key_exists('charset', $options))
		{
			$this->setCharset($options['charset']);
		}

		if (array_key_exists('language', $options))
		{
			$this->setLanguage($options['language']);
		}

		if (array_key_exists('direction', $options))
		{
			$this->setDirection($options['direction']);
		}

		if (array_key_exists('tab', $options))
		{
			$this->setTab($options['tab']);
		}

		if (array_key_exists('link', $options))
		{
			$this->setLink($options['link']);
		}

		if (array_key_exists('base', $options))
		{
			$this->setBase($options['base']);
		}

		if (array_key_exists('mediaversion', $options))
		{
			$this->setMediaVersion($options['mediaversion']);
		}
	}

	/**
	 * Returns the global JDocument object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param   string  $type        The document type to instantiate
	 * @param   array   $attributes  Array of attributes
	 *
	 * @return  object  The document object.
	 *
	 * @since   11.1
	 * @todo    This should allow custom class prefixes to be configured somehow
	 */
	public static function getInstance($type = 'html', $attributes = array())
	{
		$signature = serialize(array($type, $attributes));

		if (empty(self::$instances[$signature]))
		{
			$type  = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
			$ntype = null;

			// Determine the path and class
			$class = 'JDocument' . ucfirst($type);

			if (!class_exists($class))
			{
				// @deprecated 4.0 - JDocument objects should be autoloaded instead
				$path = __DIR__ . '/' . $type . '/' . $type . '.php';

				JLoader::register($class, $path);

				if (class_exists($class))
				{
					JLog::add('Non-autoloadable JDocument subclasses are deprecated, support will be removed in 4.0.', JLog::WARNING, 'deprecated');
				}
				// Default to the raw format
				else
				{
					$ntype = $type;
					$class = 'JDocumentRaw';
				}
			}

			// Check for a possible service from the container otherwise manually instantiate the class
			if (JFactory::getContainer()->exists($class))
			{
				$instance = JFactory::getContainer()->get($class);
			}
			else
			{
				$instance = new $class($attributes);
			}

			self::$instances[$signature] = $instance;

			if (!is_null($ntype))
			{
				// Set the type to the Document type originally requested
				$instance->setType($ntype);
			}
		}

		return self::$instances[$signature];
	}

	/**
	 * Set the document type
	 *
	 * @param   string  $type  Type document is to set to
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function setType($type)
	{
		$this->_type = $type;

		return $this;
	}

	/**
	 * Returns the document type
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public function getType()
	{
		return $this->_type;
	}

	/**
	 * Get the contents of the document buffer
	 *
	 * @return  mixed
	 *
	 * @since   11.1
	 */
	public function getBuffer()
	{
		return self::$_buffer;
	}

	/**
	 * Set the contents of the document buffer
	 *
	 * @param   string  $content  The content to be set in the buffer.
	 * @param   array   $options  Array of optional elements.
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function setBuffer($content, $options = array())
	{
		self::$_buffer = $content;

		return $this;
	}

	/**
	 * Gets a meta tag.
	 *
	 * @param   string  $name       Name of the meta HTML tag
	 * @param   string  $attribute  Attribute to use in the meta HTML tag
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public function getMetaData($name, $attribute = 'name')
	{
		// B/C old http_equiv parameter.
		if (!is_string($attribute))
		{
			$attribute = $attribute == true ? 'http-equiv' : 'name';
		}

		if ($name == 'generator')
		{
			$result = $this->getGenerator();
		}
		elseif ($name == 'description')
		{
			$result = $this->getDescription();
		}
		else
		{
			$result = isset($this->_metaTags[$attribute]) && isset($this->_metaTags[$attribute][$name]) ? $this->_metaTags[$attribute][$name] : '';
		}

		return $result;
	}

	/**
	 * Sets or alters a meta tag.
	 *
	 * @param   string  $name       Name of the meta HTML tag
	 * @param   string  $content    Value of the meta HTML tag
	 * @param   string  $attribute  Attribute to use in the meta HTML tag
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function setMetaData($name, $content, $attribute = 'name')
	{
		// B/C old http_equiv parameter.
		if (!is_string($attribute))
		{
			$attribute = $attribute == true ? 'http-equiv' : 'name';
		}

		if ($name == 'generator')
		{
			$this->setGenerator($content);
		}
		elseif ($name == 'description')
		{
			$this->setDescription($content);
		}
		else
		{
			$this->_metaTags[$attribute][$name] = $content;
		}

		return $this;
	}

	/**
	 * Adds a linked script to the page
	 *
	 * @param   string  $url      URL to the linked script.
	 * @param   array   $options  Array of options. Example: array('version' => 'auto', 'conditional' => 'lt IE 9')
	 * @param   array   $attribs  Array of attributes. Example: array('id' => 'scriptid', 'async' => 'async', 'data-test' => 1)
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 * @deprecated 4.0  The (url, mime, defer, async) method signature is deprecated, use (url, options, attributes) instead.
	 */
	public function addScript($url, $options = array(), $attribs = array())
	{
		// B/C before __DEPLOY_VERSION__
		if (!is_array($options) && (!is_array($attribs) || $attribs === array()))
		{
			JLog::add('The addScript method signature used has changed, use (url, options, attributes) instead.', JLog::WARNING, 'deprecated');

			$argList = func_get_args();
			$options = array();
			$attribs = array();

			// Old mime type parameter.
			if (!empty($argList[1]))
			{
				$attribs['mime'] = $argList[1];
			}

			// Old defer parameter.
			if (isset($argList[2]) && $argList[2])
			{
				$attribs['defer'] = true;
			}

			// Old async parameter.
			if (isset($argList[3]) && $argList[3])
			{
				$attribs['async'] = true;
			}
		}

		// Default value for type.
		if (!isset($attribs['type']) && !isset($attribs['mime']))
		{
			$attribs['type'] = 'text/javascript';
		}

		$this->_scripts[$url]            = isset($this->_scripts[$url]) ? array_replace($this->_scripts[$url], $attribs) : $attribs;
		$this->_scripts[$url]['options'] = isset($this->_scripts[$url]['options']) ? array_replace($this->_scripts[$url]['options'], $options) : $options;

		return $this;
	}

	/**
	 * Adds a linked script to the page with a version to allow to flush it. Ex: myscript.js?54771616b5bceae9df03c6173babf11d
	 * If not specified Joomla! automatically handles versioning
	 *
	 * @param   string  $url      URL to the linked script.
	 * @param   array   $options  Array of options. Example: array('version' => 'auto', 'conditional' => 'lt IE 9')
	 * @param   array   $attribs  Array of attributes. Example: array('id' => 'scriptid', 'async' => 'async', 'data-test' => 1)
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   3.2
	 * @deprecated 4.0  This method is deprecated, use addScript(url, options, attributes) instead.
	 */
	public function addScriptVersion($url, $options = array(), $attribs = array())
	{
		JLog::add('The method is deprecated, use addScript(url, attributes, options) instead.', JLog::WARNING, 'deprecated');

		// B/C before __DEPLOY_VERSION__
		if (!is_array($options) && (!is_array($attribs) || $attribs === array()))
		{
			$argList = func_get_args();
			$options = array();
			$attribs = array();

			// Old version parameter.
			$options['version'] = isset($argList[1]) && !is_null($argList[1]) ? $argList[1] : 'auto';

			// Old mime type parameter.
			if (!empty($argList[2]))
			{
				$attribs['mime'] = $argList[2];
			}

			// Old defer parameter.
			if (isset($argList[3]) && $argList[3])
			{
				$attribs['defer'] = true;
			}

			// Old async parameter.
			if (isset($argList[4]) && $argList[4])
			{
				$attribs['async'] = true;
			}
		}
		// Default value for version.
		else
		{
			$options['version'] = 'auto';
		}

		return $this->addScript($url, $options, $attribs);
	}

	/**
	 * Adds a script to the page
	 *
	 * @param   string  $content  Script
	 * @param   string  $type     Scripting mime (defaults to 'text/javascript')
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function addScriptDeclaration($content, $type = 'text/javascript')
	{
		if (!isset($this->_script[strtolower($type)]))
		{
			$this->_script[strtolower($type)] = $content;
		}
		else
		{
			$this->_script[strtolower($type)] .= chr(13) . $content;
		}

		return $this;
	}

	/**
	 * Add option for script
	 *
	 * @param   string  $key      Name in Storage
	 * @param   mixed   $options  Scrip options as array or string
	 * @param   bool    $merge    Whether merge with existing (true) or replace (false)
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   3.5
	 */
	public function addScriptOptions($key, $options, $merge = true)
	{
		if (empty($this->scriptOptions[$key]))
		{
			$this->scriptOptions[$key] = array();
		}

		if ($merge && is_array($options))
		{
			$this->scriptOptions[$key] = array_merge($this->scriptOptions[$key], $options);
		}
		else
		{
			$this->scriptOptions[$key] = $options;
		}

		return $this;
	}

	/**
	 * Get script(s) options
	 *
	 * @param   string  $key  Name in Storage
	 *
	 * @return  array  Options for given $key, or all script options
	 *
	 * @since   3.5
	 */
	public function getScriptOptions($key = null)
	{
		if ($key)
		{
			return (empty($this->scriptOptions[$key])) ? array() : $this->scriptOptions[$key];
		}
		else
		{
			return $this->scriptOptions;
		}
	}

	/**
	 * Adds a linked stylesheet to the page
	 *
	 * @param   string  $url      URL to the linked style sheet
	 * @param   array   $options  Array of options. Example: array('version' => 'auto', 'conditional' => 'lt IE 9')
	 * @param   array   $attribs  Array of attributes. Example: array('id' => 'stylesheet', 'data-test' => 1)
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 * @deprecated 4.0  The (url, mime, media, attribs) method signature is deprecated, use (url, options, attributes) instead.
	 */
	public function addStyleSheet($url, $options = array(), $attribs = array())
	{
		// B/C before __DEPLOY_VERSION__
		if (!is_array($options) && (!is_array($attribs) || $attribs === array()))
		{
			JLog::add('The addStyleSheet method signature used has changed, use (url, options, attributes) instead.', JLog::WARNING, 'deprecated');

			$argList = func_get_args();
			$options = array();
			$attribs = array();

			// Old mime type parameter.
			if (!empty($argList[1]))
			{
				$attribs['mime'] = $argList[1];
			}

			// Old media parameter.
			if (isset($argList[2]) && $argList[2])
			{
				$attribs['media'] = $argList[2];
			}

			// Old attribs parameter.
			if (isset($argList[3]) && $argList[3])
			{
				$attribs = array_replace($attribs, $argList[3]);
			}
		}

		// Default value for type.
		if (!isset($attribs['type']) && !isset($attribs['mime']))
		{
			$attribs['type'] = 'text/css';
		}

		$this->_styleSheets[$url] = isset($this->_styleSheets[$url]) ? array_replace($this->_styleSheets[$url], $attribs) : $attribs;

		if (isset($this->_styleSheets[$url]['options']))
		{
			$this->_styleSheets[$url]['options'] = array_replace($this->_styleSheets[$url]['options'], $options);
		}
		else
		{
			$this->_styleSheets[$url]['options'] = $options;
		}

		return $this;
	}

	/**
	 * Adds a linked stylesheet version to the page. Ex: template.css?54771616b5bceae9df03c6173babf11d
	 * If not specified Joomla! automatically handles versioning
	 *
	 * @param   string  $url      URL to the linked style sheet
	 * @param   array   $options  Array of options. Example: array('version' => 'auto', 'conditional' => 'lt IE 9')
	 * @param   array   $attribs  Array of attributes. Example: array('id' => 'stylesheet', 'data-test' => 1)
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   3.2
	 * @deprecated 4.0  This method is deprecated, use addStyleSheet(url, options, attributes) instead.
	 */
	public function addStyleSheetVersion($url, $options = array(), $attribs = array())
	{
		JLog::add('The method is deprecated, use addStyleSheet(url, attributes, options) instead.', JLog::WARNING, 'deprecated');

		// B/C before __DEPLOY_VERSION__
		if (!is_array($options) && (!is_array($attribs) || $attribs === array()))
		{
			$argList = func_get_args();
			$options = array();
			$attribs = array();

			// Old version parameter.
			$options['version'] = isset($argList[1]) && !is_null($argList[1]) ? $argList[1] : 'auto';

			// Old mime type parameter.
			if (!empty($argList[2]))
			{
				$attribs['mime'] = $argList[2];
			}

			// Old media parameter.
			if (isset($argList[3]) && $argList[3])
			{
				$attribs['media'] = $argList[3];
			}

			// Old attribs parameter.
			if (isset($argList[4]) && $argList[4])
			{
				$attribs = array_replace($attribs, $argList[4]);
			}
		}
		// Default value for version.
		else
		{
			$options['version'] = 'auto';
		}

		return $this->addStyleSheet($url, $options, $attribs);
	}

	/**
	 * Adds a stylesheet declaration to the page
	 *
	 * @param   string  $content  Style declarations
	 * @param   string  $type     Type of stylesheet (defaults to 'text/css')
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function addStyleDeclaration($content, $type = 'text/css')
	{
		if (!isset($this->_style[strtolower($type)]))
		{
			$this->_style[strtolower($type)] = $content;
		}
		else
		{
			$this->_style[strtolower($type)] .= chr(13) . $content;
		}

		return $this;
	}

	/**
	 * Sets the document charset
	 *
	 * @param   string  $type  Charset encoding string
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function setCharset($type = 'utf-8')
	{
		$this->_charset = $type;

		return $this;
	}

	/**
	 * Returns the document charset encoding.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public function getCharset()
	{
		return $this->_charset;
	}

	/**
	 * Sets the global document language declaration. Default is English (en-gb).
	 *
	 * @param   string  $lang  The language to be set
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function setLanguage($lang = "en-gb")
	{
		$this->language = strtolower($lang);

		return $this;
	}

	/**
	 * Returns the document language.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * Sets the global document direction declaration. Default is left-to-right (ltr).
	 *
	 * @param   string  $dir  The language direction to be set
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function setDirection($dir = "ltr")
	{
		$this->direction = strtolower($dir);

		return $this;
	}

	/**
	 * Returns the document direction declaration.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public function getDirection()
	{
		return $this->direction;
	}

	/**
	 * Sets the title of the document
	 *
	 * @param   string  $title  The title to be set
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}

	/**
	 * Return the title of the document.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set the assets version
	 *
	 * @param   string  $mediaVersion  Media version to use
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   3.2
	 */
	public function setMediaVersion($mediaVersion)
	{
		$this->mediaVersion = strtolower($mediaVersion);

		return $this;
	}

	/**
	 * Return the media version
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public function getMediaVersion()
	{
		return $this->mediaVersion;
	}

	/**
	 * Sets the base URI of the document
	 *
	 * @param   string  $base  The base URI to be set
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function setBase($base)
	{
		$this->base = $base;

		return $this;
	}

	/**
	 * Return the base URI of the document.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public function getBase()
	{
		return $this->base;
	}

	/**
	 * Sets the description of the document
	 *
	 * @param   string  $description  The description to set
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function setDescription($description)
	{
		$this->description = $description;

		return $this;
	}

	/**
	 * Return the title of the page.
	 *
	 * @return  string
	 *
	 * @since    11.1
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Sets the document link
	 *
	 * @param   string  $url  A url
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function setLink($url)
	{
		$this->link = $url;

		return $this;
	}

	/**
	 * Returns the document base url
	 *
	 * @return string
	 *
	 * @since   11.1
	 */
	public function getLink()
	{
		return $this->link;
	}

	/**
	 * Sets the document generator
	 *
	 * @param   string  $generator  The generator to be set
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function setGenerator($generator)
	{
		$this->_generator = $generator;

		return $this;
	}

	/**
	 * Returns the document generator
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public function getGenerator()
	{
		return $this->_generator;
	}

	/**
	 * Sets the document modified date
	 *
	 * @param   string  $date  The date to be set
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function setModifiedDate($date)
	{
		$this->_mdate = $date;

		return $this;
	}

	/**
	 * Returns the document modified date
	 *
	 * @return  string
	 *
	 * @since   11.1
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
	 * @param   string   $type  The document type to be sent
	 * @param   boolean  $sync  Should the type be synced with HTML?
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 *
	 * @link    http://www.w3.org/TR/xhtml-media-types
	 */
	public function setMimeEncoding($type = 'text/html', $sync = true)
	{
		$this->_mime = strtolower($type);

		// Syncing with metadata
		if ($sync)
		{
			$this->setMetaData('content-type', $type . '; charset=' . $this->_charset, true);
		}

		return $this;
	}

	/**
	 * Return the document MIME encoding that is sent to the browser.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public function getMimeEncoding()
	{
		return $this->_mime;
	}

	/**
	 * Sets the line end style to Windows, Mac, Unix or a custom string.
	 *
	 * @param   string  $style  "win", "mac", "unix" or custom string.
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
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

		return $this;
	}

	/**
	 * Returns the lineEnd
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public function _getLineEnd()
	{
		return $this->_lineEnd;
	}

	/**
	 * Sets the string used to indent HTML
	 *
	 * @param   string  $string  String used to indent ("\11", "\t", '  ', etc.).
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function setTab($string)
	{
		$this->_tab = $string;

		return $this;
	}

	/**
	 * Returns a string containing the unit for indenting HTML
	 *
	 * @return  string
	 *
	 * @since   11.1
	 */
	public function _getTab()
	{
		return $this->_tab;
	}

	/**
	 * Load a renderer
	 *
	 * @param   string  $type  The renderer type
	 *
	 * @return  JDocumentRenderer
	 *
	 * @since   11.1
	 * @throws  RuntimeException
	 */
	public function loadRenderer($type)
	{
		// New class name format adds the format type to the class name
		$class = 'JDocumentRenderer' . ucfirst($this->getType()) . ucfirst($type);

		if (!class_exists($class))
		{
			// "Legacy" class name structure
			$class = 'JDocumentRenderer' . $type;

			if (!class_exists($class))
			{
				// @deprecated 4.0 - Non-autoloadable class support is deprecated, only log a message though if a file is found
				$path = __DIR__ . '/' . $this->getType() . '/renderer/' . $type . '.php';

				if (!file_exists($path))
				{
					throw new RuntimeException('Unable to load renderer class', 500);
				}

				JLoader::register($class, $path);

				JLog::add('Non-autoloadable JDocumentRenderer subclasses are deprecated, support will be removed in 4.0.', JLog::WARNING, 'deprecated');

				// If the class still doesn't exist after including the path, we've got issues
				if (!class_exists($class))
				{
					throw new RuntimeException('Unable to load renderer class', 500);
				}
			}
		}

		return new $class($this);
	}

	/**
	 * Parses the document and prepares the buffers
	 *
	 * @param   array  $params  The array of parameters
	 *
	 * @return  JDocument instance of $this to allow chaining
	 *
	 * @since   11.1
	 */
	public function parse($params = array())
	{
		return $this;
	}

	/**
	 * Outputs the document
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
		$app = JFactory::getApplication();

		if ($mdate = $this->getModifiedDate())
		{
			$app->modifiedDate = $mdate;
		}

		$app->mimeType = $this->_mime;
		$app->charSet  = $this->_charset;
	}
}
