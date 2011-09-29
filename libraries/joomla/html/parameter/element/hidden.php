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
 * @subpackage  Parameter
 * @since       11.1
 * @deprecated  12.1     Use JFormFieldHidden instead.
 */
class JElementHidden extends JElement
{
	/**
	 * Element name
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $_name = 'Hidden';

	/**
	 * Fetch a hidden element
	 *
	 * @param   string  $name          Element name
	 * @param   string  $value         Element value
	 * @param   object  &$node         Element object
	 * @param   string  $control_name  Control name
	 *
	 * @return  string
	 *
	 * @since   11.1
	 * @deprecated    12.1
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		// Deprecation warning.
		JLog::add('JElementHidden::fetchElement() is deprecated.', JLog::WARNING, 'deprecated');

		$class = ($node->attributes('class') ? 'class="' . $node->attributes('class') . '"' : 'class="text_area"');

		return '<input type="hidden" name="' . $control_name . '[' . $name . ']" id="' . $control_name . $name . '" value="' . $value . '" ' . $class
			. ' />';
	}

	/**
	 * Fetch tooltip for a hidden element
	 *

	 * @param   string  $label         Element label
	 * @param   string  $description   Element description (which renders as a tool tip)
	 * @param   object  $xmlElement    Element object
	 * @param   string  $control_name  Control name
	 * @param   string  $name          Element name
	 *
	 * @return  string
	 *
	 * @deprecated    12.1
	 * @since   11.1
	 */
	public function fetchTooltip($label, $description, &$xmlElement, $control_name = '', $name = '')
	{
		// Deprecation warning.
		JLog::add('JElementHidden::fetchTooltip() is deprecated.', JLog::WARNING, 'deprecated');

		return false;
	}
}
