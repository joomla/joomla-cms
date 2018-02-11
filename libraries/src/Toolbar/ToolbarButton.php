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
use Joomla\Utilities\ArrayHelper;

/**
 * The ToolbarButton class.
 *
 * @method self text(string $value)
 * @method self task(string $value)
 * @method self icon(string $value)
 * @method self group(bool $value)
 * @method self buttonClass(string $value)
 * @method self attributes(array $value)
 * @method string getText()
 * @method string getTask()
 * @method string getIcon()
 * @method bool   getGroup()
 * @method string getButtonClass()
 * @method array  getAttributes()
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
	protected $parent;

	/**
	 * Property child.
	 *
	 * @var Toolbar
	 */
	protected $child;

	/**
	 * Property layout.
	 *
	 * @var string
	 */
	protected $layout;

	/**
	 * Property options.
	 *
	 * @var  array
	 */
	protected $options = [];

	/**
	 * Constructor
	 *
	 * @param string $name
	 * @param string $text
	 * @param string $task
	 */
	public function __construct(string $name = '', string $text = '', string $task = '')
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
	 * children
	 *
	 * @param callable $handler
	 *
	 * @return  static
	 */
	public function children(callable $handler)
	{
		$child = $this->getChildToolbar();

		$handler($child);

		return $this;
	}

	/**
	 * getChildToolbar
	 *
	 * @return  Toolbar
	 */
	public function getChildToolbar()
	{
		if (!$this->child)
		{
			$this->child = $this->parent->createChild($this->getName() . '-children');
		}

		return $this->child;
	}

	/**
	 * Get the HTML to render the button
	 *
	 * @param   array  &$definition Parameters to be passed
	 *
	 * @return  string
	 *
	 * @since   3.0
	 *
	 * @throws \Exception
	 */
	public function render(&$definition = null)
	{
		$childToolbar = $this->getChildToolbar();
		$hasChildren = count($childToolbar->getItems()) > 0;

		if ($definition === null)
		{
			$options['hasChildren'] = $hasChildren;

			$this->setOption('hasChildren', $hasChildren);

			$action = $this->renderButton($this->options);
		}
		// For B/C
		elseif (is_array($definition))
		{
			$action = $this->fetchButton(...$definition);
		}
		else
		{
			throw new \InvalidArgumentException('Wrong argument: $definition, should be NULL or array.');
		}

		$children = $hasChildren ? $childToolbar->render(['is_child' => true]) : '';

		// Build the HTML Button
		$layout = new FileLayout('joomla.toolbar.base');

		return $layout->render(
			[
				'action' => $action,
				'hasChildren' => $hasChildren,
				'children' => $children,
				'options' => $this->options
			]
		);
	}

	/**
	 * renderButton
	 *
	 * @param array $options
	 *
	 * @return  string
	 */
	protected function renderButton(array &$options): string
	{
		$options['name']  = $this->getName();
		$options['text']  = Text::_($this->getText());
		$options['class'] = $this->fetchIconClass($this->getIcon() ?: $this->getName());
		$options['group'] = $this->getGroup();
		$options['id']    = $this->fetchId();

		if (!empty($options['is_child']))
		{
			$options['tagName'] = 'a';
			$options['btnClass'] = ($options['button_class'] ?? '') . ' dropdown-item';
			$options['attributes']['href'] = '#';
		}
		else
		{
			$options['tagName'] = 'button';
			$options['btnClass'] = ($options['button_class'] ?? '') . ' btn btn-sm btn-outline-primary';
			$options['attributes']['type'] = 'button';
		}

		$this->prepareOptions($options);

		// Prepare custom attributes.
		unset(
			$options['attributes']['id'],
			$options['attributes']['class']
		);

		$options['htmlAttributes'] = ArrayHelper::toString($options['attributes']);

		// Instantiate a new JLayoutFile instance and render the layout
		$layout = new FileLayout($this->layout);

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
	 *
	 * @deprecated  5.0 Use render() instead.
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
	public function setParent(Toolbar $parent): self
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
	 * Method to get property Layout
	 *
	 * @return  string
	 */
	public function getLayout(): string
	{
		return $this->layout;
	}

	/**
	 * Method to set property layout
	 *
	 * @param   string $layout
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function layout(string $layout): self
	{
		$this->layout = $layout;

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
			$fieldName = static::findOptionName(lcfirst(substr($name, 3)));

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
				if (!array_key_exists(0, $args))
				{
					throw new \InvalidArgumentException(
						sprintf(
							'%s::%s() miss first argument.',
							get_called_class(),
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
				get_called_class()
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
	protected static function getAccessors(): array
	{
		return [
			'text',
			'task',
			'icon',
			'group',
			'attributes',
			'buttonClass' => 'button_class'
		];
	}

	/**
	 * Method to get property Name
	 *
	 * @return  string
	 */
	public function getName(): string
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
	public function name(string $name): self
	{
		$this->name = $name;

		return $this;
	}
}
