<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Toolbar
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a standard button with a confirm dialog
 *
 * @since  3.0
 */
class JToolbarButtonConfirm extends JToolbarButton
{
	/**
	 * Button type
	 *
	 * @var    string
	 */
	protected $_name = 'Confirm';

	/**
	 * Fetch the HTML for the button
	 *
	 * @param   string   $type      Unused string.
	 * @param   string   $msg       Message to render
	 * @param   string   $name      Name to be used as apart of the id
	 * @param   string   $text      Button text
	 * @param   string   $task      The task associated with the button
	 * @param   boolean  $list      True to allow use of lists
	 * @param   boolean  $hideMenu  True to hide the menu on click
	 *
	 * @return  string   HTML string for the button
	 *
	 * @since   3.0
	 */
	public function fetchButton($type = 'Confirm', $msg = '', $name = '', $text = '', $task = '', $list = true, $hideMenu = false)
	{
		// Store all data to the options array for use with JLayout
		$options = array();
		$options['text'] = JText::_($text);
		$options['msg'] = JText::_($msg, true);
		$options['class'] = $this->fetchIconClass($name);
		$options['doTask'] = $this->_getCommand($options['msg'], $name, $task, $list);

		// Instantiate a new JLayoutFile instance and render the layout
		$layout = new JLayoutFile('joomla.toolbar.confirm');

		return $layout->render($options);
	}

	/**
	 * Get the button CSS Id
	 *
	 * @param   string   $type      Button type
	 * @param   string   $msg       Message to display
	 * @param   string   $name      Name to be used as apart of the id
	 * @param   string   $text      Button text
	 * @param   string   $task      The task associated with the button
	 * @param   boolean  $list      True to allow use of lists
	 * @param   boolean  $hideMenu  True to hide the menu on click
	 *
	 * @return  string  Button CSS Id
	 *
	 * @since   3.0
	 */
	public function fetchId($type = 'Confirm', $msg = '', $name = '', $text = '', $task = '', $list = true, $hideMenu = false)
	{
		return $this->_parent->getName() . '-' . $name;
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @param   object   $msg   The message to display.
	 * @param   string   $name  Not used.
	 * @param   string   $task  The task used by the application
	 * @param   boolean  $list  True is requires a list confirmation.
	 *
	 * @return  string  JavaScript command string
	 *
	 * @since   3.0
	 */
	protected function _getCommand($msg, $name, $task, $list)
	{
		$message = JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
		$message = addslashes($message);

		if ($list)
		{
			$cmd = "if (document.adminForm.boxchecked.value==0){alert('$message');}else{if (confirm('$msg')){Joomla.submitbutton('$task');}}";
		}
		else
		{
			$cmd = "if (confirm('$msg')){Joomla.submitbutton('$task');}";
		}

		return $cmd;
	}
}
