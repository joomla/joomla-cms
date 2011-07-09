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
 * @deprecated  Use JForm instead
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
	 * @deprecated
	 */
	public function fetchTooltip($label, $description, &$node, $control_name, $name)
	{
		return '&#160;';
	}

	/**
	 *
	 * @since   11.1
	 *
	 * @deprecated
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		if ($value) {
			return JText::_($value);
		} else {
			return ' ';
		}
	}
}
