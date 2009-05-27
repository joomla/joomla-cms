<?php
/**
 * @version		$Id: contact.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

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
		$db = &JFactory::getDbo();

		$query = 'SELECT a.id, CONCAT(a.name, " - ",a.con_position) AS text, a.catid '
		. ' FROM #__contact_details AS a'
		. ' INNER JOIN #__categories AS c ON a.catid = c.id'
		. ' WHERE a.published = 1'
		. ' AND c.published = 1'
		. ' ORDER BY a.catid, a.name'
		;
		$db->setQuery($query);
		$options = $db->loadObjectList();

		return JHtml::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'text', $value, $control_name.$name);
	}
}
