<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  HTML
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a help popup window button
 *
 * @package		Joomla.Platform
 * @subpackage	HTML
 * @since		1.5
 */
class JButtonHelp extends JButton
{
	/**
	 * @var		string	Button type
	 */
	protected $_name = 'Help';

	/**
	 * @param	string	$type		Unused string.
	 * @param	string	$ref		The name of the help screen (its key reference).
	 * @param	boolean	$com		Use the help file in the component directory.
	 * @param	string	$override	Use this URL instead of any other.
	 * @param	string	$component	Name of component to get Help (null for current component)
	 *
	 * @return	string
	 * @since	1.5
	 */
	public function fetchButton($type = 'Help', $ref = '', $com = false, $override = null, $component = null)
	{
		$text	= JText::_('JTOOLBAR_HELP');
		$class	= $this->fetchIconClass('help');
		$doTask	= $this->_getCommand($ref, $com, $override, $component);

		$html = "<a href=\"#\" onclick=\"$doTask\" rel=\"help\" class=\"toolbar\">\n";
		$html .= "<span class=\"$class\">\n";
		$html .= "</span>\n";
		$html .= "$text\n";
		$html .= "</a>\n";

		return $html;
	}

	/**
	 * Get the button id
	 *
	 * Redefined from JButton class
	 *
	 * @return		string	Button CSS Id
	 * @since		1.5
	 */
	public function fetchId()
	{
		return $this->_parent->getName().'-'."help";
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @param	string	$ref		The name of the help screen (its key reference).
	 * @param	boolean	$com		Use the help file in the component directory.
	 * @param	string	$override	Use this URL instead of any other.
	 * @param	string	$component	Name of component to get Help (null for current component)
	 *
	 * @return	string	JavaScript command string
	 * @since	1.5
	 */
	protected function _getCommand($ref, $com, $override, $component)
	{
		// Get Help URL
		jimport('joomla.language.help');
		$url = JHelp::createURL($ref, $com, $override, $component);
		$url = htmlspecialchars($url, ENT_QUOTES);
		$cmd = "popupWindow('$url', '".JText::_('JHELP', true)."', 700, 500, 1)";

		return $cmd;
	}
}
