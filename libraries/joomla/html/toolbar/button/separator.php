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
 * Renders a button separator
 *
 * @package     Joomla.Platform
 * @subpackage  HTML
 * @since       11.1
 */
class JButtonSeparator extends JButton
{
	/**
	 * Button type
	 *
	 * @var   string
	 */
	protected $_name = 'Separator';

	public function render(&$definition)
	{
		// Initialise variables.
		$class	= null;
		$style	= null;

		// Separator class name
		$class = (empty($definition[1])) ? 'spacer' : $definition[1];
		// Custom width
		$style = (empty($definition[2])) ? null : ' style="width:' .  intval($definition[2]) . 'px;"';

		return '<li class="' . $class . '"' . $style . ">\n</li>\n";
	}

	/**
	 * Empty implementation (not required)
	 */
	public function fetchButton()
	{
	}
}
