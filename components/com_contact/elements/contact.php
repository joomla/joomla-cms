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
 * @author 		Johan Janssens <johan.janssens@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage 	Parameter
 * @since		1.5
 */

class JElement_Contact extends JElement
{
   /**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Contact';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();

		$query = "SELECT a.id, CONCAT( a.name, ' - ',a.con_position ) AS text, a.catid "
		. "\n FROM #__contact_details AS a"
		. "\n INNER JOIN #__categories AS c ON a.catid = c.id"
		. "\n WHERE a.published = 1"
		. "\n ORDER BY a.catid, a.name"
		;
		$db->setQuery( $query );
		$contacts = $db->loadObjectList( );

		$db->setQuery($query);
		$options = $db->loadObjectList();
		//array_unshift($options, mosHTML::makeOption('0', 'None', 'id', 'text'));

		return mosHTML::selectList($options, ''.$control_name.'['.$name.']', 'class="inputbox" size="10"', 'id', 'text', $value, $control_name.$name );
	}
}
?>