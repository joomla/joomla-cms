<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a textarea element
 *
 * @package		Joomla.Platform
 * @subpackage		Parameter
 * @since		11.1
 */

class JElementTextarea extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	protected $_name = 'Textarea';

	public function fetchElement($name, $value, &$node, $control_name)
	{
		$rows = $node->attributes('rows');
		$cols = $node->attributes('cols');
		$class = ($node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="text_area"');
		// convert <br /> tags so they are not visible when editing
		$value = str_replace('<br />', "\n", $value);

		return '<textarea name="'.$control_name.'['.$name.']" cols="'.$cols.'" rows="'.$rows.'" '.$class.' id="'.$control_name.$name.'" >'.$value.'</textarea>';
	}
}
