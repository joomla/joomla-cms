<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * JParameter element for selecting JXtended Comments contexts.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_comments
 * @version		1.0
 */
class JElementContext extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Context';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();
		$ctrl	= $control_name .'['. $name .']';

		$class		= $node->attributes('class');
		if (!$class) {
			$class = "inputbox";
		}

		$db->setQuery(
			'SELECT DISTINCT `context`' .
			' FROM `#__social_threads`' .
			' WHERE 1' .
			' ORDER BY `context` ASC'
		);
		$contexts = $db->loadResultArray();

		foreach ($contexts as $context)
		{
			$options[] = (object) array('id'=>$context,'title'=>ucfirst($context));
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
		} else {
			array_unshift($options, JHtml::_('select.option', '0', '- '.JText::_('All Contexts').' -', 'id', 'title'));
		}

		return JHtml::_('select.genericlist',  $options, $ctrl, $attribs, 'id', 'title', $value, $control_name.$name);
	}
}