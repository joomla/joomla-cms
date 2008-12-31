<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

/**
 * Renders a newsfeed selection element
 *
 * @package 	Newsfeeds
 * @subpackage	Parameter
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
		. ' AND c.published = 1'
		. ' ORDER BY a.catid, a.name'
		;
		$db->setQuery( $query );
		$options = $db->loadObjectList( );

		$n = count( $options );
		for ($i = 0; $i < $n; $i++)
		{
			$options[$i]->text = $options[$i]->title . '-' . $options[$i]->name;
		}

		array_unshift($options, JHtml::_('select.option', '0', '- '.JText::_('Select Feed').' -', 'id', 'text'));

		return JHtml::_(
			'select.genericlist',
			$options,
			$control_name . '[' . $name . ']',
			'class="inputbox"',
			'id',
			'text',
			$value,
			$control_name . $name
		);
	}
}