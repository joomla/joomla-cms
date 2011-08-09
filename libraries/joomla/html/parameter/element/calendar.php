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
 * @deprecated  Use JFormFieldCalendar instead.
 * @since       11.1
 */
class JElementCalendar extends JElement
{
	/**
	 * Element name
	 *
	 * @var   string
	 * @deprecated    12.1
	 * @since  11.1
	 */
	protected $_name = 'Calendar';

	/**
	 * Fetch a calendar element
	 *
	 * @param   string   $name          Field name
	 * @param   string   $value         The date value
	 * @param   object   $node          JSimpleXMLElement node object containing the settings for the element
	 * @param   string   $control_name  Control name
	 * 
	 * @return  string   HTML string for a calendar
	 *
	 * @deprecated  12.1
	 * @see    JFormFieldCalendar
	 * @since  11.1
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
