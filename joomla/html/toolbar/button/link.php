<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Renders a link button
 *
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
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
	 * @since	1.5
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
	 * @since	1.5
	 */
	protected function _getCommand($url)
	{
		return $url;
	}
}