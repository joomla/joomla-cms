<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Renders a category element
 *
 * Based on the JElementCategory in the Joomla! 1.5 Core Distribution
 *
 * @package		Joomla.Administrator
 * @version	1.0
 */
class JElementCategory extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Category';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();
		$ctrl	= $control_name .'['. $name .']';

		$section	= $node->attributes('section');
		$class		= $node->attributes('class');
		if (!$class) {
			$class = "inputbox";
		}

		if (!isset ($section)) {
			// alias for section
			$section = $node->attributes('scope');
			if (!isset ($section)) {
				$section = 'content';
			}
		}

		if ($section == 'content') {
			// This might get a conflict with the dynamic translation - TODO: search for better solution
			$query = 'SELECT c.id, CONCAT_WS("/",s.title, c.title) AS title' .
				' FROM #__categories AS c' .
				' LEFT JOIN #__sections AS s ON s.id=c.section' .
				' WHERE c.published = 1' .
				' AND s.scope = '.$db->Quote($section).
				' ORDER BY s.title, c.title';
		} else {
			$query = 'SELECT c.id, c.title' .
				' FROM #__categories AS c' .
				' WHERE c.published = 1' .
				' AND c.section = '.$db->Quote($section).
				' ORDER BY c.title';
		}
		$db->setQuery($query);
		$options = $db->loadObjectList();

		$attribs	= ' ';
		if ($v = $node->attributes('size')) {
			$attribs	.= 'size="'.$v.'" ';
		}
		if ($v = $node->attributes('class')) {
			$attribs	.= 'class="'.$v.'" ';
		} else {
			$attribs	.= 'class="inputbox" ';
		}
		if ($m = $node->attributes('multiple'))
		{
			$attribs	.= 'multiple="multiple" ';
			$ctrl		.= '[]';
		} else {
			array_unshift($options, JHtml::_('select.option', '0', '- '.JText::_('Select Category').' -', 'id', 'title'));
		}

		return JHtml::_('select.genericlist',  $options, $ctrl, $attribs, 'id', 'title', $value, $control_name.$name);
	}
}