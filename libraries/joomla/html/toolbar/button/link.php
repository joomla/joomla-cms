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
 * Renders a link button
 *
 * @package		Joomla.Platform
 * @subpackage	HTML
 * @since		11.1
 */
class JButtonLink extends JButton
{
	/**
	 * Button type
	 * @var		string
	 */
	protected $_name = 'Link';

	public function fetchButton($type='Link', $name = 'back', $text = '', $url = null)
	{
		$text	= JText::_($text);
		$class	= $this->fetchIconClass($name);
		$doTask	= $this->_getCommand($url);

		$html	= "<a href=\"$doTask\">\n";
		$html .= "<span class=\"$class\">\n";
		$html .= "</span>\n";
		$html	.= "$text\n";
		$html	.= "</a>\n";

		return $html;
	}

	/**
	 * Get the button CSS Id
	 *
	 * @param	string	$type	The button type.
	 * @param	string	$name	The name of the button.
	 *
	 * @return	string	Button CSS Id
	 * @since	11.1
	 */
	public function fetchId($type = 'Link', $name = '')
	{
		return $this->_parent->getName().'-'.$name;
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @param	object	$definition	Button definition
	 *
	 * @return	string	JavaScript command string
	 * @since	11.1
	 */
	protected function _getCommand($url)
	{
		return $url;
	}
}