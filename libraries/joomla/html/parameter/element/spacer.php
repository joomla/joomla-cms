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
	 *
	 * @since   11.1
	 *
	 * @deprecated    12.1
	 */
	public function fetchTooltip($label, $description, &$node, $control_name, $name)
	{
		return '&#160;';
	}

	/**
	 *
	 * @since   11.1
	 *
	 * @deprecated    12.1  Use JFormFieldSpacer::getInput instead.
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		// Deprecation warning.
		JLog::add('JElementSpcer::fetchElements() is deprecated.', JLog::WARNING, 'deprecated');
		
		if ($value) {
			return JText::_($value);
		} else {
			return ' ';
		}
	}
}
