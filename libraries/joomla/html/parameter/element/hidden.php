<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  HTML
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a hidden element
 *
 * @package		Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementHidden extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	protected $_name = 'Hidden';

	public function fetchElement($name, $value, &$node, $control_name)
	{
		$class = ($node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="text_area"');

		return '<input type="hidden" name="'.$control_name.'['.$name.']" id="'.$control_name.$name.'" value="'.$value.'" '.$class.' />';
	}

	public function fetchTooltip($label, $description, &$xmlElement, $control_name='', $name='')
	{
		return false;
	}
}
