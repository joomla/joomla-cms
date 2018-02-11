<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Legacy;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Toolbar\ToolbarButton;

/**
 * Renders a standard button with a confirm dialog
 *
 * @method self message(string $value)
 * @method bool getMessage()
 *
 * @since  3.0
 */
class ConfirmButton extends StandardButton
{
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
		$this->name($name)
			->text(Text::_($text))
			->list($list)
			->message(Text::_($msg))
			->task($task);

		// Store all data to the options array for use with JLayout
		$options = $this->getOptions();
		$options['name']   = $this->getName();
		$options['msg']    = Text::_($msg, true);
		$options['class']  = $this->fetchIconClass($name);
		$options['id']     = $this->fetchId('Confirm', $name);

		if ($options['id'])
		{
			$options['id'] = ' id="' . $options['id'] . '"';
		}

		$this->prepareOptions($options);

		// Instantiate a new JLayoutFile instance and render the layout
		$layout = new FileLayout($this->layout);

		return $layout->render($options);
	}

	/**
	 * getButtonClass
	 *
	 * @param string $name
	 *
	 * @return  string
	 */
	public function getButtonClass(string $name): string
	{
		return ' btn btn-sm btn-outline-danger';
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
	protected function _getCommand($name, $task, $list)
	{
		\JText::script('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
		\JText::script('ERROR');

		$msg = $this->getMessage();

		$cmd = "if (confirm('" . $msg . "')) { Joomla.submitbutton('" . $task . "'); }";

		if ($list)
		{
			$message = "{'error': [Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST')]}";
			$alert = "Joomla.renderMessages(" . $message . ")";
			$cmd   = "if (document.adminForm.boxchecked.value == 0) { " . $alert . " } else { " . $cmd . " }";
		}

		return $cmd;
	}

	/**
	 * getAccessors
	 *
	 * @return  array
	 */
	protected static function getAccessors(): array
	{
		return array_merge(
			parent::getAccessors(),
			[
				'message',
			]
		);
	}
}
