<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;

/**
 * Renders a standard button
 *
 * @method self listCheck(bool $value)
 * @method self listCheckMessage(string $value)
 * @method self form(string $value)
 * @method self formValidation(bool $value)
 * @method bool getListCheck()
 * @method bool getListCheckMessage()
 * @method bool getFormValidation()
 *
 * @since  3.0
 */
class StandardButton extends BasicButton
{
	/**
	 * Property layout.
	 *
	 * @var  string
	 *
	 * @since  4.0.0
	 */
	protected $layout = 'joomla.toolbar.standard';

	/**
	 * Prepare options for this button.
	 *
	 * @param   array  &$options  The options about this button.
	 *
	 * @return  void
	 *
	 * @since  4.0.0
	 */
	protected function prepareOptions(array &$options)
	{
		parent::prepareOptions($options);

		if (empty($options['is_child']))
		{
			$class = $this->fetchButtonClass($this->getName());

			$options['btnClass'] = $options['button_class'] = ($options['button_class'] ?? $class);
		}

		$options['onclick'] = $options['onclick'] ?? $this->_getCommand();
	}

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
	 *
	 * @deprecated  5.0 Use render() instead.
	 */
	public function fetchButton($type = 'Standard', $name = '', $text = '', $task = '', $list = true)
	{
		$this->name($name)
			->text($text)
			->task($task)
			->listCheck($list);

		return $this->renderButton($this->options);
	}

	/**
	 * Fetch button class for standard buttons.
	 *
	 * @param   string  $name  The button name.
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function fetchButtonClass(string $name): string
	{
		switch ($name)
		{
			case 'apply':
			case 'new':
				return ' btn btn-sm btn-success';

			case 'save':
			case 'save-new':
			case 'save-copy':
			case 'save-close':
			case 'publish':
				return ' btn btn-sm btn-success';

			case 'unpublish':
				return ' btn btn-sm btn-danger';

			case 'featured':
				return ' btn btn-sm btn-warning';

			case 'cancel':
				return ' btn btn-sm btn-danger';

			case 'trash':
				return ' btn btn-sm btn-danger';

			default:
				return ' btn btn-sm btn-primary';
		}
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @return  string   JavaScript command string
	 *
	 * @since   3.0
	 */
	protected function _getCommand()
	{
		Text::script($this->getListCheckMessage() ?: 'JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
		Text::script('ERROR');

		$cmd = "Joomla.submitbutton('" . $this->getTask() . "');";

		if ($this->getListCheck())
		{
			$messages = "{error: [Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST')]}";
			$alert = 'Joomla.renderMessages(' . $messages . ')';
			$cmd   = 'if (document.adminForm.boxchecked.value == 0) { ' . $alert . ' } else { ' . $cmd . ' }';
		}

		return $cmd;
	}

	/**
	 * Method to configure available option accessors.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	protected static function getAccessors(): array
	{
		return array_merge(
			parent::getAccessors(),
			[
				'listCheck',
				'listCheckMessage',
				'form',
				'formValidation'
			]
		);
	}
}
