<?php
/**
* @version		$Id:confirm.php 6961 2007-03-15 16:06:53Z tcp $
* @package		Joomla.Framework
* @subpackage	HTML
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a standard button with a confirm dialog
 *
 * @author 		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JButtonConfirm extends JButton
{
	/**
	 * Button type
	 *
	 * @access	protected
	 * @var		string
	 */
	var $_name = 'Confirm';

	function fetchButton( $type='Confirm', $msg='', $name = '', $text = '', $task = '', $list = true, $hideMenu = false )
	{
		$text	= JText::_($text);
		$msg	= JText::_($msg, true);
		$class	= $this->fetchIconClass($name);
		$doTask	= $this->_getCommand($msg, $name, $task, $list, $hideMenu);

		$html	= "<a href=\"#\" onclick=\"$doTask\" class=\"toolbar\">\n";
		$html .= "<span class=\"$class\" title=\"$text\">\n";
		$html .= "</span>\n";
		$html	.= "$text\n";
		$html	.= "</a>\n";

		return $html;
	}

	/**
	 * Get the button CSS Id
	 *
	 * @access	public
	 * @return	string	Button CSS Id
	 * @since	1.5
	 */
	function fetchId( $type='Confirm', $name = '', $text = '', $task = '', $list = true, $hideMenu = false )
	{
		return $this->_parent->_name.'-'.$name;
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @access	private
	 * @param	object	$definition	Button definition
	 * @return	string	JavaScript command string
	 * @since	1.5
	 */
	function _getCommand($msg, $name, $task, $list, $hide)
	{
		if ($hide) {
			if ($list) {
				$cmd = "javascript:if(document.adminForm.boxchecked.value==0){alert('". JText::_( 'Please make a selection from the list to', true ) ." ". JText::_( $name, true ) ."');}else{hideMainMenu();if(confirm('$msg')){submitbutton('$task');}}";
			} else {
				$cmd = "javascript:hideMainMenu();if(confirm('$msg')){submitbutton('$task');}";
			}
		} else {
			if ($list) {
				$cmd = "javascript:if(document.adminForm.boxchecked.value==0){alert('". JText::_( 'Please make a selection from the list to', true ) ." ". JText::_( $name, true ) ."');}else{if(confirm('$msg')){submitbutton('$task');}}";
			} else {
				$cmd = "javascript:if(confirm('$msg')){submitbutton('$task');}";
			}
		}

		return $cmd;
	}
}