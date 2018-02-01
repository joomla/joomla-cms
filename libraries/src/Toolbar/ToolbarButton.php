<?php
/**
 * Part of 40dev project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Joomla\CMS\Toolbar;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\String\Normalise;

/**
 * The ToolbarButton class.
 *
 * @method ToolbarButton text(string $value)
 * @method ToolbarButton task(string $value)
 * @method ToolbarButton icon(string $value)
 * @method ToolbarButton group(bool $value)
 * @method string getText()
 * @method string getTask()
 * @method string getIcon()
 * @method bool   getGroup()
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class ToolbarButton
{
	/**
	 * Property name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * reference to the object that instantiated the element
	 *
	 * @var    Toolbar
	 */
	protected $parent = null;

	/**
	 * Property options.
	 *
	 * @var  array
	 */
	protected $options = [];

	/**
	 * Constructor
	 *
	 * @param   Toolbar  $parent  The parent
	 */
	public function __construct($name, $text = '', $task = '')
	{
		$this->name($name)
			->text($text)
			->task($task);
	}

	/**
	 * prepareOptions
	 *
	 * @param array $options
	 *
	 * @return  void
	 */
	protected function prepareOptions(array &$options)
	{
		//
	}

	/**
	 * Get the HTML to render the button
	 *
	 * @param   array  &$definition  Parameters to be passed
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function render(&$definition = null)
	{
		if ($definition === null)
		{
			/*
		 * Initialise some variables
		 */
			$options = $this->getOptions();

			$options['name']     = $this->getName();
			$options['text']     = Text::_($this->getText());
			$options['class']    = $this->fetchIconClass($this->getIcon());
			$options['group']    = $this->getGroup();
			$options['id']       = $this->fetchId();
			$options['btnClass'] = 'button-' . $this->getName();

			$this->prepareOptions($options);

			// Instantiate a new JLayoutFile instance and render the layout
			$layout = new FileLayout('joomla.toolbar.standard');

			$action = $layout->render($options);

			// Build the HTML Button
			$layout = new FileLayout('joomla.toolbar.base');

			return $layout->render(['action' => $action]);
		}

		/*
		 * Initialise some variables
		 */
		$id = call_user_func_array(array(&$this, 'fetchId'), $definition);
		$action = call_user_func_array(array(&$this, 'fetchButton'), $definition);
		// Build id attribute
		if ($id)
		{
			$id = ' id="' . $id . '"';
		}
		// Build the HTML Button
		$options = array();
		$options['id'] = $id;
		$options['action'] = $action;
		$layout = new FileLayout('joomla.toolbar.base');
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
	protected function fetchId()
	{
		return $this->parent->getName() . '-' . $this->getName();
	}

	/**
	 * Method to get the CSS class name for an icon identifier
	 *
	 * Can be redefined in the final class
	 *
	 * @param   string  $identifier  Icon identification string
	 *
	 * @return  string  CSS class name
	 *
	 * @since   3.0
	 */
	public function fetchIconClass($identifier)
	{
		// It's an ugly hack, but this allows templates to define the icon classes for the toolbar
		$layout = new FileLayout('joomla.toolbar.iconclass');

		return $layout->render(array('icon' => $identifier));
	}

	/**
	 * Get the button
	 *
	 * Defined in the final button class
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	abstract public function fetchButton();

	/**
	 * Method to get property Parent
	 *
	 * @return  Toolbar
	 */
	public function getParent(): Toolbar
	{
		return $this->parent;
	}

	/**
	 * Method to set property parent
	 *
	 * @param   Toolbar $parent
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setParent(Toolbar $parent)
	{
		$this->parent = $parent;

		return $this;
	}

	/**
	 * Method to get property Options
	 *
	 * @return  array
	 */
	public function getOptions(): array
	{
		return $this->options;
	}

	/**
	 * Method to set property options
	 *
	 * @param   array $options
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setOptions(array $options): self
	{
		$this->options = $options;

		return $this;
	}

	/**
	 * getOption
	 *
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return  mixed
	 */
	public function getOption(string $name, $default = null)
	{
		return $this->options[$name] ?? $default;
	}

	/**
	 * setOption
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return  static
	 */
	public function setOption(string $name, $value): self
	{
		$this->options[$name] = $value;

		return $this;
	}

	/**
	 * __call
	 *
	 * @param string $name
	 * @param array  $args
	 *
	 * @return  mixed
	 * @throws \LogicException
	 */
	public function __call(string $name, array $args)
	{
		// getter
		if (stripos($name, 'get') === 0)
		{
			$fieldName = static::findOptionName(strtolower(substr($name, 3)));

			if ($fieldName !== false)
			{
				return $this->getOption($fieldName);
			}
		}
		// setter
		else
		{
			$fieldName = static::findOptionName($name);

			if ($fieldName !== false)
			{
				if (!isset($args[0]))
				{
					throw new \InvalidArgumentException(
						sprintf(
							'%s::%s() miss first argument.',
							__CLASS__,
							$name
						)
					);
				}

				return $this->setOption($fieldName, $args[0]);
			}
		}

		throw new \BadMethodCallException(
			sprintf(
				'Method %s() not found in class: %s',
				$name,
				__CLASS__
			)
		);
	}

	/**
	 * findOptionName
	 *
	 * @param string $name
	 *
	 * @return  bool|string
	 */
	private static function findOptionName(string $name)
	{
		$accessors = static::getAccessors();

		if (\in_array($name, $accessors, true))
		{
			return $accessors[array_search($name, $accessors, true)];
		}

		// getter with alias
		if (isset($accessors[$name]))
		{
			return $accessors[$name];
		}

		return false;
	}

	/**
	 * getAccessors
	 *
	 * @return  array
	 */
	protected static function getAccessors()
	{
		return [
			'text',
			'task',
			'icon',
			'group' => 'group'
		];
	}

	/**
	 * Method to get property Name
	 *
	 * @return  string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Method to set property name
	 *
	 * @param   string $name
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function name($name)
	{
		$this->name = $name;

		return $this;
	}
}
