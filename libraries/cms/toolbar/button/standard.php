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
 * Renders a standard button
 *
 * @since  3.0
 */
class JToolbarButtonStandard extends JToolbarButton
{
	/**
	 * Button type
	 *
	 * @var    string
	 */
	protected $_name = 'Standard';

	/**
	 * Fetch the HTML for the button
	 *
	 * @param   string   $type  Unused string.
	 * @param   string   $name  The name of the button icon class.
	 * @param   string   $text  Button text.
	 * @param   string   $task  Task associated with the button.
	 * @param   boolean  $list  True to allow lists
	 *
	 * @return  string  HTML string for the button
	 *
	 * @since   3.0
	 */
	public function fetchButton($type = 'Standard', $name = '', $text = '', $task = '', $list = true)
	{
		// Store all data to the options array for use with JLayout
		$options = array();
		$options['text'] = JText::_($text);
		$options['class'] = $this->fetchIconClass($name);
		$options['doTask'] = $this->_getCommand($options['text'], $task, $list);

		if ($name == 'apply' || $name == 'new')
		{
			$options['btnClass'] = 'btn btn-small btn-success';
			$options['class'] .= ' icon-white';
		}
		else
		{
			$options['btnClass'] = 'btn btn-small';
		}

		// Instantiate a new JLayoutFile instance and render the layout
		$layout = new JLayoutFile('joomla.toolbar.standard');

		return $layout->render($options);
	}

	/**
	 * Get the button CSS Id
	 *
	 * @param   string   $type      Unused string.
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
	public function fetchId($type = 'Standard', $name = '', $text = '', $task = '', $list = true, $hideMenu = false)
	{
		return $this->_parent->getName() . '-' . $name;
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @param   string   $name  The task name as seen by the user
	 * @param   string   $task  The task used by the application
	 * @param   boolean  $list  True is requires a list confirmation.
	 *
	 * @return  string   JavaScript command string
	 *
	 * @since   3.0
	 */
	protected function _getCommand($name, $task, $list)
	{
		JText::script('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');

		$cmd = "Joomla.submitbutton('" . $task . "');";

		if ($list)
		{
			$alert = "alert(Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));";
			$cmd   = "if (document.adminForm.boxchecked.value == 0) { " . $alert . " } else { " . $cmd . " }";
		}

		return $cmd;
	}
}
