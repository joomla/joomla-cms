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

jimport('joomla.application.module.helper');

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
	 * @param	array	$options Associative array of options
	 */
	function __construct($options = array())
	{
		parent::__construct($options);

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
	 * Get the contents of a document include
	 *
	 * @access public
	 * @param string 	$type	The type of renderer
	 * @param string 	$name	 The name of the element to render
	 * @param array   	$attribs Associative array of remaining attributes.
	 * @return 	The output of the renderer
	 */
	function getInclude($type, $name = null, $attribs = array())
	{
		$result = null;
		if(isset($this->_buffer[$type][$name])) {
			$result = $this->_buffer[$type][$name];
		}

		if($renderer = $this->loadRenderer( $type )) {
			$result = $renderer->render($name, $attribs, $result);
		};

		return $result;

	}

	/**
	 * Set the contents a document include
	 *
	 * @access public
	 * @param string 	$type		The type of renderer
	 * @param string 	$name		oke The name of the element to render
	 * @param string 	$content	The content to be set in the buffer
	 */
	function setInclude($type, $name = null, $contents)
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
		$outline   = isset($params['outline'])   ? $params['outline']   : 0;
		$template  = $params['template'];
		$file      = $params['file'];

		if ( !file_exists( $directory.DS.$template.DS.$file) ) {
			$template = '_system';
		}

		// Parse the template INI file if it exists for parameters and insert
		// them into the template.
		if (is_readable( $directory.DS.$template.DS.'params.ini' ) )
		{
			$content = file_get_contents($directory.DS.$template.DS.'params.ini');
			$this->params = new JParameter($content);
		}

		$this->template =& $template;

		// load
		$data = $this->_loadTemplate($directory.DS.$template, $file);

		// parse
		$data = $this->_parseTemplate($data, array('outline' => $outline));

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
	 * Count the modules based on the given condition
	 *
	 * @access public
	 * @param  string 	$condition	The condition to use
	 * @return integer  Number of modules found
	 */
	function countModules($condition)
	{
		$result = '';

		$words = explode(' ', $condition);
		for($i=0; $i < count($words); $i++)
		{
			if($i % 2 == 0)
			{
				//odd parts (modules)
				$name = strtolower($words[$i]);
				$words[$i] = count(JModuleHelper::getModules($name));
			}
		}

		$str = 'return '.implode(' ', $words).';';

		return eval($str);
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
			require_once($directory.DS.$filename );
			$contents = ob_get_contents();
			ob_end_clean();
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

		return $contents;
	}

	/**
	 * Parse a document template
	 *
	 * @access public
	 * @param string 	$directory	The template directory
	 * @param string 	$file 		The actual template file
	 */
	function _parseTemplate($data, $params = array())
	{
		$replace = array();
		$matches = array();

		if(preg_match_all('#<jdoc:include\ type="([^"]+)" (.*)\/>#iU', $data, $matches))
		{
			$matches[0] = array_reverse($matches[0]);
			$matches[1] = array_reverse($matches[1]);
			$matches[2] = array_reverse($matches[2]);

			if($key = array_search('component', $matches[1]))
			{
				$attribs = JUtility::parseAttributes( $matches[2][$key] );
				$name = isset($attribs['name']) ? $attribs['name'] : null;
				$renderer = $this->loadRenderer( 'component');
				$result   = $renderer->render( $name, array_merge($attribs, $params));
				$this->setInclude('component', null, $result);
			}

			$count   = count($matches[1]);

			for($i = 0; $i < $count; $i++)
			{
				$attribs = JUtility::parseAttributes( $matches[2][$i] );
				$type = $matches[1][$i];
				$name = isset($attribs['name']) ? $attribs['name'] : null;
				$replace[$i] = $this->getInclude($type, $name, array_merge($attribs, $params));
			}

			$data = str_replace($matches[0], $replace, $data);
		}

		return $data;
	}
}
?>