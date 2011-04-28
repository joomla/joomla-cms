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
 * Renders a standard button with a confirm dialog
 *
 * @package		Joomla.Platform
 * @subpackage	HTML
 * @since		11.1
 */
class JButtonConfirm extends JButton
{
	/**
	 * Button type
	 *
	 * @var		string
	 */
	protected $_name = 'Confirm';

	public function fetchButton($type='Confirm', $msg='', $name = '', $text = '', $task = '', $list = true, $hideMenu = false)
	{
		$text	= JText::_($text);
		$msg	= JText::_($msg, true);
		$class	= $this->fetchIconClass($name);
		$doTask	= $this->_getCommand($msg, $name, $task, $list);

		$html	= "<a href=\"#\" onclick=\"$doTask\" class=\"toolbar\">\n";
		$html .= "<span class=\"$class\">\n";
		$html .= "</span>\n";
		$html	.= "$text\n";
		$html	.= "</a>\n";

		return $html;
	}

	/**
	 * Get the button CSS Id
	 *
	 * @return	string	Button CSS Id
	 * @since	11.1
	 */
	public function fetchId($type='Confirm', $name = '', $text = '', $task = '', $list = true, $hideMenu = false)
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
	protected function _getCommand($msg, $name, $task, $list)
	{
		$message	= JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
		$message	= addslashes($message);

		if ($list) {
			$cmd = "javascript:if (document.adminForm.boxchecked.value==0){alert('$message');}else{if (confirm('$msg')){Joomla.submitbutton('$task');}}";
		} else {
			$cmd = "javascript:if (confirm('$msg')){Joomla.submitbutton('$task');}";
		}

		return $cmd;
	}
}