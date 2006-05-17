<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Document class, provides an easy interface to parse and display a document
 *
 * @abstract
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 * @see			patTemplate
 */

class JDocument extends JObject
{
	/**
     * Document title
     *
     * @var     string
     * @access  public
     */
    var $title = '';
	
	/**
     * Document description
     *
     * @var     string
     * @access  public
     */
    var $description = '';
	
	/**
     * Document base URL
     *
     * @var     string
     * @access  public
     */
    var $link = '';
	
	 /**
     * Contains the document language setting
     *
     * @var     string
     * @access  public
     */
    var $language = 'en';
	
	/**
     * Contains the document direction setting
     *
     * @var     string
     * @access  public
     */
    var $direction = 'ltr';
	
	/**
     * Document generator
     *
     * @var       string
     * @access    public
     */
	 var $_generator = 'Joomla! 1.5';

	/**
     * Tab string
     *
     * @var       string
     * @access    private
     */
    var $_tab = "\11";

    /**
     * Contains the line end string
     *
     * @var       string
     * @access    private
     */
    var $_lineEnd = "\12";

	/**
     * Contains the character encoding string
     *
     * @var     string
     * @access  private
     */
    var $_charset = 'utf-8';

	/**
     * Document mime type
     *
     * @var      string
     * @access   private
     */
    var $_mime = '';

	/**
     * Document namespace
     *
     * @var      string
     * @access   private
     */
    var $_namespace = '';

    /**
     * Document profile
     *
     * @var      string
     * @access   private
     */
    var $_profile = '';

    /**
     * Array of linked scripts
     *
     * @var      array
     * @access   private
     */
    var $_scripts = array();

	/**
     * Array of scripts placed in the header
     *
     * @var  array
     * @access   private
     */
    var $_script = array();

	 /**
     * Array of linked style sheets
     *
     * @var     array
     * @access  private
     */
    var $_styleSheets = array();

	/**
     * Array of included style declarations
     *
     * @var     array
     * @access  private
     */
    var $_style = array();

	/**
     * Array of meta tags
     *
     * @var     array
     * @access  private
     */
    var $_metaTags = array();
	
	/**
     * The rendering engine
     *
     * @var     object
     * @access  private
     */
    var $_engine = null;
	
	/**
     * The document type
     *
     * @var     string
     * @access  private
     */
    var $_type = null;


	/**
	* Class constructor
	*
	* @access protected
	* @param	array	$attributes Associative array of attributes
	*/
	function __construct( $attributes = array())
	{
		parent::__construct();

		if (isset($attributes['lineend'])) {
            $this->setLineEnd($attributes['lineend']);
        }

        if (isset($attributes['charset'])) {
            $this->setCharset($attributes['charset']);
        }

        if (isset($attributes['language'])) {
            $this->setLanguage($attributes['language']);
        }

		 if (isset($attributes['direction'])) {
            $this->setDirection($attributes['direction']);
        }

        if (isset($attributes['tab'])) {
            $this->setTab($attributes['tab']);
        }
		
		if (isset($attributes['link'])) {
            $this->setLink($attributes['link']);
        }
	}

	/**
	 * Returns a reference to the global JDocument object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $document = &JDocument::getInstance();</pre>
	 *
	 * @access public
	 * @param type $type The document type to instantiate
	 * @return object  The document object.
	 */
	function &getInstance($type = 'html', $attributes = array())
	{
		static $instances;

		if (!isset( $instances )) {
			$instances = array();
		}

		$signature = serialize(array($type, $attributes));

		if (empty($instances[$signature])) {
			jimport('joomla.document.'.$type.'.'.$type);
			$class = 'JDocument'.$type;
			$instances[$signature] = new $class($attributes);
		}

		return $instances[$signature];
	}
	
	 /**
     * Returns the document type
     *
     * @access    public
     * @return    string
     */
    function getType() {
        return $this->_type;
    }

	/**
     * Sets or alters a meta tag.
     *
     * @param string  $name           Value of name or http-equiv tag
     * @param string  $content        Value of the content tag
     * @param bool    $http_equiv     META type "http-equiv" defaults to null
     * @return void
     * @access public
     */
    function setMetaData($name, $content, $http_equiv = false)
    {
        if ($content == '') {
            $this->unsetMetaData($name, $http_equiv);
        } else {
            if ($http_equiv == true) {
                $this->_metaTags['http-equiv'][$name] = $content;
            } else {
                $this->_metaTags['standard'][$name] = $content;
            }
        }
    }

	 /**
     * Unsets a meta tag.
     *
     * @param string  $name           Value of name or http-equiv tag
     * @param bool    $http_equiv     META type "http-equiv" defaults to null
     * @return void
     * @access public
     */
    function unsetMetaData($name, $http_equiv = false)
    {
        if ($http_equiv == true) {
            unset($this->_metaTags['http-equiv'][$name]);
        } else {
            unset($this->_metaTags['standard'][$name]);
        }
    }

	 /**
     * Adds a linked script to the page
     *
     * @param    string  $url        URL to the linked script
     * @param    string  $type       Type of script. Defaults to 'text/javascript'
     * @access   public
     */
    function addScript($url, $type="text/javascript") {
        $this->_scripts[$url] = $type;
    }

	/**
     * Adds a script to the page
     *
     * @access   public
     * @param    string  $content   Script
     * @param    string  $type      Scripting mime (defaults to 'text/javascript')
     * @return   void
     */
    function addScriptDeclaration($content, $type = 'text/javascript') {
        $this->_script[][strtolower($type)] =& $content;
    }

	/**
     * Adds a linked stylesheet to the page
     *
     * @param    string  $url    URL to the linked style sheet
     * @param    string  $type   Mime encoding type
     * @param    string  $media  Media type that this stylesheet applies to
     * @access   public
     */
    function addStyleSheet($url, $type = 'text/css', $media = null, $attribs = array())
    {
        $this->_styleSheets[$url]['mime']    = $type;
        $this->_styleSheets[$url]['media']   = $media;
		$this->_styleSheets[$url]['attribs'] = $attribs;
    }

	 /**
     * Adds a stylesheet declaration to the page
     *
     * @param    string  $content   Style declarations
     * @param    string  $type      Type of stylesheet (defaults to 'text/css')
     * @access   public
     * @return   void
     */
    function addStyleDeclaration($content, $type = 'text/css') {
        $this->_style[][strtolower($type)] = $content;
    }

	 /**
     * Sets the document charset
     *
     * @param   string   $type  Charset encoding string
     * @access  public
     * @return  void
     */
    function setCharset($type = 'utf-8') {
        $this->_charset = $type;
    }

	/**
     * Returns the document charset encoding.
     *
     * @access public
     * @return string
     */
    function getCharset() {
        return $this->_charset;
    }

	/**
     * Sets the global document language declaration. Default is English (en).
     *
     * @access public
     * @param   string   $lang
     */
    function setLanguage($lang = "en") {
        $this->language = strtolower($lang);
    }

	/**
     * Returns the document language.
     *
     * @return string
     * @access public
     */
    function getLanguage() {
        return $this->language;
    }

	/**
     * Sets the global document direction declaration. Default is left-to-right (ltr).
     *
     * @access public
     * @param   string   $lang
     */
    function setDirection($dir = "ltr") {
        $this->direction = strtolower($dir);
    }

	/**
     * Returns the document language.
     *
     * @return string
     * @access public
     */
    function getDirection() {
        return $this->direction;
    }

	/**
     * Sets the title of the document
     *
     * @param    string    $title
     * @access   public
     */
    function setTitle($title) {
		$this->title = $title;
    }

	/**
     * Return the title of the document.
     *
     * @return   string
     * @access   public
     */
    function getTitle() {
        return $this->title;
    }
	
	/**
     * Sets the description of the document
     *
     * @param    string    $title
     * @access   public
     */
    function setDescription($description) {
		$this->description = $description;
    }

	/**
     * Return the title of the page.
     *
     * @return   string
     * @access   public
     */
    function getDescription() {
        return $this->description;
    }
	
	 /**
     * Sets the document link
     *
     * @param   string   $url  A url
     * @access  public
     * @return  void
     */
    function setLink($url) {
        $this->link = $url;
    }

	/**
     * Returns the document base url
     *
     * @access public
     * @return string
     */
    function getLink() {
        return $this->link;
    }
	
	 /**
     * Sets the document generator
     *
     * @param   string 
     * @access  public
     * @return  void
     */
    function setGenerator($generator) {
        $this->_generator = $generator;
    }

	/**
     * Returns the document generator
     *
     * @access public
     * @return string
     */
    function getGenerator() {
        return $this->_generator;
    }

	 /**
     * Sets the document MIME encoding that is sent to the browser.
     *
     * <p>This usually will be text/html because most browsers cannot yet
     * accept the proper mime settings for XHTML: application/xhtml+xml
     * and to a lesser extent application/xml and text/xml. See the W3C note
     * ({@link http://www.w3.org/TR/xhtml-media-types/
     * http://www.w3.org/TR/xhtml-media-types/}) for more details.</p>
     *
     * @param    string    $type
     * @access   public
     * @return   void
     */
    function setMimeEncoding($type = 'text/html') {
        $this->_mime = strtolower($type);
    }

	 /**
     * Sets the line end style to Windows, Mac, Unix or a custom string.
     *
     * @param   string  $style  "win", "mac", "unix" or custom string.
     * @access  public
     * @return  void
     */
    function setLineEnd($style)
    {
        switch ($style) {
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
     * @access    private
     * @return    string
     */
    function _getLineEnd() {
        return $this->_lineEnd;
    }

	/**
     * Sets the string used to indent HTML
     *
     * @param     string    $string     String used to indent ("\11", "\t", '  ', etc.).
     * @access    public
     * @return    void
     */
    function setTab($string) {
        $this->_tab = $string;
    }

	 /**
     * Returns a string containing the unit for indenting HTML
     *
     * @access    private
     * @return    string
     */
    function _getTab() {
        return $this->_tab;
    }
	
	/**
	* Load a renderer
	*
	* @access	public
	* @param	string	The renderer type
	* @return	object
	* @since 1.5
	*/
	function loadRenderer( $type ) 
	{
		if( !class_exists( 'JDocumentRenderer' ) ) {
			jimport('joomla.document.renderer');
		}

		$class	=	'JDocumentRenderer_' . $type;
		
		if( !class_exists( $class ) ) {
			if(!file_exists(dirname(__FILE__).DS.$this->_type.DS.'renderer'.DS.$type.'.php')) {
				return null;
			}
			//import renderer
			jimport('joomla.document.'.$this->_type.'.renderer.'.$type);
		}
	
		if( !class_exists( $class ) ) {
			return null;
		}

		$instance = new $class($this);

		return $instance;
	}

	/**
	 * Outputs the document to the client
	 *
	 * @access public
	 * @param boolean 	$cache		If true, cache the output 
	 * @param boolean 	$compress	If true, compress the output
	 * @param array		$params	    Associative array of attributes
	 */
	function display( $cache = false, $compress = false, $params = array())
	{
	
	}
	
   /**
	* Compress the data
	*
    * Checks the accept encoding of the browser and compresses the data before 
    * sending it to the client.
	*
	* @access	public
	* @param	string		data
	* @return	string		compressed data
	*/
	function compress( $data )
	{
		$encoding = JDocumentHelper::clientEncoding();

        if (!$encoding)
			return $data;

		if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
			return $data;
        }

		if (headers_sent())
			return $data;

        if (connection_status() !== 0)
			return $data;


        $level = 4; //ideal level

		$size = strlen($data);
        $crc  = crc32($data);

        $gzdata = "\x1f\x8b\x08\x00\x00\x00\x00\x00";
		$gzdata .= gzcompress($data, $level);

		$gzdata  = substr($gzdata, 0, strlen($gzdata) - 4);
        $gzdata .= pack("V",$crc) . pack("V", $size);


		Header('Content-Encoding: ' . $encoding);
        Header('Content-Length: ' . strlen($gzdata));
        Header('X-Content-Encoded-By: Joomla! 1.5');

		return $gzdata;
	}
}

/**
 * Document helper functions
 *
 * @static
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */
 class JDocumentHelper
 {
	function implodeAttribs($inner_glue = "=", $outer_glue = "\n", $array = null, $keepOuterKey = false)
    {
        $output = array();

        foreach($array as $key => $item)
        if (is_array ($item)) {
            if ($keepOuterKey)
                $output[] = $key;
            // This is value is an array, go and do it again!
            $output[] = JDocumentHelper::implodeAttribs($inner_glue, $outer_glue, $item, $keepOuterKey);
        } else
            $output[] = $key . $inner_glue . '"'.$item.'"';

        return implode($outer_glue, $output);
    }
	
	 /**
	* check, whether client supports compressed data
	*
	* @access	private
	* @return	boolean
	*/
	function clientEncoding()
	{
		if (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
			return false;
		}

		$encoding = false;

		if (false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
			$encoding = 'gzip';
		}

		if (false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip')) {
			$encoding = 'x-gzip';
        }

		return $encoding;
	}
 }
?>