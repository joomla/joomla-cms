<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Document
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Abstract class for a renderer
 *
 * @abstract
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */
class JDocumentRenderer extends JObject
{
	/**
	* reference to the JDocument object that instantiated the renderer
	*
	* @var		object
	* @access	protected
	*/
	var	$_doc = null;

	/**
	 * Renderer mime type
	 *
	 * @var		string
	 * @access	private
	 */
	 var $_mime = "text/html";

	/**
	* Class constructor
	*
	* @access protected
	* @param object A reference to the JDocument object that instantiated the renderer
	*/
	function __construct(&$doc) {
		$this->_doc =& $doc;
	}

	/**
	 * Renders a script and returns the results as a string
	 *
	 * @abstract
	 * @access public
	 * @param string 	$name		The name of the element to render
	 * @param array 	$array		Array of values
	 * @param string 	$content	Override the output of the renderer
	 * @return string	The output of the script
	 */
	function render( $name, $params = array(), $content = null )
	{

	}

	/**
	 * Return the content type of the renderer
	 *
	 * @return string The contentType
	 */
	function getContentType() {
		return $this->_mime;
	}
}