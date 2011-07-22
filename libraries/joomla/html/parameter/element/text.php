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
 * Renders a text element
 *
 * @package     Joomla.Platform
 * @subpackage  Parameter
 * @since       11.1
 * @deprecated  12.1   Use JFormFormFieldText instead
 */
class JElementText extends JElement
{
	/**
	 * Element name
	 *
	 * @var    string
	 */
	protected $_name = 'Text';

	/**
	 *
	 * @since   11.1
	 *
	 * @deprecated    12.1  Use JFormFieldText::getInput instead.
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		// Deprecation warning.
		JLog::add('JElementText::_fetchElement() is deprecated.', JLog::WARNING, 'deprecated');
		
		$size = ($node->attributes('size') ? 'size="'.$node->attributes('size').'"' : '');
		$class = ($node->attributes('class') ? 'class="'.$node->attributes('class').'"' : 'class="text_area"');

		// Required to avoid a cycle of encoding &

		$value = htmlspecialchars(htmlspecialchars_decode($value, ENT_QUOTES), ENT_QUOTES, 'UTF-8');

		return '<input type="text" name="'.$control_name.'['.$name.']" id="'.$control_name.$name.'" value="'.$value.'" '.$class.' '.$size.' />';
	}
}
