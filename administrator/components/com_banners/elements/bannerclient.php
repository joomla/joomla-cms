<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

/**
 * Renders a category element
 *
 * @package		Joomla.Administrator
 * @subpackage	Banners
 * @since		1.5
 */
class JElementBannerclient extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Bannerclient';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDbo();

		// This might get a conflict with the dynamic translation - TODO: search for better solution
		$query = 'SELECT cid, name' .
				' FROM #__bannerclient' .
				' ORDER BY name';
		$db->setQuery($query);
		$options = $db->loadObjectList();
		array_unshift($options, JHtml::_('select.option', '0', '- '.JText::_('Select Client').' -', 'cid', 'name'));

		return JHtml::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'cid', 'name', $value, $control_name.$name);
	}
}
