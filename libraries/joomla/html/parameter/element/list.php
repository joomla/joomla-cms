<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Parameter
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Renders a list element
 *
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementList extends JElement
{
	/**
	* Element type
	*
	* @access	protected
	* @var		string
	*/
	protected $_name = 'List';

	/**
	 * Get the options for the element
	 *
	 * @param	object	The current XML node.
	 * @return	array
	 * @since	1.6
	 */
	protected function _getOptions(&$node)
	{
		$options = array ();
		foreach ($node->children() as $option)
		{
			$val	= $option->attributes('value');
			$text	= $option->data();
			$options[] = JHtml::_('select.option', $val, JText::_($text));
		}
		return $options;
	}

	/**
	 * Fetch the HTML code for the parameter element.
	 *
	 * @param	string	The field name.
	 * @param	mixed	The value of the field.
	 * @param	object	The current XML node.
	 * @param	string	The name of the HTML control.
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		$ctrl	= $control_name .'['. $name .']';
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

		return JHtml::_(
			'select.genericlist',
			$this->_getOptions($node),
			$ctrl,
			array(
				'id' => $control_name.$name,
				'list.attr' => $attribs,
				'list.select' => $value
			)
		);
	}
}
