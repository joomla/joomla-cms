<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Banners
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

/**
 * Renders a category element
 *
 * @package		Joomla
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
		$db = &JFactory::getDBO();

		// This might get a conflict with the dynamic translation - TODO: search for better solution
		$query = 'SELECT cid, name' .
				' FROM #__bannerclient' .
				' ORDER BY name';
		$db->setQuery($query);
		$options = $db->loadObjectList();
		array_unshift($options, JHtml::_('select.option', '0', '- '.JText::_('Select Client').' -', 'cid', 'name'));

		return JHtml::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'cid', 'name', $value, $control_name.$name );
	}
}