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
 * Renders a radio element
 *
 * @author 		Johan Janssens <johan.janssens@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage 	Parameter
 * @since		1.1
 */

class JElement_Radio extends JElement {
   /**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Radio';
	
	function fetchElement($name, $value, &$node, $control_name) 
	{
		$options = array ();
		foreach ($node->children() as $option) 
		{
			$val  = $option->attributes('value');
			$text = $option->data();
			$options[] = mosHTML::makeOption($val, JText::_($text));
		}

		return mosHTML::radioList($options, ''.$control_name.'['.$name.']', '', $value, 'value', 'text', $control_name.$name );
	}
}
?>