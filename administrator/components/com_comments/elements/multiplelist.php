<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Renders a Multiple list element
 *
 * Based on the JElementList in the Joomla! 1.5 Core Distribution
 *
 * @package		Joomla.Administrator
 * @version	1.0
 */
class JElementMultiplelist extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Multiplelist';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();
		$ctrl	= $control_name .'['. $name .']';

		$class		= $node->attributes('class');
		if (!$class) {
			$class = "inputbox";
		}

		$options = array ();
		foreach ($node->children() as $option)
		{
			$val	= $option->attributes('value');
			$text	= $option->data();
			$options[] = JHtml::_('select.option', $val, JText::_($text));
		}

		$attribs	= ' ';
		if ($v = $node->attributes('size')) {
			$attribs	.= 'size="'.$v.'"';
		}
		if ($v = $node->attributes('class')) {
			$attribs	.= 'class="'.$v.'"';
		} else {
			$attribs	.= 'class="inputbox"';
		}
		if ($m = $node->attributes('multiple'))
		{
			$attribs	.= 'multiple="multiple"';
			$ctrl		.= '[]';
		}

		return JHtml::_('select.genericlist',  $options, $ctrl, $attribs, 'value', 'text', $value, $control_name.$name);
	}
}