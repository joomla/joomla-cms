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
 * Renders a hidden element
 *
 * @package     Joomla.Platform
 * @subpackage	Parameter
 * @since    	11.1
 * @deprecated	JParameter is deprecated and will be removed in a future version. Use JForm instead.
 */
class JElementHidden extends JElement
{
	/**
	 * Element name
	 *
	 * @var    string
	 */
	protected $_name = 'Hidden';

	/**
	 *
	 * @since   11.1
	 *
	 * @deprecated    12.1
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		$class = ($node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="text_area"');

		return '<input type="hidden" name="'.$control_name.'['.$name.']" id="'.$control_name.$name.'" value="'.$value.'" '.$class.' />';
	}

	/**
	 *
	 * @since   11.1
	 *
	 * @deprecated    12.1
	 */
	public function fetchTooltip($label, $description, &$xmlElement, $control_name='', $name='')
	{
		return false;
	}
}
