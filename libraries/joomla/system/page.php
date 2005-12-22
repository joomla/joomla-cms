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

jimport( 'joomla.system.object' );

/**
 * Page class, provides a simple interface for generating an XHTML compliant page header
 *
 * This module has many influences from the PEAR::HTML_Page2 package
 *
 * @author Johan Janssens <johan@joomla.be>
 * @package Joomla
 * @subpackage JFramework
 * @since 1.1
 */

class JPage extends JObject
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
     * Contains the base url
     *
     * @var     string
     * @access  private
     */
    var $_base = '';

    /**
     * Contains the page language setting
     *
     * @var     string
     * @access  private
     */
    var $_language = 'en';

    /**
     * Array of Header <link> tags
     *
     * @var     array
     * @access  private
     */
    var $_links = array();

    /**
     * Array of meta tags
     *
     * @var     array
     * @access  private
     */
    var $_metaTags = array( 'standard' => array ( 'Generator' => 'Joomla! 1.1' ) );

	/**
     * Document mime type
     *
     * @var      string
     * @access   private
     */
    var $_mime = 'text/html';

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
     * HTML page title
     *
     * @var     string
     * @access  private
     */
    var $_title = '';

	/**
     * Array of custom tags
     *
     * @var     string
     * @access  private
     */
    var $_custom = array();

	/**
	 * Create a page instance (Constructor).
	 */
	function __construct($attributes = array())  {

        if (isset($attributes['lineend'])) {
            $this->setLineEnd($attributes['lineend']);
        }

        if (isset($attributes['charset'])) {
            $this->setCharset($attributes['charset']);
        }
		
		 if (isset($attributes['base'])) {
            $this->setBase($attributes['base']);
        }

        if (isset($attributes['language'])) {
            $this->setLang($attributes['language']);
        }

        if (isset($attributes['mime'])) {
            $this->setMimeEncoding($attributes['mime']);
        }

        if (isset($attributes['tab'])) {
            $this->setTab($attributes['tab']);
        }
	}

	/**
	 * Returns a reference to the global Page object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $browser = &JPage::getInstance();</pre>
	 *
	 * @return JPage  The Page object.
	 */
	function &getInstance($attributes = array())
	{
		static $instances;

		if (!isset($instances)) {
			$instances = array();
			$instances[0] = new JPage($attributes);
		}

		return $instances[0];
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
    function addStyleSheet($url, $type = 'text/css', $media = null)
    {
        $this->_styleSheets[$url]['mime']  = $type;
        $this->_styleSheets[$url]['media'] = $media;
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
     * Adds a shortcut icon (favicon)
     *
     * <p>This adds a link to the icon shown in the favorites list or on
     * the left of the url in the address bar. Some browsers display
     * it on the tab, as well.</p>
     *
     * @param     string  $href        The link that is being related.
     * @param     string  $type        File type
     * @param     string  $relation    Relation of link
     * @access    public
     */
    function addFavicon($href, $type = 'image/x-icon', $relation = 'shortcut icon')
	{
        $this->_links[] = "<link href=\"$href\" rel=\"$relation\" type=\"$type\"";
    }

	  /**
     * Adds <link> tags to the head of the document
     *
     * <p>$relType defaults to 'rel' as it is the most common relation type used.
     * ('rev' refers to reverse relation, 'rel' indicates normal, forward relation.)
     * Typical tag: <link href="index.php" rel="Start"></p>
     *
     * @access   public
     * @param    string  $href       The link that is being related.
     * @param    string  $relation   Relation of link.
     * @param    string  $relType    Relation type attribute.  Either rel or rev (default: 'rel').
     * @param    array   $attributes Associative array of remaining attributes.
     * @return   void
     */
    function addHeadLink($href, $relation, $relType = 'rel', $attributes = array())
	{
        $attribs = mosHTML::_implode_assoc('=', ' ', $attribs);
        $generatedTag = "<link href=\"$href\" $relType=\"$relation\"" . $attribs;
        $this->_links[] = $generatedTag;
    }

	/**
	 * Adds a custom html string to the head block
	 *
	 * @param string The html to add to the head
	 * @access   public
	 * @return   void
	 */

	function addCustomTag( $html )
	{
		$this->_custom[] = trim( $html );
	}
	
	 /**
     * Sets the document base tag
     *
     * @param   string   $url  The url used in the base tag
     * @access  public
     * @return  void
     */
    function setBase($url)
	{
        $this->_base = $url;
    }
	
	/**
     * Returns the document base url
     *
     * @access public
     * @return string
     */
    function getBase()
	{
        return $this->_base;
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
     * Sets an http-equiv Content-Type meta tag
     *
     * @access   public
     * @return   void
     */
    function setMetaContentType()
    {
        $this->setMetaData('Content-Type', $this->_mime . '; charset=' . $this->_charset , true );
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
    function setMimeEncoding($type = 'text/html')
    {
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
     * Outputs the HTML content to the browser
     *
     * @access   public
     */
    function display()
    {
        // Set mime type and character encoding
        header('Content-Type: ' . $this->_mime .  '; charset=' . $this->_charset);

    }

	/**
     * Generates the HTML string for the <head> tag
     *
     * @return string
     * @access private
     */
    function renderHead()
    {
        // get line endings
        $lnEnd = $this->_getLineEnd();
        $tab = $this->_getTab();

		$tagEnd = ' />';

		$strHtml  = $tab . '<title>' . $this->getTitle() . '</title>' . $lnEnd;
		$strHtml .= $tab . '<base href=' . $this->getBase() . ' />' . $lnEnd;

        // Generate META tags
        foreach ($this->_metaTags as $type => $tag) {
            foreach ($tag as $name => $content) {
                if ($type == 'http-equiv') {
                    $strHtml .= $tab . "<meta http-equiv=\"$name\" content=\"$content\"" . $tagEnd . $lnEnd;
                } elseif ($type == 'standard') {
                    $strHtml .= $tab . "<meta name=\"$name\" content=\"$content\"" . $tagEnd . $lnEnd;
                }
            }
        }

        // Generate link declarations
        foreach ($this->_links as $link) {
            $strHtml .= $tab . $link . $tagEnd . $lnEnd;
        }

        // Generate stylesheet links
        foreach ($this->_styleSheets as $strSrc => $strAttr ) {
            $strHtml .= $tab . "<link rel=\"stylesheet\" href=\"$strSrc\" type=\"".$strAttr['mime'].'"';
            if (!is_null($strAttr['media'])){
                $strHtml .= ' media="'.$strAttr['media'].'"';
            }
            $strHtml .= $tagEnd . $lnEnd;
        }

        // Generate stylesheet declarations
        foreach ($this->_style as $styledecl) {
            foreach ($styledecl as $type => $content) {
                $strHtml .= $tab . '<style type="' . $type . '">' . $lnEnd;

                // This is for full XHTML support.
                if ($this->_mime == 'text/html' ) {
                    $strHtml .= $tab . $tab . '<!--' . $lnEnd;
                } else {
                    $strHtml .= $tab . $tab . '<![CDATA[' . $lnEnd;
                }

				$strHtml .= $content . $lnEnd;

                // See above note
                if ($this->_mime == 'text/html' ) {
                    $strHtml .= $tab . $tab . '-->' . $lnEnd;
                } else {
                    $strHtml .= $tab . $tab . ']]>' . $lnEnd;
                }
                $strHtml .= $tab . '</style>' . $lnEnd;
            }
        }

        // Generate script file links
        foreach ($this->_scripts as $strSrc => $strType) {
            $strHtml .= $tab . "<script type=\"$strType\" src=\"$strSrc\"></script>" . $lnEnd;
        }

        // Generate script declarations
        foreach ($this->_script as $script) {
            foreach ($script as $type => $content) {
                $strHtml .= $tab . '<script type="' . $type . '">' . $lnEnd;

                // This is for full XHTML support.
                if ($this->_mime == 'text/html' ) {
                    $strHtml .= $tab . $tab . '// <!--' . $lnEnd;
                } else {
                    $strHtml .= $tab . $tab . '<![CDATA[' . $lnEnd;
                }

				$strHtml .= $content . $lnEnd;

                // See above note
                if ($this->_mime == 'text/html' ) {
                    $strHtml .= $tab . $tab . '// -->' . $lnEnd;
                } else {
                    $strHtml .= $tab . $tab . '// ]]>' . $lnEnd;
                }
                $strHtml .= $tab . '</script>' . $lnEnd;
            }
        }

		foreach($this->_custom as $custom) {
			$strHtml .= $tab . $custom .$lnEnd;
		}

        return $strHtml;
    }
}

?>