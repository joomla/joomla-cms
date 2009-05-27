<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Parameter
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Renders a section element
 *
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementSection extends JElement
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
		$db = &JFactory::getDbo();

		$query = 'SELECT id, title FROM #__sections WHERE published = 1 AND scope = "content" ORDER BY title';
		$db->setQuery($query);
		$options = $db->loadObjectList();
		array_unshift($options, JHtml::_('select.option', '0', '- '.JText::_('Select Section').' -', 'id', 'title'));

		return JHtml::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'title', $value, $control_name.$name);
	}
}
