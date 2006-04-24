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
 * Parameter base class
 *
 * The JElement is the base class for all JElement types
 *
 * @abstract
 * @author 		Johan Janssens <johan.janssens@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage 	Parameter
 * @since		1.5
 */

class JElement extends JObject {
   /**
	* element name
	*
	* This has to be set in the final
	* renderer classes.
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = null;

   /**
	* reference to the object that instantiated the element
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
	* get the element name
	*
	* @access	public
	* @return	string	type of the parameter
	*/
	function getName() {
		return $this->_name;
	}

	function render(&$xmlElement, $control_name = 'params')
	{
		$name  = $xmlElement->attributes('name');
		$label = $xmlElement->attributes('label');
		$descr = $xmlElement->attributes('description');

		//get value
		$value = $this->_parent->get($name, $xmlElement->attributes('default'));

		//make sure we have a valid label
		$label = $label ? $label : $name;

		$result[0] = $this->fetchTooltip($label, $descr, $xmlElement, $control_name, $name);
		$result[1] = $this->fetchElement($name, $value, $xmlElement, $control_name);

		return $result;
	}

	function fetchTooltip($label, $description, &$xmlElement, $control_name='', $name='')
	{
		$output = '<label for="'.$control_name.$name.'">';
		$output .= mosToolTip(addslashes($description), $label, '', '', $label, '#', 0);
		$output .= '</label>';

		return $output;
	}

	function fetchElement($name, $value, &$xmlElement, $control_name) {
		return;
	}
}
?>