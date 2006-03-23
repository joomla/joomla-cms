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
 * Renders a imagelist element
 *
 * @author 		Johan Janssens <johan.janssens@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage 	Parameter
 * @since		1.1
 */

class JElement_ImageList extends JElement {
   /**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'ImageList';
	
	function fetchTooltip($label, $description, &$node, $control_name, $name) 
	{
		$output = '<label for="param'. $name .'">';
		$output .= mosToolTip(addslashes($description), $label, '', '', $label, '#', 0);
		$output .= '</label>';
		
		return $output;
	}
	
	function fetchElement($name, $value, &$node, $control_name)	
	{
		$filter =& $node->attributes('filter');
		$filter = '\.png$|\.gif$|\.jpg$|\.bmp$|\.ico$';
		
		$parameter =& $this->_parent->loadElement('filelist');
		
		return $parameter->fetchElement($name, $value, $node, $control_name);
	}
}
?>