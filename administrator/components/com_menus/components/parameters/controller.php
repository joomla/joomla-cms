<?php
/**
* @version $Id: category.php 3222 2006-04-24 01:49:01Z webImagery $
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
 * Renders a category element
 *
 * @author 		Andrew Eddie
 * @package 	Joomla
 * @subpackage 	Menus
 * @since		1.5
 */

class JElement_Controller extends JElement
{
   /**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Controller';

	function fetchElement($name, $value, &$node, $control_name)
	{
		global $database;

		/*$scope = $node->attributes('scope');
		if (!isset ($scope)) {
			$scope = 'content';
		}*/

		$helper = &$this->_parent->private_helper;

		if ($helper->hasControllers())
		{ 
			$controllers = $helper->getControllerList();
			$controlName = $control_name.'['.$name.']';

			if (count( $controllers ))
			{
				array_unshift( $controllers, mosHTML::makeOption( '', '- Select Controller -' ) );
				
				$result = mosHTML::selectList( $controllers, $controlName,
					'onchange="alert(\'Please click apply for change to take effect\')"',
					'value', 'text', $value );
			}
			else
			{
				$result = '<input type="text" name="'.$controlName.'" id="'.$control_name.$name.'" value="'.$value.'" />';
			}
		}
		return $result;
	}
}
?>