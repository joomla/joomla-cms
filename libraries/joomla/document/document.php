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

jimport('joomla.template.template');
jimport('joomla.application.extension.module');

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

class JDocument extends JTemplate
{
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
     * Contains the page language setting
     *
     * @var     string
     * @access  private
     */
    var $_language = 'en';
	
	/**
     * Contains the page direction setting
     *
     * @var     string
     * @access  private
     */
    var $_direction = 'ltr';

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
     * Page title
     *
     * @var     string
     * @access  private
     */
    var $_title = '';
	
	/**
     * Array of meta tags
     *
     * @var     array
     * @access  private
     */
    var $_metaTags = array( 'standard' => array ( 'Generator' => 'Joomla! 1.5' ) );
	

	/**
	* Class constructor
	* 
	* @access protected
	* @param	array	$attributes Associative array of attributes
	* @see JDocument
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
		
		//set the namespace
		$this->setNamespace( 'jdoc' );
		
		//add module directories
		$this->addModuleDir('Function'    ,	dirname(__FILE__). DS. 'module'. DS .'function');
		$this->addModuleDir('OutputFilter', dirname(__FILE__). DS. 'module'. DS .'filter'  );
		$this->addModuleDir('Renderer'    , dirname(__FILE__). DS. 'module'. DS .'renderer');
	}

	/**
	 * Returns a reference to the global JDocument object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $document = &JDocument::getInstance();</pre>
	 *
	 * @param type $type The document type to instantiate
	 * @access public
	 * @return jdocument  The document object.
	 */
	function &getInstance($type = 'html', $attributes = array())
	{
		static $instances;
		
		if (!isset( $instances )) {
			$instances = array();
		}
		
		$signature = serialize(array($type, $attributes));

		if (empty($instances[$signature])) {
			jimport('joomla.document.document.'.$type);
			$adapter = 'JDocument'.$type;
			$instances[$signature] = new $adapter($attributes);
		}

		return $instances[$signature];
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
    function addScriptDeclaration($content, $type = 'text/javascript')
    {
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
        $this->_language = strtolower($lang);
    }

	/**
     * Returns the document language.
     *
     * @return string
     * @access public
     */
    function getLanguage() {
        return $this->_language;
    }
	
	/**
     * Sets the global document direction declaration. Default is left-to-right (ltr).
     *
     * @access public
     * @param   string   $lang
     */
    function setDirection($dir = "ltr") {
        $this->_direction = strtolower($dir);
    }

	/**
     * Returns the document language.
     *
     * @return string
     * @access public
     */
    function getDirection() {
        return $this->_direction;
    }

	/**
     * Sets the title of the page
     *
     * @param    string    $title
     * @access   public
     */
    function setTitle($title) {
		$this->_title = $title;
    }

	/**
     * Return the title of the page.
     *
     * @return   string
     * @access   public
     */
    function getTitle() {
        return $this->_title;
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
	 * Outputs the template to the browser.
	 *
	 * @access public
	 * @param string 	$template	The name of the template
	 * @param boolean 	$file		If true, compress the output using Zlib compression
	 * @param boolean 	$compress	If true, will display information about the placeholders
	 * @param array		$params	    Associative array of attributes
	 */
	function display( $template, $file, $compress = false, $params = array())
	{
		if($compress) {
			$this->applyOutputFilter('Zlib');
		}
		
		// Set mime type and character encoding
        header('Content-Type: ' . $this->_mime .  '; charset=' . $this->_charset);

		parent::display( 'document' );
	}

	/**
     * Return the document head
     *
     * @abstract
     * @access public
     * @return string
     */
    function fetchHead() {
		return '';
    }

	/**
     * Return the document body
     *
     * @abstract
     * @access public
     * @return string
     */
    function fetchBody() {
		return '';
    }

	 /**
	* load from template cache
	*
	* @access	private
	* @param	string	name of the input (filename, shm segment, etc.)
	* @param	string	driver that is used as reader, you may also pass a Reader object
	* @param	array	options for the reader
	* @param	string	cache key
	* @return	array|boolean	either an array containing the templates, or false
	*/
	function _loadTemplatesFromCache( $input, &$reader, $options, $key )
	{
		$stat	=	&$this->loadModule( 'Stat', 'File' );
		$stat->setOptions( $options );

		/**
		 * get modification time
		 */
		$modTime   = $stat->getModificationTime( $this->_file );
		$templates = $this->_tmplCache->load( $key, $modTime );
		
		return $templates;
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
 }
?>