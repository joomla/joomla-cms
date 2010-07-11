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
 * Renders a help popup window button
 *
 * @package		Joomla.Framework
 * @subpackage		HTML
 * @since		1.5
 */
class JButtonHelp extends JButton
{
	/**
	 * Button type
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $_name = 'Help';

	public function fetchButton($type='Help', $ref = '', $com = false, $override = null)
	{
		$text	= JText::_('JTOOLBAR_HELP');
		$class	= $this->fetchIconClass('help');
		$doTask	= $this->_getCommand($ref, $com, $override);

		$html	= "<a href=\"#\" onclick=\"$doTask\" class=\"toolbar\">\n";
		$html .= "<span class=\"$class\">\n";
		$html .= "</span>\n";
		$html	.= "$text\n";
		$html	.= "</a>\n";

		return $html;
	}

	/**
	 * Get the button id
	 *
	 * Redefined from JButton class
	 *
	 * @access		public
	 * @param		string	$name	Button name
	 * @return		string	Button CSS Id
	 * @since		1.5
	 */
	public function fetchId($name)
	{
		return $this->_parent->getName().'-'."help";
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @param	object	$definition	Button definition
	 * @return	string	JavaScript command string
	 * @since	1.5
	 */
	protected function _getCommand($ref, $com, $override)
	{
		// Get Help URL
		jimport('joomla.language.help');
		$url = JHelp::createURL($ref, $com, $override);
		$url = htmlspecialchars($url, ENT_QUOTES);
		$cmd = "popupWindow('$url', '".JText::_('JHELP', true)."', 700, 500, 1)";

		return $cmd;
	}
}
