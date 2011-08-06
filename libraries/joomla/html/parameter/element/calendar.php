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
 * Renders a calendar element
 *
 * @package     Joomla.Platform
 * @subpackage  Parameter
 * @since       11.1
 * @deprecated  Use JFormFieldCalendar instead.
 */
class JElementCalendar extends JElement
{
	/**
	 * @var    string  Element named
	 *
	 * @since       11.1
	 * @deprecated    12.1
	 */
	protected $_name = 'Calendar';

	/**
	 *
	 *
	 * @since       11.1
	 *
	 * @deprecated    12.1
	 * @see           JFormFieldCalendar
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		// Deprecation warning.
		JLog::add('JElementCalendar::fetchElement() is deprecated.', JLog::WARNING, 'deprecated');

		// Load the calendar behavior
		JHtml::_('behavior.calendar');

		$format = ($node->attributes('format') ? $node->attributes('format') : '%Y-%m-%d');
		$class = $node->attributes('class') ? $node->attributes('class') : 'inputbox';
		$id = $control_name . $name;
		$name = $control_name . '[' . $name . ']';

		return JHtml::_('calendar', $value, $name, $id, $format, array('class' => $class));
	}
}
