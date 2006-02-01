<?PHP
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

/**
 * Base class for JDocument Renderers
 *
 * @abstract
 * @author		Johan Janssens <johan@joomla.be>
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.1
 */
class patTemplate_Renderer extends patTemplate_Module
{
 	/**
	* reference to the JDocument object that instantiated the module
	*
	* @access	protected
	* @var	object
	*/
	var	$_tmpl;
    
    
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
	
	/**
	* set a reference to the JDocument object that instantiated the function
	*
	* @access	public
	* @param	object		JDocument object
	*/
	function setTemplateReference( &$tmpl )
	{
		$this->_tmpl = &$tmpl;
	}
}
?>