<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

class JElementContact extends JElement
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

		$query = 'SELECT DISTINCT c.id, c.name AS text'
		. ' FROM #__contactdirectory_contacts AS c'
		. ' LEFT JOIN #__contactdirectory_con_cat_map AS map ON map.contact_id = c.id '
		. ' LEFT JOIN #__categories AS cat ON cat.id = map.category_id '
		. ' WHERE c.published = 1 AND cat.published = 1'
		. ' ORDER BY cat.title, c.name';
		$db->setQuery( $query );
		$options = $db->loadObjectList( );

		return JHtml::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'text', $value, $control_name.$name );
	}
}