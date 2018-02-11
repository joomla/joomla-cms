<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Toolbar\Button\BasicButton;
use Joomla\CMS\Toolbar\Button\ConfirmButton;
use Joomla\CMS\Toolbar\Button\CustomButton;
use Joomla\CMS\Toolbar\Button\GroupButton;
use Joomla\CMS\Toolbar\Button\HelpButton;
use Joomla\CMS\Toolbar\Button\LinkButton;
use Joomla\CMS\Toolbar\Button\PopupButton;
use Joomla\CMS\Toolbar\Button\SeparatorButton;
use Joomla\CMS\Toolbar\Button\StandardButton;

/**
 * ToolBar handler
 *
 * @method  StandardButton  standardButton(string $name = '', string $text = '', string $task = '')
 * @method  SeparatorButton  separatorButton(string $name = '', string $text = '', string $task = '')
 * @method  PopupButton  popupButton(string $name = '', string $text = '', string $task = '')
 * @method  LinkButton  linkButton(string $name = '', string $text = '', string $task = '')
 * @method  HelpButton  helpButton(string $name = '', string $text = '', string $task = '')
 * @method  CustomButton  customButton(string $name = '', string $text = '', string $task = '')
 * @method  ConfirmButton  confirmButton(string $name = '', string $text = '', string $task = '')
 * @method  BasicButton  basicButton(string $name = '', string $text = '', string $task = '')
 * @method  GroupButton  groupButton(string $name = '', string $text = '', string $task = '')
 *
 * @since  1.5
 */
class Toolbar
{
	use CoreButtonsTrait;

	/**
	 * Toolbar name
	 *
	 * @var    string
	 */
	protected $_name = [];

	/**
	 * Toolbar array
	 *
	 * @var    array
	 */
	protected $_bar = [];

	/**
	 * Directories, where button types can be stored.
	 *
	 * @var    array
	 */
	protected $_buttonPath = [];

	/**
	 * Stores the singleton instances of various toolbar.
	 *
	 * @var    Toolbar
	 * @since  2.5
	 */
	protected static $instances = array();

	/**
	 * Factory for creating Toolbar API objects
	 *
	 * @var    ToolbarFactoryInterface
	 * @since  4.0.0
	 */
	protected $factory;

	/**
	 * Constructor
	 *
	 * @param   string                   $name     The toolbar name.
	 * @param   ToolbarFactoryInterface  $factory  The toolbar factory.
	 *
	 * @since   1.5
	 */
	public function __construct($name = 'toolbar', ToolbarFactoryInterface $factory = null)
	{
		$this->_name   = $name;

		// At 5.0, require the factory to be injected
		if (!$factory)
		{
			\JLog::add(
				sprintf(
					'As of Joomla! 5.0, a %1$s must be provided to a %2$s object when creating it.',
					ToolbarFactoryInterface::class,
					get_class($this)
				),
				\JLog::WARNING,
				'deprecated'
			);

			$factory = new ContainerAwareToolbarFactory;
			$factory->setContainer(\JFactory::getContainer());
		}

		$this->setFactory($factory);

		// Set base path to find buttons.
		$this->_buttonPath[] = __DIR__ . '/Button';
	}

	/**
	 * Returns the global Toolbar object, only creating it if it doesn't already exist.
	 *
	 * @param   string  $name  The name of the toolbar.
	 *
	 * @return  Toolbar  The Toolbar object.
	 *
	 * @since   1.5
	 */
	public static function getInstance($name = 'toolbar')
	{
		if (empty(self::$instances[$name]))
		{
			self::$instances[$name] = \JFactory::getContainer()->get(ToolbarFactoryInterface::class)->createToolbar($name);
		}

		return self::$instances[$name];
	}

	/**
	 * Set the factory instance
	 *
	 * @param   ToolbarFactoryInterface  $factory  The factory instance
	 *
	 * @return  $this
	 *
	 * @since   4.0.0
	 */
	public function setFactory(ToolbarFactoryInterface $factory): self
	{
		$this->factory = $factory;

		return $this;
	}

	/**
	 * Set a value
	 *
	 * @param ToolbarButton $button
	 *
	 * @return  ToolbarButton
	 *
	 * @since   1.5
	 */
	public function appendButton(...$args)
	{
		if (count($args) === 0)
		{
			trigger_error(sprintf('%s require at least 1 argument.', __METHOD__), E_ERROR);
		}

		$button = $args[0];

		if ($button instanceof ToolbarButton)
		{
			$button->setParent($this);

			$this->_bar[] = $button;

			return $button;
		}

		$this->_bar[] = $args;

		return $button;
	}

	/**
	 * Get the list of toolbar links.
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public function getItems()
	{
		return $this->_bar;
	}

	/**
	 * setItems
	 *
	 * @param array $items
	 *
	 * @return  self
	 */
	public function setItems(array $items): self
	{
		$this->_bar = $items;

		return $this;
	}

	/**
	 * Get the name of the toolbar.
	 *
	 * @return  string
	 *
	 * @since   1.6
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Get a value.
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public function prependButton()
	{
		// Insert button into the front of the toolbar array.
		$btn = func_get_args();
		array_unshift($this->_bar, $btn);

		return true;
	}

	/**
	 * Render a toolbar.
	 *
	 * @param array $options
	 *
	 * @return  string  HTML for the toolbar.
	 *
	 * @throws \Exception
	 * @since   1.5
	 */
	public function render(array $options = [])
	{
		$html = [];

		$isChild = !empty($options['is_child']);

		// Start toolbar div.
		if (!$isChild)
		{
			$layout = new FileLayout('joomla.toolbar.containeropen');

			$html[] = $layout->render(['id' => $this->_name]);
		}

		// Render each button in the toolbar.
		foreach ($this->_bar as $button)
		{
			if ($button instanceof ToolbarButton)
			{
				// Child dropdown only support new syntax
				$button->setOption('is_child', (bool) ($options['is_child'] ?? false));

				$html[] = $button->render();
			}
			// B/C
			else
			{
				$html[] = $this->renderButton($button);
			}
		}

		// End toolbar div.
		if (!$isChild)
		{
			$layout = new FileLayout('joomla.toolbar.containerclose');

			$html[] = $layout->render([]);
		}

		return implode('', $html);
	}

	/**
	 * Render a button.
	 *
	 * @param   object &$node A toolbar node.
	 *
	 * @return  string
	 *
	 * @since   1.5
	 * @throws \UnexpectedValueException
	 */
	public function renderButton(&$node)
	{
		// Get the button type.
		$type = $node[0];

		$button = $this->loadButtonType($type);

		// Check for error.
		if ($button === false)
		{
			throw new \UnexpectedValueException(Text::sprintf('JLIB_HTML_BUTTON_NOT_DEFINED', $type));
		}

		$button->setParent($this);

		return $button->render($node);
	}

	/**
	 * Loads a button type.
	 *
	 * @param   string   $type  Button Type
	 * @param   boolean  $new   False by default
	 *
	 * @return  false|ToolbarButton
	 *
	 * @since   1.5
	 */
	public function loadButtonType($type, $new = false)
	{
		if (!class_exists('Joomla\\CMS\\Toolbar\\ToolbarButton'))
		{
			\JLog::add(\JText::_('JLIB_HTML_BUTTON_BASE_CLASS'), \JLog::WARNING, 'jerror');

			return false;
		}

		// For B/C, catch the exceptions thrown by the factory
		try
		{
			return $this->factory->createButton($this, $type);
		}
		catch (\InvalidArgumentException $e)
		{
			\JLog::add($e->getMessage(), \JLog::WARNING, 'jerror');

			return false;
		}
	}

	/**
	 * Add a directory where Toolbar should search for button types in LIFO order.
	 *
	 * You may either pass a string or an array of directories.
	 *
	 * Toolbar will be searching for an element type in the same order you
	 * added them. If the parameter type cannot be found in the custom folders,
	 * it will look in libraries/joomla/html/toolbar/button.
	 *
	 * @param   mixed  $path  Directory or directories to search.
	 *
	 * @return  void
	 *
	 * @since       1.5
	 * @deprecated  5.0  ToolbarButton classes should be autoloaded
	 */
	public function addButtonPath($path)
	{
		\JLog::add(
			sprintf(
				'Registering lookup paths for toolbar buttons is deprecated and will be removed in Joomla 5.0.'
				. ' %1$s objects should be autoloaded or a custom %2$s implementation supporting path lookups provided.',
				ToolbarButton::class,
				ToolbarFactoryInterface::class
			),
			\JLog::WARNING,
			'deprecated'
		);

		// Loop through the path directories.
		foreach ((array) $path as $dir)
		{
			// No surrounding spaces allowed!
			$dir = trim($dir);

			// Add trailing separators as needed.
			if (substr($dir, -1) !== DIRECTORY_SEPARATOR)
			{
				// Directory
				$dir .= DIRECTORY_SEPARATOR;
			}

			// Add to the top of the search dirs.
			array_unshift($this->_buttonPath, $dir);
		}
	}

	/**
	 * Get the lookup paths for button objects
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 * @deprecated  5.0  ToolbarButton classes should be autoloaded
	 */
	public function getButtonPath(): array
	{
		\JLog::add(
			sprintf(
				'Lookup paths for %s objects is deprecated and will be removed in Joomla 5.0.',
				ToolbarButton::class
			),
			\JLog::WARNING,
			'deprecated'
		);

		return $this->_buttonPath;
	}

	/**
	 * createChild
	 *
	 * @param string $name
	 *
	 * @return  static
	 */
	public function createChild($name): self
	{
		return new static($name, $this->factory);
	}

	/**
	 * __call
	 *
	 * @param string $name
	 * @param array  $args
	 *
	 * @return  ToolbarButton
	 * @throws \Exception
	 */
	public function __call($name, $args)
	{
		if (strtolower(substr($name, -6)) === 'button')
		{
			$type = substr($name, 0, -6);

			$button = $this->loadButtonType($type);

			if ($button === false)
			{
				throw new \UnexpectedValueException(sprintf('Button type: %s not found.', $type));
			}

			$button->name($args[0] ?? '')
				->text($args[1] ?? '');

			return $this->appendButton($button);
		}

		throw new \BadMethodCallException(
			sprintf(
				'Method %s() not found in class: %s',
				$name,
				get_called_class()
			)
		);
	}
}
