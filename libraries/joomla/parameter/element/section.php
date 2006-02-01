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
 * Renders a section element
 *
 * @author 		Johan Janssens <johan@joomla.be>
 * @package 	Joomla.Framework
 * @subpackage 	Parameter
 * @since		1.1
 */

class JElement_Section extends JElement
{
   /**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Section';
	
	function fetchElement($name, $value, &$node, $control_name)
	{
		global $database;

		$query = "SELECT id, title"."\n FROM #__sections"."\n WHERE published = 1"."\n AND scope = 'content'"."\n ORDER BY title";
		$database->setQuery($query);
		$options = $database->loadObjectList();
		array_unshift($options, mosHTML::makeOption('0', '- '.JText::_('Select Section').' -', 'id', 'title'));

		return mosHTML::selectList($options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'title', $value);
	}
}
?>