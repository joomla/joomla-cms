<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Toolbar
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a button separator
 *
 * @since  3.0
 */
class JToolbarButtonSeparator extends JToolbarButton
{
	/**
	 * Button type
	 *
	 * @var   string
	 */
	protected $_name = 'Separator';

	/**
	 * Get the HTML for a separator in the toolbar
	 *
	 * @param   array  &$definition  Class name and custom width
	 *
	 * @return  string  The HTML for the separator
	 *
	 * @see     JToolbarButton::render()
	 * @since   3.0
	 */
	public function render(&$definition)
	{
		// Store all data to the options array for use with JLayout
		$options = array();

		// Separator class name
		$options['class'] = (empty($definition[1])) ? '' : $definition[1];

		// Custom width
		$options['style'] = (empty($definition[2])) ? '' : ' style="width:' . (int) $definition[2] . 'px;"';

		// Instantiate a new JLayoutFile instance and render the layout
		$layout = new JLayoutFile('joomla.toolbar.separator');

		return $layout->render($options);
	}

	/**
	 * Empty implementation (not required for separator)
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function fetchButton()
	{
	}
}
