<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a password element
 *
 * @package     Joomla.Platform
 * @subpackage  Parameter
 * @since       11.1
 * @deprecated  12.1  Use JFormFieldPassword instead.
 */
class JElementPassword extends JElement
{
	/**
	 * Element name
	 *
	 * @var    string
	 */
	protected $_name = 'Password';

	/**
	 * Fetch a html for a password element
	 *
	 * @param   string       $name          Element name
	 * @param   string       $value         Element value
	 * @param   JXMLElement  &$node         JXMLElement node object containing the settings for the element
	 * @param   string       $control_name  Control name
	 *
	 * @return  string
	 *
	 * @deprecated    12.1  Use JFormFieldPasssword::getInput instead.
	 * @since   11.1
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		// Deprecation warning.
		JLog::add('JElementPassword::fetchElement() is deprecated.', JLog::WARNING, 'deprecated');

		$size = ($node->attributes('size') ? 'size="' . $node->attributes('size') . '"' : '');
		$class = ($node->attributes('class') ? 'class="' . $node->attributes('class') . '"' : 'class="text_area"');

		return '<input type="password" name="' . $control_name . '[' . $name . ']" id="' . $control_name . $name . '" value="' . $value . '" '
			. $class . ' ' . $size . ' />';
	}
}
