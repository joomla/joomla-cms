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

/**
 * Parameter base class
 * 
 * The JParameter is the base class for all JParameter types
 *
 * @author 		Johan Janssens <johan@joomla.be>
 * @package 	Joomla.Framework
 * @subpackage 	Parameters
 * @abstract
 * @since 1.1
 */

class JParameter extends JObject
{
   /**
	* parameter type
	*
	* This has to be set in the final
	* renderer classes.
	*
	* @access	protected
	* @var		string
	*/
	var	$_type = null;
	
   /**
	* reference to the object that instantiated the parameter
	*
	* @access	protected
	* @var		object
	*/
	var	$_parent = null;

	/**
	 * Constructor
	 * 
	 * @access protected
	 */
	function __construct($parent = null) {
		$this->_parent = $parent;
	}

   /**
	* get the parameter type
	*
	* @access	public
	* @return	string	type of the parameter
	*/
	function getType()
	{
		return $this->_type;
	}
	
	function render(&$xmlElement, $control_name = 'params')
	{
		$name  = $xmlElement->getAttribute('name');
		$label = $xmlElement->getAttribute('label');
		$descr = $xmlElement->getAttribute('description');
		
		//get value
		$value = $this->_parent->get($name, $xmlElement->getAttribute('default'));
		
		//make sure we have a valid label
		$label = $label ? $label : $name;
		
		$result[0] = $this->fetchTooltip($label, $descr, $xmlElement);
		$result[1] = $this->fetchElement($name, $value, $xmlElement, $control_name);
		
		return $result;
	}
	
	function fetchTooltip($label, $description, &$xmlElement) {
		return mosToolTip(addslashes($description), $label, '', '', $label, '#', 0);
	}
	
	function fetchElement($name, $value, &$xmlElement, $control_name) {
		return;
	}
}
?>