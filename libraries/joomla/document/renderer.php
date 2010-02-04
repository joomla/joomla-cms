<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Document
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Abstract class for a renderer
 *
 * @abstract
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
		$this->_doc = &$doc;
	}

	/**
	 * Renders a script and returns the results as a string
	 *
	 * @abstract
	 * @access public
	 * @param string	$name		The name of the element to render
	 * @param array		$array		Array of values
	 * @param string	$content	Override the output of the renderer
	 * @return string	The output of the script
	 */
	function render($name, $params = array(), $content = null)
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
