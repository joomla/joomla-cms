<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Renders a newsfeed selection element
 *
 * @author 		Andrew Eddie <andrew.eddie@jamboworks.com>
 * @package 	Newsfeeds
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementNewsfeed extends JElement
{
   /**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Newsfeed';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db =& JFactory::getDBO();

		$query = 'SELECT a.id, c.title, a.name'
		. ' FROM #__newsfeeds AS a'
		. ' INNER JOIN #__categories AS c ON a.catid = c.id'
		. ' WHERE a.published = 1'
		. ' ORDER BY a.catid, a.name'
		;
		$db->setQuery( $query );
		$options = $db->loadObjectList( );

		$n = count( $options );
		for ($i = 0; $i < $n; $i++)
		{
			$options[$i]->text = $options[$i]->title . '-' . $options[$i]->name;
		}

		array_unshift($options, JHTML::_('select.option', '0', '- '.JText::_('Select Feed').' -', 'id', 'text'));

		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'text', $value, $control_name.$name );
	}
}
?>
