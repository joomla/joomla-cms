<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport('joomla.template.template');
jimport('joomla.module');

/**
 * Document class, provides an easy interface to parse and display a document
 *
 * @author Johan Janssens <johan@joomla.be>
 * @subpackage JDocument
 * @abstract
 * @since 1.1
 * @see patTemplate
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
     * Array of renderers
     *
     * @var       array
     * @access    private
     */
	var $_renderers = array();


	/**
	* Class constructor
	* 
	* @access protected
	* @param  string	$type (either html or tex)
	* @param	array	$attributes Associative array of attributes
	* @see JDocument
	*/
	function __construct($type, $attributes = array())
	{
		parent::__construct($type);
		
		if (isset($attributes['lineend'])) {
            $this->setLineEnd($attributes['lineend']);
        }

        if (isset($attributes['charset'])) {
            $this->setCharset($attributes['charset']);
        }

        if (isset($attributes['language'])) {
            $this->setLang($attributes['language']);
        }

        if (isset($attributes['tab'])) {
            $this->setTab($attributes['tab']);
        }
		
		//set the namespace
		$this->setNamespace( 'jdoc' );
		
		//add module directories
		$this->addModuleDir('Function'    ,	dirname(__FILE__). DS. 'modules'. DS .'functions');
		$this->addModuleDir('OutputFilter', dirname(__FILE__). DS. 'modules'. DS .'filters'  );
		$this->addModuleDir('Renderer'    , dirname(__FILE__). DS. 'modules'. DS .'renderers');
		
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
			jimport('joomla.document.adapters.'.$type);
			$adapter = 'JDocument'.$type;
			$instances[$signature] = new $adapter($type, $attributes);
		}

		return $instances[$signature];
	}

	 /**
     * Adds a linked script to the page
     *
     * @param    string  $url        URL to the linked script
     * @param    string  $type       Type of script. Defaults to 'text/javascript'
     * @access   public
     */
    function addScript($url, $type="text/javascript")
    {
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
    function addStyleDeclaration($content, $type = 'text/css')
    {
        $this->_style[][strtolower($type)] = $content;
    }

	 /**
     * Sets the document charset
     *
     * @param   string   $type  Charset encoding string
     * @access  public
     * @return  void
     */
    function setCharset($type = 'utf-8')
	{
        $this->_charset = $type;
    }

	/**
     * Returns the document charset encoding.
     *
     * @access public
     * @return string
     */
    function getCharset()
	{
        return $this->_charset;
    }

	/**
     * Sets the global document language declaration. Default is English.
     *
     * @access public
     * @param   string   $lang
     */
    function setLang($lang = "eng_GB")
	{
        $this->_language = strtolower($lang);
    }

	/**
     * Returns the document language.
     *
     * @return string
     * @access public
     */
    function getLang()
	{
        return $this->_language;
    }

	/**
     * Sets the title of the page
     *
     * @param    string    $title
     * @access   public
     */
    function setTitle($title)
    {
        global $mainframe;
		
		if($mainframe->getCfg('pagetitles'))
		{
			$title = trim( htmlspecialchars( $title ));
			$site  = $mainframe->getCfg('sitename');
			
			$this->_title  = $title ? $site . ' - '. $title : $site;
		}
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
    function _getLineEnd()
    {
        return $this->_lineEnd;
    }

	/**
     * Sets the string used to indent HTML
     *
     * @param     string    $string     String used to indent ("\11", "\t", '  ', etc.).
     * @access    public
     * @return    void
     */
    function setTab($string)
    {
        $this->_tab = $string;
    }

	 /**
     * Returns a string containing the unit for indenting HTML
     *
     * @access    private
     * @return    string
     */
    function _getTab()
    {
        return $this->_tab;
    }
	
	/**
	 * Execute a renderer
	 *
	 * @access public
	 * @param string 	$type	The type of renderer
	 * @param string 	$name	The name of the element to render
	 * @param array 	$params	Associative array of values
	 * @return 	The output of the renderer
	 */
	function execRenderer($type, $name, $params = array()) 
	{
		jimport('joomla.document.modules.renderer');
		$module =& $this->loadModule( 'Renderer', ucfirst($type));
		return $module->render($name, $params);
	}

	/**
	 * Parse a file and create an internal patTemplate object
	 *
	 * @access public
	 * @param string 	$directory	The directory to look for the file
	 * @param string 	$filename	The actual filename
	 */
	function parse($directory, $filename = 'index.php')
	{
		$contents = $this->_load($directory, $filename);
		$this->readTemplatesFromInput( $contents, 'String' );
	}
	
	/**
	 * Outputs the template to the browser.
	 *
	 * @access public
	 * @param string 	$template	The name of the template
	 * @param boolean 	$compress	If true, compress the output using Zlib compression
	 */
	function display($template, $compress = true)
	{
		foreach($this->_renderers as $type => $names) 
		{
			foreach($names as $name) 
			{
				$html = $this->execRenderer($type, $name);
				$this->addGlobalVar($type.'_'.$name, $html);
			}
		}

		if($compress) {
			$this->applyOutputFilter('Zlib');
		}
		
		// Set mime type and character encoding
        header('Content-Type: ' . $this->_mime .  '; charset=' . $this->_charset);

		parent::display( $name );
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
	 * Load a template file
	 *
	 * @param string 	$template	The name of the template
	 * @param string 	$filename	The actual filename
	 * @return string The contents of the template 
	 */
	function _load($template, $filename)
	{
		global $mainframe, $my, $acl, $database;
		global $Itemid, $task, $option, $_VERSION;

		$contents = '';
		if ( file_exists( 'templates'.DS.$template.DS.$filename ) ) {
			
			$this->addGlobalVar( 'template', $mainframe->getTemplate());

			ob_start();
			?><jdoc:tmpl name="<?php echo $filename ?>" autoclear="yes"><?php
				require_once( 'templates'.DS.$template.DS.$filename );
			?></jdoc:tmpl><?php
			$contents = ob_get_contents();
			ob_end_clean();
		}
		
		return $contents;
	}
	
	/**
	 * Adds a renderer to be called 
	 *
	 * @param string 	$type	The renderer type
	 * @param string 	$name	The renderer name
	 * @return string The contents of the template 
	 */
	function _addRenderer($type, $name)
	{
		$this->_renderers[$type][] = $name;
	}
	
	/**
	 * Module callback function
	 * 
	 * @abstract
	 * @access protected
	 * @param string $module 	The name of the module 
	 * @param array	 $params	Array of module parameters
	 * @return string	The result to be inserted in the template
	 */
	function _moduleCallback($module, $params = array())
	{
		return '';
	}
}
?>