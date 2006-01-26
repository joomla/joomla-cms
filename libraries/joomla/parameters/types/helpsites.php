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
 * Renders a helpsites parameter
 *
 * @author 		Johan Janssens <johan@joomla.be>
 * @package 	Joomla.Framework
 * @subpackage 	Parameters
 * @abstract
 * @since 1.1
 */

class JParameter_Helpsites extends JParameter
{
   /**
	* parameter type
	*
	* @access	protected
	* @var		string
	*/
	var	$_type = 'Helpsites';
	
	function fetchElement($name, $value, &$node, $control_name)
	{
		jimport('joomla.i18n.help');
		
		$helpsites 				= JHelp :: createSiteList('http://help.joomla.org/helpsites-11.xml', $value);
		array_unshift($helpsites, mosHTML :: makeOption('', JText :: _('local')));

		return mosHTML :: selectList($helpsites, ''.$control_name.'['.$name.']', ' class="inputbox" id="helpsites"', 'value', 'text', $value, 'param'.$name);
	}
}
?>