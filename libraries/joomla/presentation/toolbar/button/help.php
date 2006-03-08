<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Renders a help popup window button
 *
 * @author 		Louis Landry <louis@webimagery.net>
 * @package 	Joomla.Framework
 * @subpackage 	Presentation
 * @since		1.1
 */
class JButton_Help extends JButton
{
	/**
	 * Button type
	 *
	 * @access	protected
	 * @var		string
	 */
	var $_name = 'Help';

	function fetchButton( $type='Help', $ref = '', $com = false )
	{
		$text	= JText::_('Help');
		$class	= $this->fetchIconClass('help');
		$doTask	= $this->_getCommand($ref, $com);

		$html  = "<a onclick=\"$doTask\" class=\"toolbar\">\n";
		$html .= "<div class=\"$class\" title=\"$text\" type=\"$type\">\n";
		$html .= "</div>\n";
		$html .= "$text\n";
		$html .= "</a>\n";

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
	 * @since		1.1
	 */
	function fetchId($name)
	{
		return $this->_parent->_name.'-'."help";
	}
	
	/**
	 * Get the JavaScript command for the button
	 * 
	 * @access	private
	 * @param	object	$definition	Button definition
	 * @return	string	JavaScript command string
	 * @since	1.1
	 */
	function _getCommand($ref, $com)
	{
		// Get Help URL
		jimport('joomla.i18n.help');
		$url = JHelp::createURL($ref, $com);

		$cmd = "window.open('$url', 'joomla_help_win', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');";
		
		return $cmd;
	}
}
?>
