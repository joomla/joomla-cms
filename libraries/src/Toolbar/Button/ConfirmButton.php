<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;

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
	 * prepareOptions
	 *
	 * @param array $options
	 *
	 * @return  void
	 */
	protected function prepareOptions(array &$options)
	{
		$options['message'] = Text::_($options['message'] ?? '');

		parent::prepareOptions($options);
	}

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
	 *
	 * @deprecated  5.0 Use render() instead.
	 */
	public function fetchButton($type = 'Confirm', $msg = '', $name = '', $text = '', $task = '', $list = true, $hideMenu = false)
	{
		$this->name($name)
			->text($text)
			->listCheck($list)
			->message($msg)
			->task($task);

		return $this->renderButton($this->options);
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
	protected function _getCommand()
	{
		\JText::script('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
		\JText::script('ERROR');

		$msg = $this->getMessage();

		$cmd = "if (confirm('" . $msg . "')) { Joomla.submitbutton('" . $this->getTask() . "'); }";

		if ($this->getListCheck())
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
