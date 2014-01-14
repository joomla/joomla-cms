<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a spacer element
 *
 * @package     Joomla.Platform
 * @subpackage  Parameter
 * @since       11.1
 * @deprecated  12.1   Use JFormFormFieldSpacer instead
 */
class JElementSpacer extends JElement
{
	/**
	 * Element name
	 *
	 * @var    string
	 */
	protected $_name = 'Spacer';

	/**
	 * Fetch tooltip for a radio button
	 *
	 * @param   string       $label         Element label
	 * @param   string       $description   Element description for tool tip
	 * @param   JXMLElement  &$node         JXMLElement node object containing the settings for the element
	 * @param   string       $control_name  Control name
	 * @param   string       $name          The name.
	 *
	 * @return  string
	 *
	 * @deprecated    12.1
	 * @since   11.1
	 */
	public function fetchTooltip($label, $description, &$node, $control_name, $name)
	{
		return '&#160;';
	}

	/**
	 * Fetch HTML for a radio button
	 *
	 * @param   string       $name          Element name
	 * @param   string       $value         Element value
	 * @param   JXMLElement  &$node         JXMLElement node object containing the settings for the element
	 * @param   string       $control_name  Control name
	 *
	 * @return  string
	 *
	 * @deprecated    12.1  Use JFormFieldSpacer::getInput instead.
	 * @since   11.1
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		// Deprecation warning.
		JLog::add('JElementSpcer::fetchElements() is deprecated.', JLog::WARNING, 'deprecated');

		if ($value)
		{
			return JText::_($value);
		}
		else
		{
			return ' ';
		}
	}
}
