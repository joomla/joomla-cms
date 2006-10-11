<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
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
     * Array of buffered output
     *
     * @var       array
     * @access    private
     */
	var $_buffer = array();

	/**
	 * Class constructor
	 *
	 * @access protected
	 * @param	array	$attributes Associative array of attributes
	 */
	function __construct($attributes = array())
	{
		parent::__construct($attributes);

		//set document type
		$this->_type = 'html';

		//set mime type
		$this->_mime = 'text/html';

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
        $href = str_replace( '\\', '/', $href );
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
	 * Get the contents of the document buffer
	 *
	 * @access public
	 * @param string 	$type	The type of renderer
	 * @param string 	$name	The name of the element to render
	 * @return 	The output of the renderer
	 */
	function get($type, $name = null, $params = array())
	{
		$result = null;
		if(isset($this->_buffer[$type][$name])) {
			return $this->_buffer[$type][$name];
		}

		$renderer = $this->loadRenderer( $type );
		$result = $renderer->render($name, $params);

		$this->set($type, $name, $result);

		return $result;

	}

	/**
	 * Set the contents the document buffer
	 *
	 * @access public
	 * @param string 	$type		The type of renderer
	 * @param string 	$name		oke The name of the element to render
	 * @param string 	$content	The content to be set in the buffer
	 */
	function set($type, $name = null, $contents)
	{
		$this->_buffer[$type][$name] = $contents;
	}

	/**
	 * Outputs the template to the browser.
	 *
	 * @access public
	 * @param boolean 	$cache		If true, cache the output
	 * @param boolean 	$compress	If true, compress the output
	 * @param array		$params	    Associative array of attributes
	 */
	function display( $caching = false, $compress = false, $params = array())
	{
		// check
		$directory = isset($params['directory']) ? $params['directory'] : 'templates';
		$template  = $params['template'];
		$file      = $params['file'];

		if ( !file_exists( $directory.DS.$template.DS.$file) ) {
			$template = '_system';
		}

		/*
		 * Buffer the output of the component before loading the template.  This is done so
		 * that non-display tasks, like save, published, etc, will not go thru the overhead of
		 * loading the template if it simply redirected.
		 */
		$renderer = $this->loadRenderer( 'component' );
		$result   = $renderer->render();
		$this->set('component', null, $result);

		//create the document engine
		$this->_engine = $this->_initEngine($template, $caching);

		// parse
		$this->_parseTemplate($directory.DS.$template, $file);

		// buffer
		$this->_bufferTemplate($params);

		// render
		$this->_renderTemplate($params);

		// fecth
		$data = $this->_engine->fetch('document');

		//output
		header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
		header( 'Cache-Control: no-store, no-cache, must-revalidate' );
		header( 'Cache-Control: post-check=0, pre-check=0', false );		// HTTP/1.1
		header( 'Pragma: no-cache' );										// HTTP/1.0
		header( 'Content-Type: ' . $this->_mime .  '; charset=' . $this->_charset);

		//compress
		if($compress) {
			$data = $this->compress($data);
		}

		echo $data;

	}

	/**
	 * Create document engine
	 *
	 * @access public
	 * @param string 	$template 	The actual template name
	 * @param boolean 	$caching	If true, cache the template
	 * @return object
	 */
	function _initEngine($template, $caching = false)
	{
		jimport('joomla.template.template');
		$instance =& JTemplate::getInstance();

		//set a reference to the document in the engine
		$instance->doc =& $this;

		//set the caching
		if($caching) {
			$instance->enableTemplateCache('File', JPATH_BASE.DS.'cache'.DS);
			$instance->setTemplateCachePrefix('tmpl_');
		}

		//set the namespace
		$instance->setNamespace( 'jdoc' );

		//add module directories
		$instance->addModuleDir('Function'    , dirname(__FILE__). DS .'function');

		//Add template variables
		$instance->addVar( 'document', 'lang_tag', $this->getLanguage() );
		$instance->addVar( 'document', 'lang_dir', $this->getDirection() );
		$instance->addVar( 'document', 'template', $template);

		//Legacy for popups
		//This is a dirty fix for now until we reach some sort of better way.
		//Requests running through component are supposed to load the default css for BC
		global $mainframe;
		$a_template = $mainframe->getTemplate();
		$instance->addVar( 'document', 'assigned_template', $a_template);

		return $instance;
	}

	/**
	 * Parse a document template
	 *
	 * @access public
	 * @param string 	$directory	The template directory
	 * @param string 	$file 		The actual template file
	 */
	function _parseTemplate($directory, $file = 'index.php')
	{
		$contents = $this->_loadTemplate( $directory, $file);
		$this->_engine->readTemplatesFromInput( $contents, 'String' );
		
		// Parse the template INI file if it exists for parameters and insert
		// them into the template.
		if (is_readable( $directory.DS.'params.ini' ) )
		{
			$content = file_get_contents($directory.DS.'params.ini');
			$params = new JParameter($content);
			$this->_engine->addVars( 'document', $params->toArray(), 'param_');
		}

		// Try to find a favicon by checking the template and root folder
		$path = $directory . DS;
		$dirs = array( $path, JPATH_BASE . DS );
		foreach ($dirs as $dir )
		{
			$icon =   $dir . 'favicon.ico';
			if (file_exists( $icon ))
			{
				$path = str_replace( JPATH_BASE . DS, '', $dir );
				$path = str_replace( '\\', '/', $path );
				$this->addFavicon( JURI::base() . $path . 'favicon.ico' );
				break;
			}
		}
	}

	/**
	 * Load a template file
	 *
	 * @param string 	$template	The name of the template
	 * @param string 	$filename	The actual filename
	 * @return string The contents of the template
	 */
	function _loadTemplate($directory, $filename)
	{	
		global $mainframe, $Itemid, $option;
		
		if ($mainframe->getCfg('legacy')) 
		{
			global $task, $_VERSION, $my, $cur_template, $database, $acl;
		
			//For backwards compatibility extract the config vars as globals
			$registry =& JFactory::getConfig();
			foreach (get_object_vars($registry->toObject()) as $k => $v) {
				$name = 'mosConfig_'.$k;
				$$name = $v;
			}
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
		$this->_engine->addVar('document', 'option', $option);

		return $contents;
	}

	/**
	 * Buffer the document
	 *
	 * @access private
	 */
	function _bufferTemplate(&$params)
	{
		if (isset( $this->_engine->_discoveredPlaceholders['document'] ))
		{
			// TODO: Johan, for some reason this is empty for a wizard
			foreach($this->_engine->_discoveredPlaceholders['document'] as $type => $includes)
			{
				foreach($includes as $include)
				{
					$result = $this->get($type, $include, $params);

					if(!$result) {
						$result = " ";
					}

					$this->set($type, $include, $result);
				}
			}
		}

		// Render the document head
		$renderer = $this->loadRenderer( 'head' );
		$result   = $renderer->render();
		$this->set('head', null, $result);

		// Render the errors
		$renderer = $this->loadRenderer( 'message' );
		$result   = $renderer->render();
		$this->set('message', null, $result);
	}

	/**
	 * Render the document
	 *
	 * @access private
	 */
	function _renderTemplate(&$params)
	{
		foreach($this->_buffer as $type => $buffers)
		{
			foreach($buffers as $buffer => $content)
			{
				$this->_engine->addVar('document', $type.'_'.$buffer, $content);
			}
		}
	}
}
?>