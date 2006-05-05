<?PHP
/**
* @version $Id: renderer.php 3222 2006-04-24 01:49:01Z webImagery $
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
	* @access	protected
	* @var	object
	*/
	var	$_doc;

	function __construct(&$doc) {
		$this->_doc = $doc;
	}
	
    /**
	 * Renders a script and returns the results as a string
	 *
	 * @abstract
	 * @access public
	 * @param string 	$name		The name of the element to render
	 * @param array 	$array		Array of values
	 * @return string	The output of the script
	 */
	function render( $name, $params = array() )
	{

	}
}
?>