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
 * DocumentHTML class, provides an easy interface to parse and display an html document
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */

class JDocumentHTML extends JDocument
{
	/**
     * Contains the base url
     *
     * @var     string
     * @access  private
     */
    var $_base = '';

	 /**
     * Array of Header <link> tags
     *
     * @var     array
     * @access  private
     */
    var $_links = array();

	/**
     * Array of custom tags
     *
     * @var     string
     * @access  private
     */
    var $_custom = array();

	/**
     * Array of renderers
     *
     * @var       array
     * @access    private
     */
	var $_renderers = array();

	/**
	 * Class constructore
	 *
	 * @access protected
	 * @param	string	$type 		(either html or tex)
	 * @param	array	$attributes Associative array of attributes
	 */
	function __construct($attributes = array())
	{
		parent::__construct($attributes);

		if (isset($attributes['base'])) {
            $this->setBase($attributes['base']);
        }

		//set mime type
		$this->_mime = 'text/html';

		//define renderer sequence
		$this->_renderers = array('component' => array(),
		                          'modules'   => array(),
		                          'module'    => array(),
		                          'head'      => array()
							);

		//set default document metadata
		 $this->setMetaData('Content-Type', $this->_mime . '; charset=' . $this->_charset , true );
		 $this->setMetaData('robots', 'index, follow' );
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
    function addHeadLink($href, $relation, $relType = 'rel', $attribs = array())
	{
        $attribs = JDocumentHelper::implodeAttribs('=', ' ', $attribs);
        $generatedTag = "<link href=\"$href\" $relType=\"$relation\" ". $attribs;
        $this->_links[] = $generatedTag;
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
     * Generates the head html and return the results as a string
     *
     * @access public
     * @return string
     */
    function fetchHead()
    {
        // get line endings
        $lnEnd = $this->_getLineEnd();
        $tab = $this->_getTab();

		$tagEnd = ' />';

		$strHtml  = $tab . '<title>' . $this->getTitle() . '</title>' . $lnEnd;
		$strHtml .= $tab . '<base href="' . $this->getBase() . '" />' . $lnEnd;

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
                $strHtml .= ' media="'.$strAttr['media'].'" ';
            }

			$strHtml .= JDocumentHelper::implodeAttribs('=', ' ', $strAttr['attribs']);

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
		jimport('joomla.document.module.renderer');

		if(!$this->moduleExists('Renderer', ucfirst($type))) {
			return false;
		}

		$module =& $this->loadModule( 'Renderer', ucfirst($type));

		if( patErrorManager::isError( $module ) ) {
			return false;
		}

		return $module->render($name, $params);
	}

	/**
	 * Parse a document template
	 *
	 * @access public
	 * @param string 	$directory	The template directory
	 * @param string 	$file 		The actual template file
	 */
	function parse($directory, $file = 'index.php')
	{
		global $mainframe;

		$contents = $this->_load( $directory, $file);
		$this->readTemplatesFromInput( $contents, 'String' );

		/*
		 * Parse the template INI file if it exists for parameters and insert
		 * them into the template.
		 */
		if (is_readable( $directory.DS.'params.ini' ) ) {
			$content = file_get_contents($directory.DS.'params.ini');
			$params = new JParameter($content);
			$this->addVars( 'document', $params->toArray(), 'param_');
		}

		/*
		 * Try to find a favicon by checking the template and root folder
		 */
		$path = $directory .'/';
		$dirs = array( $path, '' );
		foreach ($dirs as $dir ) {
			$icon =   $dir . 'favicon.ico';

			if(file_exists( JPATH_SITE .'/'. $icon )) {
				$this->addFavicon( $icon);
				break;
			}
		}
	}

	/**
	 * Outputs the template to the browser.
	 *
	 * @access public
	 * @param string 	$template	The name of the template
	 * @param boolean 	$compress	If true, compress the output using Zlib compression
	 * @param boolean 	$compress	If true, will display information about the placeholders
	 */
	function display( $template, $file, $compress = false, $params = array())
	{
		// check
		$directory = isset($params['directory']) ? $params['directory'] : 'templates';

		if ( !file_exists( $directory.DS.$template.DS.$file) ) {
			$template = '_system';
		}

		// parse
		$this->parse($directory.DS.$template, $file);

		// render
		foreach($this->_renderers as $type => $names)
		{
			foreach($names as $name)
			{
				if($html = $this->execRenderer($type, $name, $params)) {
					$this->addVar('document', $type.'_'.$name, $html);
				}
			}
		}

		$this->addVar('document', 'template', $template);

		//output
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );		// HTTP/1.5
		header( 'Pragma: no-cache' );										// HTTP/1.0

		parent::display( $template, $file, $compress, $params );
	}

	/**
	 * Load a template file
	 *
	 * @param string 	$template	The name of the template
	 * @param string 	$filename	The actual filename
	 * @return string The contents of the template
	 */
	function _load($directory, $filename)
	{
		global $mainframe, $my, $acl, $database;
		global $Itemid, $task, $option, $_VERSION;

		//For backwards compatibility extract the config vars as globals
		foreach (get_object_vars($mainframe->_registry->toObject()) as $k => $v) {
			$name = 'mosConfig_'.$k;
			$$name = $v;
		}

		$contents = '';
		//Check to see if we have a valid template file
		if ( file_exists( $directory.DS.$filename ) )
		{
			//store the file path
			$this->_file = $directory.DS.$filename;

			//get the file content
			ob_start();
			?><jdoc:tmpl name="document" autoclear="yes" unusedvars="ignore"><?php
				require_once($directory.DS.$filename );
			?></jdoc:tmpl><?php
			$contents = ob_get_contents();
			ob_end_clean();
		}

		// Add the option variable to the template
		$this->addVar('document', 'option', $option);

		// Add the language information to the template
		$this->addVar( 'document', 'lang_tag', $this->getLanguage() );
		$this->addVar( 'document', 'lang_dir', $this->getDirection() );

		return $contents;
	}

	/**
	 * Adds a renderer to be called
	 *
	 * @param string 	$type	The renderer type
	 * @param string 	$name	The renderer name
	 * @return string The contents of the template
	 */
	function _addRenderer($type, $name) {
		$this->_renderers[$type][] = $name;
	}
}
?>