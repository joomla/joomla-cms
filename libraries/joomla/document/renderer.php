<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Document
 */

defined('JPATH_PLATFORM') or die;

/**
 * Abstract class for a renderer
 *
 * @abstract
 * @package		Joomla.Platform
 * @subpackage	Document
 * @since		11.1
 */
class JDocumentRenderer extends JObject
{
	/**
	* Reference to the JDocument object that instantiated the renderer
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
	* @param object A reference to the JDocument object that instantiated the renderer
	*/
	public function __construct(&$doc)
	{
		$this->_doc = &$doc;
	}

	/**
	 * Renders a script and returns the results as a string
	 *
	 * @param string	$name		The name of the element to render
	 * @param array		$array		Array of values
	 * @param string	$content	Override the output of the renderer
	 * 
	 * @return string	The output of the script
	 */
	public function render($name, $params = null, $content = null)
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
