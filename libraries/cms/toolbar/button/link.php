<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Toolbar
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a link button
 *
 * @since  3.0
 */
class JToolbarButtonLink extends JToolbarButton
{
	/**
	 * Button type
	 * @var    string
	 */
	protected $_name = 'Link';

	/**
	 * Fetch the HTML for the button
	 *
	 * @param   string  $type  Unused string.
	 * @param   string  $name  Name to be used as apart of the id
	 * @param   string  $text  Button text
	 * @param   string  $url   The link url
	 *
	 * @return  string  HTML string for the button
	 *
	 * @since   3.0
	 */
	public function fetchButton($type = 'Link', $name = 'back', $text = '', $url = null)
	{
		// Store all data to the options array for use with JLayout
		$options = array();
		$options['text'] = JText::_($text);
		$options['class'] = $this->fetchIconClass($name);
		$options['doTask'] = $this->_getCommand($url);

		// Instantiate a new JLayoutFile instance and render the layout
		$layout = new JLayoutFile('joomla.toolbar.link');

		return $layout->render($options);
	}

	/**
	 * Get the button CSS Id
	 *
	 * @param   string  $type  The button type.
	 * @param   string  $name  The name of the button.
	 *
	 * @return  string  Button CSS Id
	 *
	 * @since   3.0
	 */
	public function fetchId($type = 'Link', $name = '')
	{
		return $this->_parent->getName() . '-' . $name;
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @param   object  $url  Button definition
	 *
	 * @return  string  JavaScript command string
	 *
	 * @since   3.0
	 */
	protected function _getCommand($url)
	{
		return $url;
	}
}
