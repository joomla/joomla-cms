<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Button;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * The TaskButton class.
 *
 * @since  4.0.0
 */
class ActionButton
{
	/**
	 * The button states profiles.
	 *
	 * @var  array
	 *
	 * @since  4.0.0
	 */
	protected $states = [];

	/**
	 * Default options for unknown state.
	 *
	 * @var  array
	 *
	 * @since  4.0.0
	 */
	protected $unknownState = [
		'value'   => null,
		'task'    => '',
		'icon'    => 'question',
		'title'   => 'Unknown state',
		'options' => [
			'disabled'  => false,
			'only_icon' => false,
			'tip' => true,
			'tip_title' => '',
			'task_prefix' => '',
			'checkbox_name' => 'cb',
		],
	];

	/**
	 * Options of this button set.
	 *
	 * @var  Registry
	 *
	 * @since  4.0.0
	 */
	protected $options;

	/**
	 * The layout path to render.
	 *
	 * @var  string
	 *
	 * @since  4.0.0
	 */
	protected $layout = 'joomla.button.action-button';

	/**
	 * ActionButton constructor.
	 *
	 * @param   array  $options  The options for all buttons in this group.
	 *
	 * @since   4.0.0
	 */
	public function __construct(array $options = [])
	{
		$this->options = new Registry($options);

		// Replace some dynamic values
		$this->unknownState['title'] = Text::_('JLIB_HTML_UNKNOWN_STATE');

		$this->preprocess();
	}

	/**
	 * Configure this object.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function preprocess()
	{
		// Implement this method.
	}

	/**
	 * Add a state profile.
	 *
	 * @param   integer  $value    The value of this state.
	 * @param   string   $task     The task you want to execute after click this button.
	 * @param   string   $icon     The icon to display for user.
	 * @param   string   $title    Title text will show if we enable tooltips.
	 * @param   array    $options  The button options, will override group options.
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function addState(int $value, string $task, string $icon = 'ok', string $title = '', array $options = []): self
	{
		// Force type to prevent null data
		$this->states[$value] = [
			'value'   => $value,
			'task'    => $task,
			'icon'    => $icon,
			'title'   => $title,
			'options' => $options
		];

		return $this;
	}

	/**
	 * Get state profile by value name.
	 *
	 * @param   integer  $value  The value name we want to get.
	 *
	 * @return  array|null  Return state profile or NULL.
	 *
	 * @since   4.0.0
	 */
	public function getState(int $value): ?array
	{
		return $this->states[$value] ?? null;
	}

	/**
	 * Remove a state by value name.
	 *
	 * @param   integer  $value  Remove state by this value.
	 *
	 * @return  static  Return to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function removeState(int $value): self
	{
		if (isset($this->states[$value]))
		{
			unset($this->states[$value]);
		}

		return $this;
	}

	/**
	 * Render action button by item value.
	 *
	 * @param   integer|null  $value    Current value of this item.
	 * @param   integer|null  $row      The row number of this item.
	 * @param   array         $options  The options to override group options.
	 *
	 * @return  string  Rendered HTML.
	 *
	 * @since   4.0.0
	 *
	 * @throws  \InvalidArgumentException
	 */
	public function render(?int $value = null, ?int $row = null, array $options = []): string
	{
		$data = $this->getState($value) ?? $this->unknownState;

		$data = ArrayHelper::mergeRecursive(
			$this->unknownState,
			$data,
			[
				'options' => $this->options->toArray()
			],
			[
				'options' => $options
			]
		);

		$data['row'] = $row;
		$data['icon'] = $this->fetchIconClass($data['icon']);

		return LayoutHelper::render($this->layout, $data);
	}

	/**
	 * Render to string.
	 *
	 * @return  string
	 *
	 * @since  4.0.0
	 */
	public function __toString(): string
	{
		try
		{
			return $this->render();
		}
		catch (\Throwable $e)
		{
			return (string) $e;
		}
	}

	/**
	 * Method to get property layout.
	 *
	 * @return  string
	 *
	 * @since  4.0.0
	 */
	public function getLayout(): string
	{
		return $this->layout;
	}

	/**
	 * Method to set property template.
	 *
	 * @param   string  $layout  The layout path.
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function setLayout(string $layout): self
	{
		$this->layout = $layout;

		return $this;
	}

	/**
	 * Method to get property options.
	 *
	 * @return  array
	 *
	 * @since  4.0.0
	 */
	public function getOptions(): array
	{
		return (array) $this->options->toArray();
	}

	/**
	 * Method to set property options.
	 *
	 * @param   array  $options  The options of this button group.
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function setOptions(array $options): self
	{
		$this->options = new Registry($options);

		return $this;
	}

	/**
	 * Get an option value.
	 *
	 * @param   string  $name     The option name.
	 * @param   mixed   $default  Default value if not exists.
	 *
	 * @return  mixed  Return option value or default value.
	 *
	 * @since   4.0.0
	 */
	public function getOption(string $name, $default = null)
	{
		return $this->options->get($name, $default);
	}

	/**
	 * Set option value.
	 *
	 * @param   string  $name   The option name.
	 * @param   mixed   $value  The option value.
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function setOption(string $name, $value): self
	{
		$this->options->set($name, $value);

		return $this;
	}

	/**
	 * Method to get the CSS class name for an icon identifier.
	 *
	 * Can be redefined in the final class.
	 *
	 * @param   string  $identifier  Icon identification string.
	 *
	 * @return  string  CSS class name.
	 *
	 * @since   4.0.0
	 */
	public function fetchIconClass(string $identifier): string
	{
		// It's an ugly hack, but this allows templates to define the icon classes for the toolbar
		$layout = new FileLayout('joomla.button.iconclass');

		return $layout->render(array('icon' => $identifier));
	}
}
