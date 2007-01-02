<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Parameter
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Renders a section element
 *
 * @author 		Johan Janssens <johan.janssens@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
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
		$db =& JFactory::getDBO();

		$query = "SELECT id, title"."\n FROM #__sections"."\n WHERE published = 1"."\n AND scope = 'content'"."\n ORDER BY title";
		$db->setQuery($query);
		$options = $db->loadObjectList();
		array_unshift($options, JHTMLSelect::option('0', '- '.JText::_('Select Section').' -', 'id', 'title'));

		return JHTMLSelect::genericList($options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'title', $value, $control_name.$name);
	}
}
?>
