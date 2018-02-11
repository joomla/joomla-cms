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
 * Renders a standard button
 *
 * @method self list(bool $value)
 * @method bool getList()
 *
 * @since  3.0
 */
class StandardButton extends ToolbarButton
{
	/**
	 * Property layout.
	 *
	 * @var  string
	 */
	protected $layout = 'joomla.toolbar.standard';

	/**
	 * prepareOptions
	 *
	 * @param array $options
	 *
	 * @return  void
	 */
	protected function prepareOptions(array &$options)
	{
		$options['doTask'] = $this->_getCommand($options['text'], $this->getTask(), $this->getList());
		$options['group']  = $this->getGroup();

		if ($options['id'])
		{
			$options['id'] = ' id="' . $options['id'] . '"';
		}

		$options['btnClass'] = ($options['button_class'] ?? $this->getButtonClass($options['name']));
	}

	/**
	 * Fetch the HTML for the button
	 *
	 * @param   string   $type   Unused string.
	 * @param   string   $name   The name of the button icon class.
	 * @param   string   $text   Button text.
	 * @param   string   $task   Task associated with the button.
	 * @param   boolean  $list   True to allow lists
	 * @param   boolean  $group  Does the button belong to a group?
	 *
	 * @return  string  HTML string for the button
	 *
	 * @since   3.0
	 */
	public function fetchButton($type = 'Standard', $name = '', $text = '', $task = '', $list = true, $group = false)
	{
		$this->name($name)
			->text(\JText::_($text))
			->task($task)
			->list($list)
			->group($group);

		// Store all data to the options array for use with JLayout
		$options = $this->getOptions();
		$options['name']  = $name;
		$options['class'] = $this->fetchIconClass($name);
		$options['id']    = $this->fetchId();

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
				return ' btn btn-sm btn-outline-success';

			case 'unpublish':
				return ' btn btn-sm btn-outline-danger';

			case 'featured':
				return ' btn btn-sm btn-outline-warning';

			case 'cancel':
				return ' btn btn-sm btn-danger';

			case 'trash':
				return ' btn btn-sm btn-outline-danger';

			default:
				return ' btn btn-sm btn-outline-primary';
		}
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
		Text::script('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
		Text::script('ERROR');

		$cmd = "Joomla.submitbutton('" . $task . "');";

		if ($list)
		{
			$messages = "{'error': [Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST')]}";
			$alert = "Joomla.renderMessages(" . $messages . ")";
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
				'list',
			]
		);
	}
}
